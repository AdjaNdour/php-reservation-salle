<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreerReservationDTOBuilder;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Service\AnnulerReservationService;
use App\Service\CreerReservationService;
use App\Service\InterfaceAuthService;
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
        private CreerReservationDTOBuilder $builderReservation,
        private ?InterfaceAuthService $authService = null
    ) {}

    public function index(): void
    {
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 8;

        $criteria = [
            'salle_id'    => $_GET['salle_id'] ?? '',
            'statut'      => $_GET['statut'] ?? '',
            'responsable' => $_GET['responsable'] ?? '',
            'date_debut'  => $_GET['date_debut'] ?? '',
            'date_fin'    => $_GET['date_fin'] ?? '',
        ];

        $activeCriteria = array_filter($criteria, fn ($v) => $v !== '' && $v !== null);

        $paginator = $this->reservationsService->getPaginated($page, $perPage, $activeCriteria);
        $salles = $this->salleService->getAll();

        $this->view->render('reservation/index', [
            'titre'           => 'Gestion des réservations',
            'reservations'    => $paginator->getItems(),
            'paginator'       => $paginator,
            'salles'          => $salles,
            'selectedSalleId' => !empty($criteria['salle_id']) ? (int) $criteria['salle_id'] : null,
            'filters'         => $criteria,
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

        // Pré-remplissage avec les informations du responsable connecté
        $user = $this->authService?->getUtilisateurConnecte();
        $nom = $user?->nom ?? ($_SESSION['user']['nom'] ?? '');
        $email = $user?->email ?? ($_SESSION['user']['email'] ?? '');

        $this->view->render('reservation/form', [
            'titre'  => 'Créer une réservation',
            'salles' => $salles,
            'errors' => [],
            'data'   => [
                'salle_id'    => $salleId,
                'responsable' => $nom,
                'email'       => $email,
            ],
        ]);
    }

    public function store(): void
    {
        $data = $_POST;

        // Si l'utilisateur est authentifié et que les champs sont omis, on utilise ses identifiants
        $user = $this->authService?->getUtilisateurConnecte();
        if (empty($data['responsable']) && $user !== null) {
            $data['responsable'] = $user->nom;
        }
        if (empty($data['email']) && $user !== null) {
            $data['email'] = $user->email;
        }

        $validationResult = $this->validator->validate($data);

        if (!$validationResult->isValid()) {
            http_response_code(422);
            $salles = $this->salleService->getAll();
            $this->view->render('reservation/form', [
                'titre'  => 'Créer une réservation',
                'salles' => $salles,
                'errors' => $validationResult->errors(),
                'data'   => $data,
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
            if (!defined('PHPUNIT_RUNNING')) { exit; }
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
            if (!defined('PHPUNIT_RUNNING')) { exit; }
        } catch (ReservationIntrouvableException $e) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Réservation introuvable',
                'message' => $e->getMessage(),
            ]);
        }
    }
}
