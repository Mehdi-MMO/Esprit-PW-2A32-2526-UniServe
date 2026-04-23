<section class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 text-center">
            <h2 class="fw-bold mb-3"><?= htmlspecialchars($title ?? 'UniServe', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="text-muted mb-4">
                Connectez-vous pour acceder a vos services universitaires en ligne.
            </p>
            <a href="<?= $this->url('/auth/login') ?>" class="btn btn-primary px-4">Se connecter</a>
        </div>
    </div>
</section>
