<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\SalleValidator;
final class CreerSalleDTO
{
    public function __construct(
        public readonly string $nom,
        public readonly string $batiment,
        public readonly int $capacite,
        public readonly string $type,
        public readonly bool $active = true
    ) {}

    public static function fromArray(array $data): self
    {
        $validationResult = (new SalleValidator())->validate($data);

        return new self(
            nom: $validationResult->validatedData()['nom'] ,
            batiment: $validationResult->validatedData()['batiment'] ,
            capacite: (int) ($validationResult->validatedData()['capacite'] ),
            type: $validationResult->validatedData()['type'] ,
            active: filter_var($validationResult->validatedData()['active'] )
        );
    }

    public function toArray(): array
    {
        return [
            'nom'      => $this->nom,
            'batiment' => $this->batiment,
            'capacite' => $this->capacite,
            'type'     => $this->type,
            'active'   => $this->active,
        ];
    }
}
