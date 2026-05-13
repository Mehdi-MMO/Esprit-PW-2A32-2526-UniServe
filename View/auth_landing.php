<header class="us-hero us-landing-hero">
    <div class="container">
        <div class="row align-items-center g-4 g-lg-5">
            <div class="col-lg-7">
                <span class="us-badge mb-3 d-inline-flex">Plateforme officielle</span>
                <div class="mb-3"><?= us_brand_logo_html($this, 'us-brand-logo--hero', true) ?></div>
                <div class="us-kicker mb-2">Services universitaires</div>
                <h1 class="us-hero-title mb-3">UniServe</h1>
                <p class="lead text-muted us-landing-text-body mb-4">
                    Accès aux demandes administratives, rendez-vous, documents, clubs et événements selon votre rôle.
                </p>
                <div class="d-flex flex-wrap gap-2 gap-sm-3">
                    <a href="<?= $this->url('/auth/login') ?>" class="btn btn-primary btn-lg px-4">Se connecter</a>
                    <a href="#fonctionnalites" class="btn btn-outline-secondary btn-lg px-4 btn-us-ghost">Découvrir le portail</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="us-landing-glass h-100">
                    <div class="us-landing-glass-inner h-100 d-flex flex-column">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                            <div class="fw-bold text-dark">Accès sécurisé</div>
                            <span class="us-badge">UniServe</span>
                        </div>
                        <p class="text-muted small us-landing-glass-body mb-0 flex-grow-1">
                            Identifiants institutionnels : après connexion, vous êtes redirigé vers le tableau de bord adapté à votre profil.
                        </p>
                        <div class="us-divider my-3"></div>
                        <div class="d-flex flex-wrap gap-2 align-items-center small text-muted">
                            <span><i class="fa-solid fa-user-graduate me-1 text-secondary" aria-hidden="true"></i>Étudiants &amp; enseignants</span>
                            <span class="d-none d-sm-inline opacity-50" aria-hidden="true">·</span>
                            <span><i class="fa-solid fa-building-user me-1 text-secondary" aria-hidden="true"></i>Staff &amp; administration</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<section id="fonctionnalites" class="us-landing-features container" aria-labelledby="fonctionnalites-heading">
    <h2 id="fonctionnalites-heading" class="us-section-heading h3 mb-4">À propos du portail</h2>

    <div class="row g-3 g-md-4">
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card">
                <div class="us-feature-icon mb-3" aria-hidden="true"><i class="fa-solid fa-clipboard-list"></i></div>
                <h3>Demandes</h3>
                <p>Création et suivi des demandes de service par catégorie.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card">
                <div class="us-feature-icon mb-3" aria-hidden="true"><i class="fa-solid fa-calendar-check"></i></div>
                <h3>Rendez-vous</h3>
                <p>Réservation de créneaux auprès des bureaux.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card">
                <div class="us-feature-icon mb-3" aria-hidden="true"><i class="fa-solid fa-folder-open"></i></div>
                <h3>Documents</h3>
                <p>Documents académiques (attestations, relevés) et parcours de certifications ; traitement côté administration.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card">
                <div class="us-feature-icon mb-3" aria-hidden="true"><i class="fa-solid fa-people-group"></i></div>
                <h3>Clubs &amp; événements</h3>
                <p>Vie associative : clubs, calendrier et inscriptions aux événements.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card">
                <div class="us-feature-icon mb-3" aria-hidden="true"><i class="fa-solid fa-user-gear"></i></div>
                <h3>Compte</h3>
                <p>Profil, mot de passe et droits selon le rôle attribué.</p>
            </article>
        </div>
        <div class="col-md-6 col-lg-4">
            <article class="us-landing-feature-card">
                <div class="us-feature-icon mb-3" aria-hidden="true"><i class="fa-solid fa-bell"></i></div>
                <h3>Notifications</h3>
                <p>Historique et alertes depuis votre tableau de bord une fois connecté.</p>
            </article>
        </div>
    </div>
</section>
