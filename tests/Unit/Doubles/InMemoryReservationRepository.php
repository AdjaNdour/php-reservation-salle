<?php

declare(strict_types=1);

namespace Tests\Unit\Doubles;

use App\Model\Reservation;
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

                // Chevauchement : nouveauDebut < existantFin ET nouvelleFin > existantDebut
                if ($debut < $rFin && $fin > $rDebut) {
                    return $r;
                }
            }
        }

        return null;
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
