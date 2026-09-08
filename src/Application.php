<?php

declare(strict_types=1);

namespace App;

use App\Service\InterfaceAuthService;
use App\View\ViewRenderer;
use FastRoute\Dispatcher;
use Psr\Container\ContainerInterface;

class Application
{
    public function __construct(
        private Dispatcher $dispatcher,
        private ContainerInterface $container,
        private ViewRenderer $view
    ) {
    }

    public function run(?string $httpMethod = null, ?string $uri = null): void
    {
        $httpMethod = $httpMethod ?? ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = $uri ?? ($_SERVER['REQUEST_URI'] ?? '/');

        // Nettoyage de la query string (ex: /reservations?salle_id=2 -> /reservations)
        if (false !== ($pos = strpos($uri, '?'))) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        // Contrôle d'accès : seule la page d'authentification est accessible hors connexion
        /** @var InterfaceAuthService $authService */
        $authService = $this->container->get(InterfaceAuthService::class);
        $publicRoutes = ['/login', '/register'];

        if (!$authService->estConnecte()) {
            if (!in_array($uri, $publicRoutes, true)) {
                if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                    session_start();
                }
                $_SESSION['flash_error'] = "Veuillez vous connecter pour accéder à l'application.";
                header('Location: /login');
                exit;
            }
        } elseif ($uri === '/') {
            header('Location: /salles');
            exit;
        }

        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                $this->view->render('error/404', [
                    'titre'   => '404 - Page introuvable',
                    'message' => "La page demandée '{$uri}' n'existe pas.",
                ]);
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                http_response_code(405);
                header('Allow: ' . implode(', ', $allowedMethods));
                $this->view->render('error/405', [
                    'titre'          => '405 - Méthode non autorisée',
                    'message'        => "La méthode {$httpMethod} n'est pas autorisée pour cette ressource.",
                    'allowedMethods' => $allowedMethods,
                ]);
                break;

            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];

                [$controllerClass, $action] = $handler;

                // Résolution du contrôleur via le conteneur d'injection de dépendances
                $controller = $this->container->get($controllerClass);

                // Typage des paramètres numériques d'URL
                $params = array_map(static function ($val) {
                    return is_numeric($val) ? (int) $val : $val;
                }, $vars);

                $controller->$action(...$params);
                break;
        }
    }
}
