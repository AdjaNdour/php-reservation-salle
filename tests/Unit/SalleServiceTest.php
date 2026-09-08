<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerSalleDTOBuilder;
use App\Model\Salle;
use App\Service\InterfaceSalleService;
use App\Service\SalleService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemorySalleRepository;

class SalleServiceTest extends TestCase
{
    private InMemorySalleRepository $salleRepository;
    private InterfaceSalleService $salleService;
    private CreerSalleDTOBuilder $builder;

    protected function setUp(): void
    {
        $this->salleRepository = new InMemorySalleRepository();
        $this->salleService = new SalleService($this->salleRepository);
        $this->builder = new CreerSalleDTOBuilder();
    }

    public function testCreerSalleAvecBuilderEtDTO(): void
    {
        $dto = $this->builder
            ->setNom('Amphithéâtre B')
            ->setBatiment('Bâtiment Central')
            ->setCapacite(150)
            ->setType('cours')
            ->setActive(true)
            ->build();

        $salle = $this->salleService->save($dto);

        $this->assertInstanceOf(Salle::class, $salle);
        $this->assertNotNull($salle->id);
        $this->assertSame('Amphithéâtre B', $salle->nom);
        $this->assertSame('Bâtiment Central', $salle->batiment);
        $this->assertSame(150, $salle->capacite);
        $this->assertSame('cours', $salle->type);
        $this->assertTrue($salle->active);

        // Vérification de la persistance dans le repository
        $saved = $this->salleRepository->findById($salle->id);
        $this->assertNotNull($saved);
        $this->assertSame('Amphithéâtre B', $saved->nom);
    }

    public function testCreerSalleViaBuilderFromArray(): void
    {
        $dto = $this->builder->fromArray([
            'nom'      => 'Laboratoire IA',
            'batiment' => 'Bâtiment Informatique',
            'capacite' => 30,
            'type'     => 'tp',
            'active'   => true,
        ])->build();

        $salle = $this->salleService->save($dto);

        $this->assertSame('Laboratoire IA', $salle->nom);
        $this->assertSame(30, $salle->capacite);
    }

    public function testModifierSalleAvecDTO(): void
    {
        $dtoInitial = $this->builder
            ->setNom('Salle 101')
            ->setBatiment('Bâtiment A')
            ->setCapacite(25)
            ->setType('cours')
            ->setActive(true)
            ->build();

        $salle = $this->salleService->save($dtoInitial);

        $dtoModifie = $this->builder
            ->setNom('Salle 101 Renommée')
            ->setBatiment('Bâtiment A')
            ->setCapacite(35)
            ->setType('reunion')
            ->setActive(true)
            ->build();

        $salleModifiee = $this->salleService->save($dtoModifie, $salle->id);

        $this->assertSame($salle->id, $salleModifiee->id);
        $this->assertSame('Salle 101 Renommée', $salleModifiee->nom);
        $this->assertSame(35, $salleModifiee->capacite);
        $this->assertSame('reunion', $salleModifiee->type);

        // Test de la méthode update()
        $dtoUpdate = $this->builder
            ->setNom('Salle 101 V3')
            ->setBatiment('Bâtiment A')
            ->setCapacite(40)
            ->setType('reunion')
            ->setActive(false)
            ->build();

        $salleV3 = $this->salleService->update($salle->id, $dtoUpdate);
        $this->assertNotNull($salleV3);
        $this->assertSame('Salle 101 V3', $salleV3->nom);
        $this->assertSame(40, $salleV3->capacite);
        $this->assertFalse($salleV3->active);
    }

    public function testModifierSalleInexistanteEchoue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("La salle avec l'identifiant 99999 n'existe pas.");

        $dto = $this->builder
            ->setNom('Salle Inconnue')
            ->setBatiment('Bâtiment X')
            ->setCapacite(10)
            ->setType('cours')
            ->setActive(true)
            ->build();

        $this->salleService->save($dto, 99999);
    }

    public function testGetAllRetourneToutesLesSalles(): void
    {
        $dto1 = $this->builder->setNom('Salle 1')->setBatiment('Bat 1')->setCapacite(20)->setType('cours')->build();
        $dto2 = $this->builder->setNom('Salle 2')->setBatiment('Bat 2')->setCapacite(30)->setType('cours')->build();

        $this->salleService->save($dto1);
        $this->salleService->save($dto2);

        $toutes = $this->salleService->getAll();
        $this->assertCount(2, $toutes);
    }

    public function testGetByIdRetourneSalleOuNull(): void
    {
        $dto = $this->builder->setNom('Salle Unique')->setBatiment('Bat U')->setCapacite(15)->setType('cours')->build();
        $salle = $this->salleService->save($dto);

        $trouvee = $this->salleService->getById($salle->id);
        $this->assertNotNull($trouvee);
        $this->assertSame('Salle Unique', $trouvee->nom);

        $nonTrouvee = $this->salleService->getById(99999);
        $this->assertNull($nonTrouvee);
    }

    public function testToggleActive(): void
    {
        $dto = $this->builder->setNom('Salle Active')->setBatiment('Bat')->setCapacite(20)->setType('cours')->setActive(true)->build();
        $salle = $this->salleService->save($dto);

        $this->assertTrue($salle->active);

        // Désactivation
        $resultat = $this->salleService->toggleActive($salle->id);
        $this->assertTrue($resultat);
        $this->assertFalse($this->salleRepository->findById($salle->id)->active);

        // Réactivation
        $this->salleService->toggleActive($salle->id);
        $this->assertTrue($this->salleRepository->findById($salle->id)->active);
    }

    public function testDeleteSalleSupprimeSalleExistante(): void
    {
        $dto = $this->builder->setNom('Salle A Supprimer')->setBatiment('Bat D')->setCapacite(25)->setType('cours')->build();
        $salle = $this->salleService->save($dto);

        $this->assertNotNull($this->salleService->getById($salle->id));

        $resultat = $this->salleService->delete($salle->id);
        $this->assertTrue($resultat);
        $this->assertNull($this->salleService->getById($salle->id));
    }

    public function testDeleteSalleInexistanteRetourneFalse(): void
    {
        $resultat = $this->salleService->delete(99999);
        $this->assertFalse($resultat);
    }
}
