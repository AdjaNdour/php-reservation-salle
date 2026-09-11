<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Middleware\AdminMiddleware;
use App\DTO\CreerSalleDTOBuilder;
use App\Service\Interface\IReservationService;
use App\Service\Interface\ISalleService;
use App\Validation\Interface\ISalleValidator;
use App\View\ViewRenderer;
use Throwable;

class SalleController extends Controller
{
    public function __construct(
        private ISalleService $salleService,
        private ISalleValidator $validator,
        ViewRenderer $view,
        private IReservationService $reservationService ,
        private AdminMiddleware $adminMiddleware
    ) {
        parent::__construct($view);
    }

    public function index(): void
    {
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
        $perPage = 5;

        $criteres = [
            'q'            => $_GET['q'] ?? '',
            'nom'          => $_GET['nom'] ?? '',
            'batiment'     => $_GET['batiment'] ?? '',
            'type'         => $_GET['type'] ?? '',
            'capacite_min' => $_GET['capacite_min'] ?? '',
            'active'       => $_GET['active'] ?? '',
        ];

        $activeCriteres = array_filter($criteres, fn($v) => $v !== '' && $v !== null);

        $paginator = $this->salleService->getPaginated($page, $perPage, $activeCriteres);
        $paginator->appends($activeCriteres);

        $this->render('salle/index', [
            'titre'     => 'Liste des salles',
            'salles'    => $paginator->items(),
            'paginator' => $paginator,
            'filters'   => $criteres,
        ]);
    }

    public function show(int $id): void
    {
        $salle = $this->salleService->getById($id);

        if ($salle === null) {
            $this->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ], 404);
            return;
        }

        $reservations = [];
        if ($this->reservationService !== null) {
            $reservations = $this->reservationService->getAll($salle->id);
        } else {
            try {
                $reservations = $salle->reservations()->orderBy('date_debut', 'desc')->get();
            } catch (Throwable) {
                $reservations = [];
            }
        }

        $this->render('salle/show', [
            'titre'        => 'Détail de la salle - ' . $salle->nom,
            'salle'        => $salle,
            'reservations' => $reservations,
        ]);
    }

    public function create(): void
    {
        if (!$this->middleware($this->adminMiddleware)) {
            return;
        }

        $this->render('salle/form', [
            'titre'  => 'Ajouter une salle',
            'salle'  => null,
            'errors' => [],
            'data'   => [
                'active' => true,
            ],
        ]);
    }

    public function store(): void
    {
        if (!$this->middleware($this->adminMiddleware)) {
            return;
        }

        $validationResult = $this->validator->validate($_POST);

        if (!$validationResult->isValid()) {
            $this->render('salle/form', [
                'titre'  => 'Ajouter une salle',
                'salle'  => null,
                'errors' => $validationResult->errors(),
                'data'   => $_POST,
            ], 422);
            return;
        }

        $data = $_POST;

        CreerSalleDTOBuilder::fromArray($data);
        $dto = CreerSalleDTOBuilder::build($this->validator);
        $salle = $this->salleService->save($dto);

        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été créée avec succès.";
        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) {
            exit;
        }
    }

    public function edit(int $id): void
    {
        $salle = $this->salleService->getById($id);

        if ($salle === null) {
            $this->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ], 404);
            return;
        }

        $this->render('salle/form', [
            'titre'  => 'Modifier la salle - ' . $salle->nom,
            'salle'  => $salle,
            'errors' => [],
            'data'   => [
                'nom'      => $salle->nom,
                'batiment' => $salle->batiment,
                'capacite' => $salle->capacite,
                'type'     => $salle->type,
                'active'   => $salle->active,
            ],
        ]);
    }

    public function update(int $id): void
    {
        $salle = $this->salleService->getById($id);

        if ($salle === null) {
            $this->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ], 404);
            return;
        }

        $validationResult = $this->validator->validate($_POST);

        if (!$validationResult->isValid()) {
            $this->render('salle/form', [
                'titre'  => 'Modifier la salle - ' . $salle->nom,
                'salle'  => $salle,
                'errors' => $validationResult->errors(),
                'data'   => $_POST,
            ], 422);
            return;
        }

        $data = $validationResult->validatedData();
        CreerSalleDTOBuilder::fromArray($data);
        $dto = CreerSalleDTOBuilder::build($this->validator);

        $salleModifiee = $this->salleService->save($dto, $id);

        $_SESSION['flash_success'] = "La salle « {$salleModifiee->nom} » a été modifiée avec succès.";
        header('Location: /salles/' . $id);
        if (!defined('PHPUNIT_RUNNING')) {
            exit;
        }
    }

    public function toggle(int $id): void
    {
        $salle = $this->salleService->getById($id);
        if ($salle === null) {
            $this->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée n'existe pas.",
            ], 404);
            return;
        }

        $this->salleService->toggleActive($id);
        $nouvelEtat = !$salle->active ? 'activée' : 'désactivée';
        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été {$nouvelEtat}.";

        $referer = $_SERVER['HTTP_REFERER'] ?? '/salles';
        header('Location: ' . $referer);
        if (!defined('PHPUNIT_RUNNING')) {
            exit;
        }
    }

    public function delete(int $id): void
    {
        $salle = $this->salleService->getById($id);
        if ($salle === null) {
            $this->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée n'existe pas.",
            ], 404);
            return;
        }

        $nom = $salle->nom;
        $this->salleService->delete($id);
        $_SESSION['flash_success'] = "La salle « {$nom} » a été supprimée avec succès.";

        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) {
            exit;
        }
    }
}
