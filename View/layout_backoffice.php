<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - BackOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/backoffice.css') ?>">
</head>
<body>
    <?php
    $currentPath = trim((string) ($_GET['url'] ?? ''), '/');
    $pathStartsWith = static function (string $prefix) use ($currentPath): bool {
        return $currentPath === $prefix || str_starts_with($currentPath, $prefix . '/');
    };
    $dashboardActive = $pathStartsWith('backoffice') || $pathStartsWith('dashboard');
    ?>
    <aside class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
            <span class="us-brand-mark" aria-hidden="true">U</span>
            <div class="lh-sm">
                <div class="fw-bold">UniServe</div>
                <div class="small text-white-50">BackOffice</div>
            </div>
        </div>
        <nav class="nav flex-column p-3 gap-1">
            <a class="nav-link <?= $dashboardActive ? 'active' : '' ?>" href="<?= $this->url('/backoffice/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
            <a class="nav-link <?= $pathStartsWith('utilisateurs') ? 'active' : '' ?>" href="<?= $this->url('/utilisateurs') ?>"><i class="bi bi-people me-2"></i>Utilisateurs</a>
            <a class="nav-link <?= $pathStartsWith('demandes') ? 'active' : '' ?>" href="<?= $this->url('/demandes') ?>"><i class="bi bi-journal-text me-2"></i>Demandes</a>
            <a class="nav-link <?= $pathStartsWith('rendezvous') ? 'active' : '' ?>" href="<?= $this->url('/rendezvous') ?>"><i class="bi bi-calendar-check me-2"></i>Rendez-vous</a>
            <a class="nav-link <?= $pathStartsWith('documents') ? 'active' : '' ?>" href="<?= $this->url('/documents') ?>"><i class="bi bi-file-earmark-text me-2"></i>Documents</a>
            <a class="nav-link <?= $pathStartsWith('evenements') ? 'active' : '' ?>" href="<?= $this->url('/evenements') ?>"><i class="bi bi-calendar-event me-2"></i>Événements</a>
        </nav>
    </aside>

    <header class="top-header ms-sidebar d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-white">
        <span class="fw-semibold">
            <?= htmlspecialchars((string) ($_SESSION['user']['prenom'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?>
            <?= htmlspecialchars((string) ($_SESSION['user']['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <a href="<?= $this->url('/auth/logout') ?>" class="btn btn-outline-danger btn-sm">Déconnexion</a>
    </header>

    <main class="ms-sidebar p-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/View/shared/js/main.js') ?>"></script>
</body>
</html>
