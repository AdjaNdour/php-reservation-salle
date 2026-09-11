<?php

declare(strict_types=1);

namespace App\Service\Interface;

use App\DTO\InscriptionDTO;
use App\DTO\LoginDTO;
use App\Model\Utilisateur;

interface IAuthService
{
    public function getByEmail(LoginDTO $dto): ?Utilisateur;
    public function tentativeConnexion(LoginDTO $dto): ?Utilisateur;

    public function connecter(Utilisateur $utilisateur): void;

    public function deconnecter(): void;

    public function estConnecte(): bool;

    public function getUtilisateurConnecte(): ?Utilisateur;

    public function estAdmin(): bool;

    public function estResponsable(): bool;

    public function inscrire(InscriptionDTO $dto): Utilisateur;
}
