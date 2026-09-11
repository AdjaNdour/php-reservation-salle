<?php

declare(strict_types=1);

namespace App\DTO;

use App\Validation\ReservationValidator;
use DateTimeImmutable;

final class CreerReservationDTOBuilder
{
    private static ?int $salleId = null;
    private static ?string $responsable = null;
    private static ?string $email = null;
    private static ?string $motif = null;
    private static ?DateTimeImmutable $dateDebut = null;
    private static ?DateTimeImmutable $dateFin = null;

    private function __construct() {}

    public static function setSalleId(int $salleId): void
    {
        self::$salleId = $salleId;
    }

    public static function setResponsable(string $responsable): void
    {
        self::$responsable = $responsable;
    }

    public static function setEmail(string $email): void
    {
        self::$email = $email;
    }

    public static function setMotif(string $motif): void
    {
        self::$motif = $motif;
    }

    public static function setDateDebut(DateTimeImmutable|string $dateDebut): void
    {
        self::$dateDebut = is_string($dateDebut) ? new DateTimeImmutable($dateDebut) : $dateDebut;
    }

    public static function setDateFin(DateTimeImmutable|string $dateFin): void
    {
        self::$dateFin = is_string($dateFin) ? new DateTimeImmutable($dateFin) : $dateFin;
    }

    public static function fromArray(array $data): void
    {
        if (isset($data['salle_id'])) {
            self::setSalleId((int) $data['salle_id']);
        }

        if (isset($data['responsable'])) {
            self::setResponsable((string) $data['responsable']);
        }

        if (isset($data['email'])) {
            self::setEmail((string) $data['email']);
        }

        if (isset($data['motif'])) {
            self::setMotif((string) $data['motif']);
        }

        if (isset($data['date_debut'])) {
            self::setDateDebut($data['date_debut']);
        }

        if (isset($data['date_fin'])) {
            self::setDateFin($data['date_fin']);
        }
    }

    public static function build(ReservationValidator $reservationValidator ): CreerReservationDTO {
        $data = [
            'salle_id' => self::$salleId,
            'responsable' => self::$responsable,
            'email' => self::$email,
            'motif' => self::$motif,
            'date_debut' => self::$dateDebut?->format('Y-m-d H:i:s'),
            'date_fin' => self::$dateFin?->format('Y-m-d H:i:s'),
        ];

        $dto = CreerReservationDTO::fromArray($data,$reservationValidator);

        self::reset();

        return $dto;
    }

    public static function reset(): void
    {
        self::$salleId = null;
        self::$responsable = null;
        self::$email = null;
        self::$motif = null;
        self::$dateDebut = null;
        self::$dateFin = null;
    }
}
