<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\InscriptionDTO;
use App\DTO\LoginDTO;
use App\Model\Utilisateur;

interface InterfaceAuthService
{
    /**
     * Tente d'authentifier un utilisateur avec ses identifiants.
     * Retourne l'entité Utilisateur si les identifiants sont valides, null sinon.
     */
    public function tentativeConnexion(LoginDTO $dto): ?Utilisateur;

    /**
     * Enregistre l'utilisateur en session (initialise les variables de session et régénère l'ID).
     */
    public function connecter(Utilisateur $utilisateur): void;

    /**
     * Détruit la session utilisateur courante.
     */
    public function deconnecter(): void;

    /**
     * Vérifie si un utilisateur est actuellement connecté.
     */
    public function estConnecte(): bool;

    /**
     * Récupère l'entité de l'utilisateur actuellement connecté ou null.
     */
    public function getUtilisateurConnecte(): ?Utilisateur;

    /**
     * Enregistre un nouvel utilisateur après vérification de l'unicité de son email.
     */
    public function inscrire(InscriptionDTO $dto): Utilisateur;
}
