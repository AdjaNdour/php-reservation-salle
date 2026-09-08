<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Salle;

interface SalleRepositoryInterface
{
    public function findAll(): array;

    public function findById(int $id): ?Salle;

    public function save(Salle $salle): Salle;

    public function toggleActive(int $id): bool;

    public function delete(int $id): bool;
}
