<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Model\Reservation;
use App\Model\Salle;
use App\Service\DashboardService;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Doubles\InMemoryReservationRepository;
use Tests\Unit\Doubles\InMemorySalleRepository;

class DashboardServiceTest extends TestCase
{
    private InMemorySalleRepository $salleRepo;
    private InMemoryReservationRepository $resRepo;
    private DashboardService $service;

    protected function setUp(): void
    {
        $this->salleRepo = new InMemorySalleRepository();
        $this->resRepo = new InMemoryReservationRepository();
        $this->service = new DashboardService($this->salleRepo, $this->resRepo);
    }

    public function testStatistiquesGlobales(): void
    {
        $this->salleRepo->save(new Salle(['nom' => 'Salle 1', 'capacite' => 30, 'type' => 'cours', 'active' => true]));
        $this->salleRepo->save(new Salle(['nom' => 'Salle 2', 'capacite' => 50, 'type' => 'amphitheatre', 'active' => false]));

        $this->resRepo->save(new Reservation([
            'salle_id'    => 1,
            'responsable' => 'User 1',
            'statut'      => Reservation::STATUT_CONFIRMEE,
            'date_debut'  => '2026-10-01 08:00:00',
            'date_fin'    => '2026-10-01 10:00:00',
        ]));

        $this->resRepo->save(new Reservation([
            'salle_id'    => 1,
            'responsable' => 'User 2',
            'statut'      => Reservation::STATUT_ANNULEE,
            'date_debut'  => '2026-10-02 08:00:00',
            'date_fin'    => '2026-10-02 10:00:00',
        ]));

        $stats = $this->service->getGlobalStatistics();

        $this->assertSame(2, $stats['total_salles']);
        $this->assertSame(1, $stats['salles_actives']);
        $this->assertSame(1, $stats['salles_inactives']);
        $this->assertSame(2, $stats['total_reservations']);
        $this->assertSame(1, $stats['reservations_confirmees']);
        $this->assertSame(1, $stats['reservations_annulees']);
        $this->assertSame(50.0, $stats['taux_confirmation']);
    }

    public function testTopSallesLesPlusReservees(): void
    {
        $this->resRepo->save(new Reservation(['salle_id' => 1, 'statut' => Reservation::STATUT_CONFIRMEE]));
        $this->resRepo->save(new Reservation(['salle_id' => 1, 'statut' => Reservation::STATUT_CONFIRMEE]));
        $this->resRepo->save(new Reservation(['salle_id' => 2, 'statut' => Reservation::STATUT_CONFIRMEE]));

        $top = $this->service->getMostUsedSalles(5);

        $this->assertNotEmpty($top);
        $this->assertSame(1, $top[0]['salle_id']);
        $this->assertSame(2, $top[0]['reservations_confirmees']);
    }
}
