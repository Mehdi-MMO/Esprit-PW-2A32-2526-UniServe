<!-- En-tête -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color:var(--brand)">
            <i class="bi bi-building me-2"></i>Gestion des Bureaux
        </h1>
        <p class="text-muted small mb-0">Gérez les bureaux disponibles pour les rendez-vous.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-calendar-check me-1"></i> Gérer les RDV
        </a>
        <a href="<?= $this->url('/bureaux/createForm') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Ajouter un bureau
        </a>
    </div>
</div>

<!-- Messages -->
<?php if (!empty($error) && $error === 'has_rdv'): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Impossible de supprimer ce bureau : il possède encore <strong><?= (int)$count ?> rendez-vous actif(s)</strong>.
    Veuillez d'abord les annuler depuis la gestion des rendez-vous.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($success)): ?>
<?php
    $msgs = [
        'created' => 'Bureau ajouté avec succès.',
        'updated' => 'Bureau mis à jour avec succès.',
        'deleted' => 'Bureau supprimé avec succès.',
    ];
    $msg = $msgs[$success] ?? 'Opération réussie.';
?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Tableau -->
<div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="border-collapse:collapse;">
            <thead style="background:linear-gradient(90deg,#0b2a5a 0%,#1565c0 100%); color:#fff;">
                <tr>
                    <th class="px-4 py-3 border-0 text-uppercase" style="font-size:.75rem; letter-spacing:.5px;">Nom du Bureau</th>
                    <th class="py-3 border-0 text-uppercase" style="font-size:.75rem; letter-spacing:.5px;">Localisation</th>
                    <th class="py-3 border-0 text-uppercase" style="font-size:.75rem; letter-spacing:.5px;">Responsable</th>
                    <th class="py-3 border-0 text-uppercase" style="font-size:.75rem; letter-spacing:.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count_rows = 0; foreach ($bureaux as $row): $count_rows++; ?>
                <tr style="border-bottom:1px solid rgba(11,42,90,.05);">
                    <td class="px-4 py-3 fw-semibold" style="color:#111827;">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:32px;height:32px;background:rgba(11,42,90,.06);color:var(--brand);border-radius:8px;display:grid;place-items:center;">
                                <i class="bi bi-building-fill"></i>
                            </div>
                            <?= htmlspecialchars($row['nom']) ?>
                        </div>
                    </td>
                    <td class="py-3 text-muted" style="font-size:.85rem;">
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($row['localisation'] ?? 'Non spécifiée') ?>
                    </td>
                    <td class="py-3 text-muted" style="font-size:.85rem;">
                        <?php if (!empty($row['responsable'])): ?>
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($row['responsable']) ?>
                        <?php else: ?>
                            <span class="text-muted fst-italic">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="py-3">
                        <div class="d-flex gap-2">
                            <a href="<?= $this->url('/bureaux/editForm/' . $row['id']) ?>"
                               class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1" style="border-radius:8px; font-weight:600;">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                            <form action="<?= $this->url('/bureaux/delete/' . $row['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Supprimer ce bureau ?')">
                                <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" style="border-radius:8px; font-weight:600;">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($count_rows === 0): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <div style="width:64px;height:64px;background:rgba(11,42,90,.06);color:var(--brand);border-radius:16px;display:inline-grid;place-items:center;font-size:1.8rem;margin-bottom:12px;">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="fw-bold text-dark mb-1">Aucun bureau enregistré</div>
                        <div class="small">Cliquez sur le bouton "Ajouter un bureau" pour commencer.</div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
