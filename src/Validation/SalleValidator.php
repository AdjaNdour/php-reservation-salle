<?php

declare(strict_types=1);

namespace App\Validation;

use App\Model\Salle;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

final class SalleValidator implements ValidatorInterface
{
    private const TYPES_AUTORISES = Salle::TYPES_AUTORISES;

    public function validate(array $data): ValidationResult
    {
        $rules = [
            'nom'      => v::stringType()->notBlank()->length(2, 100),
            'batiment' => v::stringType()->notBlank()->length(2, 100),
            'capacite' => v::intVal()->between(1, 1000),
            'type'     => v::in(self::TYPES_AUTORISES),
            'active'   => v::boolVal(),
        ];

        $messages = [
            'nom'      => "Le nom de la salle est obligatoire et doit contenir entre 2 et 100 caractères.",
            'batiment' => "Le bâtiment est obligatoire et doit contenir entre 2 et 100 caractères.",
            'capacite' => "La capacité doit être un entier compris entre 1 et 1 000 places.",
            'type'     => "Le type de salle est invalide. Types acceptés : " . implode(', ', self::TYPES_AUTORISES) . ".",
            'active'   => "La valeur d'activation doit être un booléen.",
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
            'nom'      => trim((string) ($data['nom'] ?? '')),
            'batiment' => trim((string) ($data['batiment'] ?? '')),
            'capacite' => (int) ($data['capacite'] ?? 0),
            'type'     => trim((string) ($data['type'] ?? '')),
            'active'   => filter_var($data['active'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];

        return new ValidationResult(valid: true, data: $validatedData);
    }
}
