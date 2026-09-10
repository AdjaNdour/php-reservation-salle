<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreerSalleDTO;
use App\DTO\CreerSalleDTOBuilder;
use App\Model\Salle;
use App\Service\InterfaceAuthService;
use App\Service\InterfaceReservationService;
use App\Service\InterfaceSalleService;
use App\Validation\SalleValidator;
use App\View\ViewRenderer;
use Throwable;

class SalleController
{
    public function __construct(
        private InterfaceSalleService $salleService,
        private CreerSalleDTOBuilder $builderSalle,
        private SalleValidator $validator,
        private ViewRenderer $view,
        private ?InterfaceAuthService $authService = null,
        private ?InterfaceReservationService $reservationService = null
    ) {}

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

        $activeCriteres = array_filter($criteres, fn ($v) => $v !== '' && $v !== null);

        $paginator = $this->salleService->getPaginated($page, $perPage, $activeCriteres);

        $this->view->render('salle/index', [
            'titre'     => 'Liste des salles',
            'salles'    => $paginator->getItems(),
            'paginator' => $paginator,
            'filters'   => $criteres,
        ]);
    }

    public function show(int $id): void
    {
        $salle = $this->salleService->getById($id);

        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ]);
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

        $this->view->render('salle/show', [
            'titre'        => 'Détail de la salle - ' . $salle->nom,
            'salle'        => $salle,
            'reservations' => $reservations,
        ]);
    }

    public function create(): void
    {
        if (!$this->checkAdmin()) {
            return;
        }

        $this->view->render('salle/form', [
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
        if (!$this->checkAdmin()) {
            return;
        }

        $validationResult = $this->validator->validate($_POST);

        if (!$validationResult->isValid()) {
            http_response_code(422);
            $this->view->render('salle/form', [
                'titre'  => 'Ajouter une salle',
                'salle'  => null,
                'errors' => $validationResult->errors(),
                'data'   => $_POST,
            ]);
            return;
        }

        $data = $_POST;
        $dto = $this->builderSalle
            ->setNom($data['nom'])
            ->setBatiment($data['batiment'])
            ->setCapacite((int) $data['capacite'])
            ->setType($data['type'])
            ->setActive((bool) ($data['active'] ?? true))
            ->build();

        $salle = $this->salleService->save($dto);

        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été créée avec succès.";
        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }

    public function edit(int $id): void
    {
        if (!$this->checkAdmin()) {
            return;
        }

        $salle = $this->salleService->getById($id);

        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ]);
            return;
        }

        $this->view->render('salle/form', [
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
        if (!$this->checkAdmin()) {
            return;
        }

        $salle = $this->salleService->getById($id);

        if ($salle === null) {
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ]);
            return;
        }

        $validationResult = $this->validator->validate($_POST);

        if (!$validationResult->isValid()) {
            $this->view->render('salle/form', [
                'titre'  => 'Modifier la salle - ' . $salle->nom,
                'salle'  => $salle,
                'errors' => $validationResult->errors(),
                'data'   => $_POST,
            ]);
            return;
        }

        $data = $validationResult->validatedData();
        $dto = $this->builderSalle
            ->setNom($data['nom'])
            ->setBatiment($data['batiment'])
            ->setCapacite((int) $data['capacite'])
            ->setType($data['type'])
            ->setActive((bool) ($data['active'] ?? true))
            ->build();

        $salleModifiee = $this->salleService->save($dto, $id);

        $_SESSION['flash_success'] = "La salle « {$salleModifiee->nom} » a été modifiée avec succès.";
        header('Location: /salles/' . $id);
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }

    public function toggle(int $id): void
    {
        if (!$this->checkAdmin()) {
            return;
        }

        $salle = $this->salleService->getById($id);
        if ($salle === null) {
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée n'existe pas.",
            ]);
            return;
        }

        $this->salleService->toggleActive($id);
        $nouvelEtat = !$salle->active ? 'activée' : 'désactivée';
        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été {$nouvelEtat}.";

        $referer = $_SERVER['HTTP_REFERER'] ?? '/salles';
        header('Location: ' . $referer);
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }

    public function delete(int $id): void
    {
        if (!$this->checkAdmin()) {
            return;
        }

        $salle = $this->salleService->getById($id);
        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée n'existe pas.",
            ]);
            return;
        }

        $nom = $salle->nom;
        $this->salleService->delete($id);
        $_SESSION['flash_success'] = "La salle « {$nom} » a été supprimée avec succès.";

        header('Location: /salles');
        if (!defined('PHPUNIT_RUNNING')) { exit; }
    }

    private function checkAdmin(): bool
    {
        if ($this->authService !== null && !$this->authService->estAdmin()) {
            http_response_code(403);
            $this->view->render('error/403', [
                'titre'   => '403 - Accès refusé',
                'message' => "Cette action est strictement réservée aux administrateurs.",
            ]);
            return false;
        }

        return true;
    }
}
