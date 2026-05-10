<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - BackOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
    $segments = $currentPath === '' ? [] : explode('/', $currentPath);
    $seg2 = $segments[1] ?? '';
    $clubSecond = ['manageClubs', 'createClubForm', 'createClub', 'editClubForm', 'editClub', 'deleteClub', 'approveClub', 'rejectClub'];
    $eventSecond = ['manage', 'createForm', 'create', 'editForm', 'edit', 'delete', 'approveEvent', 'rejectEvent', 'inscriptions', 'checkIn'];
    $evenementsPrefix = (($segments[0] ?? '') === 'evenements');
    $clubsNavActive = $evenementsPrefix && in_array($seg2, $clubSecond, true);
    $eventsNavActive = $evenementsPrefix && in_array($seg2, $eventSecond, true);
    ?>
    <aside class="sidebar d-flex flex-column shadow-sm">
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
            <a class="nav-link <?= $clubsNavActive ? 'active' : '' ?>" href="<?= $this->url('/evenements/manageClubs') ?>"><i class="bi bi-collection me-2"></i>Clubs</a>
            <a class="nav-link <?= $eventsNavActive ? 'active' : '' ?>" href="<?= $this->url('/evenements/manage') ?>"><i class="bi bi-calendar-event me-2"></i>Événements</a>
            <a class="nav-link <?= $pathStartsWith('demandes') ? 'active' : '' ?>" href="<?= $this->url('/demandes') ?>"><i class="bi bi-journal-text me-2"></i>Demandes</a>
            <a class="nav-link <?= $pathStartsWith('rendezvous') ? 'active' : '' ?>" href="<?= $this->url('/rendezvous') ?>"><i class="bi bi-calendar-check me-2"></i>Rendez-vous</a>
            <a class="nav-link <?= $pathStartsWith('documents') ? 'active' : '' ?>" href="<?= $this->url('/documents') ?>"><i class="bi bi-file-earmark-text me-2"></i>Documents</a>
        </nav>
    </aside>

    <header class="top-header ms-sidebar d-flex justify-content-between align-items-center px-3 px-md-4 py-3 border-bottom bg-white">
        <div>
            <div class="us-kicker mb-1">Back office</div>
            <span class="fw-semibold">
                <?= htmlspecialchars((string) ($_SESSION['user']['prenom'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars((string) ($_SESSION['user']['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= $this->url('/users/profile') ?>" class="btn btn-outline-secondary btn-sm">Mon profil</a>
            <a href="<?= $this->url('/auth/logout') ?>" class="btn btn-outline-danger btn-sm">Déconnexion</a>
        </div>
    </header>

    <main class="ms-sidebar p-3 p-md-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/View/shared/js/main.js') ?>"></script>
</body>
</html>
