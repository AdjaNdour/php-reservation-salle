<?php

declare(strict_types=1);

namespace App\Service;

interface InterfaceDashboardService
{
    /**
     * @return array<int, array{salle_id: int, nom: string, batiment: string, type: string, capacite: int, active: bool, total_reservations: int, reservations_confirmees: int, reservations_annulees: int, total_heures: float}>
     */
    public function getMostUsedSalles(int $limit = 5): array;

    /**
     * @return array{total_salles: int, salles_actives: int, salles_inactives: int, total_reservations: int, reservations_confirmees: int, reservations_annulees: int, taux_confirmation: float}
     */
    public function getGlobalStatistics(): array;
}
