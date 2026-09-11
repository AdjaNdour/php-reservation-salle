<?php

declare(strict_types=1);

namespace App\Controller\Middleware;

use App\Controller\Middleware\Interface\IMiddleware;
use App\Service\Interface\IAuthService;
use App\Session\SessionManager;

class AuthMiddleware implements IMiddleware
{
    public function __construct(
        private IAuthService $authService,
        private ?SessionManager $sessionManager = null
    ) {
    }

    public function handle(): bool
    {
        if (!$this->authService->estConnecte()) {
            if ($this->sessionManager !== null) {
                $this->sessionManager->saveData('flash_error', "Veuillez vous connecter pour accéder à l'application.");
            } else {
                if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                    session_start();
                }
                $_SESSION['flash_error'] = "Veuillez vous connecter pour accéder à l'application.";
            }

            header('Location: /login');
            if (!defined('PHPUNIT_RUNNING')) {
                exit;
            }
            return false;
        }

        return true;
    }
}
