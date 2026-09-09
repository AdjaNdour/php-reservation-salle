<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\InterfaceDashboardService;
use App\View\ViewRenderer;

final class DashboardController
{
    public function __construct(
        private InterfaceDashboardService $dashboardService,
        private ViewRenderer $view
    ) {}

    public function index(): void
    {
        $stats = $this->dashboardService->getGlobalStatistics();
        $topSalles = $this->dashboardService->getMostUsedSalles(10);

        $this->view->render('dashboard/index', [
            'titre'     => 'Tableau de bord - Salles les plus utilisées',
            'stats'     => $stats,
            'topSalles' => $topSalles,
        ]);
    }
}
