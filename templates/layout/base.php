<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($titre ?? 'UnivRésa - Gestion des Réservations') ?></title>
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body>
    <?php if (!empty($_SESSION['user'])): ?>
    <?php $isAdmin = ($_SESSION['user']['role'] ?? '') === 'admin'; ?>
    <header>
        <nav class="navbar">
            <a href="/salles" class="navbar-brand">
                🏛️ UnivRésa
            </a>
            <div class="navbar-links">
                <a href="/salles">Salles</a>
                <?php if ($isAdmin): ?>
                    <a href="/salles/create">+ Nouvelle salle</a>
                <?php endif; ?>
                <a href="/reservations">Réservations</a>
                <a href="/dashboard">📊 Tableau de bord</a>
                <a href="/reservations/create" class="btn btn-primary btn-sm" style="color:#fff;">+ Réserver</a>
                <span style="color: #94a3b8; font-size: 0.9rem; margin-left: 8px; display: inline-flex; align-items: center; gap: 6px;">
                    👤 <?= htmlspecialchars($_SESSION['user']['nom'] ?? '') ?>
                    <?php if ($isAdmin): ?>
                        <span class="badge badge-danger" style="font-size: 0.7rem; padding: 2px 6px;">Admin</span>
                    <?php else: ?>
                        <span class="badge badge-info" style="font-size: 0.7rem; padding: 2px 6px;">Responsable</span>
                    <?php endif; ?>
                </span>
                <a href="/logout" class="btn btn-secondary btn-sm" style="color:#e2e8f0; background-color:#334155; border-color:#475569;">
                    Déconnexion
                </a>
            </div>
        </nav>
    </header>
    <?php endif; ?>

    <main class="container">
        <?php if (!empty($_SESSION['flash_success'])): ?>
            <div class="alert alert-success">
                <span>✅ <?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
            <div class="alert alert-danger">
                <span>⚠️ <?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?= $content ?? "" ?>
    </main>

    <footer>
        <p>&copy; <?= date('Y') ?> UnivRésa — Système de Gestion des Réservations de Salles Universitaires</p>
    </footer>
</body>
</html>
