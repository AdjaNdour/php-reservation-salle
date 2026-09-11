<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Model\Reservation;
use App\Model\Salle;
use App\Model\Utilisateur;
use App\Repository\EloquentReservationRepository;
use App\Repository\EloquentSalleRepository;
use App\Repository\EloquentUtilisateurRepository;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class EloquentIntegrationTest extends TestCase
{
    private EloquentSalleRepository $salleRepo;
    private EloquentReservationRepository $resRepo;
    private EloquentUtilisateurRepository $userRepo;

    public static function setUpBeforeClass(): void
    {
        try {
            // Initialiser la connexion Eloquent via le container
            $builder = new \DI\ContainerBuilder();
            $builder->useAutowiring(true);
            $builder->addDefinitions(dirname(__DIR__, 2) . '/config/container.php');
            $container = $builder->build();
            $container->get(\Illuminate\Database\Capsule\Manager::class);
        } catch (\Throwable $e) {
            self::markTestSkipped("Base de données non accessible pour les tests d'intégration : " . $e->getMessage());
        }
    }

    protected function setUp(): void
    {
        $this->salleRepo = new EloquentSalleRepository();
        $this->resRepo = new EloquentReservationRepository();
        $this->userRepo = new EloquentUtilisateurRepository();
    }

    /**
     * 1. Test de création d'une salle avec Eloquent et vérification de persistance.
     */
    public function testCreationSalleAvecEloquent(): void
    {
        $salle = new Salle([
            'nom'      => 'Salle Intégration Eloquent',
            'batiment' => 'Bâtiment Test',
            'capacite' => 55,
            'type'     => 'laboratoire',
            'active'   => true,
        ]);

        $savedSalle = $this->salleRepo->save($salle);

        $this->assertNotNull($savedSalle->id);
        $this->assertGreaterThan(0, $savedSalle->id);

        $retrieved = $this->salleRepo->findById($savedSalle->id);
        $this->assertNotNull($retrieved);
        $this->assertSame('Salle Intégration Eloquent', $retrieved->nom);
        $this->assertSame(55, $retrieved->capacite);
        $this->assertTrue($retrieved->active);

        // Nettoyage
        $savedSalle->delete();
    }

    /**
     * 2. Test de la relation salle/réservations.
     */
    public function testRelationSalleReservations(): void
    {
        $salle = $this->salleRepo->save(new Salle([
            'nom'      => 'Salle Test Relation',
            'batiment' => 'Bâtiment R',
            'capacite' => 25,
            'type'     => 'cours',
            'active'   => true,
        ]));

        $reservation = $this->resRepo->save(new Reservation([
            'salle_id'    => $salle->id,
            'responsable' => 'Mariama Sarr',
            'email'       => 'mariama@universite.sn',
            'motif'       => 'TP Algorithmique',
            'date_debut'  => '2026-10-15 08:00:00',
            'date_fin'    => '2026-10-15 10:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]));

        // Vérification HasMany : $salle->reservations
        $reservations = $salle->reservations;
        $this->assertCount(1, $reservations);
        $this->assertSame('Mariama Sarr', $reservations->first()->responsable);

        // Vérification BelongsTo : $reservation->salle
        $this->assertSame($salle->nom, $reservation->salle->nom);

        // Nettoyage
        $reservation->delete();
        $salle->delete();
    }

    /**
     * 3. Test de la recherche de chevauchement via le repository Eloquent.
     */
    public function testRechercheChevauchement(): void
    {
        $salle = $this->salleRepo->save(new Salle([
            'nom'      => 'Salle Test Conflit',
            'batiment' => 'Bâtiment C',
            'capacite' => 30,
            'type'     => 'reunion',
            'active'   => true,
        ]));

        // Réservation existante : 10h00 -> 12h00
        $res = $this->resRepo->save(new Reservation([
            'salle_id'    => $salle->id,
            'responsable' => 'Alioune Sall',
            'email'       => 'alioune@universite.sn',
            'motif'       => 'Point d\'étape',
            'date_debut'  => '2026-10-20 10:00:00',
            'date_fin'    => '2026-10-20 12:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]));

        // Recherche d'un créneau qui chevauche (11h00 -> 13h00)
        $debutChevauchant = new DateTimeImmutable('2026-10-20 11:00:00');
        $finChevauchante = new DateTimeImmutable('2026-10-20 13:00:00');

        $conflit = $this->resRepo->findConflictingReservation($salle->id, $debutChevauchant, $finChevauchante);
        $this->assertNotNull($conflit);
        $this->assertSame($res->id, $conflit->id);

        // Recherche d'un créneau adjacent non chevauchant (12h00 -> 14h00)
        $debutAdjacent = new DateTimeImmutable('2026-10-20 12:00:00');
        $finAdjacente = new DateTimeImmutable('2026-10-20 14:00:00');

        $nonConflit = $this->resRepo->findConflictingReservation($salle->id, $debutAdjacent, $finAdjacente);
        $this->assertNull($nonConflit);

        // Nettoyage
        $res->delete();
        $salle->delete();
    }

    /**
     * 4. Test de l'annulation d'une réservation.
     */
    public function testAnnulationReservation(): void
    {
        $salle = $this->salleRepo->save(new Salle([
            'nom'      => 'Salle Test Annulation',
            'batiment' => 'Bâtiment A',
            'capacite' => 15,
            'type'     => 'cours',
            'active'   => true,
        ]));

        $res = $this->resRepo->save(new Reservation([
            'salle_id'    => $salle->id,
            'responsable' => 'Modou Fall',
            'email'       => 'modou@universite.sn',
            'motif'       => 'Séminaire',
            'date_debut'  => '2026-11-01 14:00:00',
            'date_fin'    => '2026-11-01 16:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]));

        $this->assertTrue($res->estConfirmee());

        // Annulation via le repository
        $succes = $this->resRepo->cancel($res->id);
        $this->assertTrue($succes);

        $resAnnulee = $this->resRepo->findById($res->id);
        $this->assertNotNull($resAnnulee);
        $this->assertSame(Reservation::STATUT_ANNULEE, $resAnnulee->statut);
        $this->assertTrue($resAnnulee->estAnnulee());

        // Nettoyage
        $res->delete();
        $salle->delete();
    }

    /**
     * 5. Test de suppression d'une salle via EloquentSalleRepository.
     */
    public function testSuppressionSalleViaRepository(): void
    {
        $salle = $this->salleRepo->save(new Salle([
            'nom'      => 'Salle Test Suppression Repo',
            'batiment' => 'Bâtiment S',
            'capacite' => 45,
            'type'     => 'cours',
            'active'   => true,
        ]));

        $this->assertNotNull($this->salleRepo->findById($salle->id));

        $supprime = $this->salleRepo->delete($salle->id);
        $this->assertTrue($supprime);
        $this->assertNull($this->salleRepo->findById($salle->id));

        $supprimeInexistant = $this->salleRepo->delete(99999);
        $this->assertFalse($supprimeInexistant);
    }

    /**
     * 7. Test de persistance et recherche d'un utilisateur avec Eloquent.
     */
    public function testGestionUtilisateurViaRepository(): void
    {
        $testEmail = 'integration_test_' . uniqid() . '@univ.sn';
        $user = new Utilisateur([
            'nom'      => 'Utilisateur Test Intégration',
            'email'    => $testEmail,
            'password' => password_hash('motdepasse123', PASSWORD_BCRYPT),
        ]);

        $savedUser = $this->userRepo->save($user);
        $this->assertNotNull($savedUser->id);
        $this->assertGreaterThan(0, $savedUser->id);

        $retrievedByEmail = $this->userRepo->findByEmail($testEmail);
        $this->assertNotNull($retrievedByEmail);
        $this->assertSame('Utilisateur Test Intégration', $retrievedByEmail->nom);
        $this->assertTrue($retrievedByEmail->verifierMotDePasse('motdepasse123'));
        $this->assertFalse($retrievedByEmail->verifierMotDePasse('faux_mdp'));

        $retrievedById = $this->userRepo->findById($savedUser->id);
        $this->assertNotNull($retrievedById);
        $this->assertSame($testEmail, $retrievedById->email);

        // Nettoyage
        $deleted = $this->userRepo->delete($savedUser->id);
        $this->assertTrue($deleted);
        $this->assertNull($this->userRepo->findById($savedUser->id));
    }
}
