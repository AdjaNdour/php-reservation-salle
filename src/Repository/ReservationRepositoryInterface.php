<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Reservation;
use App\Pagination\Paginator;
use DateTimeInterface;

interface ReservationRepositoryInterface
{
    public function findAll(?int $salleId = null): array;

    public function findById(int $id): ?Reservation;

    public function findConflictingReservation(int $salleId, DateTimeInterface $debut, DateTimeInterface $fin): ?Reservation;

    public function findByCriteria(array $criteria = []): array;

    /**
     * @return Paginator<Reservation>
     */
    public function paginate(int $page = 1, int $perPage = 10, array $criteria = []): Paginator;

    /**
     * Retourne les statistiques des salles les plus utilisées
     * @return array<int, array{salle_id: int, nom: string, batiment: string, capacite: int, total_reservations: int, reservations_confirmees: int, reservations_annulees: int, total_heures: float}>
     */
    public function getMostUsedSalles(int $limit = 5): array;

    public function countTotal(): int;

    public function countByStatus(string $status): int;

    public function save(Reservation $reservation): Reservation;

    public function cancel(int $id): bool;
}
