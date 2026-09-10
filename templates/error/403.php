<div class="card" style="text-align: center; padding: 60px 20px; max-width: 600px; margin: 40px auto;">
    <div style="font-size: 4rem; margin-bottom: 16px;">🚫</div>
    <h1 style="color: #ef4444; font-size: 2rem; margin-bottom: 12px;">403 — Accès interdit</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 24px;">
        <?= htmlspecialchars($message ?? "Vous n'avez pas les autorisations nécessaires pour accéder à cette ressource.") ?>
    </p>
    <a href="/salles" class="btn btn-primary">Retour aux salles</a>
</div>
