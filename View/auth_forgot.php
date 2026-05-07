<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="us-card p-4 p-md-4">
                <h1 class="h4 mb-2">Mot de passe oublie</h1>
                <p class="text-muted small mb-3">Entrez votre adresse @gmail.com pour recevoir un code OTP.</p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php elseif (!empty($success)): ?>
                    <div class="alert alert-success py-2 small mb-3" role="alert">
                        <?= htmlspecialchars((string) $success, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="<?= $this->url('/auth/forgot') ?>" data-validate-account-form="1">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email</label>
                        <input type="email" id="email" class="form-control" name="email" value="<?= htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="prenom.nom@gmail.com" data-required-label="Email" data-validate-email="institutional" required>
                        <div class="form-text">Adresse @gmail.com uniquement.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Envoyer le code</button>
                </form>

                <div class="text-center mt-3">
                    <a href="<?= $this->url('/auth/login') ?>" class="small text-decoration-none">Retour a la connexion</a>
                </div>
            </div>
        </div>
    </div>
</section>
