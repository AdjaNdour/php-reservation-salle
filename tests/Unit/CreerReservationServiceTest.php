<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerReservationDTO;
use App\DTO\CreerReservationDTOBuilder;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Model\Reservation;
use App\Model\Salle;
use App\Service\CreerReservationService;
use App\Validation\ReservationValidator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemoryReservationRepository;
use Tests\Unit\Doubles\InMemorySalleRepository;

class CreerReservationServiceTest extends TestCase
{
    private InMemorySalleRepository $salleRepository;
    private InMemoryReservationRepository $reservationRepository;
    private CreerReservationService $service;
    private ReservationValidator $validator;
    private Salle $salleActive;
    private Salle $salleInactive;

    protected function setUp(): void
    {
        $this->salleRepository = new InMemorySalleRepository();
        $this->reservationRepository = new InMemoryReservationRepository();
        $this->service = new CreerReservationService($this->salleRepository, $this->reservationRepository);
        $this->validator = new ReservationValidator();

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

    private function creerDto(
        int $salleId,
        string $responsable,
        string $email,
        string $motif,
        DateTimeImmutable $debut,
        DateTimeImmutable $fin
    ): CreerReservationDTO {
        CreerReservationDTOBuilder::setSalleId($salleId);
        CreerReservationDTOBuilder::setResponsable($responsable);
        CreerReservationDTOBuilder::setEmail($email);
        CreerReservationDTOBuilder::setMotif($motif);
        CreerReservationDTOBuilder::setDateDebut($debut);
        CreerReservationDTOBuilder::setDateFin($fin);

        return CreerReservationDTOBuilder::build($this->validator);
    }

    // 1. Test d'une réservation valide.

    public function testReservationValideEstAcceptee(): void
    {
        $demain = new DateTimeImmutable('+1 day');
        $debut = $demain->setTime(10, 0);
        $fin = $demain->setTime(12, 0);

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa.ndiaye@universite.sn',
            'Cours d\'architecture logicielle',
            $debut,
            $fin
        );

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame($this->salleActive->id, $reservation->salle_id);
        $this->assertSame('Awa Ndiaye', $reservation->responsable);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->statut);
    }

    public function testReservationChangeStatutSalleAInactive(): void
    {
        $demain = new DateTimeImmutable('+1 day');
        $debut = $demain->setTime(10, 0);
        $fin = $demain->setTime(12, 0);

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa.ndiaye@universite.sn',
            'Cours de PHP POO',
            $debut,
            $fin
        );

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

        $demain = new DateTimeImmutable('+1 day');
        $dto = $this->creerDto(
            99999, // Inexistante
            'Awa Ndiaye',
            'awa.ndiaye@universite.sn',
            'Soutenance de mémoire',
            $demain->setTime(14, 0),
            $demain->setTime(16, 0)
        );

        $this->service->executer($dto);
    }

    // 3. Test avec salle inactive.

    public function testSalleInactiveEchoue(): void
    {
        $this->expectException(SalleIndisponibleException::class);
        $this->expectExceptionMessage("Cette salle ne peut pas être réservée car elle est inactive.");

        $demain = new DateTimeImmutable('+1 day');
        $dto = $this->creerDto(
            $this->salleInactive->id,
            'Adja Ndour',
            'adja@universite.sn',
            'Réunion d\'équipe pédagogique',
            $demain->setTime(14, 0),
            $demain->setTime(16, 0)
        );

        $this->service->executer($dto);
    }

    
    // 4. Test avec date de fin antérieure ou égale au début.

    public function testDateFinAnterieureAuDebutEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de début doit obligatoirement précéder la date de fin.");

        $demain = new DateTimeImmutable('+1 day');
        $debut = $demain->setTime(14, 0);
        $fin = $demain->setTime(12, 0); // Antérieur

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa@universite.sn',
            'Séance de révision',
            $debut,
            $fin
        );

        $this->service->executer($dto);
    }

    public function testDateFinEgaleAuDebutEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de début doit obligatoirement précéder la date de fin.");

        $demain = new DateTimeImmutable('+1 day');
        $debut = $demain->setTime(14, 0);
        $fin = $demain->setTime(14, 0); // Égale

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa@universite.sn',
            'Séance instantanée',
            $debut,
            $fin
        );

        $this->service->executer($dto);
    }

    // 5. Test d'une durée supérieure à quatre heures.

    public function testDureeSuperieureAQuatreHeuresEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("Une réservation ne peut pas dépasser quatre heures.");

        $demain = new DateTimeImmutable('+1 day');
        $debut = $demain->setTime(8, 0);
        $fin = $demain->setTime(14, 0); // 6 heures

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa@universite.sn',
            'Journée d\'intégration',
            $debut,
            $fin
        );

        $this->service->executer($dto);
    }

    public function testDureeExacteDeQuatreHeuresEstAcceptee(): void
    {
        $demain = new DateTimeImmutable('+1 day');
        $debut = $demain->setTime(8, 0);
        $fin = $demain->setTime(12, 0); // Exactement 4 heures

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa@universite.sn',
            'Session intensive de 4 heures',
            $debut,
            $fin
        );

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->statut);
    }

    // 6. Test d'une date passée.

    public function testDatePasseeEchoue(): void
    {
        $this->expectException(ReservationIntrouvableException::class);
        $this->expectExceptionMessage("La date de réservation doit débuter dans le futur.");

        $hier = new DateTimeImmutable('-1 day');
        $debut = $hier->setTime(10, 0);
        $fin = $hier->setTime(12, 0);

        $dto = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa@universite.sn',
            'Cours passé',
            $debut,
            $fin
        );

        $this->service->executer($dto);
    }

    // 7. Test de conflit (chevauchement) avec une réservation existante.

    public function testConflitAvecReservationExistanteEchoue(): void
    {
        $demain = new DateTimeImmutable('+1 day');

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

        $dtoConflit = $this->creerDto(
            $this->salleActive->id,
            'Fatou Ba',
            'fatou@universite.sn',
            'Atelier design pattern',
            $demain->setTime(11, 30),
            $demain->setTime(13, 0)
        );

        $this->service->executer($dtoConflit);
    }

    public function testConflitChevauchementDebutEchoue(): void
    {
        $demain = new DateTimeImmutable('+1 day');

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

        $dtoConflit = $this->creerDto(
            $this->salleActive->id,
            'Babacar Ndiaye',
            'babacar@universite.sn',
            'TP Base de données',
            $demain->setTime(9, 30),
            $demain->setTime(11, 0)
        );

        $this->service->executer($dtoConflit);
    }

    public function testConflitEnglobantEchoue(): void
    {
        $demain = new DateTimeImmutable('+1 day');

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

        $dtoConflit = $this->creerDto(
            $this->salleActive->id,
            'Sokhna Seck',
            'sokhna@universite.sn',
            'Grand Séminaire',
            $demain->setTime(9, 30),
            $demain->setTime(12, 30)
        );

        $this->service->executer($dtoConflit);
    }

    // 8. Test de réservations voisines sans chevauchement (10h-12h puis 12h-14h).

    public function testReservationVoisineSansChevauchementEstAcceptee(): void
    {
        $demain = new DateTimeImmutable('+1 day');

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
        $dtoVoisine = $this->creerDto(
            $this->salleActive->id,
            'Awa Ndiaye',
            'awa.ndiaye@universite.sn',
            'Cours Génie Logiciel',
            $demain->setTime(12, 0),
            $demain->setTime(14, 0)
        );

        $res2 = $this->service->executer($dtoVoisine);

        $this->assertNotNull($res2->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $res2->statut);
    }

    public function testReservationVoisinePrecedenteSansChevauchementEstAcceptee(): void
    {
        $demain = new DateTimeImmutable('+1 day');

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
        $dtoPrecedente = $this->creerDto(
            $this->salleActive->id,
            'Cheikh Fall',
            'cheikh@universite.sn',
            'Cours Mathématiques',
            $demain->setTime(8, 0),
            $demain->setTime(10, 0)
        );

        $res2 = $this->service->executer($dtoPrecedente);

        $this->assertNotNull($res2->id);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $res2->statut);
    }

    // 9. Test de création via builder fromArray
    public function testCreationReservationViaBuilderFromArray(): void
    {
        $demain = new DateTimeImmutable('+2 days');
        CreerReservationDTOBuilder::fromArray([
            'salle_id'    => $this->salleActive->id,
            'responsable' => 'Ibrahima Diallo',
            'email'       => 'ibrahima@universite.sn',
            'motif'       => 'Examen final',
            'date_debut'  => $demain->setTime(8, 0)->format('Y-m-d H:i:s'),
            'date_fin'    => $demain->setTime(10, 0)->format('Y-m-d H:i:s'),
        ]);
        $dto = CreerReservationDTOBuilder::build($this->validator);

        $reservation = $this->service->executer($dto);

        $this->assertNotNull($reservation->id);
        $this->assertSame('Ibrahima Diallo', $reservation->responsable);
    }
}
