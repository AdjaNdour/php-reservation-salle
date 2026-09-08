<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreerReservationDTO;
use App\Model\Reservation;

interface InterfaceReservationService
{
    public function getAll(?int $salleId = null): array;
    public function getById(int $id): ?Reservation;
    public function save(CreerReservationDTO $dto): Reservation;
}
