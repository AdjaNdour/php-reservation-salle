<?php

namespace App\DTO;

use App\Validation\ReservationValidator;
use DateTimeImmutable;
use App\Validation\ValidationResult;

final class CreerReservationDTO
{
    public function __construct(
        public readonly int $salleId,
        public readonly string $responsable,
        public readonly string $email,
        public readonly string $motif,
        public readonly DateTimeImmutable $dateDebut,
        public readonly DateTimeImmutable $dateFin
    ) {}

    public static function fromArray(array $data): self
    {
        $validationResult = (new ReservationValidator())->validate($data);
        return new self(
            salleId: (int) ($validationResult->validatedData()['salle_id']),
            responsable: $validationResult->validatedData()['responsable'] ,
            email: $validationResult->validatedData()['email'] ,
            motif: $validationResult->validatedData()['motif'] ,
            dateDebut: new DateTimeImmutable($validationResult->validatedData()['date_debut'] ),
            dateFin: new DateTimeImmutable($validationResult->validatedData()['date_fin'])
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
