<?php

declare(strict_types=1);

namespace Tests\Unit\Doubles;

use App\Model\Reservation;
use App\Pagination\Paginator;
use App\Repository\ReservationRepositoryInterface;
use DateTimeImmutable;
use DateTimeInterface;

class InMemoryReservationRepository implements ReservationRepositoryInterface
{
    private array $reservations = [];
    private int $autoIncrement = 1;

    public function findAll(?int $salleId = null): array
    {
        if ($salleId === null) {
            return array_values($this->reservations);
        }

        return array_values(array_filter(
            $this->reservations,
            static fn (Reservation $r) => $r->salle_id === $salleId
        ));
    }

    public function findById(int $id): ?Reservation
    {
        return $this->reservations[$id] ?? null;
    }

    public function findConflictingReservation(int $salleId, DateTimeInterface $debut, DateTimeInterface $fin): ?Reservation
    {
        foreach ($this->reservations as $r) {
            if ($r->salle_id === $salleId && $r->statut === Reservation::STATUT_CONFIRMEE) {
                $rDebut = $r->date_debut instanceof DateTimeInterface
                    ? $r->date_debut
                    : new DateTimeImmutable((string) $r->date_debut);

                $rFin = $r->date_fin instanceof DateTimeInterface
                    ? $r->date_fin
                    : new DateTimeImmutable((string) $r->date_fin);

                if ($debut < $rFin && $fin > $rDebut) {
                    return $r;
                }
            }
        }

        return null;
    }

    public function findByCriteria(array $criteria = []): array
    {
        $filtered = array_filter($this->reservations, function (Reservation $r) use ($criteria) {
            if (!empty($criteria['salle_id']) && (int) $r->salle_id !== (int) $criteria['salle_id']) {
                return false;
            }
            if (!empty($criteria['statut']) && $r->statut !== $criteria['statut']) {
                return false;
            }
            if (!empty($criteria['responsable']) && stripos($r->responsable, $criteria['responsable']) === false) {
                return false;
            }
            if (!empty($criteria['email']) && stripos($r->email, $criteria['email']) === false) {
                return false;
            }
            return true;
        });

        return array_values($filtered);
    }

    public function paginate(int $page = 1, int $perPage = 10, array $criteria = []): Paginator
    {
        $filtered = $this->findByCriteria($criteria);
        $totalItems = count($filtered);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filtered, $offset, $perPage);

        return new Paginator($items, $totalItems, $page, $perPage);
    }

    public function countTotal(): int
    {
        return count($this->reservations);
    }

    public function countByStatus(string $status): int
    {
        return count(array_filter($this->reservations, fn (Reservation $r) => $r->statut === $status));
    }

    public function getMostUsedSalles(int $limit = 5): array
    {
        $counts = [];
        foreach ($this->reservations as $r) {
            $sid = (int) $r->salle_id;
            if (!isset($counts[$sid])) {
                $counts[$sid] = [
                    'salle_id'                => $sid,
                    'nom'                     => 'Salle #' . $sid,
                    'batiment'                => 'Bâtiment',
                    'type'                    => 'cours',
                    'active'                  => true,
                    'capacite'                => 30,
                    'total_reservations'      => 0,
                    'reservations_confirmees' => 0,
                    'reservations_annulees'   => 0,
                    'total_heures'            => 2.0,
                ];
            }
            $counts[$sid]['total_reservations']++;
            if ($r->statut === Reservation::STATUT_CONFIRMEE) {
                $counts[$sid]['reservations_confirmees']++;
            } else {
                $counts[$sid]['reservations_annulees']++;
            }
        }

        usort($counts, fn ($a, $b) => $b['reservations_confirmees'] <=> $a['reservations_confirmees']);
        return array_slice(array_values($counts), 0, $limit);
    }

    public function save(Reservation $reservation): Reservation
    {
        if ($reservation->id === null || $reservation->id === 0) {
            $reservation->id = $this->autoIncrement++;
        }
        $this->reservations[$reservation->id] = $reservation;
        return $reservation;
    }

    public function cancel(int $id): bool
    {
        if (!isset($this->reservations[$id])) {
            return false;
        }

        $this->reservations[$id]->statut = Reservation::STATUT_ANNULEE;
        return true;
    }
}
