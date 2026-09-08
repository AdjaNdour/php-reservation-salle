<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreerReservationDTOBuilder;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Service\AnnulerReservationService;
use App\Service\CreerReservationService;
use App\Service\InterfaceReservationService;
use App\Service\InterfaceSalleService;
use App\Validation\ReservationValidator;
use App\View\ViewRenderer;

class ReservationController
{
    public function __construct(
        private InterfaceReservationService $reservationsService,
        private InterfaceSalleService $salleService,
        private ReservationValidator $validator,
        private CreerReservationService $creerService,
        private AnnulerReservationService $annulerService,
        private ViewRenderer $view,
        private CreerReservationDTOBuilder $builderReservation
    ) {

    }

    public function index(): void
    {
        $salleId = isset($_GET['salle_id']) && is_numeric($_GET['salle_id'])
            ? (int) $_GET['salle_id']
            : null;

        $reservations = $this->reservationsService->getAll($salleId);
        $salles = $this->salleService->getAll();

        $this->view->render('reservation/index', [
            'titre'           => 'Gestion des réservations',
            'reservations'    => $reservations,
            'salles'          => $salles,
            'selectedSalleId' => $salleId,
        ]);
    }

    public function show(int $id): void
    {
        $reservation = $this->reservationsService->getById($id);

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
        $salles = $this->salleService->getAll();
        $salleId = $_GET['salle_id'] ?? '';

        $this->view->render('reservation/form', [
            'titre'  => 'Créer une réservation',
            'salles' => $salles,
            'errors' => [],
            'data'   => [ 'salle_id' => $salleId ],
        ]);
    }

    public function store(): void
    {
        $data = $_POST;
        $validationResult = $this->validator->validate($data);
        
        if (!$validationResult->isValid()) {
            $salles = $this->salleService->getAll();
            $this->view->render('reservation/form', [
                'titre'  => 'Créer une réservation',
                'salles' => $salles,
                'errors' => $validationResult->errors(),
                'data' => $data,
            ]);
            return;
        }

        try {
            $validatedData = $validationResult->validatedData();
            $dto = $this->builderReservation
                ->setSalleId((int) $validatedData['salle_id'])
                ->setResponsable($validatedData['responsable'])
                ->setEmail($validatedData['email'])
                ->setMotif($validatedData['motif'])
                ->setDateDebut(new \DateTimeImmutable($validatedData['date_debut']))
                ->setDateFin(new \DateTimeImmutable($validatedData['date_fin']))
                ->build();

            $reservation = $this->creerService->executer($dto);

            $_SESSION['flash_success'] = "La réservation #{$reservation->id} a été confirmée avec succès.";
            header('Location: /reservations/' . $reservation->id);
            exit;
        } catch (SalleIndisponibleException|ReservationIntrouvableException $e) {
            http_response_code(422);
            $salles = $this->salleService->getAll();
            $errors = [];
            $message = $e->getMessage();

            if (str_contains($message, 'futur')) {
                $errors['date_debut'] = $message;
            } elseif (str_contains($message, 'précéder')) {
                $errors['date_debut'] = $message;
            } elseif (str_contains($message, 'quatre heures')) {
                $errors['date_fin'] = $message;
            } elseif (str_contains($message, 'inactive') || str_contains($message, 'n\'existe pas')) {
                $errors['salle_id'] = $message;
            } else {
                $errors['metier'] = $message;
            }

            $this->view->render('reservation/form', [
                'titre'        => 'Créer une réservation',
                'salles'       => $salles,
                'errors'       => $errors,
                'data'         => $data,
                'errorMessage' => $message,
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
