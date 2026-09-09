<?php
$valSalleId = (string) ($data['salle_id'] ?? '');
$valResponsable = (string) ($data['responsable'] ?? ($_SESSION['user']['nom'] ?? ''));
$valEmail = (string) ($data['email'] ?? ($_SESSION['user']['email'] ?? ''));
$valMotif = (string) ($data['motif'] ?? '');
$valDateDebut = (string) ($data['date_debut'] ?? '');
$valDateFin = (string) ($data['date_fin'] ?? '');
$salles = $salles ?? [];
?>

<div class="page-header">
    <h1>📅 Réserver une salle universitaire</h1>
    <a href="/reservations" class="btn btn-secondary">← Retour à la liste</a>
</div>

<?php if (isset($errors['metier'])): ?>
    <div class="alert alert-danger">
        <span>⚠️ <strong>Refus de la réservation :</strong> <?= htmlspecialchars($errors['metier']) ?></span>
    </div>
<?php endif; ?>

<div class="card" style="max-width: 680px; margin: 0 auto;">
    <form method="POST" action="/reservations" novalidate>
        <!-- Sélection de la salle -->
        <div class="form-group">
            <label for="salle_id">Salle à réserver *</label>
            <select id="salle_id" name="salle_id" class="<?= isset($errors['salle_id']) ? 'input-error' : '' ?>">
                <option value="">-- Choisissez une salle --</option>
                <?php foreach ($salles as $salle): ?>
                    <option value="<?= $salle->id ?>" <?= ($valSalleId === (string) $salle->id) ? 'selected' : '' ?> <?= !$salle->active ? 'disabled' : '' ?>>
                        <?= htmlspecialchars($salle->nom) ?> (<?= htmlspecialchars($salle->batiment) ?> - <?= htmlspecialchars((string) $salle->capacite) ?> places) <?= !$salle->active ? '— [INACTIVE]' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['salle_id'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['salle_id']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Responsable -->
        <div class="form-group">
            <label for="responsable">Nom du responsable / Enseignant *</label>
            <input type="text" id="responsable" name="responsable" value="<?= htmlspecialchars($valResponsable) ?>" 
            class="<?= isset($errors['responsable']) ? 'input-error' : '' ?>" placeholder="Ex : Dr. Awa Ndiaye">
            <?php if (isset($errors['responsable'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['responsable']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Email -->
        <div class="form-group">
            <label for="email">Adresse électronique institutionnelle *</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($valEmail) ?>" 
            class="<?= isset($errors['email']) ? 'input-error' : '' ?>" placeholder="Ex : awa.ndiaye@universite.sn">
            <?php if (isset($errors['email'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Motif -->
        <div class="form-group">
            <label for="motif">Motif de la réservation (cours, soutenance, réunion...) *</label>
            <textarea id="motif" name="motif" rows="3" class="<?= isset($errors['motif']) ? 'input-error' : '' ?>" placeholder="Ex : Cours magistral d'architecture logicielle..."><?= htmlspecialchars($valMotif) ?></textarea>
            <?php if (isset($errors['motif'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['motif']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Date de début -->
        <div class="form-group">
            <label for="date_debut">Date et heure de début *</label>
            <input type="datetime-local" id="date_debut" name="date_debut" 
            value="<?= htmlspecialchars($valDateDebut) ?>" class="<?= isset($errors['date_debut']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['date_debut'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['date_debut']) ?></span>
            <?php endif; ?>
        </div>

        <!-- Date de fin -->
        <div class="form-group">
            <label for="date_fin">Date et heure de fin * (durée maximale : 4 heures)</label>
            <input type="datetime-local" id="date_fin" name="date_fin" 
            value="<?= htmlspecialchars($valDateFin) ?>" class="<?= isset($errors['date_fin']) ? 'input-error' : '' ?>">
            <?php if (isset($errors['date_fin'])): ?>
                <span class="field-error"><?= htmlspecialchars($errors['date_fin']) ?></span>
            <?php endif; ?>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                ✅ Confirmer la réservation
            </button>
            <a href="/reservations" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>
