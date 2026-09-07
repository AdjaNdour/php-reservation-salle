<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerReservationDTO;
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
    private Salle $salleActive;
    private Salle $salleInactive;

    protected function setUp(): void
    {
        $this->salleRepository = new InMemorySalleRepository();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->service = new CreerReservationService($this->salleRepository, $this->reservationRepository);

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

        $dto = new CreerReservationDTO(
            salleId: $this->salleActive->id,
            responsable: 'Awa Ndiaye',
            email: 'awa.ndiaye@universite.sn',
            motif: 'Cours d\'architecture logicielle',
            dateDebut: $debut,
            dateFin: $fin
        );

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame($this->salleActive->id, $reservation->salle_id);
        $this->assertSame('Awa Ndiaye', $reservation->responsable);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->statut);
    }

    
    // 2. Test avec salle inexistante.
    
    public function testSalleInexistanteEchoue(): void
    {
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("La salle demandée n'existe pas.");

        $demain = new \DateTimeImmutable('+1 day');
        $dto = new CreerReservationDTO(
            salleId: 99999, // Inexistante
            responsable: 'Awa Ndiaye',
            email: 'awa.ndiaye@universite.sn',
            motif: 'Soutenance de mémoire',
            dateDebut: $demain->setTime(14, 0),
            dateFin: $demain->setTime(16, 0)
        );

        $this->service->executer($dto);
    }

    
    // 3. Test avec salle inactive.
    
    public function testSalleInactiveEchoue(): void
    {
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("Cette salle ne peut pas être réservée car elle est inactive.");

        $demain = new \DateTimeImmutable('+1 day');
        $dto = new CreerReservationDTO(
            salleId: $this->salleInactive->id,
            responsable: 'Adja Ndour',
            email: 'adja@universite.sn',
            motif: 'Réunion d\'équipe pédagogique',
            dateDebut: $demain->setTime(14, 0),
            dateFin: $demain->setTime(16, 0)
        );

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

        $dto = new CreerReservationDTO(
            salleId: $this->salleActive->id,
            responsable: 'Awa Ndiaye',
            email: 'awa@universite.sn',
            motif: 'Séance de révision',
            dateDebut: $debut,
            dateFin: $fin
        );

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

        $dto = new CreerReservationDTO(
            salleId: $this->salleActive->id,
            responsable: 'Awa Ndiaye',
            email: 'awa@universite.sn',
            motif: 'Journée d\'intégration',
            dateDebut: $debut,
            dateFin: $fin
        );

        $this->service->executer($dto);
    }

    
    // 6. Test d'une date passée.
    
    public function testDatePasseeEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de réservation doit débuter dans le futur.");

        $hier = new \DateTimeImmutable('-1 day');
        $debut = $hier->setTime(10, 0);
        $fin = $hier->setTime(12, 0);

        $dto = new CreerReservationDTO(
            salleId: $this->salleActive->id,
            responsable: 'Awa Ndiaye',
            email: 'awa@universite.sn',
            motif: 'Cours passé',
            dateDebut: $debut,
            dateFin: $fin
        );

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

        $dtoConflit = new CreerReservationDTO(
            salleId: $this->salleActive->id,
            responsable: 'Fatou Ba',
            email: 'fatou@universite.sn',
            motif: 'Atelier design pattern',
            dateDebut: $demain->setTime(11, 30),
            dateFin: $demain->setTime(13, 0)
        );

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
        $dtoVoisine = new CreerReservationDTO(
            salleId: $this->salleActive->id,
            responsable: 'Awa Ndiaye',
            email: 'awa.ndiaye@universite.sn',
            motif: 'Cours Génie Logiciel',
            dateDebut: $demain->setTime(12, 0),
            dateFin: $demain->setTime(14, 0)
        );

        $res2 = $this->service->executer($dtoVoisine);

        $this->assertNotNull($res2->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $res2->statut);
    }
}
