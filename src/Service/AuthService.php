<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\InscriptionDTO;
use App\DTO\LoginDTO;
use App\Model\Utilisateur;
use App\Repository\UtilisateurRepositoryInterface;
use InvalidArgumentException;

final class AuthService implements InterfaceAuthService
{
    public function __construct(
        private UtilisateurRepositoryInterface $utilisateurRepository
    ) {}

    public function getByEmail(LoginDTO $dto): ?Utilisateur
    {
        $utilisateur = $this->utilisateurRepository->findByEmail($dto->email);
        if ($utilisateur === null) {
            return null;
        }

        if (!$utilisateur->verifierMotDePasse($dto->password)) {
            return null;
        }

        return $utilisateur;
    }

    public function tentativeConnexion(LoginDTO $dto): ?Utilisateur
    {
        return $this->getByEmail($dto);
    }

    public function connecter(Utilisateur $utilisateur): void
    {
        $this->ensureSessionActive();

        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }

        $_SESSION['user'] = [
            'id'    => $utilisateur->id,
            'nom'   => $utilisateur->nom,
            'email' => $utilisateur->email,
            'role'  => $utilisateur->role ?? Utilisateur::ROLE_RESPONSABLE,
        ];
    }

    public function deconnecter(): void
    {
        $this->ensureSessionActive();

        unset($_SESSION['user']);

        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            session_regenerate_id(true);
        }
    }

    public function estConnecte(): bool
    {
        $this->ensureSessionActive();

        return !empty($_SESSION['user']['id']);
    }

    public function getUtilisateurConnecte(): ?Utilisateur
    {
        if (!$this->estConnecte()) {
            return null;
        }

        $id = (int) $_SESSION['user']['id'];
        return $this->utilisateurRepository->findById($id);
    }

    public function estAdmin(): bool
    {
        $this->ensureSessionActive();

        if (!empty($_SESSION['user']['role'])) {
            return $_SESSION['user']['role'] === Utilisateur::ROLE_ADMIN;
        }

        $user = $this->getUtilisateurConnecte();
        return $user?->estAdmin() ?? false;
    }

    public function estResponsable(): bool
    {
        $this->ensureSessionActive();

        if (!empty($_SESSION['user']['role'])) {
            return in_array($_SESSION['user']['role'], [
                Utilisateur::ROLE_RESPONSABLE,
                Utilisateur::ROLE_ADMIN,
                Utilisateur::ROLE_ENSEIGNANT,
            ], true);
        }

        $user = $this->getUtilisateurConnecte();
        return $user?->estResponsable() ?? false;
    }

    public function inscrire(InscriptionDTO $dto): Utilisateur
    {
        $existant = $this->utilisateurRepository->findByEmail($dto->email);
        if ($existant !== null) {
            throw new InvalidArgumentException("Cette adresse email est déjà utilisée.");
        }

        $utilisateur = new Utilisateur([
            'nom'      => $dto->nom,
            'email'    => $dto->email,
            'password' => password_hash($dto->password, PASSWORD_BCRYPT),
            'role'     => $dto->role,
        ]);

        return $this->utilisateurRepository->save($utilisateur);
    }

    private function ensureSessionActive(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }
}
