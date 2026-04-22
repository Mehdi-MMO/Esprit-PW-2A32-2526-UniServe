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
        .nav-link-disabled {
            cursor: default !important;
            pointer-events: none !important;
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
        <nav class="nav flex-column p-3 gap-1">
            <?php
            $currentUrl = $_SERVER['REQUEST_URI'] ?? '';
            $isRdv = strpos($currentUrl, 'page=back') !== false;
            ?>
            <!-- Liens désactivés (autres modules) -->
            <span class="nav-link nav-link-disabled"><i class="bi bi-speedometer2 me-2"></i>Dashboard</span>
            <span class="nav-link nav-link-disabled"><i class="bi bi-people me-2"></i>Utilisateurs</span>
            <span class="nav-link nav-link-disabled"><i class="bi bi-journal-text me-2"></i>Demandes de service</span>

            <!-- Lien actif — Rendez-vous (votre module) -->
            <a class="nav-link <?= $isRdv ? 'active' : '' ?>" href="index.php?page=back">
                <i class="bi bi-calendar-check me-2"></i>Rendez-vous
            </a>

            <!-- Liens désactivés (autres modules) -->
            <span class="nav-link nav-link-disabled"><i class="bi bi-file-earmark-text me-2"></i>Documents académiques</span>
            <span class="nav-link nav-link-disabled"><i class="bi bi-calendar-event me-2"></i>Événements</span>
        </nav>
    </aside>

    <header class="top-header ms-sidebar d-flex justify-content-between align-items-center px-4 py-3 border-bottom bg-white">
        <span class="fw-semibold">
            <?= htmlspecialchars((string) ($_SESSION['user']['prenom'] ?? 'Utilisateur'), ENT_QUOTES, 'UTF-8') ?>
            <?= htmlspecialchars((string) ($_SESSION['user']['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
        </span>
        <!-- Bouton Déconnexion désactivé -->
        <span class="btn btn-outline-danger btn-sm" style="cursor:default;pointer-events:none;">Deconnexion</span>
    </header>

    <main class="ms-sidebar p-4">
        <?= $content ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->url('/public/js/main.js') ?>"></script>
</body>
</html>