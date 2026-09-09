<?php
$valNom = (string) ($data['nom'] ?? '');
$valEmail = (string) ($data['email'] ?? '');
$valRole = (string) ($data['role'] ?? 'responsable');
?>

<div class="auth-wrapper" style="max-width: 480px; margin: 40px auto;">
    <div class="card" style="padding: 32px; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 3rem; margin-bottom: 8px;">📝</div>
            <h1 style="font-size: 1.5rem; margin-bottom: 6px;">Inscription</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Créez votre compte pour réserver des salles
            </p>
        </div>

        <form method="POST" action="/register" novalidate>
            <!-- Nom complet -->
            <div class="form-group">
                <label for="nom">Nom complet *</label>
                <input 
                    type="text" 
                    id="nom" 
                    name="nom" 
                    value="<?= htmlspecialchars($valNom) ?>" 
                    placeholder="ex : Mariama Diop" 
                    class="<?= isset($errors['nom']) ? 'input-error' : '' ?>"
                    required 
                    autofocus
                >
                <?php if (isset($errors['nom'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['nom']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Adresse électronique institutionnelle *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="<?= htmlspecialchars($valEmail) ?>" 
                    placeholder="ex : mariama.diop@univ.sn" 
                    class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
                    required
                >
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Rôle -->
            <div class="form-group">
                <label for="role">Profil / Rôle</label>
                <select id="role" name="role">
                    <option value="responsable" <?= $valRole === 'responsable' ? 'selected' : '' ?>>Responsable de salle / Événement</option>
                    <option value="enseignant" <?= $valRole === 'enseignant' ? 'selected' : '' ?>>Enseignant / Chercheur</option>
                </select>
            </div>

            <!-- Mot de passe -->
            <div class="form-group">
                <label for="password">Mot de passe (min. 6 caractères) *</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    class="<?= isset($errors['password']) ? 'input-error' : '' ?>"
                    required
                >
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Confirmation Mot de passe -->
            <div class="form-group">
                <label for="password_confirmation">Confirmer le mot de passe *</label>
                <input 
                    type="password" 
                    id="password_confirmation" 
                    name="password_confirmation" 
                    placeholder="••••••••" 
                    class="<?= isset($errors['password_confirmation']) ? 'input-error' : '' ?>"
                    required
                >
                <?php if (isset($errors['password_confirmation'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password_confirmation']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Bouton de soumission -->
            <div style="margin-top: 24px;">
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 11px; font-size: 1rem;">
                    Créer mon compte
                </button>
            </div>
        </form>

        <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color); text-align: center; font-size: 0.9rem; color: var(--text-muted);">
            Vous avez déjà un compte ? 
            <a href="/login" style="color: var(--primary-color); font-weight: 600; text-decoration: none;">
                Se connecter
            </a>
        </div>
    </div>
</div>
