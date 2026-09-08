<?php

namespace App\DTO;

use App\Validation\ReservationValidator;
use DateTimeImmutable;

final class CreerReservationDTO
{
    private function __construct(
        public readonly int $salleId,
        public readonly string $responsable,
        public readonly string $email,
        public readonly string $motif,
        public readonly DateTimeImmutable $dateDebut,
        public readonly DateTimeImmutable $dateFin,
    ) {}

    public static function fromArray(array $data, ?ReservationValidator $reservationValidator = null): self
    {
        if ($reservationValidator !== null) {
            $validationResult = $reservationValidator->validate($data);
            if ($validationResult->isValid()) {
                $data = $validationResult->validatedData();
            }
        }

        $dateDebut = isset($data['date_debut'])
            ? ($data['date_debut'] instanceof DateTimeImmutable ? $data['date_debut'] : new DateTimeImmutable((string) $data['date_debut']))
            : new DateTimeImmutable();

        $dateFin = isset($data['date_fin'])
            ? ($data['date_fin'] instanceof DateTimeImmutable ? $data['date_fin'] : new DateTimeImmutable((string) $data['date_fin']))
            : new DateTimeImmutable();

        return new self(
            salleId: (int) ($data['salle_id'] ?? 0),
            responsable: (string) ($data['responsable'] ?? ''),
            email: (string) ($data['email'] ?? ''),
            motif: (string) ($data['motif'] ?? ''),
            dateDebut: $dateDebut,
            dateFin: $dateFin
        );
    }

    public function toArray(): array
    {
        return [
            'salle_id'    => $this->salleId,
            'responsable' => $this->responsable,
            'email'       => $this->email,
            'motif'       => $this->motif,
            'date_debut'  => $this->dateDebut->format('Y-m-d H:i:s'),
            'date_fin'    => $this->dateFin->format('Y-m-d H:i:s'),
        ];
    }
}
