<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    $currentPath = trim((string) ($_GET['url'] ?? ''), '/');
    $pathStartsWith = static function (string $prefix) use ($currentPath): bool {
        return $currentPath === $prefix || str_starts_with($currentPath, $prefix . '/');
    };
    $clubsNavActive = $pathStartsWith('evenements/clubs')
        || $pathStartsWith('evenements/clubShow')
        || ($pathStartsWith('evenements/createClubRequestForm') || $pathStartsWith('evenements/createClubRequest'))
        || $pathStartsWith('clubs')
        || $pathStartsWith('events/clubs')
        || $pathStartsWith('events/clubShow')
        || ($pathStartsWith('events/createClubRequestForm') || $pathStartsWith('events/createClubRequest'));
    $eventsNavActive = ($pathStartsWith('evenements') || $pathStartsWith('events')) && !$clubsNavActive;
    $clubsEventsNavActive = $clubsNavActive || $eventsNavActive;
    ?>
    <meta name="description" content="Portail UniServe — services universitaires, demandes, rendez-vous, documents, vie associative.">
    <title><?= htmlspecialchars(isset($title) && $title !== '' ? (string) $title . ' · UniServe' : 'UniServe', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <?php if ($eventsNavActive): ?>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $this->asset('/View/shared/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('/View/shared/css/frontoffice.css') ?>">
</head>
<body class="us-frontoffice-layout">
    <a class="visually-hidden-focusable btn btn-sm btn-outline-secondary position-fixed top-0 start-0 m-2 z-3" href="#contenu-principal" style="background: var(--surface);">Aller au contenu</a>
    <?php
    $notifCount = 0;
    if (isset($_SESSION['user']['id'])) {
        try {
            $notifCount = (new NotificationModel())->countUnread((int) $_SESSION['user']['id']);
        } catch (Throwable $e) {
            $notifCount = 0;
        }
    }

    require_once __DIR__ . '/shared/helpers.php';
    $navPhotoUrl = null;
    $navAvatarInitial = 'U';
    $navDisplayName = '';
    $navEmail = '';
    if (isset($_SESSION['user']['id'])) {
        $navPhotoUrl = profile_photo_public_url((string) ($_SESSION['user']['photo_profil'] ?? ''), $this);
        $navAvatarInitial = profile_avatar_initial(
            (string) ($_SESSION['user']['prenom'] ?? ''),
            (string) ($_SESSION['user']['nom'] ?? '')
        );
        $navDisplayName = trim(trim((string) ($_SESSION['user']['prenom'] ?? '')) . ' ' . trim((string) ($_SESSION['user']['nom'] ?? '')));
        $navEmail = trim((string) ($_SESSION['user']['email'] ?? ''));
    }
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
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1 gap-xxl-2 us-front-nav-main">
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('frontoffice') ? 'active' : '' ?>" href="<?= $this->url('/frontoffice/dashboard') ?>" title="Tableau de bord">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link <?= ($pathStartsWith('demandes') || $pathStartsWith('services')) ? 'active' : '' ?>" href="<?= $this->url('/demandes') ?>" title="Demandes de service : vos dossiers, nouvelle demande, et catalogue des types"><span class="d-xl-none">Demandes</span><span class="d-none d-xl-inline">Mes demandes</span></a></li>
                    <li class="nav-item"><a class="nav-link <?= $pathStartsWith('rendezvous') ? 'active' : '' ?>" href="<?= $this->url('/rendezvous') ?>" title="Prendre rendez-vous avec un bureau"><span class="d-xl-none">RDV</span><span class="d-none d-xl-inline">Rendez-vous</span></a></li>
                    <?php
                    $documentsNavActive = $pathStartsWith('documents');
                    $certificationsNavActive = $pathStartsWith('certifications');
                    $documentsGroupActive = $documentsNavActive || $certificationsNavActive;
                    ?>
                    <li class="nav-item dropdown us-nav-documents">
                        <a class="nav-link dropdown-toggle<?= $documentsGroupActive ? ' active' : '' ?>"
                           href="#"
                           id="usNavDocuments"
                           role="button"
                           data-bs-toggle="dropdown"
                           data-bs-auto-close="true"
                           aria-expanded="false"
                           aria-haspopup="true"
                           title="Cours et certifications (DOCAC)">
                            <span class="d-xl-none">Docs</span><span class="d-none d-xl-inline">Documents</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end border-0 shadow" aria-labelledby="usNavDocuments">
                            <li>
                                <a class="dropdown-item<?= $certificationsNavActive ? ' active' : '' ?>" href="<?= htmlspecialchars($this->url('/certifications') . '#us-parcours-cours', ENT_QUOTES, 'UTF-8') ?>">Cours</a>
                            </li>
                            <li>
                                <a class="dropdown-item<?= $certificationsNavActive ? ' active' : '' ?>" href="<?= htmlspecialchars($this->url('/certifications') . '#us-parcours-certificats', ENT_QUOTES, 'UTF-8') ?>">Certifications</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown us-nav-clubs-events">
                        <a class="nav-link dropdown-toggle<?= $clubsEventsNavActive ? ' active' : '' ?>"
                           href="#"
                           id="usNavClubsEvents"
                           role="button"
                           data-bs-toggle="dropdown"
                           data-bs-auto-close="true"
                           aria-expanded="false"
                           aria-haspopup="true"
                           title="Clubs étudiants et événements (inscriptions, agenda)">
                            <span class="d-xl-none">Clubs &amp; agenda</span><span class="d-none d-xl-inline">Clubs &amp; événements</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end border-0 shadow" aria-labelledby="usNavClubsEvents">
                            <li>
                                <a class="dropdown-item<?= $clubsNavActive ? ' active' : '' ?>" href="<?= $this->url('/evenements/clubs') ?>">Clubs</a>
                            </li>
                            <li>
                                <a class="dropdown-item<?= $eventsNavActive ? ' active' : '' ?>" href="<?= $this->url('/evenements') ?>">Événements</a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2 ms-lg-2">
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
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle d-inline-flex align-items-center px-2 px-sm-3" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Mon compte">
                            <?php if ($navPhotoUrl !== null): ?>
                                <img src="<?= htmlspecialchars($navPhotoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="" class="us-nav-account-avatar rounded-circle me-2" width="32" height="32" decoding="async">
                            <?php else: ?>
                                <span class="us-nav-account-initial rounded-circle me-2" aria-hidden="true"><?= htmlspecialchars($navAvatarInitial, ENT_QUOTES, 'UTF-8') ?></span>
                            <?php endif; ?>
                            <span class="d-none d-sm-inline">Mon compte</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <?php if ($navDisplayName !== '' || $navEmail !== ''): ?>
                                <li class="px-3 pt-2 pb-1 text-start">
                                    <?php if ($navDisplayName !== ''): ?>
                                        <div class="fw-semibold small"><?= htmlspecialchars($navDisplayName, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                    <?php if ($navEmail !== ''): ?>
                                        <div class="small text-muted text-truncate" style="max-width: 14rem;"><?= htmlspecialchars($navEmail, ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= $this->url('/users/profile') ?>">Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">Déconnexion</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <main id="contenu-principal" class="container-fluid us-main-front px-3 px-sm-4 px-xl-5 mt-5 pt-4 pb-5">
        <?= $content ?>
    </main>

    <footer class="us-site-footer border-top mt-auto py-4">
        <div class="container">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 small text-muted">
                <span>&copy; <?= date('Y') ?> UniServe</span>
                <span class="text-center text-md-end">Portail des services universitaires</span>
            </div>
        </div>
    </footer>

    <?php require __DIR__ . '/shared/chat_widget.php'; ?>
    <?php if ($eventsNavActive): ?>
        <?php require __DIR__ . '/shared/front_map_widget.php'; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php if ($eventsNavActive): ?>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <?php endif; ?>
    <script src="<?= $this->asset('/View/shared/js/main.js') ?>"></script>
    <?php if (isset($_SESSION['user']['id'])): ?>
        <script src="<?= $this->asset('/View/shared/js/notifications.js') ?>"></script>
    <?php endif; ?>
</body>
</html>
