<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= htmlspecialchars(isset($title) && $title !== '' ? (string) $title . ' · UniServe Admin' : 'UniServe · Admin', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $this->asset('/View/shared/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('/View/shared/css/backoffice.css') ?>">
    <link rel="icon" href="<?= $this->asset('/View/shared/assets/img/logo.png') ?>" type="image/png">
</head>
<body class="us-backoffice-layout">
    <?php
    $currentPath = trim((string) ($_GET['url'] ?? ''), '/');
    $pathStartsWith = static function (string $prefix) use ($currentPath): bool {
        return $currentPath === $prefix || str_starts_with($currentPath, $prefix . '/');
    };
    $dashboardActive = $pathStartsWith('backoffice') || $pathStartsWith('dashboard');
    $rdvModuleActive = $pathStartsWith('rendezvous') || $pathStartsWith('bureaux');
    $clubsEventsNavActive = $pathStartsWith('evenements');
    $demandesQueueActive = $pathStartsWith('demandes');
    $typesDemandesActive = $pathStartsWith('services');
    $documentsScolariteActive = $pathStartsWith('documents');
    $notifCount = 0;
    if (isset($_SESSION['user']['id'])) {
        try {
            $notifCount = (new NotificationModel())->countUnread((int) $_SESSION['user']['id']);
        } catch (Throwable $e) {
            $notifCount = 0;
        }
    }
    ?>
    <aside class="sidebar d-flex flex-column shadow-sm">
        <div class="p-4 border-bottom border-secondary-subtle d-flex align-items-center gap-2 flex-shrink-0">
            <?= us_brand_logo_html($this, 'us-brand-logo--sidebar', true) ?>
            <div class="lh-sm">
                <div class="fw-bold">UniServe</div>
                <div class="small text-white-50">BackOffice</div>
            </div>
        </div>
        <nav class="nav flex-column p-3 gap-1 flex-grow-1 overflow-y-auto">
            <a class="nav-link <?= $dashboardActive ? 'active' : '' ?>" href="<?= $this->url('/backoffice/dashboard') ?>"><i class="bi bi-speedometer2 me-2"></i>Tableau de bord</a>
            <a class="nav-link <?= $pathStartsWith('utilisateurs') ? 'active' : '' ?>" href="<?= $this->url('/utilisateurs') ?>"><i class="bi bi-people me-2"></i>Utilisateurs</a>
            <a class="nav-link <?= $clubsEventsNavActive ? 'active' : '' ?>" href="<?= $this->url('/evenements/manage') ?>" <?= $clubsEventsNavActive ? 'aria-current="page"' : '' ?>><i class="bi bi-collection me-2"></i>Clubs &amp; événements</a>
            <div class="us-sidebar-nav-group">
                <a class="nav-link <?= $demandesQueueActive ? 'active' : '' ?>"
                   href="<?= $this->url('/demandes') ?>"
                   <?= $demandesQueueActive ? 'aria-current="page"' : '' ?>><i class="bi bi-journal-text me-2"></i>Demandes</a>
                <a class="nav-link nav-link--sub <?= $typesDemandesActive ? 'active' : '' ?>"
                   href="<?= $this->url('/services') ?>"
                   <?= $typesDemandesActive ? 'aria-current="page"' : '' ?>><i class="bi bi-grid me-2"></i>Types de demandes</a>
            </div>
            <div class="us-sidebar-nav-group">
                <a class="nav-link <?= $rdvModuleActive ? 'active' : '' ?>"
                   href="<?= $this->url('/rendezvous') ?>"
                   <?= $rdvModuleActive ? 'aria-current="page"' : '' ?>><i class="bi bi-calendar-check me-2"></i>Rendez-vous</a>
            </div>
            <a class="nav-link <?= $documentsScolariteActive ? 'active' : '' ?>"
               href="<?= $this->url('/documents') ?>"
               <?= $documentsScolariteActive ? 'aria-current="page"' : '' ?>><i class="bi bi-file-earmark-text me-2"></i>Documents (scolarité)</a>
            <a class="nav-link <?= $pathStartsWith('certifications') ? 'active' : '' ?>" href="<?= $this->url('/certifications/manage') ?>"><i class="bi bi-mortarboard me-2"></i>Certifications (parcours)</a>
        </nav>
    </aside>

    <header class="top-header ms-sidebar d-flex flex-wrap justify-content-between align-items-center px-3 px-md-4 py-3 border-bottom bg-white">
        <div class="min-w-0 flex-grow-1 me-2">
            <div class="us-kicker mb-1">Back office</div>
            <span class="fw-semibold text-truncate d-inline-block mw-100">
                <?= htmlspecialchars((string) ($_SESSION['user']['prenom'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars((string) ($_SESSION['user']['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-auto">
            <?php if (isset($_SESSION['user']['id'])): ?>
                <div class="dropdown us-notifs-dropdown"
                     data-notifs-root
                     data-endpoint="<?= $this->url('/notifications/getUnread') ?>"
                     data-mark-endpoint="<?= $this->url('/notifications/markReadJson') ?>"
                     data-mark-all-endpoint="<?= $this->url('/notifications/markAllRead') ?>"
                     data-page-url="<?= $this->url('/notifications') ?>">
                    <button class="us-notifs-bell <?= $pathStartsWith('notifications') ? 'is-active' : '' ?>"
                            type="button"
                            data-bs-toggle="dropdown"
                            data-bs-auto-close="outside"
                            aria-expanded="false"
                            aria-label="Notifications"
                            title="Notifications">
                        <i class="fa-regular fa-bell" aria-hidden="true"></i>
                        <span class="us-nav-badge us-notifs-count" data-notifs-badge<?= $notifCount > 0 ? '' : ' hidden' ?>><?= (int) $notifCount ?></span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end us-notifs-panel" data-notifs-panel>
                        <div class="us-notifs-panel-head">
                            <div>
                                <div class="us-kicker">Notifications</div>
                                <div class="us-notifs-count-label" data-notifs-count-label>
                                    <?= $notifCount > 0
                                        ? (int) $notifCount . ' non lue' . ($notifCount > 1 ? 's' : '')
                                        : 'Tout est à jour' ?>
                                </div>
                            </div>
                            <a class="us-notifs-panel-link" href="<?= $this->url('/notifications') ?>" title="Ouvrir la page des notifications" aria-label="Ouvrir la page des notifications">
                                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                            </a>
                        </div>
                        <div class="us-notifs-panel-body" data-notifs-list>
                            <div class="us-notifs-loading">
                                <i class="fa-solid fa-circle-notch fa-spin" aria-hidden="true"></i>
                                <span>Chargement&hellip;</span>
                            </div>
                        </div>
                        <div class="us-notifs-panel-foot">
                            <form method="post" action="<?= $this->url('/notifications/markAllRead') ?>" class="m-0" data-notifs-markall-form>
                                <button type="submit" class="btn btn-sm btn-outline-secondary">Tout marquer lu</button>
                            </form>
                            <a class="btn btn-sm btn-primary" href="<?= $this->url('/notifications') ?>">Voir tout</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <a href="<?= $this->url('/auth/logout') ?>" class="btn btn-outline-danger btn-sm">Déconnexion</a>
        </div>
    </header>

    <main class="ms-sidebar p-3 p-md-4">
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/shared/chat_widget.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->asset('/View/shared/js/main.js') ?>"></script>
    <?php if (isset($_SESSION['user']['id'])): ?>
        <script src="<?= $this->asset('/View/shared/js/notifications.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
