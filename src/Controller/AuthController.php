<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\InscriptionDTO;
use App\DTO\LoginDTO;
use App\Service\Interface\IAuthService;
use App\Validation\Interface\IInscriptionValidator;
use App\Validation\Interface\ILoginValidator;
use App\View\ViewRenderer;
use InvalidArgumentException;

class AuthController extends Controller
{
    public function __construct(
        private IAuthService $authService,
        private ILoginValidator $loginValidator,
        private IInscriptionValidator $inscriptionValidator,
        ViewRenderer $view
    ) {
        parent::__construct($view);
    }

    public function showLoginForm(): void
    {
        if ($this->authService->estConnecte()) {
            $_SESSION['flash_success'] = "Vous êtes déjà connecté.";
            header('Location: /salles');
            if (!defined('PHPUNIT_RUNNING')) { exit; }
            return;
        }

        $this->render('auth/login', [
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
            $this->render('auth/login', [
                'titre'  => 'Connexion - UnivRésa',
                'errors' => $validationResult->errors(),
                'data'   => ['email' => $data['email'] ?? ''],
            ], 422);
            return;
        }

        $dto = LoginDTO::fromArray($validationResult->validatedData());
        $utilisateur = $this->authService->getByEmail($dto);

        if ($utilisateur === null) {
            $this->render('auth/login', [
                'titre'  => 'Connexion - UnivRésa',
                'errors' => ['general' => 'Adresse email ou mot de passe incorrect.'],
                'data'   => ['email' => $data['email'] ?? ''],
            ], 401);
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

        $this->render('auth/register', [
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
            $this->render('auth/register', [
                'titre'  => 'Inscription - UnivRésa',
                'errors' => $validationResult->errors(),
                'data'   => [
                    'nom'   => $data['nom'] ?? '',
                    'email' => $data['email'] ?? '',
                ],
            ], 422);
            return;
        }

        $dto = InscriptionDTO::fromArray($validationResult->validatedData());

        try {
            $utilisateur = $this->authService->inscrire($dto);
        } catch (InvalidArgumentException $e) {
            $this->render('auth/register', [
                'titre'  => 'Inscription - UnivRésa',
                'errors' => ['email' => $e->getMessage()],
                'data'   => [
                    'nom'   => $data['nom'] ?? '',
                    'email' => $data['email'] ?? '',
                ],
            ], 422);
            return;
        }

        $this->authService->connecter($utilisateur);
        $_SESSION['flash_success'] = "Votre compte a été créé avec succès ! Bienvenue, {$utilisateur->nom}.";

        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }
}
