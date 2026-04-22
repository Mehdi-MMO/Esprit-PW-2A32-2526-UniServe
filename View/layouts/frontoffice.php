<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - FrontOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $this->url('/public/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/public/css/frontoffice.css') ?>">
</head>
<body>

    <!-- ===== NAVBAR ===== -->
    <nav class="navbar navbar-expand-lg us-topbar navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= $this->url('/dashboard/index') ?>">
                <span class="us-brand-mark" aria-hidden="true">U</span>
                <span>UniServe</span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarFront">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarFront">
                <ul class="navbar-nav mx-auto gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'active' : '' ?>"
                           href="<?= $this->url('/dashboard/index') ?>">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $this->url('/dashboard/index') ?>">Mon profil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'demandes') !== false ? 'active' : '' ?>"
                           href="<?= $this->url('/demandes') ?>">Demandes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'rendezvous') !== false ? 'active' : '' ?>"
                           href="<?= $this->url('/rendezvous') ?>">Rendez-vous</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'documents') !== false ? 'active' : '' ?>"
                           href="<?= $this->url('/documents/index') ?>">Documents</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], 'evenements') !== false ? 'active' : '' ?>"
                           href="<?= $this->url('/evenements') ?>">Événements</a>
                    </li>
                </ul>

                <!-- Menu utilisateur -->
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm d-flex align-items-center gap-2 dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="us-brand-mark" style="width:28px;height:28px;font-size:.8rem" aria-hidden="true">U</span>
                        Mon compte
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <span class="dropdown-item-text fw-semibold">
                                <?= htmlspecialchars((string)($_SESSION['user']['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                <?= htmlspecialchars((string)($_SESSION['user']['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- ===== CONTENU ===== -->
    <main class="container-fluid py-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/public/js/main.js') ?>"></script>
    <script src="<?= $this->url('/public/js/abc.js') ?>"></script>
</body>
</html>