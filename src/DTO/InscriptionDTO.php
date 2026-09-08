<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\InscriptionValidator;

final class InscriptionDTO
{
    public function __construct(
        public readonly string $nom,
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromArray(array $data, ?InscriptionValidator $validator = null): self
    {
        if ($validator !== null) {
            $validationResult = $validator->validate($data);
            if ($validationResult->isValid()) {
                $data = $validationResult->validatedData();
            }
        }

        return new self(
            nom: trim((string) ($data['nom'] ?? '')),
            email: strtolower(trim((string) ($data['email'] ?? ''))),
            password: (string) ($data['password'] ?? '')
        );
    }

    public function toArray(): array
    {
        return [
            'nom'      => $this->nom,
            'email'    => $this->email,
            'password' => $this->password,
        ];
    }
}
