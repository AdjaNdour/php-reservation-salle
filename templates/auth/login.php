<?php
$valEmail = (string) ($data['email'] ?? '');
?>

<div class="auth-wrapper" style="max-width: 440px; margin: 40px auto;">
    <div class="card" style="padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 3rem; margin-bottom: 8px;">🏛️</div>
            <h1 style="font-size: 1.6rem; margin-bottom: 4px; color: #0f172a;">UnivRésa</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 12px;">
                Système de Réservation de Salles Universitaires
            </p>
            <div style="display: inline-block; padding: 4px 12px; background: #eff6ff; color: #2563eb; border-radius: 9999px; font-size: 0.85rem; font-weight: 600;">
                🔐 Espace Authentification
            </div>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-danger" style="margin-bottom: 20px; font-size: 0.9rem;">
                <span>⚠️ <?= htmlspecialchars($errors['general']) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" novalidate>
            <!-- Email -->
            <div class="form-group">
                <label for="email">Adresse électronique</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= htmlspecialchars($valEmail) ?>" 
                    placeholder="ex : adja@univ.sn" 
                    class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
                    autofocus
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    class="<?= isset($errors['password']) ? 'input-error' : '' ?>"
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Bouton de soumission -->
            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px; font-size: 1rem;">
                    Se connecter
                </button>
            </div>
        </form>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center; font-size: 0.9rem; color: var(--text-muted);">
            Vous n'avez pas encore de compte ? 
            <a href="/register" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">
                Créer un compte
            </a>
        </div>
    </div>

    <!-- Boîte d'aide pour tester facilement -->
    <div style="margin-top: 16px; padding: 14px; background: #f8fafc; border: 1px dashed var(--border-color); border-radius: var(--radius-md); font-size: 0.85rem; color: var(--text-muted);">
        <strong>💡 Comptes de démonstration :</strong>
        <div style="margin-top: 6px;">
            • <code>adja@univ.sn</code> / <code>passer123</code> (Enseignant)<br>
            • <code>mariama@univ.sn</code> / <code>passer123</code> (Responsable)<br>
            • <code>admin@univ.sn</code> / <code>admin123</code> (Admin)
        </div>
    </div>
</div>
