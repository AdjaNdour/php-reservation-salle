<?php

namespace App\Validation;

use Respect\Validation\Validator ;

class ReservationValidator implements ValidatorInterface
{
    public function validate(array $data): ValidationResult
    {
        $errors = []; $validatedData = [];

        $salleId = $data['salle_id'] ?? null;
        if (!Validator::intVal()->positive()->validate($salleId)) {
            $errors['salle_id'] = "La salle sélectionnée est invalide.";
        } else {
            $validatedData['salle_id'] = (int) $salleId;
        }

        $responsable = trim((string) ($data['responsable'] ?? ''));
        if (!Validator::stringType()->length(2, 120)->validate($responsable)) {
            $errors['responsable'] = "Le nom du responsable est obligatoire et doit contenir entre 2 et 120 caractères.";
        } else {
            $validatedData['responsable'] = $responsable;
        }

        $email = trim((string) ($data['email'] ?? ''));
        if (!Validator::email()->validate($email)) {
            $errors['email'] = "L'adresse électronique saisie est invalide.";
        } else {
            $validatedData['email'] = $email;
        }

        $motif = trim((string) ($data['motif'] ?? ''));
        if (!Validator::stringType()->length(5, 255)->validate($motif)) {
            $errors['motif'] = "Le motif doit contenir entre 5 et 255 caractères.";
        } else {
            $validatedData['motif'] = $motif;
        }

        $dateDebutRaw = trim((string) ($data['date_debut'] ?? ''));
        if ($dateDebutRaw === '' || !Validator::dateTime()->validate($dateDebutRaw)) {
            $errors['date_debut'] = "La date et l'heure de début doivent être une date valide.";
        } else {
            $validatedData['date_debut'] = $dateDebutRaw;
        }

        $dateFinRaw = trim((string) ($data['date_fin'] ?? ''));
        if ($dateFinRaw === '' || !Validator::dateTime()->validate($dateFinRaw)) {
            $errors['date_fin'] = "La date et l'heure de fin doivent être une date valide.";
        } else {
            $validatedData['date_fin'] = $dateFinRaw;
        }

        return new ValidationResult(empty($errors), $errors, $validatedData);
    }
}
