<?php

declare(strict_types=1);

namespace App\Validation\Interface;

use App\Validation\ValidationResult;

interface IValidatorResult
{
    public function validate(array $data): ValidationResult;
}
