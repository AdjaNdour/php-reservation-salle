<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\DTO\CreerReservationDTO;
use App\Model\Reservation;
use Illuminate\Pagination\LengthAwarePaginator;

interface IReservationService
{
    public function getAll(?int $salleId = null): array;

    public function search(array $criteria = []): array;

    public function getPaginated(int $page = 1, int $perPage = 10, array $criteria = []): LengthAwarePaginator;

    public function getById(int $id): ?Reservation;

    public function save(CreerReservationDTO $dto): Reservation;
}
