<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="us-card p-4 p-md-4">
                <?php require __DIR__ . '/shared/auth_brand_strip.php'; ?>
                <h1 class="h4 mb-2">Mot de passe oublié</h1>
                <p class="text-muted small mb-3">Entrez votre adresse email institutionnelle pour recevoir un code OTP.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $this->url('/auth/forgot') ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" id="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="prenom.nom@domaine.autorisé" autocomplete="email" required>
                        <div class="form-text">Domaine autorisé selon <code>INSTITUTIONAL_EMAIL_DOMAINS</code> (voir .env).</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Envoyer le code</button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= $this->url('/auth/login') ?>" class="small text-decoration-none">Retour à la connexion</a>
                </div>
            </div>
        </div>
    </div>
</section>
