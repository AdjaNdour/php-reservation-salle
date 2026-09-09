<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\InscriptionDTO;
use App\DTO\LoginDTO;
use App\Service\InterfaceAuthService;
use App\Validation\InscriptionValidator;
use App\Validation\LoginValidator;
use App\View\ViewRenderer;
use InvalidArgumentException;

class AuthController
{
    public function __construct(
        private InterfaceAuthService $authService,
        private LoginValidator $loginValidator,
        private InscriptionValidator $inscriptionValidator,
        private ViewRenderer $view
    ) {}

    public function showLoginForm(): void
    {
        if ($this->authService->estConnecte()) {
            $_SESSION['flash_success'] = "Vous êtes déjà connecté.";
            header('Location: /salles');
            if (!defined('PHPUNIT_RUNNING')) { exit; }
            return;
        }

        $this->view->render('auth/login', [
            'titre'  => 'Connexion - UnivRésa',
            'errors' => [],
            'data'   => ['email' => ''],
        ]);
    }

    public function login(): void
    {
        $data = $_POST;
        $validationResult = $this->loginValidator->validate($data);

        if (!$validationResult->isValid()) {
            http_response_code(422);
            $this->view->render('auth/login', [
                'titre'  => 'Connexion - UnivRésa',
                'errors' => $validationResult->errors(),
                'data'   => ['email' => $data['email'] ?? ''],
            ]);
            return;
        }

        $dto = LoginDTO::fromArray($validationResult->validatedData());
        $utilisateur = $this->authService->tentativeConnexion($dto);

        if ($utilisateur === null) {
            http_response_code(401);
            $this->view->render('auth/login', [
                'titre'  => 'Connexion - UnivRésa',
                'errors' => ['general' => 'Adresse email ou mot de passe incorrect.'],
                'data'   => ['email' => $data['email'] ?? ''],
            ]);
            return;
        }

        $this->authService->connecter($utilisateur);
        $_SESSION['flash_success'] = "Bienvenue, {$utilisateur->nom} ! Vous êtes connecté.";

        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }

    public function logout(): void
    {
        $this->authService->deconnecter();
        $_SESSION['flash_success'] = "Vous avez été déconnecté avec succès.";

        header('Location: /login');
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }

    public function showRegisterForm(): void
    {
        if ($this->authService->estConnecte()) {
            header('Location: /salles');
            if (!defined('PHPUNIT_RUNNING')) { exit; }
            return;
        }

        $this->view->render('auth/register', [
            'titre'  => 'Inscription - UnivRésa',
            'errors' => [],
            'data'   => ['nom' => '', 'email' => ''],
        ]);
    }

    public function register(): void
    {
        $data = $_POST;
        $validationResult = $this->inscriptionValidator->validate($data);

        if (!$validationResult->isValid()) {
            http_response_code(422);
            $this->view->render('auth/register', [
                'titre'  => 'Inscription - UnivRésa',
                'errors' => $validationResult->errors(),
                'data'   => [
                    'nom'   => $data['nom'] ?? '',
                    'email' => $data['email'] ?? '',
                ],
            ]);
            return;
        }

        $dto = InscriptionDTO::fromArray($validationResult->validatedData());

        try {
            $utilisateur = $this->authService->inscrire($dto);
        } catch (InvalidArgumentException $e) {
            http_response_code(422);
            $this->view->render('auth/register', [
                'titre'  => 'Inscription - UnivRésa',
                'errors' => ['email' => $e->getMessage()],
                'data'   => [
                    'nom'   => $data['nom'] ?? '',
                    'email' => $data['email'] ?? '',
                ],
            ]);
            return;
        }

        $this->authService->connecter($utilisateur);
        $_SESSION['flash_success'] = "Votre compte a été créé avec succès ! Bienvenue, {$utilisateur->nom}.";

        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }
}
