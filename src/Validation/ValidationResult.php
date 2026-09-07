<?php

namespace App\Validation;

class ValidationResult
{

    public function __construct(
        private readonly bool $isValid,
        private readonly array $errors = [],
        private readonly array $validatedData = []
    ) {
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    public function error(string $field): ?string
    {
        return $this->errors[$field] ?? null;
    }

    public function validatedData(): array
    {
        return $this->validatedData;
    }
}
