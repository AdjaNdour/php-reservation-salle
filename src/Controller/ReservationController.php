<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreerReservationDTO;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SalleRepositoryInterface;
use App\Service\AnnulerReservationService;
use App\Service\CreerReservationService;
use App\Validation\ReservationValidator;
use App\View\ViewRenderer;

class ReservationController
{
    public function __construct(
        private ReservationRepositoryInterface $reservations,
        private SalleRepositoryInterface $salles,
        private ReservationValidator $validator,
        private CreerReservationService $creerService,
        private AnnulerReservationService $annulerService,
        private ViewRenderer $view
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $salleId = isset($_GET['salle_id']) && is_numeric($_GET['salle_id'])
            ? (int) $_GET['salle_id']
            : null;

        $reservations = $this->reservations->findAll($salleId);
        $salles = $this->salles->findAll();

        $this->view->render('reservation/index', [
            'titre'           => 'Gestion des réservations',
            'reservations'    => $reservations,
            'salles'          => $salles,
            'selectedSalleId' => $salleId,
        ]);
    }

    public function show(int $id): void
    {
        $reservation = $this->reservations->findById($id);

        if ($reservation === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Réservation introuvable',
                'message' => "La réservation demandée (ID: {$id}) n'existe pas.",
            ]);
            return;
        }

        $this->view->render('reservation/show', [
            'titre'       => 'Détail de la réservation #' . $reservation->id,
            'reservation' => $reservation,
        ]);
    }

    public function create(): void
    {
        $salles = $this->salles->findAll();
        $salleId = $_GET['salle_id'] ?? '';

        $this->view->render('reservation/form', [
            'titre'  => 'Créer une réservation',
            'salles' => $salles,
            'errors' => [],
            'data'   => [
                'salle_id' => $salleId,
            ],
        ]);
    }

    public function store(): void
    {        
        $data = $_POST;
        $validationResult = $this->validator->validate($data);
        
        if (!$validationResult->isValid()) {
            http_response_code(422);
            $salles = $this->salles->findAll();
            $this->view->render('reservation/form', [
                'titre'  => 'Créer une réservation',
                'salles' => $salles,
                'errors' => $validationResult->errors(),
                'data'   => $data,
            ]);
            return;
        }

        try {            
            $dto = CreerReservationDTO::fromArray($validationResult->validatedData());
            $reservation = $this->creerService->executer($dto);
            $_SESSION['flash_success'] = "La réservation #{$reservation->id} a été confirmée avec succès.";
            header('Location: /reservations/' . $reservation->id);
            exit;
        } catch (SalleIndisponibleException $e) {
            http_response_code(422);
            $salles = $this->salles->findAll();
            $this->view->render('reservation/form', [
                'titre'        => 'Créer une réservation',
                'salles'       => $salles,
                'errors'       => ['metier' => $e->getMessage()],
                'data'         => $data,
                'errorMessage' => $e->getMessage(),
            ]);
        }
    }

    public function cancel(int $id): void
    {
        try {
            $reservation = $this->annulerService->executer($id);
            $_SESSION['flash_success'] = "La réservation #{$reservation->id} a été annulée avec succès.";
            header('Location: /reservations/' . $id);
            exit;
        } catch (ReservationIntrouvableException $e) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Réservation introuvable',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
