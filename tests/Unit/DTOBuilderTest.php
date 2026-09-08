<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerReservationDTO;
use App\DTO\CreerReservationDTOBuilder;
use App\DTO\CreerSalleDTO;
use App\DTO\CreerSalleDTOBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class DTOBuilderTest extends TestCase
{
    public function testCreerSalleDTOBuilderFluentSetters(): void
    {
        $builder = new CreerSalleDTOBuilder();

        $dto = $builder
            ->setNom('Salle Multimédia')
            ->setBatiment('Bâtiment D')
            ->setCapacite(45)
            ->setType('tp')
            ->setActive(false)
            ->build();

        $this->assertInstanceOf(CreerSalleDTO::class, $dto);
        $this->assertSame('Salle Multimédia', $dto->nom);
        $this->assertSame('Bâtiment D', $dto->batiment);
        $this->assertSame(45, $dto->capacite);
        $this->assertSame('tp', $dto->type);
        $this->assertFalse($dto->active);
    }

    public function testCreerSalleDTOBuilderFromArray(): void
    {
        $builder = new CreerSalleDTOBuilder();

        $dto = $builder->fromArray([
            'nom'      => 'Amphi 500',
            'batiment' => 'Pôle Central',
            'capacite' => 500,
            'type'     => 'cours',
            'active'   => true,
        ])->build();

        $this->assertSame('Amphi 500', $dto->nom);
        $this->assertSame('Pôle Central', $dto->batiment);
        $this->assertSame(500, $dto->capacite);
        $this->assertSame('cours', $dto->type);
        $this->assertTrue($dto->active);
    }

    public function testCreerSalleDTOBuilderResetApresBuild(): void
    {
        $builder = new CreerSalleDTOBuilder();

        $dto1 = $builder
            ->setNom('Salle 1')
            ->setBatiment('Bat 1')
            ->setCapacite(20)
            ->setType('cours')
            ->setActive(false)
            ->build();

        $this->assertSame('Salle 1', $dto1->nom);

        // Après build(), le builder doit être réinitialisé
        $dto2 = $builder
            ->setNom('Salle 2')
            ->setBatiment('Bat 2')
            ->setCapacite(30)
            ->setType('reunion')
            ->build();

        $this->assertSame('Salle 2', $dto2->nom);
        $this->assertSame('Bat 2', $dto2->batiment);
        $this->assertTrue($dto2->active); // Valeur par défaut rétablie
    }

    public function testCreerReservationDTOBuilderFluentSetters(): void
    {
        $builder = new CreerReservationDTOBuilder();
        $debut = new DateTimeImmutable('2026-10-10 10:00:00');
        $fin = new DateTimeImmutable('2026-10-10 12:00:00');

        $dto = $builder
            ->setSalleId(5)
            ->setResponsable('Fatou Ndiaye')
            ->setEmail('fatou@univ.sn')
            ->setMotif('Atelier POO')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $this->assertInstanceOf(CreerReservationDTO::class, $dto);
        $this->assertSame(5, $dto->salleId);
        $this->assertSame('Fatou Ndiaye', $dto->responsable);
        $this->assertSame('fatou@univ.sn', $dto->email);
        $this->assertSame('Atelier POO', $dto->motif);
        $this->assertEquals($debut, $dto->dateDebut);
        $this->assertEquals($fin, $dto->dateFin);
    }

    public function testCreerReservationDTOBuilderAvecStringDates(): void
    {
        $builder = new CreerReservationDTOBuilder();

        $dto = $builder
            ->setSalleId(3)
            ->setResponsable('Moussa Fall')
            ->setEmail('moussa@univ.sn')
            ->setMotif('Réunion de département')
            ->setDateDebut('2026-10-12 14:00:00')
            ->setDateFin('2026-10-12 16:00:00')
            ->build();

        $this->assertInstanceOf(DateTimeImmutable::class, $dto->dateDebut);
        $this->assertInstanceOf(DateTimeImmutable::class, $dto->dateFin);
        $this->assertSame('2026-10-12 14:00:00', $dto->dateDebut->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-12 16:00:00', $dto->dateFin->format('Y-m-d H:i:s'));
    }

    public function testCreerReservationDTOBuilderFromArray(): void
    {
        $builder = new CreerReservationDTOBuilder();

        $dto = $builder->fromArray([
            'salle_id'    => 8,
            'responsable' => 'Ousmane Sow',
            'email'       => 'ousmane@univ.sn',
            'motif'       => 'Soutenance de thèse',
            'date_debut'  => '2026-11-05 09:00:00',
            'date_fin'    => '2026-11-05 12:00:00',
        ])->build();

        $this->assertSame(8, $dto->salleId);
        $this->assertSame('Ousmane Sow', $dto->responsable);
        $this->assertSame('ousmane@univ.sn', $dto->email);
        $this->assertSame('Soutenance de thèse', $dto->motif);
    }

    public function testCreerReservationDTOBuilderResetApresBuild(): void
    {
        $builder = new CreerReservationDTOBuilder();

        $dto1 = $builder
            ->setSalleId(1)
            ->setResponsable('Awa')
            ->setEmail('awa@univ.sn')
            ->setMotif('Test')
            ->setDateDebut('2026-10-10 10:00:00')
            ->setDateFin('2026-10-10 12:00:00')
            ->build();

        $this->assertSame(1, $dto1->salleId);

        $dto2 = $builder
            ->setSalleId(2)
            ->setResponsable('Modou')
            ->setEmail('modou@univ.sn')
            ->setMotif('Autre test')
            ->setDateDebut('2026-10-11 10:00:00')
            ->setDateFin('2026-10-11 12:00:00')
            ->build();

        $this->assertSame(2, $dto2->salleId);
        $this->assertSame('Modou', $dto2->responsable);
    }
}
