<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerReservationDTOBuilder;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Model\Reservation;
use App\Model\Salle;
use App\Service\CreerReservationService;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemoryReservationRepository;
use Tests\Unit\Doubles\InMemorySalleRepository;

class CreerReservationServiceTest extends TestCase
{
    private InMemorySalleRepository $salleRepository;
    private InMemoryReservationRepository $reservationRepository;
    private CreerReservationService $service;
    private CreerReservationDTOBuilder $builder;
    private Salle $salleActive;
    private Salle $salleInactive;

    protected function setUp(): void
    {
        $this->salleRepository = new InMemorySalleRepository();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->service = new CreerReservationService($this->salleRepository, $this->reservationRepository);
        $this->builder = new CreerReservationDTOBuilder();

        // Salle active pour les tests
        $this->salleActive = new Salle([
            'nom'      => 'Salle B12',
            'batiment' => 'Bâtiment B',
            'capacite' => 40,
            'type'     => 'cours',
            'active'   => true,
        ]);
        $this->salleRepository->save($this->salleActive);

        // Salle inactive
        $this->salleInactive = new Salle([
            'nom'      => 'Salle Rénovation',
            'batiment' => 'Bâtiment C',
            'capacite' => 20,
            'type'     => 'reunion',
            'active'   => false,
        ]);
        $this->salleRepository->save($this->salleInactive);
    }

    
    // 1. Test d'une réservation valide.
    
