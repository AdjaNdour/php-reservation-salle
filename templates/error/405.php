<div class="card" style="text-align: center; padding: 60px 20px; max-width: 600px; margin: 40px auto;">
    <div style="font-size: 4rem; margin-bottom: 16px;">🚫</div>
    <h1 style="color: #ef4444; font-size: 2rem; margin-bottom: 12px;">405 — Méthode non autorisée</h1>
    <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 16px;">
        <?= htmlspecialchars($message ?? 'La méthode HTTP utilisée pour cette requête n\'est pas autorisée.') ?>
    </p>
    <?php if (!empty($allowedMethods)): ?>
        <p style="margin-bottom: 24px; font-size: 0.95rem;">
            Méthodes autorisées : <strong><?= htmlspecialchars(implode(', ', $allowedMethods)) ?></strong>
        </p>
    <?php endif; ?>
    <a href="/salles" class="btn btn-primary">Retour à l'accueil</a>
</div>
