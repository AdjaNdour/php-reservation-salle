<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\LoginValidator;

final class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromArray(array $data, ?LoginValidator $validator = null): self
    {
        if ($validator !== null) {
            $validationResult = $validator->validate($data);
            if ($validationResult->isValid()) {
                $data = $validationResult->validatedData();
            }
        }

        return new self(
            email: strtolower(trim((string) ($data['email'] ?? ''))),
            password: (string) ($data['password'] ?? '')
        );
    }

    public function toArray(): array
    {
        return [
            'email'    => $this->email,
            'password' => $this->password,
        ];
    }
}
