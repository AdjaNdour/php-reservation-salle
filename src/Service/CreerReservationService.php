<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\CreerReservationDTO;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Model\Reservation;
use App\Model\Salle;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SalleRepositoryInterface;

final class CreerReservationService
{
    public function __construct(
        private SalleRepositoryInterface $repoSalles,
        private ReservationRepositoryInterface $repoReservations
    ) {}

    public function executer(CreerReservationDTO $dto): Reservation
    {
        $salle = $this->verifierSalle($dto);
        $this->verifierDates($dto);
        $this->verifierDuree($dto);
        $this->verifierDateFuture($dto);
        $this->verifierConflit($dto);

        $reservation = new Reservation([
            'salle_id'    => $dto->salleId,
            'responsable' => $dto->responsable,
            'email'       => $dto->email,
            'motif'       => $dto->motif,
            'date_debut'  => $dto->dateDebut->format('Y-m-d H:i:s'),
            'date_fin'    => $dto->dateFin->format('Y-m-d H:i:s'),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);

        $reservationSauvegardee = $this->repoReservations->save($reservation);

        $salle->active = false;
        $this->repoSalles->save($salle);

        return $reservationSauvegardee;
    }

    private function verifierDuree(CreerReservationDTO $dto): void
    {
        $dureeSecondes = $dto->dateFin->getTimestamp() - $dto->dateDebut->getTimestamp();
        if ($dureeSecondes > 4 * 60 * 60) {
            throw new ReservationIntrouvableException("Une réservation ne peut pas dépasser quatre heures.");
        }
    }

    private function verifierDates(CreerReservationDTO $dto): void
    {
        if ($dto->dateDebut >= $dto->dateFin) {
            throw new ReservationIntrouvableException("La date de début doit obligatoirement précéder la date de fin.");
        }
    }

    private function verifierSalle(CreerReservationDTO $dto): Salle
    {
        $salle = $this->repoSalles->findById($dto->salleId);

        if ($salle === null) {
            throw new SalleIndisponibleException("La salle demandée n'existe pas.");
        }

        if (!$salle->estActive()) {
            throw new SalleIndisponibleException("Cette salle ne peut pas être réservée car elle est inactive.");
        }

        return $salle;
    }

    private function verifierDateFuture(CreerReservationDTO $dto): void
    {
        $maintenant = new \DateTimeImmutable();

        if ($dto->dateDebut <= $maintenant) {
            throw new ReservationIntrouvableException("La date de réservation doit débuter dans le futur.");
        }
    }

    private function verifierConflit(CreerReservationDTO $dto): void
    {
        $conflit = $this->repoReservations->findConflictingReservation($dto->salleId, $dto->dateDebut, $dto->dateFin);
        if ($conflit !== null) {
            throw new SalleIndisponibleException(
                "La salle est indisponible pendant cette période."
            );
        }
    }
}
