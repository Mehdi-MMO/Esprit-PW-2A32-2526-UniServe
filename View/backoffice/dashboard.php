<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Pilotage</div>
        <h1 class="h3 mb-1">Bienvenue dans le Back Office UniServe</h1>
        <p class="text-muted mb-0">Administration des utilisateurs et supervision des services.</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Utilisateurs</div>
                <div class="us-module-copy">Création, modification et désactivation des comptes.</div>
                <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Demandes</div>
                <div class="us-module-copy">Traitement administratif des demandes de service.</div>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Rendez-vous</div>
                <div class="us-module-copy">Planification, validation et suivi des rendez-vous.</div>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Documents</div>
                <div class="us-module-copy">Validation des demandes documentaires et suivi de livraison.</div>
                <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Événements</div>
                <div class="us-module-copy">Organisation institutionnelle et suivi des inscriptions.</div>
                <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
</div>

