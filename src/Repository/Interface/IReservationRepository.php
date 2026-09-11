<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\Reservation;
use DateTimeInterface;
use Illuminate\Pagination\LengthAwarePaginator;

interface IReservationRepository
{
    public function findAll(?int $salleId = null): array;

    public function findById(int $id): ?Reservation;

    public function findConflictingReservation(int $salleId, DateTimeInterface $debut, DateTimeInterface $fin): ?Reservation;

    public function findByCriteria(array $criteria = []): array;

    public function paginate(int $page = 1, int $perPage = 10, array $criteria = []): LengthAwarePaginator;

    public function getMostUsedSalles(int $limit = 5): array;

    public function countTotal(): int;

    public function countByStatus(string $status): int;

    public function save(Reservation $reservation): Reservation;

    public function cancel(int $id): bool;
}
