<?php

namespace App\DTO;

use App\Model\Reservation;

final class CreerReservationDTOBuilder
{
    private ?int $salleId = null;
    private ?string $responsable = null ;
    private ?string $email = null;
    private ?string $motif = null;
    private ?\DateTimeImmutable $dateDebut = null;
    private ?\DateTimeImmutable $dateFin = null;

    public function setSalleId(int $salleId): self
    {
        $this->salleId = $salleId;
        return $this;
    }

    public function setResponsable(string $responsable): self
    {
        $this->responsable = $responsable;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;
        return $this;
    }

    public function setDateDebut(\DateTimeImmutable $dateDebut): self
    {
        $this->dateDebut = $dateDebut;
        return $this;
    }

    public function setDateFin(\DateTimeImmutable $dateFin): self
    {
        $this->dateFin = $dateFin;
        return $this;
    }

    public function build(): CreerReservationDTO
    {
        return new CreerReservationDTO(
            salleId: $this->salleId,
            responsable: $this->responsable,
            email: $this->email,
            motif: $this->motif,
            dateDebut: $this->dateDebut,
            dateFin: $this->dateFin
        );
    }
}