<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreerReservationDTO;
use App\Model\Reservation;
use App\Pagination\Paginator;

interface InterfaceReservationService
{
    public function getAll(?int $salleId = null): array;

    public function search(array $criteria = []): array;

    /**
     * @return Paginator<Reservation>
     */
    public function getPaginated(int $page = 1, int $perPage = 10, array $criteria = []): Paginator;

    public function getById(int $id): ?Reservation;

    public function save(CreerReservationDTO $dto): Reservation;
}
