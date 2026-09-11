<?php

declare(strict_types=1);

namespace App\Repository\Interface;

use App\Model\Salle;
use Illuminate\Pagination\LengthAwarePaginator;

interface ISalleRepository
{
    public function findAll(): array;

    public function findById(int $id): ?Salle;

    public function findByCriteria(array $criteria = []): array;

    public function paginate(int $page = 1, int $perPage = 5, array $criteria = []): LengthAwarePaginator;

    public function save(Salle $salle): Salle;

    public function toggleActive(int $id): bool;

    public function delete(int $id): bool;
}
