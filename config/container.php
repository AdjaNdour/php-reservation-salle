<?php

declare(strict_types=1);

use App\Application;
use App\Controller\ReservationController;
use App\Controller\SalleController;
use App\DTO\CreerReservationDTOBuilder;
use App\DTO\CreerSalleDTOBuilder;
use App\Repository\EloquentReservationRepository;
use App\Repository\EloquentSalleRepository;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SalleRepositoryInterface;
use App\Service\AnnulerReservationService;
use App\Service\CreerReservationService;
use App\Service\InterfaceReservationService;
use App\Service\InterfaceSalleService;
use App\Service\ReservationService;
use App\Service\SalleService;
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


    SalleValidator::class => autowire(SalleValidator::class),
    ReservationValidator::class => autowire(ReservationValidator::class),

    CreerReservationService::class => autowire(CreerReservationService::class),
    AnnulerReservationService::class => autowire(AnnulerReservationService::class),
    InterfaceSalleService::class => autowire(SalleService::class),
    InterfaceReservationService::class => autowire(ReservationService::class),

    ViewRenderer::class => autowire(ViewRenderer::class),

    SalleController::class => autowire(SalleController::class),
    ReservationController::class => autowire(ReservationController::class),

    CreerReservationDTOBuilder::class => autowire(CreerReservationDTOBuilder::class),
    CreerSalleDTOBuilder::class => autowire(CreerSalleDTOBuilder::class),

    Dispatcher::class => factory(static function (): Dispatcher {
        $routesCallable = require dirname(__DIR__) . '/routes/web.php';
        return \FastRoute\simpleDispatcher($routesCallable);
    }),

    Application::class => autowire(Application::class),
];
