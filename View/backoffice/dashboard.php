<div class="us-section-card p-4 p-md-5 mb-3" style="background: linear-gradient(135deg, rgba(11,42,90,0.98), rgba(159,122,47,0.90)); color:#fff;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="small text-uppercase" style="letter-spacing:.08em;opacity:.82;">Pilotage</div>
            <h1 class="h3 mb-2 mt-1">Tableau de bord administration</h1>
            <p class="mb-0" style="opacity:.9;">Supervision globale des utilisateurs, services, documents et événements de l’université.</p>
        </div>
        <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-light btn-sm">Gérer les utilisateurs</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Administration</div>
            <div class="us-stat-value">Utilisateurs</div>
            <div class="small text-muted">Gestion des rôles et accès</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Workflow</div>
            <div class="us-stat-value">Demandes</div>
            <div class="small text-muted">Traitement et suivi interne</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Planification</div>
            <div class="us-stat-value">Rendez-vous</div>
            <div class="small text-muted">Validation des créneaux</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Vie campus</div>
            <div class="us-stat-value">Événements</div>
            <div class="small text-muted">Modération clubs/événements</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Utilisateurs</div>
                <div class="us-module-copy">Contrôler les comptes, rôles et état d’accès.</div>
                <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Demandes</div>
                <div class="us-module-copy">Piloter les demandes administratives en attente.</div>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Rendez-vous</div>
                <div class="us-module-copy">Visualiser, valider et reprogrammer les rendez-vous.</div>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Documents</div>
                <div class="us-module-copy">Suivre validation, rejet et livraison documentaire.</div>
                <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Événements</div>
                <div class="us-module-copy">Approuver, rejeter et suivre la participation.</div>
                <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
</div>

