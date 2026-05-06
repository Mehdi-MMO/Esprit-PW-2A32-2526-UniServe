<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= htmlspecialchars($title) ?></h1>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
    <?php if (empty($services)): ?>
        <div class="col-12">
            <div class="alert alert-info text-center py-4 border-0 shadow-sm">
                <i class="bi bi-info-circle fs-3 d-block mb-2"></i>
                Aucun service n'est disponible pour le moment.
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="col">
                <?php if ($service->getActif()): ?>
                    <div class="card h-100 shadow-sm border-0 border-top border-4 border-primary hover-shadow" onclick="window.location.href='<?= $this->url('/demandes/create?service_id=' . $service->getId()) ?>'" style="transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;">
                <?php else: ?>
                    <div class="card h-100 shadow-sm border-0 border-top border-4 border-danger opacity-75">
                <?php endif; ?>
                        
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    <i class="bi bi-diagram-2 fs-4"></i>
                                </div>
                                <?php if ($service->getActif()): ?>
                                    <span class="badge bg-success rounded-pill px-3 py-2 fw-normal">Disponible</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-2 fw-normal">Indisponible</span>
                                <?php endif; ?>
                            </div>
                            
                            <h5 class="card-title text-dark fw-bold mb-2"><?= htmlspecialchars($service->getNom() ?? '') ?></h5>
                            <p class="card-text text-muted flex-grow-1" style="font-size: 0.95rem;">
                                <?= htmlspecialchars($service->getDescription() ?? '') ?>
                            </p>
                            
                            <?php if ($service->getActif()): ?>
                                <div class="mt-3 pt-3 border-top text-end">
                                    <span class="text-primary fw-medium small">Faire une demande <i class="bi bi-arrow-right ms-1"></i></span>
                                </div>
                            <?php else: ?>
                                <div class="mt-3 pt-3 border-top text-end">
                                    <span class="text-danger fw-medium small"><i class="bi bi-x-circle ms-1"></i> Momentanément fermé</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<style>
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>
