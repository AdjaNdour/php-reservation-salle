<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controller\AuthController;
use App\Model\Utilisateur;
use App\Service\AuthService;
use App\Validation\InscriptionValidator;
use App\Validation\LoginValidator;
use App\View\ViewRenderer;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemoryUtilisateurRepository;

class AuthControllerTest extends TestCase
{
    private InMemoryUtilisateurRepository $repo;
    private AuthService $authService;
    private LoginValidator $loginValidator;
    private InscriptionValidator $inscriptionValidator;
    private ViewRenderer $viewMock;
    private AuthController $controller;

    protected function setUp(): void
    {
        $_SESSION = [];

        $this->repo = new InMemoryUtilisateurRepository();
        $this->authService = new AuthService($this->repo);
        $this->loginValidator = new LoginValidator();
        $this->inscriptionValidator = new InscriptionValidator();

        $this->viewMock = $this->createMock(ViewRenderer::class);

        $this->controller = new AuthController(
            $this->authService,
            $this->loginValidator,
            $this->inscriptionValidator,
            $this->viewMock
        );
    }

    public function testShowLoginFormAfficheLaVueQuandNonConnecte(): void
    {
        $this->viewMock
            ->expects($this->once())
            ->method('render')
            ->with('auth/login', $this->callback(function (array $data) {
                return isset($data['titre']) && empty($data['errors']);
            }));

        $this->controller->showLoginForm();
    }

    public function testLoginAvecChampsInvalidesReafficheFormulaireAvecErreurs(): void
    {
        $_POST = [
            'email'    => 'bad-email',
            'password' => '',
        ];

        $this->viewMock
            ->expects($this->once())
            ->method('render')
            ->with('auth/login', $this->callback(function (array $data) {
                return !empty($data['errors']['email']) && !empty($data['errors']['password']);
            }));

        $this->controller->login();
    }

    public function testLoginAvecMauvaisIdentifiantsReafficheErreurGenerale(): void
    {
        $user = new Utilisateur([
            'nom'      => 'Alice',
            'email'    => 'alice@univ.sn',
            'password' => password_hash('bonpass123', PASSWORD_BCRYPT),
        ]);
        $this->repo->save($user);

        $_POST = [
            'email'    => 'alice@univ.sn',
            'password' => 'fauxpass123',
        ];

        $this->viewMock
            ->expects($this->once())
            ->method('render')
            ->with('auth/login', $this->callback(function (array $data) {
                return isset($data['errors']['general']);
            }));

        $this->controller->login();
    }

    public function testShowRegisterFormAfficheLaVue(): void
    {
        $this->viewMock
            ->expects($this->once())
            ->method('render')
            ->with('auth/register', $this->callback(function (array $data) {
                return isset($data['titre']) && empty($data['errors']);
            }));

        $this->controller->showRegisterForm();
    }
}
