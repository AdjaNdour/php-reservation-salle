<div class="page-header">
    <h1>🏛️ <?= htmlspecialchars($salle->nom) ?></h1>
    <div style="display: flex; gap: 10px;">
        <a href="/salles/<?= $salle->id ?>/edit" class="btn btn-secondary">✏️ Modifier</a>
        <form method="POST" action="/salles/<?= $salle->id ?>/edit" style="display: inline;">
            <!-- Formulaire toggle ou édition -->
        </form>
        <?php if ($salle->active): ?>
            <a href="/reservations/create?salle_id=<?= $salle->id ?>" class="btn btn-primary">📅 Réserver cette salle</a>
        <?php endif; ?>
        <a href="/salles" class="btn btn-secondary">← Retour à la liste</a>
    </div>
</div>

<div class="card">
    <h2 style="font-size: 1.25rem; margin-bottom: 16px; color: #0f172a;">Informations générales</h2>
    <div class="details-grid">
        <div class="detail-item">
            <div class="label">Identifiant</div>
            <div class="value">#<?= htmlspecialchars((string) $salle->id) ?></div>
        </div>
        <div class="detail-item">
            <div class="label">Bâtiment</div>
            <div class="value"><?= htmlspecialchars($salle->batiment) ?></div>
        </div>
        <div class="detail-item">
            <div class="label">Capacité d'accueil</div>
            <div class="value"><?= htmlspecialchars((string) $salle->capacite) ?> places</div>
        </div>
        <div class="detail-item">
            <div class="label">Type de salle</div>
            <div class="value">
                <span class="badge badge-info"><?= htmlspecialchars($salle->type) ?></span>
            </div>
        </div>
        <div class="detail-item">
            <div class="label">État</div>
            <div class="value">
                <?php if ($salle->active): ?>
                    <span class="badge badge-success">Active (Disponible aux réservations)</span>
                <?php else: ?>
                    <span class="badge badge-danger">Inactive (Indisponible)</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="page-header" style="margin-bottom: 16px;">
        <h2 style="font-size: 1.25rem; color: #0f172a;">Réservations de cette salle</h2>
        <a href="/reservations?salle_id=<?= $salle->id ?>" class="btn btn-secondary btn-sm">Filtrer les réservations</a>
    </div>

    <?php if ($reservations->isEmpty()): ?>
        <p style="color: var(--text-muted);">Aucune réservation pour cette salle.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Responsable</th>
                        <th>Motif</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td>#<?= htmlspecialchars((string) $res->id) ?></td>
                            <td><?= htmlspecialchars($res->responsable) ?></td>
                            <td><?= htmlspecialchars($res->motif) ?></td>
                            <td><?= htmlspecialchars($res->date_debut->format('d/m/Y H:i')) ?></td>
                            <td><?= htmlspecialchars($res->date_fin->format('d/m/Y H:i')) ?></td>
                            <td>
                                <?php if ($res->statut === 'confirmée'): ?>
                                    <span class="badge badge-success">confirmée</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">annulée</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <a href="/reservations/<?= $res->id ?>" class="btn btn-secondary btn-sm">Voir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
