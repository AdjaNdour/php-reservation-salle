<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreerReservationDTOBuilder;
use App\Exception\ReservationIntrouvableException;
use App\Exception\SalleIndisponibleException;
use App\Service\Interface\IAnnulerReservationService;
use App\Service\Interface\IAuthService;
use App\Service\Interface\ICreerReservationService;
use App\Service\Interface\IReservationService;
use App\Service\Interface\ISalleService;
use App\Validation\Interface\IReservationValidator;
use App\View\ViewRenderer;

class ReservationController extends Controller
{
    public function __construct(
        private IReservationService $reservationsService,
        private ISalleService $salleService,
        private IReservationValidator $validator,
        private ICreerReservationService $creerService,
        private IAnnulerReservationService $annulerService,
        ViewRenderer $view,
        private IAuthService $authService
    ) {
        parent::__construct($view);
    }

    public function index(): void
    {
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 5;

        $criteria = [
            'salle_id'    => $_GET['salle_id'] ?? '',
            'statut'      => $_GET['statut'] ?? '',
            'responsable' => $_GET['responsable'] ?? '',
            'date_debut'  => $_GET['date_debut'] ?? '',
            'date_fin'    => $_GET['date_fin'] ?? '',
        ];

        $activeCriteria = array_filter($criteria, fn($v) => $v !== '' && $v !== null);

        $paginator = $this->reservationsService->getPaginated($page, $perPage, $activeCriteria);
        $paginator->appends($activeCriteria);
        $salles = $this->salleService->getAll();

        $this->render('reservation/index', [
            'titre'           => 'Gestion des réservations',
            'reservations'    => $paginator->items(),
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
            $this->render('error/404', [
                'titre'   => 'Réservation introuvable',
                'message' => "La réservation demandée (ID: {$id}) n'existe pas.",
            ], 404);
            return;
        }

        $this->render('reservation/show', [
            'titre'       => 'Détail de la réservation #' . $reservation->id,
            'reservation' => $reservation,
        ]);
    }

    public function create(): void
    {
        $salles = $this->salleService->getAll();
        $salleId = $_GET['salle_id'] ?? '';

        $user = $this->authService?->getUtilisateurConnecte();
        $nom = $user?->nom ?? ($_SESSION['user']['nom'] ?? '');
        $email = $user?->email ?? ($_SESSION['user']['email'] ?? '');

        $this->render('reservation/form', [
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
            $salles = $this->salleService->getAll();
            $this->render('reservation/form', [
                'titre'  => 'Créer une réservation',
                'salles' => $salles,
                'errors' => $validationResult->errors(),
                'data'   => $data,
            ], 422);
            return;
        }

        try {
            CreerReservationDTOBuilder::fromArray($data);
            $dto = CreerReservationDTOBuilder::build($this->validator);

            $reservation = $this->creerService->executer($dto);

            $_SESSION['flash_success'] = "La réservation #{$reservation->id} a été confirmée avec succès.";
            header('Location: /reservations/' . $reservation->id);
            if (!defined('PHPUNIT_RUNNING')) {
                exit;
            }
        } catch (SalleIndisponibleException | ReservationIntrouvableException $e) {
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

            $this->render('reservation/form', [
                'titre'        => 'Créer une réservation',
                'salles'       => $salles,
                'errors'       => $errors,
                'data'         => $data,
                'errorMessage' => $message,
            ], 422);
        }
    }

    public function cancel(int $id): void
    {
        try {
            $reservation = $this->annulerService->executer($id);
            $_SESSION['flash_success'] = "La réservation #{$reservation->id} a été annulée avec succès.";
            header('Location: /reservations/' . $id);
            if (!defined('PHPUNIT_RUNNING')) {
                exit;
            }
        } catch (ReservationIntrouvableException $e) {
            $this->render('error/404', [
                'titre'   => 'Réservation introuvable',
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
