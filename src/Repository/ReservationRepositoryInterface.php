<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Reservation;
use DateTimeInterface;

interface ReservationRepositoryInterface
{
    public function findAll(?int $salleId = null): array;

    public function findById(int $id): ?Reservation;

    public function findConflictingReservation(int $salleId, DateTimeInterface $debut, DateTimeInterface $fin): ?Reservation;

    public function save(Reservation $reservation): Reservation;

    public function cancel(int $id): bool;
}
