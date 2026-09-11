<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Utilisateur;
use App\Repository\Interface\IUtilisateurRepository;

class EloquentUtilisateurRepository implements IUtilisateurRepository
{

    public function findAll(): array
    {
        return Utilisateur::query()
            ->orderBy('nom', 'asc')
            ->get()
            ->all();
    }

    public function findById(int $id): ?Utilisateur
    {
        return Utilisateur::query()->find($id);
    }

    public function findByEmail(string $email): ?Utilisateur
    {
        return Utilisateur::query()->where('email', $email)->first();
    }

    public function save(Utilisateur $utilisateur): Utilisateur
    {
        $utilisateur->save();
        return $utilisateur;
    }

    public function delete(int $id): bool
    {
        $utilisateur = $this->findById($id);
        if ($utilisateur === null) {
            return false;
        }

        return (bool) $utilisateur->delete();
    }
}
