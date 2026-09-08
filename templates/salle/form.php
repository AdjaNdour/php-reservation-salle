<?php

$isEdit = !empty($salle);
$actionUrl = $isEdit ? '/salles/' . $salle->id . '/edit' : '/salles';
$valNom = $data['nom'] ?? ($salle->nom ?? '');
$valBatiment = $data['batiment'] ?? ($salle->batiment ?? '');
$valCapacite = $data['capacite'] ?? ($salle->capacite ?? '');
$valType = $data['type'] ?? ($salle->type ?? 'cours');
$valActive = isset($data['active']) ? (bool) $data['active'] : ($salle->active ?? true);
?>

<div class="page-header">
    <h1><?= $isEdit ? '✏️ Modifier la salle' : '🏛️ Ajouter une nouvelle salle' ?></h1>
    <a href="<?= $isEdit ? '/salles/' . $salle->id : '/salles' ?>" class="btn btn-secondary">← Retour</a>
</div>

<div class="card" style="max-width: 680px; margin: 0 auto;">
    <form method="POST" action="<?= $actionUrl ?>">
        <div class="form-group">
            <label for="nom">Nom de la salle *</label>
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars((string) $valNom) ?>" 
            class="<?= isset($errors['nom']) ? 'input-error' : '' ?>"placeholder="Ex : Amphithéâtre A, Salle B12...">
            <?php if (isset($errors['nom'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['nom']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="batiment">Bâtiment *</label>
            <input type="text" id="batiment" name="batiment" value="<?= htmlspecialchars((string) $valBatiment) ?>" 
            class="<?= isset($errors['batiment']) ? 'input-error' : '' ?>" placeholder="Ex : Bâtiment Sciences, Bâtiment B..."  >
            <?php if (isset($errors['batiment'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['batiment']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="capacite">Capacité (nombre de places) *</label>
            <input type="number" id="capacite" name="capacite" min="1" max="1000" value="<?= htmlspecialchars((string) $valCapacite) ?>" 
            class="<?= isset($errors['capacite']) ? 'input-error' : '' ?>" placeholder="Ex : 40">
            <?php if (isset($errors['capacite'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['capacite']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="type">Type d'usage de la salle *</label>
            <select id="type" name="type" class="<?= isset($errors['type']) ? 'input-error' : '' ?>" >
                <?php foreach (\App\Model\Salle::TYPES_AUTORISES as $typeOption): ?>
                    <option value="<?= $typeOption ?>" <?= $valType === $typeOption ? 'selected' : '' ?>>
                        <?= ucfirst($typeOption) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['type'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['type']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group" style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
            <input type="checkbox" id="active" name="active" value="1" <?= $valActive ? 'checked' : '' ?>
            style="width: 18px; height: 18px; cursor: pointer;">
            <label for="active" style="margin-bottom: 0; cursor: pointer;">
                Salle active (disponible immédiatement pour les réservations)
            </label>
            <?php if (isset($errors['active'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['active']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                💾 <?= $isEdit ? 'Enregistrer les modifications' : 'Créer la salle' ?>
            </button>
            <a href="<?= $isEdit ? '/salles/' . $salle->id : '/salles' ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
