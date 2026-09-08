<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\InscriptionDTO;
use App\DTO\LoginDTO;
use App\Model\Utilisateur;
use App\Service\AuthService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemoryUtilisateurRepository;

class AuthServiceTest extends TestCase
{
    private InMemoryUtilisateurRepository $repo;
    private AuthService $authService;

    protected function setUp(): void
    {
        $this->repo = new InMemoryUtilisateurRepository();
        $this->authService = new AuthService($this->repo);

        // Réinitialiser la session pour les tests
        $_SESSION = [];
    }

    public function testAuthentificationReussie(): void
    {
        $user = new Utilisateur([
            'nom'      => 'Awa Diop',
            'email'    => 'awa@univ.sn',
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
        ]);
        $this->repo->save($user);

        $dto = new LoginDTO(email: 'awa@univ.sn', password: 'secret123');
        $result = $this->authService->tentativeConnexion($dto);

        $this->assertNotNull($result);
        $this->assertSame('Awa Diop', $result->nom);
        $this->assertSame('awa@univ.sn', $result->email);
    }

    public function testAuthentificationEchoueSiUtilisateurInexistant(): void
    {
        $dto = new LoginDTO(email: 'inconnu@univ.sn', password: 'secret123');
        $result = $this->authService->tentativeConnexion($dto);

        $this->assertNull($result);
    }

    public function testAuthentificationEchoueSiMotDePasseIncorrect(): void
    {
        $user = new Utilisateur([
            'nom'      => 'Awa Diop',
            'email'    => 'awa@univ.sn',
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
        ]);
        $this->repo->save($user);

        $dto = new LoginDTO(email: 'awa@univ.sn', password: 'mauvais_mdp');
        $result = $this->authService->tentativeConnexion($dto);

        $this->assertNull($result);
    }

    public function testConnexionEtDeconnexionSession(): void
    {
        $user = new Utilisateur([
            'nom'      => 'Test User',
            'email'    => 'test@univ.sn',
            'password' => password_hash('secret123', PASSWORD_BCRYPT),
        ]);
        $this->repo->save($user);

        $this->assertFalse($this->authService->estConnecte());

        $this->authService->connecter($user);
        $this->assertTrue($this->authService->estConnecte());
        $this->assertSame($user->id, $_SESSION['user']['id']);
        $this->assertSame('Test User', $_SESSION['user']['nom']);

        $currentUser = $this->authService->getUtilisateurConnecte();
        $this->assertNotNull($currentUser);
        $this->assertSame('Test User', $currentUser->nom);

        $this->authService->deconnecter();
        $this->assertFalse($this->authService->estConnecte());
        $this->assertEmpty($_SESSION['user'] ?? null);
    }

    public function testInscriptionNouvelUtilisateur(): void
    {
        $dto = new InscriptionDTO(
            nom: 'Moussa Faye',
            email: 'moussa@univ.sn',
            password: 'motdepassefort'
        );

        $user = $this->authService->inscrire($dto);

        $this->assertNotNull($user->id);
        $this->assertSame('Moussa Faye', $user->nom);
        $this->assertSame('moussa@univ.sn', $user->email);
        $this->assertTrue($user->verifierMotDePasse('motdepassefort'));
    }

    public function testInscriptionEchoueSiEmailExisteDeja(): void
    {
        $existing = new Utilisateur([
            'nom'      => 'Moussa Faye',
            'email'    => 'moussa@univ.sn',
            'password' => password_hash('motdepassefort', PASSWORD_BCRYPT),
        ]);
        $this->repo->save($existing);

        $dto = new InscriptionDTO(
            nom: 'Autre Nom',
            email: 'moussa@univ.sn',
            password: 'autre_mot_de_passe'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Cette adresse email est déjà utilisée.");

        $this->authService->inscrire($dto);
    }
}
