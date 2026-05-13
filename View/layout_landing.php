<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Connexion et accès au portail UniServe.">
    <meta name="theme-color" content="#0b2a5a">
    <?php
    $ogTitle = isset($title) && (string) $title !== ''
        ? htmlspecialchars((string) $title . ' · UniServe', ENT_QUOTES, 'UTF-8')
        : 'UniServe';
    $ogDescription = 'Portail institutionnel UniServe : demandes, rendez-vous, documents, certifications, clubs, événements et notifications.';
    ?>
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= $ogTitle ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription, ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars(isset($title) && $title !== '' ? (string) $title . ' · UniServe' : 'UniServe', ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="<?= $this->asset('/View/shared/css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('/View/shared/css/landing.css') ?>">
    <link rel="icon" href="<?= $this->asset('/View/shared/assets/img/logo.png') ?>" type="image/png">
</head>
<body class="us-landing">
    <a class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-sm btn-primary z-3" href="#main-content">Aller au contenu</a>
    <nav class="navbar us-topbar us-landing-nav navbar-dark shadow-sm" aria-label="Navigation principale"><div class="container"><a class="navbar-brand d-inline-flex align-items-center us-navbar-brand-logo" href="<?= $this->url('/') ?>" aria-label="UniServe — accueil"><?= us_brand_logo_html($this, 'us-brand-logo--nav us-brand-logo--on-dark', false) ?></a>
        </div>
    </nav>
    <main id="main-content" class="us-landing-main">
        <?php if (!empty($content)): ?>
            <?= $content ?>
        <?php endif; ?>
    </main>

    <footer class="us-landing-footer py-4 border-top" role="contentinfo">
        <div class="container text-center">
            <div class="text-muted small">&copy; <?= date('Y') ?> UniServe. Tous droits réservés.</div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $this->asset('/View/shared/js/main.js') ?>"></script>
</body>
</html>
