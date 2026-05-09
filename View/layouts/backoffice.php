<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniServe - BackOffice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $this->url('/public/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->url('/public/css/backoffice.css') ?>">
    <style>
        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
            padding: 3px 6px;
            border-radius: 50%;
        }
        .notif-dropdown {
            width: 300px;
            max-height: 400px;
            overflow-y: auto;
            z-index: 9999;
        }
        .notif-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            font-size: 13px;
        }
        .notif-item:last-child {
            border-bottom: none;
        }
        .notif-item.unread {
            background-color: #f8f9ff;
        }
    </style>
</head>
<body>
    <aside class="sidebar d-flex flex-column">
        <div class="p-4 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
            <span class="us-brand-mark" aria-hidden="true">U</span>
            <div class="lh-sm">
                <div class="fw-bold">UniServe</div>
                <div class="small text-white-50">BackOffice</div>
            </div>
        </div>
        <?php $currentUri = $_SERVER['REQUEST_URI'] ?? ''; ?>
        <nav class="nav flex-column p-3 gap-1">
            <a class="nav-link <?= strpos($currentUri, '/dashboard') !== false ? 'active' : '' ?>" href="<?= $this->url('/dashboard/index') ?>"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            <a class="nav-link <?= strpos($currentUri, '/utilisateurs') !== false ? 'active' : '' ?>" href="<?= $this->url('/utilisateurs') ?>"><i class="bi bi-people me-2"></i>Utilisateurs</a>
            <a class="nav-link <?= strpos($currentUri, '/demandes') !== false ? 'active' : '' ?>" href="<?= $this->url('/demandes/backoffice') ?>"><i class="bi bi-journal-text me-2"></i>Demandes de service</a>
            <a class="nav-link <?= strpos($currentUri, '/rendezvous') !== false ? 'active' : '' ?>" href="<?= $this->url('/rendezvous') ?>"><i class="bi bi-calendar-check me-2"></i>Rendez-vous</a>
            <a class="nav-link <?= strpos($currentUri, '/documents') !== false ? 'active' : '' ?>" href="<?= $this->url('/documents') ?>"><i class="bi bi-file-earmark-text me-2"></i>Documents académiques</a>
            <a class="nav-link <?= strpos($currentUri, '/evenements') !== false ? 'active' : '' ?>" href="<?= $this->url('/evenements') ?>"><i class="bi bi-calendar-event me-2"></i>Événements</a>
        </nav>
    </aside>

    <header class="top-header ms-sidebar d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-white">
        <div>
            <!-- Espace vide à gauche si besoin -->
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-light position-relative rounded-circle p-2" type="button" data-bs-toggle="dropdown" id="notifBtn">
                    <i class="bi bi-bell fs-5"></i>
                    <span class="badge bg-danger notif-badge d-none" id="notifBadge">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg notif-dropdown" aria-labelledby="notifBtn">
                    <div class="px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <span class="fw-bold">Notifications</span>
                        <a href="<?= $this->url('/notifications/markAllAsRead') ?>" class="small text-decoration-none">Tout lire</a>
                    </div>
                    <div id="notifList">
                        <div class="p-3 text-center text-muted small">Chargement...</div>
                    </div>
                </div>
            </div>
            <span class="fw-semibold me-2">
                <?= htmlspecialchars((string) ($_SESSION['user']['prenom'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?>
                <?= htmlspecialchars((string) ($_SESSION['user']['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            </span>
            <a href="<?= $this->url('/auth/logout') ?>" class="btn btn-outline-danger btn-sm">Deconnexion</a>
        </div>
    </header>

    <main class="ms-sidebar p-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/public/js/main.js') ?>"></script>
    <script>
        async function loadNotifications() {
            try {
                const response = await fetch('<?= $this->url('/notifications/getUnread') ?>');
                const notifs = await response.json();
                const badge = document.getElementById('notifBadge');
                const list = document.getElementById('notifList');
                
                if (notifs.length > 0) {
                    badge.innerText = notifs.length;
                    badge.classList.remove('d-none');
                    list.innerHTML = '';
                    notifs.forEach(n => {
                        list.innerHTML += `
                            <a href="<?= $this->url('/notifications/markAsRead/') ?>${n.id}" class="notif-item unread text-decoration-none text-dark d-block">
                                <div class="mb-1">${n.message}</div>
                                <div class="small text-muted">${new Date(n.cree_le).toLocaleString('fr-FR')}</div>
                            </a>
                        `;
                    });
                } else {
                    badge.classList.add('d-none');
                    list.innerHTML = '<div class="p-3 text-center text-muted small">Aucune nouvelle notification</div>';
                }
            } catch (e) {
                console.error("Erreur chargement notifications", e);
            }
        }
        
        loadNotifications();
        setInterval(loadNotifications, 5000); // Rafraîchir toutes les 5s pour plus de réactivité
    </script>
</body>
</html>
