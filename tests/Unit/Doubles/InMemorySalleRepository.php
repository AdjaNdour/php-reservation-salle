<?php

declare(strict_types=1);

namespace Tests\Unit\Doubles;

use App\Model\Salle;
use App\Repository\SalleRepositoryInterface;

class InMemorySalleRepository implements SalleRepositoryInterface
{
    
    private array $salles = [];
    private int $autoIncrement = 1;

    public function findAll(): array
    {
        return array_values($this->salles);
    }

    public function findById(int $id): ?Salle
    {
        return $this->salles[$id] ?? null;
    }

    public function save(Salle $salle): Salle
    {
        if ($salle->id === null || $salle->id === 0) {
            $salle->id = $this->autoIncrement++;
        }
        $this->salles[$salle->id] = $salle;
        return $salle;
    }

    public function toggleActive(int $id): bool
    {
        if (!isset($this->salles[$id])) {
            return false;
        }

        $this->salles[$id]->active = !$this->salles[$id]->active;
        return true;
    }
}
