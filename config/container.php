<?php

declare(strict_types=1);

use App\Application;
use App\Controller\AuthController;
use App\Controller\DashboardController;
use App\Controller\ReservationController;
use App\Controller\SalleController;
use App\DTO\CreerReservationDTOBuilder;
use App\DTO\CreerSalleDTOBuilder;
use App\Repository\EloquentReservationRepository;
use App\Repository\EloquentSalleRepository;
use App\Repository\EloquentUtilisateurRepository;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SalleRepositoryInterface;
use App\Repository\UtilisateurRepositoryInterface;
use App\Service\AnnulerReservationService;
use App\Service\AuthService;
use App\Service\CreerReservationService;
use App\Service\DashboardService;
use App\Service\InterfaceAuthService;
use App\Service\InterfaceDashboardService;
use App\Service\InterfaceReservationService;
use App\Service\InterfaceSalleService;
use App\Service\ReservationService;
use App\Service\SalleService;
use App\Validation\InscriptionValidator;
use App\Validation\LoginValidator;
use App\Validation\ReservationValidator;
use App\Validation\SalleValidator;
use App\View\ViewRenderer;
use FastRoute\Dispatcher;
use Illuminate\Database\Capsule\Manager as CapsuleManager;

use function DI\autowire;
use function DI\factory;

return [

    CapsuleManager::class => factory(static function (): CapsuleManager {
        $capsule = require __DIR__ . '/database.php';
        return $capsule;
    }),

    SalleRepositoryInterface::class => autowire(EloquentSalleRepository::class),
    ReservationRepositoryInterface::class => autowire(EloquentReservationRepository::class),
    UtilisateurRepositoryInterface::class => autowire(EloquentUtilisateurRepository::class),

    SalleValidator::class => autowire(SalleValidator::class),
    ReservationValidator::class => autowire(ReservationValidator::class),
    LoginValidator::class => autowire(LoginValidator::class),
    InscriptionValidator::class => autowire(InscriptionValidator::class),

    CreerReservationService::class => autowire(CreerReservationService::class),
    AnnulerReservationService::class => autowire(AnnulerReservationService::class),
    InterfaceSalleService::class => autowire(SalleService::class),
    InterfaceReservationService::class => autowire(ReservationService::class),
    InterfaceAuthService::class => autowire(AuthService::class),
    InterfaceDashboardService::class => autowire(DashboardService::class),

    ViewRenderer::class => autowire(ViewRenderer::class),

    SalleController::class => autowire(SalleController::class),
    ReservationController::class => autowire(ReservationController::class),
    AuthController::class => autowire(AuthController::class),
    DashboardController::class => autowire(DashboardController::class),

    CreerReservationDTOBuilder::class => autowire(CreerReservationDTOBuilder::class),
    CreerSalleDTOBuilder::class => autowire(CreerSalleDTOBuilder::class),

    Dispatcher::class => factory(static function (): Dispatcher {
        $routesCallable = require dirname(__DIR__) . '/routes/web.php';
        return \FastRoute\simpleDispatcher($routesCallable);
    }),

    Application::class => autowire(Application::class),
];
