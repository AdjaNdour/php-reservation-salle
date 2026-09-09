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
        <?php if (isset($paginator) && $paginator->getTotalPages() > 1): ?>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: 12px;">
                <div style="color: var(--text-muted); font-size: 0.875rem;">
                    Affichage de <strong><?= $paginator->getFromIndex() ?></strong> à <strong><?= $paginator->getToIndex() ?></strong> sur <strong><?= $paginator->getTotalItems() ?></strong> salles
                </div>
                <div style="display: flex; gap: 6px; align-items: center;">
                    <?php if ($paginator->hasPreviousPage()): ?>
                        <a href="<?= $paginator->urlForPage($paginator->getPreviousPage()) ?>" class="btn btn-secondary btn-sm">&laquo; Précédent</a>
                    <?php endif; ?>

                    <?php for ($p = 1; $p <= $paginator->getTotalPages(); $p++): ?>
                        <a href="<?= $paginator->urlForPage($p) ?>" class="btn btn-sm <?= $p === $paginator->getCurrentPage() ? 'btn-primary' : 'btn-secondary' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($paginator->hasNextPage()): ?>
                        <a href="<?= $paginator->urlForPage($paginator->getNextPage()) ?>" class="btn btn-secondary btn-sm">Suivant &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
