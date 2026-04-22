<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/main.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg us-topbar navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $this->url('/') ?>">
                <span class="us-brand-mark" aria-hidden="true">U</span>
                <span>UniServe</span>
            </a>
            <a class="btn btn-outline-light btn-sm px-3" href="<?= $this->url('/auth/login') ?>">Se connecter</a>
        </div>
    </nav>
    <main class="pt-4 pb-5">
        <?php if (!empty($content)): ?>
            <?= $content ?>
        <?php endif; ?>
    </main>

    <footer class="py-4 border-top bg-white">
        <div class="container text-center text-muted">
            &copy; <?= date('Y') ?> UniServe. Tous droits réservés.
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/View/shared/js/main.js') ?>"></script>
</body>
</html>
