<?php

declare(strict_types=1);

namespace App\Exception;

use DomainException;

class SalleIndisponibleException extends DomainException
{
    public static function conflit(\DateTimeInterface $debut, \DateTimeInterface $fin): self
    {
        return new self(sprintf(
            'La salle est deja reservee du %s au %s',
            $debut->format('d/m/Y H:i'),
            $fin->format('d/m/Y H:i')
        ));
    }

    public static function salleInactive(): self
    {
        return new self('Cette salle n\'est pas active');
    }

    public static function dureeExcessive(): self
    {
        return new self('La duree de reservation ne peut pas depasser 24 heures');
    }

    public static function salleNonTrouvee(): self
    {
        return new self('La salle n\'existe pas');
    }
}
