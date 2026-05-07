<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - FrontOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/View/shared/css/frontoffice.css') ?>">
</head>
<body>
    <?php
    $currentPath = trim((string) ($_GET['url'] ?? ''), '/');
    $pathStartsWith = static function (string $prefix) use ($currentPath): bool {
        return $currentPath === $prefix || str_starts_with($currentPath, $prefix . '/');
    };

    $notifications = [];
    $notifCount = 0;
    $userId = (int) ($_SESSION['user']['id'] ?? 0);
    if ($userId > 0) {
        try {
            $model = new Model();

            $nearestStmt = $model->query(
                'SELECT id, titre, date_debut
                 FROM evenements
                 WHERE statut = "ouvert" AND date_debut >= NOW()
                 ORDER BY date_debut ASC
                 LIMIT 1'
            );
            $nearest = $nearestStmt->fetch();
            if ($nearest) {
                $notifications[] = [
                    'label' => 'Prochain evenement: ' . (string) ($nearest['titre'] ?? ''),
                    'meta' => (string) ($nearest['date_debut'] ?? ''),
                    'url' => $this->url('/evenements/show/' . (int) ($nearest['id'] ?? 0)),
                ];
            }
            $approvedEventsStmt = $model->query(
                'SELECT id, titre, statut
                 FROM evenements
                 WHERE cree_par = ? AND statut = "ouvert"
                 ORDER BY id DESC
                 LIMIT 3',
                [$userId]
            );
            foreach ($approvedEventsStmt->fetchAll() as $evt) {
                $notifications[] = [
                    'label' => 'Evenement approuve: ' . (string) ($evt['titre'] ?? ''),
                    'meta' => 'Statut: ' . (string) ($evt['statut'] ?? ''),
                    'url' => $this->url('/evenements/show/' . (int) ($evt['id'] ?? 0)),
                ];
            }

            try {
                $approvedClubsStmt = $model->query(
                    'SELECT id, nom, statut_validation
                     FROM clubs
                     WHERE cree_par = ? AND statut_validation = "approuve"
                     ORDER BY id DESC
                     LIMIT 3',
                    [$userId]
                );
                foreach ($approvedClubsStmt->fetchAll() as $club) {
                    $notifications[] = [
                        'label' => 'Club approuve: ' . (string) ($club['nom'] ?? ''),
                        'meta' => 'Validation: ' . (string) ($club['statut_validation'] ?? ''),
                        'url' => $this->url('/evenements/clubShow/' . (int) ($club['id'] ?? 0)),
                    ];
                }
            } catch (Throwable $_ignoreClubSchema) {
                // Clubs schema might not yet have ownership/validation columns.
            }
        } catch (Throwable $_ignoreNotifications) {
            // Fail-safe: never block layout for notification errors.
        }
    }
    $notifCount = count($notifications);
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
                    <button class="btn btn-outline-light position-relative px-3 me-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <?php if ($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notifCount ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px;">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <?php if ($notifCount === 0): ?>
                            <li><span class="dropdown-item-text text-muted small">Aucune notification pour le moment.</span></li>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="<?= htmlspecialchars((string) ($notif['url'] ?? '#'), ENT_QUOTES, 'UTF-8') ?>">
                                        <div class="fw-semibold small"><?= htmlspecialchars((string) ($notif['label'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars((string) ($notif['meta'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="dropdown ms-lg-1">
                    <button class="btn btn-outline-light dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="rounded-circle bg-light text-dark px-2 py-1 me-2">U</span>
                        <span class="d-none d-sm-inline">Mon compte</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= $this->url('/auth/logout') ?>">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mt-5 pt-4 pb-4">
        <?= $content ?>
    </main>

    <?php require __DIR__ . '/shared/chat_widget.php'; ?>
    <?php require __DIR__ . '/shared/front_map_widget.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="<?= $this->url('/View/shared/js/main.js') ?>"></script>
</body>
</html>
