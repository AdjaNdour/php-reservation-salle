<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Reservation;
use App\Model\Salle;
use App\Pagination\Paginator;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentReservationRepository implements ReservationRepositoryInterface
{
    public function findAll(?int $salleId = null): array
    {
        $query = Reservation::query()->with('salle')->orderBy('date_debut', 'desc');

        if ($salleId !== null && $salleId > 0) {
            $query->where('salle_id', $salleId);
        }

        return $query->get()->all();
    }

    public function findById(int $id): ?Reservation
    {
        return Reservation::query()->with('salle')->find($id);
    }

    public function findConflictingReservation(int $salleId, DateTimeInterface $debut, DateTimeInterface $fin): ?Reservation
    {
        return Reservation::query()
            ->where('salle_id', $salleId)
            ->where('statut', Reservation::STATUT_CONFIRMEE)
            ->where('date_debut', '<', $fin->format('Y-m-d H:i:s'))
            ->where('date_fin', '>', $debut->format('Y-m-d H:i:s'))
            ->first();
    }

    public function findByCriteria(array $criteria = []): array
    {
        return $this->buildCriteriaQuery($criteria)
            ->with('salle')
            ->orderBy('date_debut', 'desc')
            ->get()
            ->all();
    }

    public function paginate(int $page = 1, int $perPage = 10, array $criteria = []): Paginator
    {
        $query = $this->buildCriteriaQuery($criteria);
        $totalItems = $query->count();

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $items = $query->with('salle')
            ->orderBy('date_debut', 'desc')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->all();

        return new Paginator($items, $totalItems, $page, $perPage);
    }

    public function countTotal(): int
    {
        return Reservation::query()->count();
    }

    public function countByStatus(string $status): int
    {
        return Reservation::query()->where('statut', $status)->count();
    }

    public function getMostUsedSalles(int $limit = 5): array
    {
        $salles = Salle::query()->with('reservations')->get();
        $stats = [];

        foreach ($salles as $salle) {
            $reservations = $salle->reservations ?? collect();
            $confirmees = $reservations->where('statut', Reservation::STATUT_CONFIRMEE);
            $annulees = $reservations->where('statut', Reservation::STATUT_ANNULEE);

            $totalHeures = 0.0;
            foreach ($confirmees as $res) {
                $debut = is_string($res->date_debut) ? strtotime($res->date_debut) : $res->date_debut->getTimestamp();
                $fin = is_string($res->date_fin) ? strtotime($res->date_fin) : $res->date_fin->getTimestamp();
                if ($fin > $debut) {
                    $totalHeures += ($fin - $debut) / 3600.0;
                }
            }

            $stats[] = [
                'salle_id'                => (int) $salle->id,
                'nom'                     => $salle->nom,
                'batiment'                => $salle->batiment,
                'type'                    => $salle->type,
                'capacite'                => (int) $salle->capacite,
                'active'                  => (bool) $salle->active,
                'total_reservations'      => $reservations->count(),
                'reservations_confirmees' => $confirmees->count(),
                'reservations_annulees'   => $annulees->count(),
                'total_heures'            => round($totalHeures, 1),
            ];
        }

        usort($stats, function ($a, $b) {
            if ($b['reservations_confirmees'] === $a['reservations_confirmees']) {
                return $b['total_heures'] <=> $a['total_heures'];
            }
            return $b['reservations_confirmees'] <=> $a['reservations_confirmees'];
        });

        return array_slice($stats, 0, $limit);
    }

    public function save(Reservation $reservation): Reservation
    {
        $reservation->save();
        return $reservation;
    }

    public function cancel(int $id): bool
    {
        $reservation = $this->findById($id);
        if ($reservation === null) {
            return false;
        }

        $reservation->statut = Reservation::STATUT_ANNULEE;
        return $reservation->save();
    }

    private function buildCriteriaQuery(array $criteria): Builder
    {
        $query = Reservation::query();

        if (!empty($criteria['salle_id']) && is_numeric($criteria['salle_id'])) {
            $query->where('salle_id', (int) $criteria['salle_id']);
        }

        if (!empty($criteria['statut'])) {
            $query->where('statut', trim((string) $criteria['statut']));
        }

        if (!empty($criteria['responsable'])) {
            $query->where('responsable', 'like', '%' . trim((string) $criteria['responsable']) . '%');
        }

        if (!empty($criteria['email'])) {
            $query->where('email', 'like', '%' . trim((string) $criteria['email']) . '%');
        }

        if (!empty($criteria['date_debut'])) {
            $query->where('date_debut', '>=', trim((string) $criteria['date_debut']));
        }

        if (!empty($criteria['date_fin'])) {
            $query->where('date_fin', '<=', trim((string) $criteria['date_fin']));
        }

        return $query;
    }
}
