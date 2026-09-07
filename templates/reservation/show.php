<div class="page-header">
    <h1>📅 Réservation #<?= htmlspecialchars((string) $reservation->id) ?></h1>
    <div style="display: flex; gap: 10px;">
        <?php if ($reservation->statut === 'confirmée'): ?>
            <form method="POST" action="/reservations/<?= $reservation->id ?>/cancel" onsubmit="return confirm('Confirmez-vous l\'annulation de cette réservation ?');">
                <button type="submit" class="btn btn-danger">❌ Annuler la réservation</button>
            </form>
        <?php endif; ?>
        <a href="/reservations" class="btn btn-secondary">← Retour à la liste</a>
    </div>
</div>

<div class="card">
    <h2 style="font-size: 1.25rem; margin-bottom: 16px; color: #0f172a;">Détails de la réservation</h2>
    <div class="details-grid">
        <div class="detail-item">
            <div class="label">Salle réservée</div>
            <div class="value">
                <a href="/salles/<?= $reservation->salle_id ?>" style="color: var(--primary-color); text-decoration: none;">
                    🏛️ <?= htmlspecialchars($reservation->salle ? $reservation->salle->nom : 'Salle #' . $reservation->salle_id) ?>
                </a>
                <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">
                    <?= htmlspecialchars($reservation->salle ? $reservation->salle->batiment : '') ?> (<?= htmlspecialchars($reservation->salle ? (string)$reservation->salle->capacite : '') ?> places)
                </div>
            </div>
        </div>

        <div class="detail-item">
            <div class="label">Responsable</div>
            <div class="value"><?= htmlspecialchars($reservation->responsable) ?></div>
            <div style="font-size: 0.85rem; color: var(--text-muted); font-weight: normal; margin-top: 4px;">
                ✉️ <?= htmlspecialchars($reservation->email) ?>
            </div>
        </div>

        <div class="detail-item">
            <div class="label">Créneau horaire</div>
            <div class="value" style="font-size: 1rem;">
                Du <?= htmlspecialchars($reservation->date_debut->format('d/m/Y à H:i')) ?><br>
                Au <?= htmlspecialchars($reservation->date_fin->format('d/m/Y à H:i')) ?>
            </div>
        </div>

        <div class="detail-item">
            <div class="label">Statut actuel</div>
            <div class="value">
                <?php if ($reservation->statut === 'confirmée'): ?>
                    <span class="badge badge-success">confirmée</span>
                <?php else: ?>
                    <span class="badge badge-danger">annulée</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div style="margin-top: 16px;">
        <h3 style="font-size: 1rem; color: var(--text-muted); margin-bottom: 8px;">Motif de la réservation :</h3>
        <p style="background: #f8fafc; padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); font-size: 1rem;">
            <?= nl2br(htmlspecialchars($reservation->motif)) ?>
        </p>
    </div>
</div>
