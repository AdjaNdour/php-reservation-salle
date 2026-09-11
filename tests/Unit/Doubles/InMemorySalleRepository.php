<?php

declare(strict_types=1);

namespace Tests\Unit\Doubles;

use App\Model\Salle;
use App\Repository\Interface\ISalleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class InMemorySalleRepository implements ISalleRepository
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

    public function findByCriteria(array $criteria = []): array
    {
        $filtered = array_filter($this->salles, function (Salle $salle) use ($criteria) {
            if (!empty($criteria['nom']) && stripos($salle->nom, $criteria['nom']) === false) {
                return false;
            }
            if (!empty($criteria['batiment']) && stripos($salle->batiment, $criteria['batiment']) === false) {
                return false;
            }
            if (!empty($criteria['type']) && $salle->type !== $criteria['type']) {
                return false;
            }
            if (!empty($criteria['capacite_min']) && $salle->capacite < (int) $criteria['capacite_min']) {
                return false;
            }
            if (isset($criteria['active']) && $criteria['active'] !== '') {
                $expected = in_array($criteria['active'], [true, 1, '1', 'true', 'active'], true);
                if ((bool) $salle->active !== $expected) {
                    return false;
                }
            }
            return true;
        });

        return array_values($filtered);
    }

    public function paginate(int $page = 1, int $perPage = 5, array $criteria = []): LengthAwarePaginator
    {
        $filtered = $this->findByCriteria($criteria);
        $totalItems = count($filtered);
        $offset = ($page - 1) * $perPage;
        $items = array_slice($filtered, $offset, $perPage);

        return new LengthAwarePaginator($items, $totalItems, $perPage, $page);
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

    public function delete(int $id): bool
    {
        if (!isset($this->salles[$id])) {
            return false;
        }

        unset($this->salles[$id]);
        return true;
    }
}
