<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\Model\Reservation;

interface IAnnulerReservationService
{
    public function executer(int $reservationId): Reservation;
}
