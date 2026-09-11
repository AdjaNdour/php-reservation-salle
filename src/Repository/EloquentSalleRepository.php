<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Salle;
use App\Repository\Interface\ISalleRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentSalleRepository implements ISalleRepository
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

    public function paginate(int $page = 1, int $perPage = 5, array $criteria = []): LengthAwarePaginator
    {
        return $this->buildCriteriaQuery($criteria)
            ->orderBy('nom', 'asc')
            ->paginate(
                perPage: $perPage,
                page: max(1, $page)
            );
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

        $filters = [
            'q' => function (Builder $query, mixed $value): void {
                $search = '%' . trim((string) $value) . '%';
                $query->where(function (Builder $subQuery) use ($search): void {
                    $subQuery->where('nom', 'like', $search)->orWhere('batiment', 'like', $search);
                });
            },

            'nom' => fn(Builder $query, mixed $value) =>
            $query->where('nom', 'like', '%' . trim((string) $value) . '%'),

            'batiment' => fn(Builder $query, mixed $value) =>
            $query->where('batiment', 'like', '%' . trim((string) $value) . '%'),

            'type' => fn(Builder $query, mixed $value) =>
            $query->where('type', trim((string) $value)),

            'capacite_min' => fn(Builder $query, mixed $value) =>
            $query->where('capacite', '>=', (int) $value),

            'active' => fn(Builder $query, mixed $value) =>
            $query->where('active', in_array($value, [true, 1, '1', 'true', 'active'], true)),
        ];

        foreach ($filters as $key => $filter) {
            if (isset($criteria[$key]) && $criteria[$key] !== '' && ($key !== 'capacite_min' || is_numeric($criteria[$key]))) {
                $filter($query, $criteria[$key]);
            }
        }

        return $query;
    }
}
