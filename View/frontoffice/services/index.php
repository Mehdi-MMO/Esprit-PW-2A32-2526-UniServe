<?php
$services = $services ?? [];
?>

<div class="us-card p-4 mb-4">
    <nav class="small text-muted mb-2" aria-label="Fil d'Ariane">
        <a href="<?= $this->url('/demandes') ?>" class="text-decoration-none">Demandes de service</a>
        <span class="mx-1" aria-hidden="true">/</span>
        <span class="text-body-secondary">Types proposés</span>
    </nav>
    <h1 class="h4 mb-2"><?= htmlspecialchars((string) ($title ?? 'Types de demandes'), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted small mb-0">Il s’agit du <strong>catalogue</strong> des rubriques pour lesquelles vous pouvez ouvrir une <strong>demande de service</strong> (même parcours que « Mes demandes »).</p>
</div>
<div class="row g-3">
    <?php if ($services === []): ?>
        <p class="text-muted">Aucun service actif pour le moment.</p>
    <?php else: ?>
        <?php foreach ($services as $s): ?>
            <?php $sid = (int) ($s['id'] ?? 0); ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 card-title"><?= htmlspecialchars((string) ($s['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="card-text small text-muted flex-grow-1"><?= nl2br(htmlspecialchars((string) ($s['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                        <?php if ($sid > 0): ?>
                            <div class="mt-3 pt-2 border-top">
                                <a href="<?= $this->url('/demandes/createForm?categorie_id=' . $sid) ?>" class="btn btn-primary btn-sm w-100">
                                    Faire une demande pour ce type
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<p class="mt-4 small text-muted mb-0">
    <a href="<?= $this->url('/demandes') ?>">Retour à mes demandes</a>
    <span class="text-muted mx-2" aria-hidden="true">·</span>
    Ou ouvrir le formulaire sans type pré-sélectionné : <a href="<?= $this->url('/demandes/createForm') ?>">Nouvelle demande</a>.
</p>
