<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\Utilisateur;

interface IUtilisateurRepository
{
    public function findAll(): array;

    public function findById(int $id): ?Utilisateur;

    public function findByEmail(string $email): ?Utilisateur;

    public function save(Utilisateur $utilisateur): Utilisateur;

    public function delete(int $id): bool;
}
