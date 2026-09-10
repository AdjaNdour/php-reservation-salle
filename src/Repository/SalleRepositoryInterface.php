<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Salle;
use App\Pagination\Paginator;

interface SalleRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?Salle;

    public function findByCriteria(array $criteria = []): array;

    /**
     * @return Paginator<Salle>
     */
    public function paginate(int $page = 1, int $perPage = 5, array $criteria = []): Paginator;

    public function save(Salle $salle): Salle;

    public function toggleActive(int $id): bool;

    public function delete(int $id): bool;
}
