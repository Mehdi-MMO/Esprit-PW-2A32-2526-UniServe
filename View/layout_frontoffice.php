<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - FrontOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/frontoffice.css') ?>">
</head>
<body>
    <?php
    $currentPath = trim((string) ($_GET['url'] ?? ''), '/');
    $pathStartsWith = static function (string $prefix) use ($currentPath): bool {
        return $currentPath === $prefix || str_starts_with($currentPath, $prefix . '/');
    };
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top us-topbar shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $this->url('/') ?>">
                <span class="us-brand-mark" aria-hidden="true">U</span>
                <span>UniServe</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#frontNav" aria-controls="frontNav" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="frontNav">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-2">
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('frontoffice') ? 'active' : '' ?>" href="<?= $this->url('/frontoffice/dashboard') ?>">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('demandes') ? 'active' : '' ?>" href="<?= $this->url('/demandes') ?>">Demandes</a></li>
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('rendezvous') ? 'active' : '' ?>" href="<?= $this->url('/rendezvous') ?>">Rendez-vous</a></li>
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('documents') ? 'active' : '' ?>" href="<?= $this->url('/documents') ?>">Documents</a></li>
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('evenements') ? 'active' : '' ?>" href="<?= $this->url('/evenements') ?>">Événements</a></li>
                </ul>
                <div class="dropdown ms-lg-2">
                    <button class="btn btn-outline-light dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="rounded-circle bg-light text-dark px-2 py-1 me-2">U</span>
                        <span class="d-none d-sm-inline">Mon compte</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $this->url('/users/profile') ?>">Mon profil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mt-5 pt-4 pb-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/View/shared/js/main.js') ?>"></script>
</body>
</html>
