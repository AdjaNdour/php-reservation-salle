<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Validation\ReservationValidator;
use App\Validation\SalleValidator;
use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    private SalleValidator $salleValidator;
    private ReservationValidator $reservationValidator;

    protected function setUp(): void
    {
        $this->salleValidator = new SalleValidator();
        $this->reservationValidator = new ReservationValidator();
    }

    public function testCapaciteNegativeInvalide(): void
    {
        $result = $this->salleValidator->validate([
            'nom'      => 'Salle Test',
            'batiment' => 'Bâtiment B',
            'capacite' => -10,
            'type'     => 'cours',
            'active'   => true,
        ]);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('capacite', $result->errors());
        $this->assertStringContainsString('La capacité doit être un entier compris entre 1 et 1 000', $result->errors()['capacite']);
    }

    public function testTypeDeSalleInconnuInvalide(): void
    {
        $result = $this->salleValidator->validate([
            'nom'      => 'Salle Test',
            'batiment' => 'Bâtiment B',
            'capacite' => 30,
            'type'     => 'piscine_inconnue',
            'active'   => true,
        ]);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('type', $result->errors());
        $this->assertStringContainsString('Le type de salle est invalide', $result->errors()['type']);
    }

    public function testResponsableVideInvalide(): void
    {
        $result = $this->reservationValidator->validate([
            'salle_id'    => 1,
            'responsable' => '',
            'email'       => 'valide@universite.sn',
            'motif'       => 'Séance de cours magistral',
            'date_debut'  => '2026-09-10 10:00:00',
            'date_fin'    => '2026-09-10 12:00:00',
        ]);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('responsable', $result->errors());
        $this->assertStringContainsString('Le nom du responsable est obligatoire', $result->errors()['responsable']);
    }

    public function testAdresseElectroniqueInvalide(): void
    {
        $result = $this->reservationValidator->validate([
            'salle_id'    => 1,
            'responsable' => 'Awa Ndiaye',
            'email'       => 'email-invalide-sans-arobase',
            'motif'       => 'Séance de cours magistral',
            'date_debut'  => '2026-09-10 10:00:00',
            'date_fin'    => '2026-09-10 12:00:00',
        ]);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('email', $result->errors());
        $this->assertStringContainsString('L\'adresse électronique saisie est invalide', $result->errors()['email']);
    }

    public function testDateIncorrecteInvalide(): void
    {
        $result = $this->reservationValidator->validate([
            'salle_id'    => 1,
            'responsable' => 'Awa Ndiaye',
            'email'       => 'awa@universite.sn',
            'motif'       => 'Séance de cours magistral',
            'date_debut'  => 'pas-une-date',
            'date_fin'    => '2026-99-99 25:99:99',
        ]);

        $this->assertFalse($result->isValid());
        $this->assertArrayHasKey('date_debut', $result->errors());
        $this->assertArrayHasKey('date_fin', $result->errors());
        $this->assertStringContainsString('date valide', $result->errors()['date_debut']);
        $this->assertStringContainsString('date valide', $result->errors()['date_fin']);
    }
}
