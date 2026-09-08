<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Utilisateur;

interface UtilisateurRepositoryInterface
{
 
    public function findAll(): array;

    public function findById(int $id): ?Utilisateur;

    public function findByEmail(string $email): ?Utilisateur;

    public function save(Utilisateur $utilisateur): Utilisateur;

    public function delete(int $id): bool;
}
