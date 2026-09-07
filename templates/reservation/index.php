<div class="page-header">
    <h1>📅 Gestion des réservations</h1>
    <a href="/reservations/create" class="btn btn-primary">+ Nouvelle réservation</a>
</div>

<!-- Barre de filtrage par salle -->
<div class="filter-bar">
    <form method="GET" action="/reservations" style="display: flex; gap: 12px; align-items: center; width: 100%;">
        <label for="salle_id" style="margin-bottom: 0; white-space: nowrap;">Filtrer par salle :</label>
        <select id="salle_id" name="salle_id" onchange="this.form.submit()">
            <option value="">-- Toutes les salles --</option>
            <?php foreach ($salles as $salle): ?>
                <option value="<?= $salle->id ?>" <?= ($selectedSalleId !== null && (int)$selectedSalleId === $salle->id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($salle->nom) ?> (<?= htmlspecialchars($salle->batiment) ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($selectedSalleId): ?>
            <a href="/reservations" class="btn btn-secondary btn-sm">Réinitialiser le filtre</a>
        <?php endif; ?>
    </form>
</div>

<div class="card">
    <?php if (empty($reservations)): ?>
        <p style="color: var(--text-muted);">Aucune réservation trouvée pour ce critère.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Salle</th>
                        <th>Responsable</th>
                        <th>Motif</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td>#<?= htmlspecialchars((string) $res->id) ?></td>
                            <td>
                                <strong>
                                    <a href="/salles/<?= $res->salle_id ?>" style="color: var(--primary-color); text-decoration: none;">
                                        <?= htmlspecialchars($res->salle ? $res->salle->nom : 'Salle #' . $res->salle_id) ?>
                                    </a>
                                </strong>
                            </td>
                            <td>
                                <div><?= htmlspecialchars($res->responsable) ?></div>
                                <small style="color: var(--text-muted);"><?= htmlspecialchars($res->email) ?></small>
                            </td>
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
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="/reservations/<?= $res->id ?>" class="btn btn-secondary btn-sm">Détail</a>
                                <?php if ($res->statut === 'confirmée'): ?>
                                    <form method="POST" action="/reservations/<?= $res->id ?>/cancel" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?');">
                                        <button type="submit" class="btn btn-danger btn-sm">Annuler</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
