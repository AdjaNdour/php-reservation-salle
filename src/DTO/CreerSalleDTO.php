<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\SalleValidator;
final class CreerSalleDTO
{
    private function __construct(
        public readonly string $nom,
        public readonly string $batiment,
        public readonly int $capacite,
        public readonly string $type,
        public readonly bool $active = true
    ) {}

    public static function fromArray(array $data, ?SalleValidator $salleValidator = null): self
    {
        if ($salleValidator !== null) {
            $validationResult = $salleValidator->validate($data);
            if ($validationResult->isValid()) {
                $data = $validationResult->validatedData();
            }
        }

        return new self(
            nom: (string) ($data['nom'] ?? ''),
            batiment: (string) ($data['batiment'] ?? ''),
            capacite: (int) ($data['capacite'] ?? 0),
            type: (string) ($data['type'] ?? ''),
            active: isset($data['active']) ? (bool) $data['active'] : true
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
