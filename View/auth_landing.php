<?php
$heroStats = [
    ['value' => '2 400+', 'label' => 'Étudiants'],
    ['value' => '6',      'label' => 'Services'],
    ['value' => '99%',    'label' => 'Disponibilité'],
];
$pic    = $this->asset('/View/shared/assets/img/pic.jpg');
$pic2   = $this->asset('/View/shared/assets/img/pic2.jpg');
$pic3   = $this->asset('/View/shared/assets/img/pic3.jpg');
?>

<!-- ══════════════════════════════════ HERO ══════════════════════════════════ -->
<header class="us-landing-hero">
    <div class="us-hero-blob us-hero-blob-1" aria-hidden="true"></div>
    <div class="us-hero-blob us-hero-blob-2" aria-hidden="true"></div>

    <div class="container">
        <div class="row align-items-center g-4 g-xl-5">

            <!-- Left copy -->
            <div class="col-lg-6 col-xl-5">
                <h1 class="us-hero-title mb-3">
                    <?= us_brand_logo_html($this, 'us-brand-logo--hero us-brand-logo--on-light', false) ?>
                    <span class="d-block us-hero-tagline mt-2">
                        Le portail des services universitaires
                    </span>
                </h1>
                <p class="lead us-landing-text-body mb-4">
                    Gérez vos demandes administratives, réservez vos rendez-vous, accédez à vos documents officiels et suivez la vie du campus — tout en un seul endroit.
                </p>
                <div class="d-flex flex-wrap gap-2 gap-sm-3 mb-5">
                    <a href="<?= $this->url('/auth/login') ?>" class="btn btn-primary btn-lg px-4 d-inline-flex align-items-center gap-2">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Se connecter
                    </a>
                    <a href="#fonctionnalites" class="btn btn-outline-secondary btn-lg px-4 btn-us-ghost d-inline-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap"></i>
                        Découvrir le portail
                    </a>
                </div>

                <!-- Stat pills -->
                <div class="us-hero-stats d-flex flex-wrap gap-3">
                    <?php foreach ($heroStats as $s): ?>
                    <div class="us-hero-stat-pill">
                        <span class="us-hero-stat-value"><?= $s['value'] ?></span>
                        <span class="us-hero-stat-label"><?= $s['label'] ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right photo collage -->
            <div class="col-lg-6 col-xl-7 d-none d-lg-block">
                <div class="us-photo-collage">
                    <!-- Large image -->
                    <div class="us-photo-main">
                        <img src="<?= $pic2 ?>" alt="Étudiante travaillant sur son ordinateur" class="us-photo-img" loading="eager">
                    </div>
                    <!-- Stacked smaller image -->
                    <div class="us-photo-secondary">
                        <img src="<?= $pic3 ?>" alt="Étudiante avec lunettes prenant des notes" class="us-photo-img" loading="lazy">
                    </div>
                    <!-- Floating stat card -->
                    <div class="us-photo-float-card us-photo-float-card--tl">
                        <div class="us-photo-float-icon"><i class="bi bi-clipboard-check-fill"></i></div>
                        <div>
                            <div class="us-photo-float-val">1 200+</div>
                            <div class="us-photo-float-lbl">Demandes traitées</div>
                        </div>
                    </div>
                    <!-- Floating badge bottom-right -->
                    <div class="us-photo-float-card us-photo-float-card--br">
                        <i class="bi bi-shield-lock-fill" style="color:var(--brand);font-size:1.1rem;flex-shrink:0;"></i>
                        <span>Accès sécurisé</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</header>

<!-- ══════════════════════════════ SOCIAL PROOF STRIP ══════════════════════════════ -->
<div class="us-social-proof-strip">
    <div class="us-social-proof-bg" style="background-image:url('<?= $pic ?>');" aria-hidden="true"></div>
    <div class="us-social-proof-overlay" aria-hidden="true"></div>
    <div class="container us-social-proof-content">
        <p class="us-social-proof-quote">
            "UniServe simplifie toutes mes démarches universitaires en un seul endroit."
        </p>
        <div class="us-social-proof-meta">
            <span class="us-social-proof-author">Étudiante en Master — Promotion 2025</span>
        </div>
    </div>
</div>

<!-- ════════════════════════════ FEATURES SECTION ════════════════════════════ -->
<section id="fonctionnalites" class="us-landing-features container" aria-labelledby="fonctionnalites-heading">
    <div class="text-center mb-5">
        <h2 id="fonctionnalites-heading" class="us-section-heading h2 mb-2">Tout ce dont vous avez besoin</h2>
        <p class="text-muted" style="max-width:520px; margin: 0 auto;">UniServe centralise l'ensemble des démarches universitaires dans une interface unifiée et moderne.</p>
    </div>

    <div class="row g-3 g-md-4">
        <?php
        $features = [
            ['icon'=>'bi-clipboard-check-fill','color'=>'#e0e7ff','accent'=>'#4338ca','title'=>'Demandes de service','desc'=>'Création et suivi en ligne de toutes vos demandes administratives, classées par catégorie et statut.'],
            ['icon'=>'bi-calendar-check-fill','color'=>'#d1fae5','accent'=>'#065f46','title'=>'Rendez-vous','desc'=>'Réservez un créneau auprès des bureaux disponibles directement depuis votre tableau de bord.'],
            ['icon'=>'bi-file-earmark-text-fill','color'=>'#ede9fe','accent'=>'#6d28d9','title'=>'Documents académiques','desc'=>'Demandez attestations, relevés de notes et certificats de scolarité avec suivi de traitement.'],
            ['icon'=>'bi-patch-check-fill','color'=>'#fef3c7','accent'=>'#92400e','title'=>'Certifications','desc'=>'Suivez vos parcours de certification, validez des compétences et téléchargez vos badges.'],
            ['icon'=>'bi-people-fill','color'=>'#fce7f3','accent'=>'#9d174d','title'=>'Clubs & Événements','desc'=>'Rejoignez des clubs, consultez le calendrier et inscrivez-vous aux événements du campus.'],
            ['icon'=>'bi-bell-fill','color'=>'#e0f2fe','accent'=>'#075985','title'=>'Notifications','desc'=>'Recevez des alertes en temps réel sur l\'avancement de vos dossiers et les actualités de l\'université.'],
        ];
        foreach ($features as $f):
        ?>
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card h-100">
                <div class="us-feature-icon mb-3" style="background:<?= $f['color'] ?>; color:<?= $f['accent'] ?>;" aria-hidden="true">
                    <i class="bi <?= $f['icon'] ?>"></i>
                </div>
                <h3><?= $f['title'] ?></h3>
                <p><?= $f['desc'] ?></p>
            </article>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ════════════════════════════ CTA STRIP ════════════════════════════ -->
<section class="us-landing-cta-strip" aria-label="Accéder au portail">
    <div class="container text-center">
        <h2 class="mb-2 fw-800">Prêt à commencer ?</h2>
        <p class="mb-4" style="color:rgba(255,255,255,0.75);">Connectez-vous avec vos identifiants universitaires.</p>
        <a href="<?= $this->url('/auth/login') ?>" class="btn btn-light btn-lg px-5 d-inline-flex align-items-center gap-2" style="color:var(--brand);font-weight:700;border-radius:12px;">
            <i class="bi bi-box-arrow-in-right"></i>
            Accéder au portail
        </a>
    </div>
</section>
