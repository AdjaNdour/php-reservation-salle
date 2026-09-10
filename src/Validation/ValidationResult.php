<?php

namespace App\Validation;

class ValidationResult
{

    private readonly bool $isValid;
    private readonly array $errors;
    private readonly array $validatedData;

    public function __construct(
        bool $isValid = true,
        array $errors = [],
        array $validatedData = [],
        ?bool $valid = null,
        ?array $data = null
    ) {
        $this->isValid = $valid ?? $isValid;
        $this->errors = $errors;
        $this->validatedData = $data ?? $validatedData;
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
