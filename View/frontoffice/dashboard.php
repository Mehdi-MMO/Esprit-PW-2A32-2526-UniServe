<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Bienvenue dans le Front Office UniServe</h1>
        <p class="text-muted mb-0">Demandes, rendez-vous et documents pour vos activités académiques (modules bientot disponibles).</p>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="fw-semibold mb-1">Demandes</div>
                <div class="text-muted small mb-3">Accedez a vos demandes de service (placeholder).</div>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-primary btn-sm mt-auto">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="fw-semibold mb-1">Rendez-vous</div>
                <div class="text-muted small mb-3">Gerez vos rendez-vous (placeholder).</div>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm mt-auto">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="fw-semibold mb-1">Documents</div>
                <div class="text-muted small mb-3">Demandez et suivez vos documents (placeholder).</div>
                <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm mt-auto">Ouvrir</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="fw-semibold mb-1">Événements</div>
                <div class="text-muted small mb-3">Inscription et suivi des evenements (placeholder).</div>
                <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm mt-auto">Ouvrir</a>
            </div>
        </div>
    </div>
</div>

