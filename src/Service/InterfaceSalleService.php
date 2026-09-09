<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreerSalleDTO;
use App\Model\Salle;
use App\Pagination\Paginator;

interface InterfaceSalleService
{
    public function getAll(): array;

    public function search(array $criteria = []): array;

    /**
     * @return Paginator<Salle>
     */
    public function getPaginated(int $page = 1, int $perPage = 6, array $criteria = []): Paginator;

    public function getById(int $id): ?Salle;

    public function save(CreerSalleDTO $dto, ?int $id = null): Salle;

    public function update(int $id, CreerSalleDTO $dto): ?Salle;

    public function toggleActive(int $id): bool;

    public function delete(int $id): bool;
}
