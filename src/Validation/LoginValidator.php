<?php

declare(strict_types=1);

namespace App\Validation;

use Respect\Validation\Validator;

class LoginValidator implements ValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];
        $validatedData = [];

        $email = trim((string) ($data['email'] ?? ''));
        if (!Validator::email()->validate($email)) {
            $errors['email'] = "L'adresse email est obligatoire et doit être valide.";
        } else {
            $validatedData['email'] = strtolower($email);
        }

        $password = (string) ($data['password'] ?? '');
        if (!Validator::stringType()->notEmpty()->validate($password)) {
            $errors['password'] = "Le mot de passe est obligatoire.";
        } else {
            $validatedData['password'] = $password;
        }

        return new ValidationResult(empty($errors), $errors, $validatedData);
    }
}
