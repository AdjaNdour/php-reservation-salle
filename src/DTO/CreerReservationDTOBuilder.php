<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\ReservationValidator;
use DateTimeImmutable;

final class CreerReservationDTOBuilder
{
    private ?int $salleId = null;
    private ?string $responsable = null;
    private ?string $email = null;
    private ?string $motif = null;
    private ?DateTimeImmutable $dateDebut = null;
    private ?DateTimeImmutable $dateFin = null;

    public function __construct(
        private ?ReservationValidator $reservationValidator = null
    ) {}

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

    public function setDateDebut(DateTimeImmutable|string $dateDebut): self
    {
        $this->dateDebut = is_string($dateDebut) ? new DateTimeImmutable($dateDebut) : $dateDebut;
        return $this;
    }

    public function setDateFin(DateTimeImmutable|string $dateFin): self
    {
        $this->dateFin = is_string($dateFin) ? new DateTimeImmutable($dateFin) : $dateFin;
        return $this;
    }

    public function fromArray(array $data): self
    {
        if (isset($data['salle_id'])) {
            $this->setSalleId((int) $data['salle_id']);
        }
        if (isset($data['responsable'])) {
            $this->setResponsable((string) $data['responsable']);
        }
        if (isset($data['email'])) {
            $this->setEmail((string) $data['email']);
        }
        if (isset($data['motif'])) {
            $this->setMotif((string) $data['motif']);
        }
        if (isset($data['date_debut'])) {
            $this->setDateDebut($data['date_debut']);
        }
        if (isset($data['date_fin'])) {
            $this->setDateFin($data['date_fin']);
        }

        return $this;
    }

    public function reset(): self
    {
        $this->salleId = null;
        $this->responsable = null;
        $this->email = null;
        $this->motif = null;
        $this->dateDebut = null;
        $this->dateFin = null;

        return $this;
    }

    public function build(): CreerReservationDTO
    {
        $data = [
            'salle_id' => $this->salleId,
            'responsable' => $this->responsable,
            'email' => $this->email,
            'motif' => $this->motif,
            'date_debut' => $this->dateDebut?->format('Y-m-d H:i:s'),
            'date_fin' => $this->dateFin?->format('Y-m-d H:i:s'),
        ];
        $dto = CreerReservationDTO::fromArray($data, $this->reservationValidator);
        $this->reset();
        return $dto;
    }
}
