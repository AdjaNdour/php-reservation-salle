<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTO\CreerReservationDTOBuilder;
use App\Model\Reservation;
use App\Model\Salle;
use App\Service\CreerReservationService;
use App\Service\InterfaceReservationService;
use App\Service\ReservationService;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemoryReservationRepository;
use Tests\Unit\Doubles\InMemorySalleRepository;

class ReservationServiceTest extends TestCase
{
    private InMemorySalleRepository $salleRepo;
    private InMemoryReservationRepository $reservationRepo;
    private CreerReservationService $creerService;
    private InterfaceReservationService $reservationService;
    private CreerReservationDTOBuilder $builder;
    private Salle $salle;

    protected function setUp(): void
    {
        $this->salleRepo = new InMemorySalleRepository();
        $this->reservationRepo = new InMemoryReservationRepository();
        $this->creerService = new CreerReservationService($this->salleRepo, $this->reservationRepo);
        $this->reservationService = new ReservationService($this->reservationRepo, $this->creerService);
        $this->builder = new CreerReservationDTOBuilder();

        $this->salle = new Salle([
            'nom'      => 'Salle B12',
            'batiment' => 'Bâtiment B',
            'capacite' => 40,
            'type'     => 'cours',
            'active'   => true,
        ]);
        $this->salleRepo->save($this->salle);
    }

    public function testGetAllRetourneToutesLesReservations(): void
    {
        $res1 = new Reservation([
            'salle_id'    => $this->salle->id,
            'responsable' => 'Professeur A',
            'email'       => 'profa@univ.sn',
            'motif'       => 'Cours 1',
            'date_debut'  => '2026-10-01 08:00:00',
            'date_fin'    => '2026-10-01 10:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $res2 = new Reservation([
            'salle_id'    => $this->salle->id,
            'responsable' => 'Professeur B',
            'email'       => 'profb@univ.sn',
            'motif'       => 'Cours 2',
            'date_debut'  => '2026-10-01 10:00:00',
            'date_fin'    => '2026-10-01 12:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->reservationRepo->save($res1);
        $this->reservationRepo->save($res2);

        $toutes = $this->reservationService->getAll();
        $this->assertCount(2, $toutes);
    }

    public function testGetAllFiltreParSalle(): void
    {
        $salle2 = new Salle([
            'nom'      => 'Salle C10',
            'batiment' => 'Bâtiment C',
            'capacite' => 20,
            'type'     => 'reunion',
            'active'   => true,
        ]);
        $this->salleRepo->save($salle2);

        $res1 = new Reservation([
            'salle_id'    => $this->salle->id,
            'responsable' => 'Professeur A',
            'email'       => 'profa@univ.sn',
            'motif'       => 'Cours 1',
            'date_debut'  => '2026-10-01 08:00:00',
            'date_fin'    => '2026-10-01 10:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $res2 = new Reservation([
            'salle_id'    => $salle2->id,
            'responsable' => 'Professeur B',
            'email'       => 'profb@univ.sn',
            'motif'       => 'Cours 2',
            'date_debut'  => '2026-10-01 10:00:00',
            'date_fin'    => '2026-10-01 12:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);

        $this->reservationRepo->save($res1);
        $this->reservationRepo->save($res2);

        $reservationsSalle1 = $this->reservationService->getAll($this->salle->id);
        $this->assertCount(1, $reservationsSalle1);
        $this->assertSame('Professeur A', $reservationsSalle1[0]->responsable);
    }

    public function testGetByIdRetourneReservation(): void
    {
        $res = new Reservation([
            'salle_id'    => $this->salle->id,
            'responsable' => 'Awa Ndiaye',
            'email'       => 'awa@univ.sn',
            'motif'       => 'Recherche',
            'date_debut'  => '2026-10-02 14:00:00',
            'date_fin'    => '2026-10-02 16:00:00',
            'statut'      => Reservation::STATUT_CONFIRMEE,
        ]);
        $saved = $this->reservationRepo->save($res);

        $trouvee = $this->reservationService->getById($saved->id);
        $this->assertNotNull($trouvee);
        $this->assertSame('Awa Ndiaye', $trouvee->responsable);

        $inconnue = $this->reservationService->getById(99999);
        $this->assertNull($inconnue);
    }

    public function testSaveAvecCreerReservationDTO(): void
    {
        $demain = new \DateTimeImmutable('+2 days');
        $dto = $this->builder
            ->setSalleId($this->salle->id)
            ->setResponsable('Moussa Sene')
            ->setEmail('moussa.sene@univ.sn')
            ->setMotif('Soutenance thèse')
            ->setDateDebut($demain->setTime(9, 0))
            ->setDateFin($demain->setTime(11, 0))
            ->build();

        $reservation = $this->reservationService->save($dto);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertNotNull($reservation->id);
        $this->assertSame('Moussa Sene', $reservation->responsable);
        $this->assertSame(Reservation::STATUT_CONFIRMEE, $reservation->statut);

        $enBase = $this->reservationRepo->findById($reservation->id);
        $this->assertNotNull($enBase);
        $this->assertSame('Moussa Sene', $enBase->responsable);
    }
}
