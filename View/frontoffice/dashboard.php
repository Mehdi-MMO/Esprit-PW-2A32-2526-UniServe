<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Front office</div>
        <h1 class="h3 mb-1">Bienvenue dans le Front Office UniServe</h1>
        <p class="text-muted mb-0">Vos services académiques depuis un espace unique et clair.</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Demandes</div>
                <div class="us-module-copy">Soumettre et suivre vos demandes de service.</div>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Rendez-vous</div>
                <div class="us-module-copy">Consulter, réserver et suivre vos rendez-vous.</div>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Documents</div>
                <div class="us-module-copy">Demander vos documents et suivre leur traitement.</div>
                <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Événements</div>
                <div class="us-module-copy">Voir les événements universitaires et vos inscriptions.</div>
                <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
</div>

