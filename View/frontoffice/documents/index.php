<?php
require_once __DIR__ . '/../../shared/helpers.php';

$demandes = $demandes ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
$teacher_notice = !empty($teacher_notice ?? false);

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
} else {
    $flash = null;
}
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Scolarité</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Documents'), ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($teacher_notice): ?>
            <p class="text-muted mb-0">Les demandes de documents académiques en ligne sont réservées aux étudiants. Pour toute question, contactez la scolarité.</p>
        <?php else: ?>
            <p class="text-muted mb-0">Demandez un relevé, une attestation ou une copie conforme et suivez le traitement.</p>
        <?php endif; ?>
    </div>
    <?php if (!$teacher_notice): ?>
        <a href="<?= $this->url('/documents/createForm') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Nouvelle demande</a>
    <?php endif; ?>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($teacher_notice): ?>
    <div class="us-card p-4 text-muted">
        <p class="mb-0">Vous pouvez utiliser les autres modules du menu (rendez-vous, événements selon votre profil).</p>
    </div>
<?php else: ?>

<?php
$kpi = [
    ['title' => 'Total', 'value' => $stats['total'] ?? 0, 'border' => 'primary'],
    ['title' => 'En attente', 'value' => $stats['en_attente'] ?? 0, 'border' => 'warning'],
    ['title' => 'En validation', 'value' => $stats['en_validation'] ?? 0, 'border' => 'info'],
    ['title' => 'Validé', 'value' => $stats['valide'] ?? 0, 'border' => 'success'],
    ['title' => 'Livré', 'value' => $stats['livre'] ?? 0, 'border' => 'success'],
    ['title' => 'Rejeté', 'value' => $stats['rejete'] ?? 0, 'border' => 'danger'],
];
?>
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4">
    <?php foreach ($kpi as $cell): ?>
        <div class="col">
            <div class="card h-100 border-top border-4 border-<?= htmlspecialchars($cell['border'], ENT_QUOTES, 'UTF-8') ?> shadow-sm">
                <div class="card-body py-3 text-center">
                    <div class="small text-muted text-uppercase"><?= htmlspecialchars($cell['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="h4 mb-0 fw-bold"><?= (int) $cell['value'] ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="us-section-card">
    <div class="card-body p-0">
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Type de document</th>
                        <th>Statut</th>
                        <th>Demandée le</th>
                        <th>Validée le</th>
                        <th>Livrée le</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Aucune demande pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $row): ?>
                            <?php
                            $st = (string) ($row['statut'] ?? '');
                            $label = $statut_labels[$st] ?? $st;
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($row['type_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($row['type_description'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars((string) $row['type_description'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['demandee_le'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['validee_le'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['livree_le'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                            <?php if ($st === 'rejete' && !empty($row['note_validation'])): ?>
                                <tr class="border-top-0">
                                    <td colspan="5" class="small text-danger pt-0 pb-3">
                                        <strong>Motif :</strong> <?= htmlspecialchars((string) $row['note_validation'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>