    public function testReservationValideEstAcceptee(): void
    {
        $demain = new \DateTimeImmutable('+1 day');
        $debut = $demain->setTime(10, 0);
        $fin = $demain->setTime(12, 0);

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa.ndiaye@universite.sn')
            ->setMotif('Cours d\'architecture logicielle')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame($this->salleActive->id, $reservation->salle_id);
        $this->assertSame('Awa Ndiaye', $reservation->responsable);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->statut);
    }

    public function testReservationChangeStatutSalleAInactive(): void
    {
        $demain = new \DateTimeImmutable('+1 day');
        $debut = $demain->setTime(10, 0);
        $fin = $demain->setTime(12, 0);

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa.ndiaye@universite.sn')
            ->setMotif('Cours de PHP POO')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $this->assertTrue($this->salleRepository->findById($this->salleActive->id)->estActive());

        $this->service->executer($dto);

        $salleApres = $this->salleRepository->findById($this->salleActive->id);
        $this->assertNotNull($salleApres);
        $this->assertFalse($salleApres->estActive());
        $this->assertFalse($salleApres->active);
    }

    
    // 2. Test avec salle inexistante.
    
    public function testSalleInexistanteEchoue(): void
    {
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("La salle demandée n'existe pas.");

        $demain = new \DateTimeImmutable('+1 day');
        $dto = $this->builder
            ->setSalleId(99999) // Inexistante
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa.ndiaye@universite.sn')
            ->setMotif('Soutenance de mémoire')
            ->setDateDebut($demain->setTime(14, 0))
            ->setDateFin($demain->setTime(16, 0))
            ->build();

        $this->service->executer($dto);
    }

    
    // 3. Test avec salle inactive.
    
    public function testSalleInactiveEchoue(): void
    {
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("Cette salle ne peut pas être réservée car elle est inactive.");

        $demain = new \DateTimeImmutable('+1 day');
        $dto = $this->builder
            ->setSalleId($this->salleInactive->id)
            ->setResponsable('Adja Ndour')
            ->setEmail('adja@universite.sn')
            ->setMotif('Réunion d\'équipe pédagogique')
            ->setDateDebut($demain->setTime(14, 0))
            ->setDateFin($demain->setTime(16, 0))
            ->build();

        $this->service->executer($dto);
    }

    
    // 4. Test avec date de fin antérieure ou égale au début.
    
    public function testDateFinAnterieureAuDebutEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de début doit obligatoirement précéder la date de fin.");

        $demain = new \DateTimeImmutable('+1 day');
        $debut = $demain->setTime(14, 0);
        $fin = $demain->setTime(12, 0); // Antérieur

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa@universite.sn')
            ->setMotif('Séance de révision')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $this->service->executer($dto);
    }

    public function testDateFinEgaleAuDebutEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de début doit obligatoirement précéder la date de fin.");

        $demain = new \DateTimeImmutable('+1 day');
        $debut = $demain->setTime(14, 0);
        $fin = $demain->setTime(14, 0); // Égale

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa@universite.sn')
            ->setMotif('Séance instantanée')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $this->service->executer($dto);
    }

    
    // 5. Test d'une durée supérieure à quatre heures.
    
    public function testDureeSuperieureAQuatreHeuresEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("Une réservation ne peut pas dépasser quatre heures.");

        $demain = new \DateTimeImmutable('+1 day');
        $debut = $demain->setTime(8, 0);
        $fin = $demain->setTime(14, 0); // 6 heures

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa@universite.sn')
            ->setMotif('Journée d\'intégration')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $this->service->executer($dto);
    }

    public function testDureeExacteDeQuatreHeuresEstAcceptee(): void
    {
        $demain = new \DateTimeImmutable('+1 day');
        $debut = $demain->setTime(8, 0);
        $fin = $demain->setTime(12, 0); // Exactement 4 heures

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa@universite.sn')
            ->setMotif('Session intensive de 4 heures')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->statut);
    }

    
    // 6. Test d'une date passée.
    
    public function testDatePasseeEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de réservation doit débuter dans le futur.");

        $hier = new \DateTimeImmutable('-1 day');
        $debut = $hier->setTime(10, 0);
        $fin = $hier->setTime(12, 0);

        $dto = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa@universite.sn')
            ->setMotif('Cours passé')
            ->setDateDebut($debut)
            ->setDateFin($fin)
            ->build();

        $this->service->executer($dto);
    }

    
    // 7. Test de conflit (chevauchement) avec une réservation existante.
    
    public function testConflitAvecReservationExistanteEchoue(): void
    {
        $demain = new \DateTimeImmutable('+1 day');

        // Réservation existante : 10h00 -> 12h00
        $existante = new Reservation([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Moussa Diop',
            'email'       => 'moussa@universite.sn',
            'motif'       => 'Conférence introductive',
            'date_debut'  => $demain->setTime(10, 0),
            'date_fin'    => $demain->setTime(12, 0),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $this->reservationRepository->save($existante);

        // Nouvelle demande en conflit : 11h30 -> 13h00
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("La salle est indisponible pendant cette période.");

        $dtoConflit = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Fatou Ba')
            ->setEmail('fatou@universite.sn')
            ->setMotif('Atelier design pattern')
            ->setDateDebut($demain->setTime(11, 30))
            ->setDateFin($demain->setTime(13, 0))
            ->build();

        $this->service->executer($dtoConflit);
    }

    public function testConflitChevauchementDebutEchoue(): void
    {
        $demain = new \DateTimeImmutable('+1 day');

        $existante = new Reservation([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Moussa Diop',
            'email'       => 'moussa@universite.sn',
            'motif'       => 'Conférence introductive',
            'date_debut'  => $demain->setTime(10, 0),
            'date_fin'    => $demain->setTime(12, 0),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $this->reservationRepository->save($existante);

        // Nouvelle demande qui commence avant et se termine pendant l'existante : 09h30 -> 11h00
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("La salle est indisponible pendant cette période.");

        $dtoConflit = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Babacar Ndiaye')
            ->setEmail('babacar@universite.sn')
            ->setMotif('TP Base de données')
            ->setDateDebut($demain->setTime(9, 30))
            ->setDateFin($demain->setTime(11, 0))
            ->build();

        $this->service->executer($dtoConflit);
    }

    public function testConflitEnglobantEchoue(): void
    {
        $demain = new \DateTimeImmutable('+1 day');

        $existante = new Reservation([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Moussa Diop',
            'email'       => 'moussa@universite.sn',
            'motif'       => 'Conférence introductive',
            'date_debut'  => $demain->setTime(10, 0),
            'date_fin'    => $demain->setTime(11, 0),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $this->reservationRepository->save($existante);

        // Nouvelle demande qui englobe entièrement l'existante : 09h30 -> 12h30
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("La salle est indisponible pendant cette période.");

        $dtoConflit = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Sokhna Seck')
            ->setEmail('sokhna@universite.sn')
            ->setMotif('Grand Séminaire')
            ->setDateDebut($demain->setTime(9, 30))
            ->setDateFin($demain->setTime(12, 30))
            ->build();

        $this->service->executer($dtoConflit);
    }

    
    // 8. Test de réservations voisines sans chevauchement (10h-12h puis 12h-14h).
    
    public function testReservationVoisineSansChevauchementEstAcceptee(): void
    {
        $demain = new \DateTimeImmutable('+1 day');

        // Réservation 1 : 10h00 -> 12h00
        $res1 = new Reservation([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Moussa Diop',
            'email'       => 'moussa@universite.sn',
            'motif'       => 'Cours Réseaux',
            'date_debut'  => $demain->setTime(10, 0),
            'date_fin'    => $demain->setTime(12, 0),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $this->reservationRepository->save($res1);

        // Réservation 2 voisine : 12h00 -> 14h00 (doit réussir sans conflit)
        $dtoVoisine = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Awa Ndiaye')
            ->setEmail('awa.ndiaye@universite.sn')
            ->setMotif('Cours Génie Logiciel')
            ->setDateDebut($demain->setTime(12, 0))
            ->setDateFin($demain->setTime(14, 0))
            ->build();

        $res2 = $this->service->executer($dtoVoisine);

        $this->assertNotNull($res2->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $res2->statut);
    }

    public function testReservationVoisinePrecedenteSansChevauchementEstAcceptee(): void
    {
        $demain = new \DateTimeImmutable('+1 day');

        // Réservation existante : 10h00 -> 12h00
        $res1 = new Reservation([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Moussa Diop',
            'email'       => 'moussa@universite.sn',
            'motif'       => 'Cours Réseaux',
            'date_debut'  => $demain->setTime(10, 0),
            'date_fin'    => $demain->setTime(12, 0),
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $this->reservationRepository->save($res1);

        // Réservation voisine précédente : 08h00 -> 10h00 (doit réussir sans conflit)
        $dtoPrecedente = $this->builder
            ->setSalleId($this->salleActive->id)
            ->setResponsable('Cheikh Fall')
            ->setEmail('cheikh@universite.sn')
            ->setMotif('Cours Mathématiques')
            ->setDateDebut($demain->setTime(8, 0))
            ->setDateFin($demain->setTime(10, 0))
            ->build();

        $res2 = $this->service->executer($dtoPrecedente);

        $this->assertNotNull($res2->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $res2->statut);
    }

    // 9. Test de création via builder fromArray
    public function testCreationReservationViaBuilderFromArray(): void
    {
        $demain = new \DateTimeImmutable('+2 days');
        $dto = $this->builder->fromArray([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Ibrahima Diallo',
            'email'       => 'ibrahima@universite.sn',
            'motif'       => 'Examen final',
            'date_debut'  => $demain->setTime(8, 0),
            'date_fin'    => $demain->setTime(10, 0),
        ])->build();

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame('Ibrahima Diallo', $reservation->responsable);
    }
}
