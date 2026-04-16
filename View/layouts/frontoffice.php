<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - FrontOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $this->url('/public/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/public/css/frontoffice.css') ?>">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top us-topbar">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $this->url('/') ?>">
                <span class="us-brand-mark" aria-hidden="true">U</span>
                <span>UniServe</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontNav" aria-controls="frontNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="frontNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="<?= $this->url('/frontoffice/dashboard') ?>">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $this->url('/demandes') ?>">Demandes</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $this->url('/rendezvous') ?>">Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $this->url('/documents') ?>">Documents</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= $this->url('/evenements') ?>">Événements</a></li>
                </ul>
                <div class="dropdown">
                    <button class="btn btn-outline-light dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="rounded-circle bg-light text-dark px-2 py-1 me-2">U</span>
                        <span class="d-none d-sm-inline">Mon compte</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $this->url('/users/profile') ?>">Mon profil</a></li>
                        <li><a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mt-5 pt-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/public/js/main.js') ?>"></script>
</body>
</html>
