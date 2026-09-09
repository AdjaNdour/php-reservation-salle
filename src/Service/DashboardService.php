<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Reservation;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SalleRepositoryInterface;

final class DashboardService implements InterfaceDashboardService
{
    public function __construct(
        private SalleRepositoryInterface $salleRepository,
        private ReservationRepositoryInterface $reservationRepository
    ) {}

    public function getMostUsedSalles(int $limit = 5): array
    {
        return $this->reservationRepository->getMostUsedSalles($limit);
    }

    public function getGlobalStatistics(): array
    {
        $salles = $this->salleRepository->findAll();
        $totalSalles = count($salles);
        $sallesActives = count(array_filter($salles, fn ($s) => (bool) $s->active));
        $sallesInactives = $totalSalles - $sallesActives;

        $totalReservations = $this->reservationRepository->countTotal();
        $confirmees = $this->reservationRepository->countByStatus(Reservation::STATUT_CONFIRMEE);
        $annulees = $this->reservationRepository->countByStatus(Reservation::STATUT_ANNULEE);

        $tauxConfirmation = $totalReservations > 0
            ? round(($confirmees / $totalReservations) * 100, 1)
            : 0.0;

        return [
            'total_salles'            => $totalSalles,
            'salles_actives'          => $sallesActives,
            'salles_inactives'        => $sallesInactives,
            'total_reservations'      => $totalReservations,
            'reservations_confirmees' => $confirmees,
            'reservations_annulees'   => $annulees,
            'taux_confirmation'       => $tauxConfirmation,
        ];
    }
}
