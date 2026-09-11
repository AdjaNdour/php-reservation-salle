<?php

declare(strict_types=1);

namespace Tests\Unit\Doubles;

use App\Model\Utilisateur;
use App\Repository\Interface\IUtilisateurRepository;

class InMemoryUtilisateurRepository implements IUtilisateurRepository
{
    private array $utilisateurs = [];
    private int $autoIncrement = 1;

    public function findAll(): array
    {
        return array_values($this->utilisateurs);
    }

    public function findById(int $id): ?Utilisateur
    {
        return $this->utilisateurs[$id] ?? null;
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        $email = strtolower(trim($email));
        foreach ($this->utilisateurs as $user) {
            if (strtolower($user->email) === $email) {
                return $user;
            }
        }
        return null;
    }

    public function save(Utilisateur $utilisateur): Utilisateur
    {
        if ($utilisateur->id === null || $utilisateur->id === 0) {
            $utilisateur->id = $this->autoIncrement++;
        }
        $this->utilisateurs[$utilisateur->id] = $utilisateur;
        return $utilisateur;
    }

    public function delete(int $id): bool
    {
        if (!isset($this->utilisateurs[$id])) {
            return false;
        }

        unset($this->utilisateurs[$id]);
        return true;
    }
}
