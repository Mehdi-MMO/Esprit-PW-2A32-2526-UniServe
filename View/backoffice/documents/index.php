<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Module back office</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Documents académiques'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="us-page-subtitle">Ce module est en préparation.</p>
    </div>
</div>

<div class="us-card us-empty-state us-empty-card">
    <div class="d-flex align-items-start gap-3">
        <span class="us-feature-icon"><i class="bi bi-file-earmark-check"></i></span>
        <div class="flex-grow-1">
            <div class="us-empty-title">À venir</div>
            <div class="us-empty-copy">Ici : valider les demandes, suivre la production et gerer la livraison.</div>
            <ul class="us-list-tight">
                <li>Validation administrative par etapes.</li>
                <li>Suivi des delais de production.</li>
                <li>Traçabilite de la remise des documents.</li>
            </ul>
        </div>
    </div>
</div>
