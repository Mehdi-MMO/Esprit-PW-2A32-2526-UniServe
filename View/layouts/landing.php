<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $this->url('/public/css/main.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg us-topbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $this->url('/') ?>">
                <span class="us-brand-mark" aria-hidden="true">U</span>
                <span>UniServe</span>
            </a>
            <a class="btn btn-outline-light btn-sm px-3" href="<?= $this->url('/auth/login') ?>">Connexion</a>
        </div>
    </nav>

    <header class="us-hero py-5">
        <div class="container py-3 py-lg-4">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="us-badge mb-3">Plateforme officielle</span>
                    <h1 class="display-5 fw-bold mb-3">Portail UniServe</h1>
                    <p class="lead text-muted mb-4">Connectez-vous pour acceder a vos services universitaires.</p>
                    <a href="<?= $this->url('/auth/login') ?>" class="btn btn-primary btn-lg px-4">Se connecter</a>
                </div>
                <div class="col-lg-5">
                    <div class="us-card p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="fw-semibold">Acces securise</div>
                            <span class="us-badge">UniServe</span>
                        </div>
                        <div class="text-muted">
                            Un environnement unifie pour les etudiants, enseignants et personnels, avec une presentation claire et institutionnelle.
                        </div>
                        <div class="us-divider my-3"></div>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="text-muted small">Connexion par email</span>
                            <span class="text-muted small">•</span>
                            <span class="text-muted small">Interface responsive</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <?php if (!empty($content)): ?>
        <div class="pb-5">
            <?= $content ?>
        </div>
    <?php endif; ?>

    <footer class="py-4 border-top bg-white">
        <div class="container text-center text-muted">
            &copy; <?= date('Y') ?> UniServe. Tous droits reserves.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/public/js/main.js') ?>"></script>
</body>
</html>
