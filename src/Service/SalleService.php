<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreerSalleDTO;
use App\Model\Salle;
use App\Repository\Interface\ISalleRepository;
use App\Service\Interface\ISalleService;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

final class SalleService implements ISalleService
{
    public function __construct(
        private ISalleRepository $repoSalles
    ) {}

    public function getAll(): array
    {
        return $this->repoSalles->findAll();
    }

    public function search(array $criteria = []): array
    {
        return $this->repoSalles->findByCriteria($criteria);
    }

    public function getPaginated(int $page = 1, int $perPage = 6, array $criteria = []): LengthAwarePaginator
    {
        return $this->repoSalles->paginate($page, $perPage, $criteria);
    }

    public function getById(int $id): ?Salle
    {
        return $this->repoSalles->findById($id);
    }

    public function save(CreerSalleDTO $dto, ?int $id = null): Salle
    {
        if ($id !== null) {
            $salle = $this->repoSalles->findById($id);
            if ($salle === null) {
                throw new InvalidArgumentException("La salle avec l'identifiant {$id} n'existe pas.");
            }
            $salle->fill($dto->toArray());
        } else {
            $salle = new Salle($dto->toArray());
        }

        return $this->repoSalles->save($salle);
    }

    public function update(int $id, CreerSalleDTO $dto): ?Salle
    {
        return $this->save($dto, $id);
    }

    public function toggleActive(int $id): bool
    {
        return $this->repoSalles->toggleActive($id);
    }

    public function delete(int $id): bool
    {
        return $this->repoSalles->delete($id);
    }
}
