<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="us-card p-4 p-md-4">
                <h1 class="h4 mb-2">Nouveau mot de passe</h1>
                <p class="text-muted small mb-3">Choisissez un nouveau mot de passe sécurisé.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $this->url('/auth/reset/' . (string) ($resetToken ?? '')) ?>">
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-semibold">Nouveau mot de passe</label>
                        <input type="password" id="new_password" class="form-control" name="new_password" autocomplete="new-password" required minlength="<?= (int) User::MIN_PASSWORD_LENGTH ?>">
                        <div class="form-text">Minimum <?= (int) User::MIN_PASSWORD_LENGTH ?> caractères.</div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-semibold">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" class="form-control" name="confirm_password" autocomplete="new-password" required minlength="<?= (int) User::MIN_PASSWORD_LENGTH ?>">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Mettre à jour le mot de passe</button>
                </form>
            </div>
        </div>
    </div>
</section>
