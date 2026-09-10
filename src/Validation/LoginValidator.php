<?php

declare(strict_types=1);

namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

final class LoginValidator implements ValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $rules = [
            'email'    => v::email(),
            'password' => v::stringType()->notEmpty(),
        ];

        $messages = [
            'email'    => "L'adresse email est obligatoire et doit être valide.",
            'password' => "Le mot de passe est obligatoire.",
        ];

        $errors = [];

        foreach ($rules as $champ => $validator) {
            try {
                $valeur = is_string($data[$champ] ?? null) ? trim((string) $data[$champ]) : ($data[$champ] ?? null);
                $validator->assert($valeur);
            } catch (NestedValidationException $exception) {
                $errors[$champ] = $messages[$champ]
                    ?? current($exception->getMessages())
                    ?: "Le champ {$champ} est invalide.";
            }
        }

        if ($errors !== []) {
            return new ValidationResult(valid: false, errors: $errors);
        }

        $validatedData = [
            'email'    => strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => (string) ($data['password'] ?? ''),
        ];

        return new ValidationResult(valid: true, data: $validatedData);
    }
}
