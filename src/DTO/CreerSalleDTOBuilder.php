<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\SalleValidator;

final class CreerSalleDTOBuilder
{
    private static ?string $nom = null;
    private static ?string $batiment = null;
    private static ?int $capacite = null;
    private static ?string $type = null;
    private static bool $active = true;

    private function __construct(){}

    public static function setNom(string $nom): void
    {
        self::$nom = $nom;
    }

    public static function setBatiment(string $batiment): void
    {
        self::$batiment = $batiment;
    }

    public static function setCapacite(int $capacite): void
    {
        self::$capacite = $capacite;
    }

    public static function setType(string $type): void
    {
        self::$type = $type;
    }

    public static function setActive(bool $active): void
    {
        self::$active = $active;
    }

    public static function fromArray(array $data): void
    {
        if (isset($data['nom'])) {
            self::setNom((string) $data['nom']);
        }

        if (isset($data['batiment'])) {
            self::setBatiment((string) $data['batiment']);
        }

        if (isset($data['capacite'])) {
            self::setCapacite((int) $data['capacite']);
        }

        if (isset($data['type'])) {
            self::setType((string) $data['type']);
        }

        if (isset($data['active'])) {
            self::setActive((bool) $data['active']);
        }
    }

    public static function build(
        SalleValidator $salleValidator
    ): CreerSalleDTO {
        $data = [
            'nom' => self::$nom,
            'batiment' => self::$batiment,
            'capacite' => self::$capacite,
            'type' => self::$type,
            'active' => self::$active,
        ];

        $dto = CreerSalleDTO::fromArray(
            $data,
            $salleValidator
        );

        self::reset();

        return $dto;
    }

    public static function reset(): void
    {
        self::$nom = null;
        self::$batiment = null;
        self::$capacite = null;
        self::$type = null;
        self::$active = true;
    }
}
