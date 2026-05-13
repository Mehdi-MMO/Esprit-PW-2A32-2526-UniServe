<div class="us-front-map-widget" data-front-map-widget>
    <button type="button" class="btn btn-warning us-front-map-toggle" data-front-map-toggle>
        <i class="fa-solid fa-location-dot me-1"></i> Carte
    </button>

    <div class="us-front-map-backdrop d-none" data-front-map-backdrop></div>

    <div class="us-front-map-panel d-none" data-front-map-panel>
        <div class="us-front-map-header">
            <strong>Carte campus</strong>
            <button type="button" class="btn-close" aria-label="Fermer" data-front-map-close></button>
        </div>

        <div class="us-front-map-toolbar">
            <input
                type="text"
                class="form-control form-control-sm"
                placeholder="Rechercher une adresse..."
                data-front-map-search
            >
            <button type="button" class="btn btn-primary btn-sm" data-front-map-search-btn>OK</button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-front-map-locate>
                Ma position
            </button>
        </div>

        <div class="us-front-map-canvas" data-front-map-canvas></div>
        <div class="us-front-map-footer small text-muted" data-front-map-status>
            Cliquez sur la carte pour choisir un point.
        </div>
    </div>
</div>
