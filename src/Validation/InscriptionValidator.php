<?php

declare(strict_types=1);

namespace App\Validation;

use Respect\Validation\Validator;

class InscriptionValidator implements ValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];
        $validatedData = [];

        // Validation nom
        $nom = trim((string) ($data['nom'] ?? ''));
        if (!Validator::stringType()->length(2, 100)->validate($nom)) {
            $errors['nom'] = "Le nom complet est obligatoire (entre 2 et 100 caractères).";
        } else {
            $validatedData['nom'] = $nom;
        }

        // Validation email
        $email = trim((string) ($data['email'] ?? ''));
        if (!Validator::email()->validate($email)) {
            $errors['email'] = "L'adresse email institutionnelle est invalide.";
        } else {
            $validatedData['email'] = strtolower($email);
        }

        // Validation mot de passe
        $password = (string) ($data['password'] ?? '');
        if (!Validator::stringType()->length(6, 128)->validate($password)) {
            $errors['password'] = "Le mot de passe doit comporter au moins 6 caractères.";
        } else {
            $validatedData['password'] = $password;
        }

        // Confirmation du mot de passe
        $passwordConfirmation = (string) ($data['password_confirmation'] ?? '');
        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = "Les deux mots de passe ne correspondent pas.";
        }

        return new ValidationResult(empty($errors), $errors, $validatedData);
    }
}
