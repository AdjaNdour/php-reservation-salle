<div class="page-header">
    <div>
        <h1>📊 Tableau de bord d'utilisation des salles</h1>
        <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 4px;">
            Statistiques globales et classement des salles universitaires les plus fréquentées
        </p>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="/reservations/create" class="btn btn-primary">+ Nouvelle réservation</a>
        <a href="/salles" class="btn btn-secondary">Voir les salles</a>
    </div>
</div>

<!-- Cartes Statistiques Globales -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="card" style="padding: 18px; margin-bottom: 0;">
        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Salles</div>
        <div style="font-size: 2rem; font-weight: 700; color: #0f172a; margin-top: 4px;"><?= (int) ($stats['total_salles'] ?? 0) ?></div>
        <div style="font-size: 0.85rem; color: #15803d; margin-top: 4px;">
            <?= (int) ($stats['salles_actives'] ?? 0) ?> actives &bull; <?= (int) ($stats['salles_inactives'] ?? 0) ?> inactives
        </div>
    </div>

    <div class="card" style="padding: 18px; margin-bottom: 0;">
        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Total Réservations</div>
        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color); margin-top: 4px;"><?= (int) ($stats['total_reservations'] ?? 0) ?></div>
        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Toutes périodes confondues</div>
    </div>

    <div class="card" style="padding: 18px; margin-bottom: 0;">
        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Réservations Confirmées</div>
        <div style="font-size: 2rem; font-weight: 700; color: #16a34a; margin-top: 4px;"><?= (int) ($stats['reservations_confirmees'] ?? 0) ?></div>
        <div style="font-size: 0.85rem; color: #15803d; margin-top: 4px;">
            Taux de succès : <?= $stats['taux_confirmation'] ?? 0 ?>%
        </div>
    </div>

    <div class="card" style="padding: 18px; margin-bottom: 0;">
        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Réservations Annulées</div>
        <div style="font-size: 2rem; font-weight: 700; color: #dc2626; margin-top: 4px;"><?= (int) ($stats['reservations_annulees'] ?? 0) ?></div>
        <div style="font-size: 0.85rem; color: #dc2626; margin-top: 4px;">Créneaux libérés</div>
    </div>
</div>

<!-- Classement des salles les plus utilisées -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="font-size: 1.25rem; font-weight: 700; color: #0f172a;">
            🏆 Top des salles les plus réservées
        </h2>
        <span class="badge badge-info"><?= count($topSalles) ?> salles listées</span>
    </div>

    <?php if (empty($topSalles)): ?>
        <p style="color: var(--text-muted); text-align: center; padding: 24px;">
            Aucune donnée de réservation disponible pour le moment.
        </p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th style="width: 50px;">Rang</th>
                        <th>Salle</th>
                        <th>Bâtiment</th>
                        <th>Type</th>
                        <th>Capacité</th>
                        <th>Total Résas</th>
                        <th>Confirmées</th>
                        <th>Annulées</th>
                        <th>Volume horaire</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topSalles as $index => $item): ?>
                        <tr>
                            <td>
                                <strong style="font-size: 1.05rem; color: <?= $index === 0 ? '#eab308' : ($index === 1 ? '#94a3b8' : ($index === 2 ? '#b45309' : '#64748b')) ?>;">
                                    #<?= $index + 1 ?>
                                </strong>
                            </td>
                            <td>
                                <a href="/salles/<?= (int) $item['salle_id'] ?>" style="font-weight: 600; color: var(--primary-color); text-decoration: none;">
                                    <?= htmlspecialchars($item['nom']) ?>
                                </a>
                                <?php if (isset($item['active']) && !$item['active']): ?>
                                    <span class="badge badge-danger" style="margin-left: 6px; font-size: 0.7rem;">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['batiment']) ?></td>
                            <td><span class="badge badge-muted"><?= htmlspecialchars($item['type'] ?? 'cours') ?></span></td>
                            <td><strong><?= (int) $item['capacite'] ?></strong> pl.</td>
                            <td><strong><?= (int) $item['total_reservations'] ?></strong></td>
                            <td>
                                <span class="badge badge-success"><?= (int) $item['reservations_confirmees'] ?></span>
                            </td>
                            <td>
                                <?php if (($item['reservations_annulees'] ?? 0) > 0): ?>
                                    <span class="badge badge-danger"><?= (int) $item['reservations_annulees'] ?></span>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong style="color: #0f172a;"><?= (float) $item['total_heures'] ?> h</strong>
                            </td>
                            <td style="text-align: right;">
                                <a href="/salles/<?= (int) $item['salle_id'] ?>" class="btn btn-secondary btn-sm">Détail</a>
                                <?php if ($item['active'] ?? true): ?>
                                    <a href="/reservations/create?salle_id=<?= (int) $item['salle_id'] ?>" class="btn btn-primary btn-sm">Réserver</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
