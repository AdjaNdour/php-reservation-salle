<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Salle;
use App\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;

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
        return Salle::query()->find($id);
    }

    public function findByCriteria(array $criteria = []): array
    {
        return $this->buildCriteriaQuery($criteria)
            ->orderBy('nom', 'asc')
            ->get()
            ->all();
    }

    public function paginate(int $page = 1, int $perPage = 5, array $criteria = []): Paginator
    {
        $query = $this->buildCriteriaQuery($criteria);
        $totalItems = $query->count();

        $page = max(1, $page);
        $perPage = max(1, $perPage);
        $offset = ($page - 1) * $perPage;

        $items = $query->orderBy('nom', 'asc')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->all();

        return new Paginator($items, $totalItems, $page, $perPage);
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

    public function delete(int $id): bool
    {
        $salle = $this->findById($id);
        if ($salle === null) {
            return false;
        }

        return (bool) $salle->delete();
    }

    private function buildCriteriaQuery(array $criteria): Builder
    {
        $query = Salle::query();

        if (!empty($criteria['q'])) {
            $search = '%' . trim((string) $criteria['q']) . '%';
            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery->where('nom', 'like', $search)
                    ->orWhere('batiment', 'like', $search);
            });
        }

        if (!empty($criteria['nom'])) {
            $query->where('nom', 'like', '%' . trim((string) $criteria['nom']) . '%');
        }

        if (!empty($criteria['batiment'])) {
            $query->where('batiment', 'like', '%' . trim((string) $criteria['batiment']) . '%');
        }

        if (!empty($criteria['type'])) {
            $query->where('type', trim((string) $criteria['type']));
        }

        if (!empty($criteria['capacite_min']) && is_numeric($criteria['capacite_min'])) {
            $query->where('capacite', '>=', (int) $criteria['capacite_min']);
        }

        if (isset($criteria['active']) && $criteria['active'] !== '') {
            $isActive = in_array($criteria['active'], [true, 1, '1', 'true', 'active'], true);
            $query->where('active', $isActive);
        }

        return $query;
    }
}
