<?php
declare(strict_types=1);

$bureaux = $bureaux ?? [];
$search_bq = (string) ($search_bq ?? '');
$generated_at = (string) ($generated_at ?? '');
?>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Bureaux — export</h1>
            <p class="text-muted small mb-0">
                Généré le <?= htmlspecialchars($generated_at, ENT_QUOTES, 'UTF-8') ?>
                · <?= count($bureaux) ?> ligne(s)
                <?php if ($search_bq !== ''): ?>
                    · Filtre : « <?= htmlspecialchars($search_bq, ENT_QUOTES, 'UTF-8') ?> »
                <?php endif; ?>
            </p>
        </div>
        <button type="button" class="btn btn-primary d-print-none" onclick="window.print()">
            <i class="fa-solid fa-print me-2" aria-hidden="true"></i>Imprimer ou enregistrer en PDF
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nom</th>
                    <th>Localisation</th>
                    <th>Type de service</th>
                    <th>Actif</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bureaux === []): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">Aucun bureau.</td></tr>
                <?php else: ?>
                    <?php foreach ($bureaux as $b): ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($b['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($b['localisation'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><code class="small"><?= htmlspecialchars((string) ($b['type_service'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                            <td><?= (int) ($b['actif'] ?? 0) === 1 ? 'Oui' : 'Non' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="small text-muted d-print-none mb-0">Choisissez « Enregistrer au format PDF » dans la boîte d’impression.</p>
</div>
