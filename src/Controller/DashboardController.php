<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Interface\IDashboardService;
use App\Service\Interface\InterfaceDashboardService;
use App\View\ViewRenderer;

class DashboardController extends Controller
{
    public function __construct(
        private IDashboardService $dashboardService,
        ViewRenderer $view
    ) {
        parent::__construct($view);
    }

    public function index(): void
    {
        $stats = $this->dashboardService->getGlobalStatistics();
        $topSalles = $this->dashboardService->getMostUsedSalles(10);

        $this->render('dashboard/index', [
            'titre'     => 'Tableau de bord - Salles les plus utilisées',
            'stats'     => $stats,
            'topSalles' => $topSalles,
        ]);
    }
}
