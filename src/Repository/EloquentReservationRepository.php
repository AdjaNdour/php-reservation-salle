<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Reservation;
use DateTimeInterface;

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

    public function save(Reservation $reservation): Reservation
    {
        $reservation->save();
        return $reservation;
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
    public function cancel(int $id): bool
    {
        $reservation = $this->findById($id);
        if ($reservation === null) {
            return false;
        }

        $reservation->statut = Reservation::STATUT_ANNULEE;
        return $reservation->save();
    }
}
