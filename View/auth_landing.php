<section class="container py-5">
    <div class="row align-items-center g-4">
        <div class="col-lg-7">
            <span class="us-badge mb-3">Plateforme officielle</span>
            <div class="us-kicker mb-2">Services universitaires</div>
            <h1 class="display-5 fw-bold mb-3">UniServe</h1>
            <p class="lead text-muted mb-4">
                Accès aux demandes administratives, rendez-vous, documents, clubs et événements selon votre rôle.
            </p>
            <a href="<?= $this->url('/auth/login') ?>" class="btn btn-primary btn-lg px-4">Se connecter</a>
        </div>

        <div class="col-lg-5">
            <div class="us-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="fw-semibold">Accès sécurisé</div>
                    <span class="us-badge">UniServe</span>
                </div>
                <p class="text-muted mb-0 small">
                    Identifiants institutionnels : après connexion, vous êtes redirigé vers le tableau de bord adapté à votre profil.
                </p>
                <div class="us-divider my-3"></div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="text-muted small">Étudiants &amp; enseignants</span>
                    <span class="text-muted small">·</span>
                    <span class="text-muted small">Staff &amp; administration</span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 pt-2">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <h2 class="h4 mb-0">À propos du portail</h2>
        </div>

        <div class="row g-3">
            <div class="col-md-6 col-lg-4">
                <div class="us-section-card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Demandes</div>
                        <div class="text-muted small">Création et suivi des demandes de service par catégorie.</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="us-section-card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Rendez-vous</div>
                        <div class="text-muted small">Réservation de créneaux auprès des bureaux.</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="us-section-card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Documents</div>
                        <div class="text-muted small">Demandes de documents académiques et traitement côté administration.</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="us-section-card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Clubs &amp; événements</div>
                        <div class="text-muted small">Vie associative : clubs, calendrier et inscriptions aux événements.</div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="us-section-card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">Compte</div>
                        <div class="text-muted small">Profil, mot de passe et droits selon le rôle attribué.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
