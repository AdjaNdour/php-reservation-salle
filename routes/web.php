<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\ReservationController;
use App\Controller\SalleController;
use FastRoute\RouteCollector;

return static function (RouteCollector $r): void {
    
    $r->addRoute('GET', '/', [SalleController::class, 'index']);

    // Authentification
    $r->addRoute('GET', '/login', [AuthController::class, 'showLoginForm']);
    $r->addRoute('POST', '/login', [AuthController::class, 'login']);
    $r->addRoute('GET', '/logout', [AuthController::class, 'logout']);
    $r->addRoute('POST', '/logout', [AuthController::class, 'logout']);
    $r->addRoute('GET', '/register', [AuthController::class, 'showRegisterForm']);
    $r->addRoute('POST', '/register', [AuthController::class, 'register']);

    $r->addRoute('GET', '/salles', [SalleController::class, 'index']);
    $r->addRoute('GET', '/salles/create', [SalleController::class, 'create']);
    $r->addRoute('POST', '/salles', [SalleController::class, 'store']);
    $r->addRoute('GET', '/salles/{id:\d+}', [SalleController::class, 'show']);
    $r->addRoute('GET', '/salles/{id:\d+}/edit', [SalleController::class, 'edit']);
    $r->addRoute('POST', '/salles/{id:\d+}/edit', [SalleController::class, 'update']);
    $r->addRoute('POST', '/salles/{id:\d+}/toggle', [SalleController::class, 'toggle']);
    $r->addRoute('POST', '/salles/{id:\d+}/delete', [SalleController::class, 'delete']);
    $r->addRoute('DELETE', '/salles/{id:\d+}', [SalleController::class, 'delete']);

    $r->addRoute('GET', '/reservations', [ReservationController::class, 'index']);
    $r->addRoute('GET', '/reservations/create', [ReservationController::class, 'create']);
    $r->addRoute('POST', '/reservations', [ReservationController::class, 'store']);
    $r->addRoute('GET', '/reservations/{id:\d+}', [ReservationController::class, 'show']);
    $r->addRoute('POST', '/reservations/{id:\d+}/cancel', [ReservationController::class, 'cancel']);
};
