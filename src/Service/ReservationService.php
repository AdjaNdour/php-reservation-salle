<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreerReservationDTO;
use App\Model\Reservation;
use App\Pagination\Paginator;
use App\Repository\ReservationRepositoryInterface;

final class ReservationService implements InterfaceReservationService
{
    public function __construct(
        private ReservationRepositoryInterface $repoResev,
        private ?CreerReservationService $creerReservationService = null
    ) {}

    public function getAll(?int $salleId = null): array
    {
        return $this->repoResev->findAll($salleId);
    }

    public function search(array $criteria = []): array
    {
        return $this->repoResev->findByCriteria($criteria);
    }

    public function getPaginated(int $page = 1, int $perPage = 10, array $criteria = []): Paginator
    {
        return $this->repoResev->paginate($page, $perPage, $criteria);
    }

    public function getById(int $id): ?Reservation
    {
        return $this->repoResev->findById($id);
    }

    public function save(CreerReservationDTO $dto): Reservation
    {
        if ($this->creerReservationService !== null) {
            return $this->creerReservationService->executer($dto);
        }

        $reservation = new Reservation([
            'salle_id'    => $dto->salleId,
            'responsable' => $dto->responsable,
            'email'       => $dto->email,
            'motif'       => $dto->motif,
            'date_debut'  => $dto->dateDebut->format('Y-m-d H:i:s'),
            'date_fin'    => $dto->dateFin->format('Y-m-d H:i:s'),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);

        return $this->repoResev->save($reservation);
    }
}
