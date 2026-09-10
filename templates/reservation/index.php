<div class="page-header">
    <div>
        <h1>📅 Gestion des réservations</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
            Consultez les réservations, filtrez selon plusieurs critères et gérez les annulations
        </p>
    </div>
    <a href="/reservations/create" class="btn btn-primary">+ Nouvelle réservation</a>
</div>

<!-- Barre de recherche multicritère -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="/reservations">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end;">
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Salle</label>
                <select name="salle_id">
                    <option value="">Toutes les salles</option>
                    <?php foreach ($salles as $salle): ?>
                        <option value="<?= $salle->id ?>" <?= ($selectedSalleId !== null && (int)$selectedSalleId === (int)$salle->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($salle->nom) ?> (<?= htmlspecialchars($salle->batiment) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Statut</label>
                <select name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="confirmée" <?= ($filters['statut'] ?? '') === 'confirmée' ? 'selected' : '' ?>>Confirmée</option>
                    <option value="annulée" <?= ($filters['statut'] ?? '') === 'annulée' ? 'selected' : '' ?>>Annulée</option>
                </select>
            </div>
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Responsable</label>
                <input type="text" name="responsable" placeholder="Nom du responsable..." value="<?= htmlspecialchars($filters['responsable'] ?? '') ?>">
            </div>
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">À partir du</label>
                <input type="datetime-local" name="date_debut" value="<?= htmlspecialchars($filters['date_debut'] ?? '') ?>">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">🔍 Filtrer</button>
                <a href="/reservations" class="btn btn-secondary" title="Réinitialiser">↺</a>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <?php if (empty($reservations)): ?>
        <p style="text-align: center; color: var(--text-muted); padding: 30px;">
            Aucune réservation ne correspond à vos critères de recherche.
        </p>
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

        <!-- Pagination -->
        <?php if (isset($paginator) && $paginator->getTotalItems() > 0): ?>
            <?php
            $totalPages = $paginator->getTotalPages();
            $currentPage = $paginator->getCurrentPage();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            ?>
            <div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px;">
                <div class="pagination-info" style="color: var(--text-muted); font-size: 0.875rem;">
                    Affichage de <strong><?= $paginator->getFromIndex() ?></strong> à <strong><?= $paginator->getToIndex() ?></strong> sur <strong><?= $paginator->getTotalItems() ?></strong> <?= $paginator->getTotalItems() > 1 ? 'réservations' : 'réservation' ?>
                    <?php if ($totalPages > 1): ?>
                        (Page <strong><?= $currentPage ?></strong> sur <strong><?= $totalPages ?></strong>)
                    <?php endif; ?>
                </div>
                <div class="pagination-controls" style="display: flex; gap: 6px; align-items: center;">
                    <!-- Bouton Précédent -->
                    <?php if ($paginator->hasPreviousPage()): ?>
                        <a href="<?= $paginator->urlForPage($paginator->getPreviousPage()) ?>" class="btn btn-secondary btn-sm">&laquo; Précédent</a>
                    <?php else: ?>
                        <span class="btn btn-secondary btn-sm disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">&laquo; Précédent</span>
                    <?php endif; ?>

                    <!-- Première page si fenêtre décalée -->
                    <?php if ($startPage > 1): ?>
                        <a href="<?= $paginator->urlForPage(1) ?>" class="btn btn-secondary btn-sm">1</a>
                        <?php if ($startPage > 2): ?>
                            <span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Pages numérotées -->
                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <?php if ($p === $currentPage): ?>
                            <span class="btn btn-primary btn-sm" style="font-weight: 700; cursor: default;"><?= $p ?></span>
                        <?php else: ?>
                            <a href="<?= $paginator->urlForPage($p) ?>" class="btn btn-secondary btn-sm"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Dernière page si fenêtre décalée -->
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>
                        <?php endif; ?>
                        <a href="<?= $paginator->urlForPage($totalPages) ?>" class="btn btn-secondary btn-sm"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <!-- Bouton Suivant -->
                    <?php if ($paginator->hasNextPage()): ?>
                        <a href="<?= $paginator->urlForPage($paginator->getNextPage()) ?>" class="btn btn-secondary btn-sm">Suivant &raquo;</a>
                    <?php else: ?>
                        <span class="btn btn-secondary btn-sm disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">Suivant &raquo;</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
