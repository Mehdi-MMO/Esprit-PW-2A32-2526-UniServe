<!-- En-tête -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color:var(--brand)">
            <i class="bi bi-building me-2"></i>Gestion des Bureaux
        </h1>
        <p class="text-muted small mb-0">Gérez les bureaux disponibles pour les rendez-vous.</p>
    </div>
    <a href="index.php?page=back&module=offices&action=create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un bureau
    </a>
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
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:var(--brand); color:#fff;">
                <tr>
                    <th class="px-4 py-3">Nom du Bureau</th>
                    <th class="py-3">Localisation</th>
                    <th class="py-3">Responsable</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count_rows = 0; while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): $count_rows++; ?>
                <tr>
                    <td class="px-4 fw-semibold"><?= htmlspecialchars($row['nom']) ?></td>
                    <td class="text-muted">
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($row['localisation']) ?>
                    </td>
                    <td class="text-muted">
                        <?php if (!empty($row['responsable'])): ?>
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($row['responsable']) ?>
                        <?php else: ?>
                            <span class="text-muted fst-italic">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?page=back&module=offices&action=edit&id=<?= $row['id'] ?>"
                           class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </a>
                        <a href="index.php?page=back&module=offices&action=delete&id=<?= $row['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Supprimer ce bureau ?')">
                            <i class="bi bi-trash me-1"></i>Supprimer
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count_rows === 0): ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <i class="bi bi-building fs-3 d-block mb-2"></i>
                        Aucun bureau enregistré.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
