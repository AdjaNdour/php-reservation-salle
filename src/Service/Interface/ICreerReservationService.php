<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\DTO\CreerReservationDTO;
use App\Model\Reservation;

interface ICreerReservationService
{
    public function executer(CreerReservationDTO $dto): Reservation;
}
