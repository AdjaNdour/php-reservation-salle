<?php

declare(strict_types=1);

namespace App\DTO;

use App\Model\Utilisateur;
use App\Validation\InscriptionValidator;

final class InscriptionDTO
{
    public function __construct(
        public readonly string $nom,
        public readonly string $email,
        public readonly string $password,
        public readonly string $role = Utilisateur::ROLE_RESPONSABLE
    ) {}

    public static function fromArray(array $data, ?InscriptionValidator $validator = null): self
    {
        if ($validator !== null) {
            $validationResult = $validator->validate($data);
            if ($validationResult->isValid()) {
                $data = $validationResult->validatedData();
            }
        }

        $role = trim((string) ($data['role'] ?? Utilisateur::ROLE_RESPONSABLE));
        if (!in_array($role, Utilisateur::ROLES_AUTORISES, true)) {
            $role = Utilisateur::ROLE_RESPONSABLE;
        }

        return new self(
            nom: trim((string) ($data['nom'] ?? '')),
            email: strtolower(trim((string) ($data['email'] ?? ''))),
            password: (string) ($data['password'] ?? ''),
            role: $role
        );
    }

    public function toArray(): array
    {
        return [
            'nom'      => $this->nom,
            'email'    => $this->email,
            'password' => $this->password,
            'role'     => $this->role,
        ];
    }
}
