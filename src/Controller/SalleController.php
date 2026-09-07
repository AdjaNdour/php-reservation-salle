<?php

declare(strict_types=1);

namespace App\Controller;

use App\DTO\CreerSalleDTO;
use App\Model\Salle;
use App\Repository\SalleRepositoryInterface;
use App\Validation\SalleValidator;
use App\View\ViewRenderer;

class SalleController
{
    public function __construct(
        private SalleRepositoryInterface $salles,
        private SalleValidator $validator,
        private ViewRenderer $view
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $salles = $this->salles->findAll();
        $this->view->render('salle/index', [
            'titre'  => 'Liste des salles',
            'salles' => $salles,
        ]);
    }

    public function show(int $id): void
    {
        $salle = $this->salles->findById($id);

        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre' => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ]);
            return;
        }

        $this->view->render('salle/show', [
            'titre' => 'Détail de la salle - ' . $salle->nom,
            'salle' => $salle,
            'reservations' => $salle->reservations()->orderBy('date_debut', 'desc')->get(),
        ]);
    }

    public function create(): void
    {
        $this->view->render('salle/form', [
            'titre' => 'Ajouter une salle',
            'salle' => null,
            'errors' => [],
            'data' => [
                'active' => true,
            ],
        ]);
    }

    public function store(): void
    {
        $validationResult = $this->validator->validate($_POST);

        if (!$validationResult->isValid()) {
            http_response_code(422);
            $this->view->render('salle/form', [
                'titre' => 'Ajouter une salle',
                'salle' => null,
                'errors' => $validationResult->errors(),
                'data' => $_POST,
            ]);
            return;
        }

        $dto = CreerSalleDTO::fromArray($validationResult->validatedData());
        $salle = new Salle($dto->toArray());
        $this->salles->save($salle);

        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été créée avec succès.";
        header('Location: /salles');
        exit;
    }

    public function edit(int $id): void
    {
        $salle = $this->salles->findById($id);

        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre' => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ]);
            return;
        }

        $this->view->render('salle/form', [
            'titre' => 'Modifier la salle - ' . $salle->nom,
            'salle' => $salle,
            'errors' => [],
            'data' => [
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
        $salle = $this->salles->findById($id);

        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée (ID: {$id}) n'existe pas.",
            ]);
            return;
        }

        $validationResult = $this->validator->validate($_POST);

        if (!$validationResult->isValid()) {
            http_response_code(422);
            $this->view->render('salle/form', [
                'titre'  => 'Modifier la salle - ' . $salle->nom,
                'salle'  => $salle,
                'errors' => $validationResult->errors(),
                'data'   => $_POST,
            ]);
            return;
        }

        $dto = CreerSalleDTO::fromArray($validationResult->validatedData());
        $salle->fill($dto->toArray());
        $this->salles->save($salle);

        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été modifiée avec succès.";
        header('Location: /salles/' . $id);
        exit;
    }

    public function toggle(int $id): void
    {
        $salle = $this->salles->findById($id);
        if ($salle === null) {
            http_response_code(404);
            $this->view->render('error/404', [
                'titre'   => 'Salle introuvable',
                'message' => "La salle demandée n'existe pas.",
            ]);
            return;
        }

        $this->salles->toggleActive($id);
        $nouvelEtat = !$salle->active ? 'activée' : 'désactivée';
        $_SESSION['flash_success'] = "La salle « {$salle->nom} » a été {$nouvelEtat}.";

        $referer = $_SERVER['HTTP_REFERER'] ?? '/salles';
        header('Location: ' . $referer);
        exit;
    }
}
