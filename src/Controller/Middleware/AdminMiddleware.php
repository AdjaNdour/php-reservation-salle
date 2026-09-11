<?php

declare(strict_types=1);

namespace App\Controller\Middleware;

use App\Controller\Middleware\Interface\IMiddleware;
use App\Service\Interface\IAuthService;
use App\View\ViewRenderer;

class AdminMiddleware implements IMiddleware
{
    public function __construct(
        private IAuthService $authService,
        private ?ViewRenderer $view = null
    ) {
    }

    public function handle(): bool
    {
        if (!$this->authService->estAdmin()) {
            if ($this->view !== null) {
                $this->view->render('error/403', [
                    'titre'   => '403 - Accès refusé',
                    'message' => "Cette action est strictement réservée aux administrateurs.",
                ], 403);
            } else {
                http_response_code(403);
                echo "Cette action est strictement réservée aux administrateurs.";
            }

            if (!defined('PHPUNIT_RUNNING')) {
                exit;
            }
            return false;
        }

        return true;
    }
}
