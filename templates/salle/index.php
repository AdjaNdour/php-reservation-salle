<?php $isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin'; ?>

<div class="page-header">
    <div>
        <h1>🏛️ Salles universitaires</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
            Consultez les disponibilités, effectuez des recherches multicritères et gérez les salles
        </p>
    </div>
    <?php if ($isAdmin): ?>
        <a href="/salles/create" class="btn btn-primary">+ Ajouter une salle</a>
    <?php endif; ?>
</div>

<!-- Formulaire de recherche multicritère -->
<div class="card" style="padding: 16px 20px; margin-bottom: 24px;">
    <form method="GET" action="/salles">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: flex-end;">
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Recherche rapide</label>
                <input type="text" name="q" placeholder="Nom ou bâtiment..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>">
            </div>
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Type de salle</label>
                <select name="type">
                    <option value="">Tous les types</option>
                    <?php foreach (\App\Model\Salle::TYPES_AUTORISES as $type): ?>
                        <option value="<?= $type ?>" <?= ($filters['type'] ?? '') === $type ? 'selected' : '' ?>>
                            <?= ucfirst($type) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Capacité min.</label>
                <input type="number" name="capacite_min" placeholder="Ex: 30" min="1" value="<?= htmlspecialchars((string) ($filters['capacite_min'] ?? '')) ?>">
            </div>
            <div>
                <label style="font-size: 0.825rem; margin-bottom: 4px;">Statut</label>
                <select name="active">
                    <option value="">Tous les statuts</option>
                    <option value="1" <?= ($filters['active'] ?? '') === '1' ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= ($filters['active'] ?? '') === '0' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">🔍 Filtrer</button>
                <a href="/salles" class="btn btn-secondary" title="Réinitialiser">↺</a>
            </div>
        </div>
    </form>
</div>

<!-- Liste des salles -->
<div class="card">
    <?php if (empty($salles)): ?>
        <p style="text-align: center; color: var(--text-muted); padding: 30px;">
            Aucune salle ne correspond à vos critères de recherche.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom de la salle</th>
                        <th>Bâtiment</th>
                        <th>Capacité</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($salles as $salle): ?>
                        <tr>
                            <td>#<?= htmlspecialchars((string) $salle->id) ?></td>
                            <td>
                                <strong>
                                    <a href="/salles/<?= $salle->id ?>" style="color: var(--primary-color); text-decoration: none;">
                                        <?= htmlspecialchars($salle->nom) ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?= htmlspecialchars($salle->batiment) ?></td>
                            <td><?= htmlspecialchars((string) $salle->capacite) ?> places</td>
                            <td>
                                <span class="badge badge-info"><?= htmlspecialchars($salle->type) ?></span>
                            </td>
                            <td>
                                <?php if ($salle->active): ?>
                                    <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <a href="/salles/<?= $salle->id ?>" class="btn btn-secondary btn-sm">Détail</a>
                                <?php if ($isAdmin): ?>
                                    <a href="/salles/<?= $salle->id ?>/edit" class="btn btn-secondary btn-sm">Modifier</a>
                                <?php endif; ?>
                                <?php if ($salle->active): ?>
                                    <a href="/reservations/create?salle_id=<?= $salle->id ?>" class="btn btn-primary btn-sm">Réserver</a>
                                <?php endif; ?>
                                <?php if ($isAdmin): ?>
                                    <form method="POST" action="/salles/<?= $salle->id ?>/delete" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette salle ?');">
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if (isset($paginator) && $paginator->total() > 0): ?>
            <?php
            $totalPages = $paginator->lastPage();
            $currentPage = $paginator->currentPage();
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            ?>
            <div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px;">
                <div class="pagination-info" style="color: var(--text-muted); font-size: 0.875rem;">
                    Affichage de <strong><?= $paginator->firstItem() ?></strong> à <strong><?= $paginator->lastItem() ?></strong> sur <strong><?= $paginator->total() ?></strong> <?= $paginator->total() > 1 ? 'salles' : 'salle' ?>
                    <?php if ($totalPages > 1): ?>
                        (Page <strong><?= $currentPage ?></strong> sur <strong><?= $totalPages ?></strong>)
                    <?php endif; ?>
                </div>
                <div class="pagination-controls" style="display: flex; gap: 6px; align-items: center;">
                    <!-- Bouton Précédent -->
                    <?php if (!$paginator->onFirstPage()): ?>
                        <a href="<?= $paginator->previousPageUrl() ?>" class="btn btn-secondary btn-sm">&laquo; Précédent</a>
                    <?php else: ?>
                        <span class="btn btn-secondary btn-sm disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">&laquo; Précédent</span>
                    <?php endif; ?>

                    <!-- Première page si fenêtre décalée -->
                    <?php if ($startPage > 1): ?>
                        <a href="<?= $paginator->url(1) ?>" class="btn btn-secondary btn-sm">1</a>
                        <?php if ($startPage > 2): ?>
                            <span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Pages numérotées -->
                    <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                        <?php if ($p === $currentPage): ?>
                            <span class="btn btn-primary btn-sm" style="font-weight: 700; cursor: default;"><?= $p ?></span>
                        <?php else: ?>
                            <a href="<?= $paginator->url($p) ?>" class="btn btn-secondary btn-sm"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <!-- Dernière page si fenêtre décalée -->
                    <?php if ($endPage < $totalPages): ?>
                        <?php if ($endPage < $totalPages - 1): ?>
                            <span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>
                        <?php endif; ?>
                        <a href="<?= $paginator->url($totalPages) ?>" class="btn btn-secondary btn-sm"><?= $totalPages ?></a>
                    <?php endif; ?>

                    <!-- Bouton Suivant -->
                    <?php if ($paginator->hasMorePages()): ?>
                        <a href="<?= $paginator->nextPageUrl() ?>" class="btn btn-secondary btn-sm">Suivant &raquo;</a>
                    <?php else: ?>
                        <span class="btn btn-secondary btn-sm disabled" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">Suivant &raquo;</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
