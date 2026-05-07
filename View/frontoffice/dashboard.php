<div class="us-section-card p-4 p-md-5 mb-3" style="background: linear-gradient(135deg, rgba(11,42,90,0.96), rgba(10,22,40,0.95)); color:#fff;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="small text-uppercase" style="letter-spacing:.08em;opacity:.8;">Front office</div>
            <h1 class="h3 mb-2 mt-1">Tableau de bord étudiant / enseignant</h1>
            <p class="mb-0" style="opacity:.86;">Gérez vos demandes, documents, rendez-vous et événements depuis un espace unifié.</p>
        </div>
        <a href="<?= $this->url('/evenements') ?>" class="btn btn-light btn-sm">Explorer les événements</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Suivi rapide</div>
            <div class="us-stat-value">Demandes</div>
            <div class="small text-muted">Historique et statut en un clic</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Planning</div>
            <div class="us-stat-value">Rendez-vous</div>
            <div class="small text-muted">Vos créneaux à venir</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Académique</div>
            <div class="us-stat-value">Documents</div>
            <div class="small text-muted">Attestations et relevés</div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-stat-card h-100">
            <div class="us-stat-label">Campus</div>
            <div class="us-stat-value">Événements</div>
            <div class="small text-muted">Inscriptions et activités</div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Demandes</div>
                <div class="us-module-copy">Créer et suivre vos demandes administratives.</div>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Rendez-vous</div>
                <div class="us-module-copy">Réserver et consulter vos rendez-vous.</div>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Documents</div>
                <div class="us-module-copy">Centraliser vos demandes documentaires.</div>
                <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="us-module-card h-100">
            <div class="card-body d-flex flex-column">
                <div class="us-module-title">Événements</div>
                <div class="us-module-copy">Voir les événements approuvés et vous inscrire.</div>
                <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm us-dashboard-action">Ouvrir</a>
            </div>
        </div>
    </div>
</div>

