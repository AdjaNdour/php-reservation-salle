<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Salle;

class EloquentSalleRepository implements SalleRepositoryInterface
{
   
    public function findAll(): array
    {
        return Salle::query()
                    ->orderBy('nom', 'asc')
                    ->get()
                    ->all();
    }

    public function findById(int $id): ?Salle
    {
        return Salle::query()
                    ->find($id);
    }

    public function save(Salle $salle): Salle
    {
        $salle->save();
        return $salle;
    }

    public function toggleActive(int $id): bool
    {
        $salle = $this->findById($id);
        if ($salle === null) {
            return false;
        }

        $salle->active = !$salle->active;
        return $salle->save();
    }
}
