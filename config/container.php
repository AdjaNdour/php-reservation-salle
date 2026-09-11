<?php

declare(strict_types=1);

use App\Application;
use App\Repository\EloquentReservationRepository;
use App\Repository\EloquentSalleRepository;
use App\Repository\EloquentUtilisateurRepository;
use App\Repository\Interface\IReservationRepository;
use App\Repository\Interface\ISalleRepository;
use App\Repository\Interface\IUtilisateurRepository;
use App\Repository\Interface\ReservationRepositoryInterface;
use App\Repository\Interface\SalleRepositoryInterface;
use App\Repository\Interface\UtilisateurRepositoryInterface;
use App\Service\AnnulerReservationService;
use App\Service\AuthService;
use App\Service\CreerReservationService;
use App\Service\DashboardService;
use App\Service\Interface\IAnnulerReservationService;
use App\Service\Interface\IAuthService;
use App\Service\Interface\ICreerReservationService;
use App\Service\Interface\IDashboardService;
use App\Service\Interface\IReservationService;
use App\Service\Interface\ISalleService;
use App\Service\ReservationService;
use App\Service\SalleService;
use App\Validation\InscriptionValidator;
use App\Validation\Interface\IInscriptionValidator;
use App\Validation\Interface\ILoginValidator;
use App\Validation\Interface\IReservationValidator;
use App\Validation\Interface\ISalleValidator;
use App\Validation\LoginValidator;
use App\Validation\ReservationValidator;
use App\Validation\SalleValidator;
use App\View\FormatInterface;
use App\View\HtmlFormat;
use App\View\JsonFormat;
use App\View\ViewRenderer;
use FastRoute\Dispatcher;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Psr\Container\ContainerInterface;

use function DI\autowire;
use function DI\factory;

return [

    CapsuleManager::class => factory(static function (): CapsuleManager {
        static $capsule = null;

        if ($capsule !== null) {
            return $capsule;
        }

        $config = require __DIR__ . '/database.php';

        $capsule = new CapsuleManager();
        $capsule->addConnection($config);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        try {
            $capsule->getConnection()->getPdo();
        } catch (\Throwable $e) {
            $driver = $config['driver'] ?? 'mysql';
            throw new \RuntimeException(
                sprintf("Erreur de connexion à la base de données (%s) : %s", $driver, $e->getMessage()),
                (int) $e->getCode(),
                $e
            );
        }

        return $capsule;
    }),

    ISalleRepository::class => autowire(EloquentSalleRepository::class),
    IReservationRepository::class => autowire(EloquentReservationRepository::class),
    IUtilisateurRepository::class => autowire(EloquentUtilisateurRepository::class),

    ISalleValidator::class => autowire(SalleValidator::class),
    IReservationValidator::class => autowire(ReservationValidator::class),
    ILoginValidator::class => autowire(LoginValidator::class),
    IInscriptionValidator::class => autowire(InscriptionValidator::class),

    ICreerReservationService::class => autowire(CreerReservationService::class),
    IAnnulerReservationService::class => autowire(AnnulerReservationService::class),

    ISalleService::class => autowire(SalleService::class),
    IReservationService::class => autowire(ReservationService::class),
    IAuthService::class => autowire(AuthService::class),
    IDashboardService::class => autowire(DashboardService::class),
   
    HtmlFormat::class => factory(static function (): HtmlFormat {
        return new HtmlFormat(dirname(__DIR__) . '/templates');
    }),

    JsonFormat::class => autowire(JsonFormat::class),

    FormatInterface::class => factory(static function (ContainerInterface $c): FormatInterface {
        $config = require __DIR__ . '/view.php';
        return strtolower((string) ($config['format'] ?? 'html')) === 'json' ? $c->get(JsonFormat::class) : $c->get(HtmlFormat::class);
    }),

    ViewRenderer::class => autowire(ViewRenderer::class),

    Dispatcher::class => factory(static function (): Dispatcher {
        $routesCallable = require dirname(__DIR__) . '/routes/web.php';
        return \FastRoute\simpleDispatcher($routesCallable);
    }),

    \App\Session\SessionManager::class => autowire(\App\Session\SessionManager::class),
    \App\Controller\Middleware\AuthMiddleware::class => autowire(\App\Controller\Middleware\AuthMiddleware::class),
    \App\Controller\Middleware\AdminMiddleware::class => autowire(\App\Controller\Middleware\AdminMiddleware::class),

    Application::class => autowire(Application::class),
];
