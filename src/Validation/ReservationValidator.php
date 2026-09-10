<?php

declare(strict_types=1);

namespace App\Validation;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

final class ReservationValidator implements ValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $rules = [
            'salle_id'    => v::intVal()->positive(),
            'responsable' => v::stringType()->notBlank()->length(2, 120),
            'email'       => v::email(),
            'motif'       => v::stringType()->notBlank()->length(5, 255),
            'date_debut'  => v::notBlank()->dateTime(),
            'date_fin'    => v::notBlank()->dateTime(),
        ];

        $messages = [
            'salle_id'    => "La salle sélectionnée est invalide.",
            'responsable' => "Le nom du responsable est obligatoire et doit contenir entre 2 et 120 caractères.",
            'email'       => "L'adresse électronique saisie est invalide.",
            'motif'       => "Le motif doit contenir entre 5 et 255 caractères.",
            'date_debut'  => "La date et l'heure de début doivent être une date valide.",
            'date_fin'    => "La date et l'heure de fin doivent être une date valide.",
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
            'salle_id'    => (int) ($data['salle_id'] ?? 0),
            'responsable' => trim((string) ($data['responsable'] ?? '')),
            'email'       => trim((string) ($data['email'] ?? '')),
            'motif'       => trim((string) ($data['motif'] ?? '')),
            'date_debut'  => trim((string) ($data['date_debut'] ?? '')),
            'date_fin'    => trim((string) ($data['date_fin'] ?? '')),
        ];

        return new ValidationResult(valid: true, data: $validatedData);
    }
}
