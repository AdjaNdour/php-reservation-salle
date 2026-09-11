<?php

declare(strict_types=1);

namespace App\Validation;

use App\Validation\Interface\IInscriptionValidator;
use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Validator as v;

final class InscriptionValidator implements IInscriptionValidator
{
    public function validate(array $data): ValidationResult
    {
        $password = (string) ($data['password'] ?? '');

        $rules = [
            'nom'                   => v::stringType()->notBlank()->length(2, 100),
            'email'                 => v::email(),
            'password'              => v::stringType()->length(6, 128),
            'password_confirmation' => v::equals($password),
        ];

        $messages = [
            'nom'                   => "Le nom complet est obligatoire (entre 2 et 100 caractères).",
            'email'                 => "L'adresse email institutionnelle est invalide.",
            'password'              => "Le mot de passe doit comporter au moins 6 caractères.",
            'password_confirmation' => "Les deux mots de passe ne correspondent pas.",
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
            'email'    => strtolower(trim((string) ($data['email'] ?? ''))),
            'password' => $password,
        ];

        return new ValidationResult(valid: true, data: $validatedData);
    }
}
