<?php
$activeTab = $_GET['tab'] ?? 'appointments';
$badgeMap  = [
    'pending'   => 'bg-warning text-dark',
    'confirmed' => 'bg-success',
    'cancelled' => 'bg-danger',
];
$labelMap  = [
    'pending'   => 'En attente',
    'confirmed' => 'Confirmé',
    'cancelled' => 'Annulé',
];
?>

<!-- En-tête -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color:var(--brand)">
            <i class="bi bi-calendar-check me-2"></i>Gestion des Rendez-vous
        </h1>
        <p class="text-muted small mb-0">Gérez les rendez-vous et les bureaux depuis cette page.</p>
    </div>
    <?php if ($activeTab === 'offices'): ?>
    <a href="index.php?page=back&module=offices&action=create" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Ajouter un bureau
    </a>
    <?php endif; ?>
</div>

<!-- Alertes -->
<?php if (!empty($error) && $error === 'has_rdv'): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Impossible de supprimer ce bureau : il possède encore <strong><?= (int)$count ?> rendez-vous actif(s)</strong>.
    Veuillez d'abord les annuler.
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

<!-- Onglets -->
<ul class="nav nav-tabs mb-4" id="backTabs">
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'appointments' ? 'active fw-semibold' : '' ?>"
           href="index.php?page=back&module=appointments&tab=appointments">
            <i class="bi bi-calendar-check me-2"></i>Rendez-vous
            <?php
            $totalRdv = 0;
            if (!empty($stmtCount)) {
                $totalRdv = $stmtCount;
            }
            ?>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === 'offices' ? 'active fw-semibold' : '' ?>"
           href="index.php?page=back&module=appointments&tab=offices">
            <i class="bi bi-building me-2"></i>Bureaux
        </a>
    </li>
</ul>

<!-- ===== ONGLET RENDEZ-VOUS ===== -->
<?php if ($activeTab === 'appointments'): ?>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:var(--brand); color:#fff;">
                <tr>
                    <th class="px-4 py-3">Étudiant</th>
                    <th class="py-3">Bureau</th>
                    <th class="py-3">Sujet</th>
                    <th class="py-3">Date &amp; Heure</th>
                    <th class="py-3">Statut</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php $count = 0; while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): $count++; $statut = strtolower($row['statut']); ?>
                <tr>
                    <td class="px-4 fw-semibold"><?= htmlspecialchars($row['nom_etudiant']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['objet']) ?></td>
                    <td class="text-muted small">
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('d/m/Y', strtotime($row['date_rdv'])) ?>
                        &nbsp;à&nbsp;<?= htmlspecialchars($row['heure_rdv']) ?>
                    </td>
                    <td>
                        <span class="badge <?= $badgeMap[$statut] ?? 'bg-secondary' ?>">
                            <?= $labelMap[$statut] ?? htmlspecialchars($row['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($statut === 'pending'): ?>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=confirmed&tab=appointments"
                               class="btn btn-sm btn-outline-success me-1">
                                <i class="bi bi-check-lg me-1"></i>Approuver
                            </a>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=cancelled&tab=appointments"
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-lg me-1"></i>Rejeter
                            </a>
                        <?php else: ?>
                            <span class="text-muted fst-italic small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count === 0): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                        Aucun rendez-vous enregistré.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== ONGLET BUREAUX ===== -->
<?php else: ?>
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
                <?php $count_rows = 0; while ($rowB = $stmtBureaux->fetch(PDO::FETCH_ASSOC)): $count_rows++; ?>
                <tr>
                    <td class="px-4 fw-semibold"><?= htmlspecialchars($rowB['nom']) ?></td>
                    <td class="text-muted">
                        <i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($rowB['localisation']) ?>
                    </td>
                    <td class="text-muted">
                        <?php if (!empty($rowB['responsable'])): ?>
                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($rowB['responsable']) ?>
                        <?php else: ?>
                            <span class="fst-italic">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?page=back&module=offices&action=edit&id=<?= $rowB['id'] ?>"
                           class="btn btn-sm btn-outline-primary me-1">
                            <i class="bi bi-pencil me-1"></i>Modifier
                        </a>
                        <a href="index.php?page=back&module=offices&action=delete&id=<?= $rowB['id'] ?>"
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
<?php endif; ?>