<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\ReservationIntrouvableException;
use App\Model\Reservation;
use App\Repository\Interface\IReservationRepository;
use App\Service\Interface\IAnnulerReservationService;

final class AnnulerReservationService implements IAnnulerReservationService
{
    public function __construct(
        private IReservationRepository $reservations
    ) {
    }

    public function executer(int $reservationId): Reservation
    {
        $reservation = $this->reservations->findById($reservationId);
        if ($reservation === null) {
            throw new ReservationIntrouvableException(
                sprintf("La réservation avec l'identifiant %d est introuvable.", $reservationId)
            );
        }

        if (!$reservation->estAnnulee()) {
            $this->reservations->cancel($reservationId);
            $reservation->statut = Reservation::STATUT_ANNULEE;
        }

        return $reservation;
    }
}
