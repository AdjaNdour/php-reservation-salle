<div class="page-header">
    <h1>🏛️ Liste des salles universitaires</h1>
    <a href="/salles/create" class="btn btn-primary">+ Ajouter une salle</a>
</div>

<div class="card">
    <?php if (empty($salles)): ?>
        <p>Aucune salle enregistrée pour le moment.</p>
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
                            <td style="text-align: right;">
                                <a href="/salles/<?= $salle->id ?>" class="btn btn-secondary btn-sm">Détail</a>
                                <a href="/salles/<?= $salle->id ?>/edit" class="btn btn-secondary btn-sm">Modifier</a>
                                <?php if ($salle->active): ?>
                                    <a href="/reservations/create?salle_id=<?= $salle->id ?>" class="btn btn-primary btn-sm">Réserver</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
