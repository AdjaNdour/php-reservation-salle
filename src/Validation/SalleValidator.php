<?php

namespace App\Validation;

use App\Model\Salle;
use Respect\Validation\Validator ;

class SalleValidator implements ValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];
        $validatedData = [];

        // Validation du nom
        $nom = trim((string) ($data['nom'] ?? ''));
        if (!Validator::stringType()->length(2, 100)->validate($nom)) {
            $errors['nom'] = "Le nom de la salle est obligatoire et doit contenir entre 2 et 100 caractères.";
        } else {
            $validatedData['nom'] = $nom;
        }

        // Validation du bâtiment
        $batiment = trim((string) ($data['batiment'] ?? ''));
        if (!Validator::stringType()->length(2, 100)->validate($batiment)) {
            $errors['batiment'] = "Le bâtiment est obligatoire et doit contenir entre 2 et 100 caractères.";
        } else {
            $validatedData['batiment'] = $batiment;
        }

        // Validation de la capacité
        $capacite = $data['capacite'] ?? null;
        if (!Validator::intVal()->between(1, 1000)->validate($capacite)) {
            $errors['capacite'] = "La capacité doit être un entier compris entre 1 et 1 000 places.";
        } else {
            $validatedData['capacite'] = (int) $capacite;
        }

        // Validation du type
        $type = trim((string) ($data['type'] ?? ''));
        if (!Validator::in(Salle::TYPES_AUTORISES)->validate($type)) {
            $errors['type'] = "Le type de salle est invalide. Types acceptés : " . implode(', ', Salle::TYPES_AUTORISES) . ".";
        } else {
            $validatedData['type'] = $type;
        }

        // Validation du statut active
        $activeRaw = $data['active'] ?? false;
        $active = filter_var($activeRaw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($active === null) {
            $errors['active'] = "La valeur d'activation doit être un booléen.";
        } else {
            $validatedData['active'] = $active;
        }

        return new ValidationResult(empty($errors), $errors, $validatedData);
    }
}
