<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerSalleDTOBuilder;
use App\Model\Salle;
use App\Service\Interface\ISalleService;
use App\Service\SalleService;
use App\Validation\SalleValidator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemorySalleRepository;

class SalleServiceTest extends TestCase
{
    private InMemorySalleRepository $salleRepository;
    private ISalleService $salleService;
    private SalleValidator $validator;

    protected function setUp(): void
    {
        $this->salleRepository = new InMemorySalleRepository();
        $this->salleService = new SalleService($this->salleRepository);
        $this->validator = new SalleValidator();
    }

    public function testCreerSalleAvecBuilderEtDTO(): void
    {
        CreerSalleDTOBuilder::setNom('Amphithéâtre B');
        CreerSalleDTOBuilder::setBatiment('Bâtiment Central');
        CreerSalleDTOBuilder::setCapacite(150);
        CreerSalleDTOBuilder::setType('cours');
        CreerSalleDTOBuilder::setActive(true);
        $dto = CreerSalleDTOBuilder::build($this->validator);

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
        CreerSalleDTOBuilder::fromArray([
            'nom'      => 'Laboratoire IA',
            'batiment' => 'Bâtiment Informatique',
            'capacite' => 30,
            'type'     => 'tp',
            'active'   => true,
        ]);
        $dto = CreerSalleDTOBuilder::build($this->validator);

        $salle = $this->salleService->save($dto);

        $this->assertSame('Laboratoire IA', $salle->nom);
        $this->assertSame(30, $salle->capacite);
    }

    public function testModifierSalleAvecDTO(): void
    {
        CreerSalleDTOBuilder::fromArray([
            'nom'      => 'Salle 101',
            'batiment' => 'Bâtiment A',
            'capacite' => 25,
            'type'     => 'cours',
            'active'   => true,
        ]);
        $dtoInitial = CreerSalleDTOBuilder::build($this->validator);

        $salle = $this->salleService->save($dtoInitial);

        CreerSalleDTOBuilder::fromArray([
            'nom'      => 'Salle 101 Renommée',
            'batiment' => 'Bâtiment A',
            'capacite' => 35,
            'type'     => 'reunion',
            'active'   => true,
        ]);
        $dtoModifie = CreerSalleDTOBuilder::build($this->validator);

        $salleModifiee = $this->salleService->save($dtoModifie, $salle->id);

        $this->assertSame($salle->id, $salleModifiee->id);
        $this->assertSame('Salle 101 Renommée', $salleModifiee->nom);
        $this->assertSame(35, $salleModifiee->capacite);
        $this->assertSame('reunion', $salleModifiee->type);

        // Test de la méthode update()
        CreerSalleDTOBuilder::fromArray([
            'nom'      => 'Salle 101 V3',
            'batiment' => 'Bâtiment A',
            'capacite' => 40,
            'type'     => 'reunion',
            'active'   => false,
        ]);
        $dtoUpdate = CreerSalleDTOBuilder::build($this->validator);

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

        CreerSalleDTOBuilder::fromArray([
            'nom'      => 'Salle Inconnue',
            'batiment' => 'Bâtiment X',
            'capacite' => 10,
            'type'     => 'cours',
            'active'   => true,
        ]);
        $dto = CreerSalleDTOBuilder::build($this->validator);

        $this->salleService->save($dto, 99999);
    }

    public function testGetAllRetourneToutesLesSalles(): void
    {
        CreerSalleDTOBuilder::fromArray(['nom' => 'Salle 1', 'batiment' => 'Bat 1', 'capacite' => 20, 'type' => 'cours', 'active' => true]);
        $dto1 = CreerSalleDTOBuilder::build($this->validator);
        CreerSalleDTOBuilder::fromArray(['nom' => 'Salle 2', 'batiment' => 'Bat 2', 'capacite' => 30, 'type' => 'cours', 'active' => true]);
        $dto2 = CreerSalleDTOBuilder::build($this->validator);

        $this->salleService->save($dto1);
        $this->salleService->save($dto2);

        $toutes = $this->salleService->getAll();
        $this->assertCount(2, $toutes);
    }

    public function testGetByIdRetourneSalleOuNull(): void
    {
        CreerSalleDTOBuilder::fromArray(['nom' => 'Salle Unique', 'batiment' => 'Bat U', 'capacite' => 15, 'type' => 'cours', 'active' => true]);
        $dto = CreerSalleDTOBuilder::build($this->validator);
        $salle = $this->salleService->save($dto);

        $trouvee = $this->salleService->getById($salle->id);
        $this->assertNotNull($trouvee);
        $this->assertSame('Salle Unique', $trouvee->nom);

        $nonTrouvee = $this->salleService->getById(99999);
        $this->assertNull($nonTrouvee);
    }

    public function testToggleActive(): void
    {
        CreerSalleDTOBuilder::fromArray(['nom' => 'Salle Active', 'batiment' => 'Bat', 'capacite' => 20, 'type' => 'cours', 'active' => true]);
        $dto = CreerSalleDTOBuilder::build($this->validator);
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
        CreerSalleDTOBuilder::fromArray(['nom' => 'Salle A Supprimer', 'batiment' => 'Bat D', 'capacite' => 25, 'type' => 'cours', 'active' => true]);
        $dto = CreerSalleDTOBuilder::build($this->validator);
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
