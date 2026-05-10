<?php
require_once __DIR__ . '/../../shared/helpers.php';

$demandes = $demandes ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
$statut_filter = (string) ($statut_filter ?? '');
$q = (string) ($q ?? '');

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
} else {
    $flash = null;
}
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Documents académiques'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Validation et suivi des demandes de documents officiels.</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<?php
$statCards = [
    ['title' => 'Total', 'value' => $stats['total'] ?? 0, 'color' => 'primary', 'icon' => 'fa-solid fa-file-lines'],
    ['title' => 'En attente', 'value' => $stats['en_attente'] ?? 0, 'color' => 'warning', 'icon' => 'fa-solid fa-hourglass-half'],
    ['title' => 'En validation', 'value' => $stats['en_validation'] ?? 0, 'color' => 'info', 'icon' => 'fa-solid fa-magnifying-glass'],
    ['title' => 'Validé', 'value' => $stats['valide'] ?? 0, 'color' => 'success', 'icon' => 'fa-solid fa-circle-check'],
    ['title' => 'Livré', 'value' => $stats['livre'] ?? 0, 'color' => 'success', 'icon' => 'fa-solid fa-box-archive'],
    ['title' => 'Rejeté', 'value' => $stats['rejete'] ?? 0, 'color' => 'danger', 'icon' => 'fa-solid fa-circle-xmark'],
];
echo '<div class="mb-4">' . renderStatGrid($statCards) . '</div>';
?>

<div class="us-section-card mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= $this->url('/documents') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1" for="dq">Recherche</label>
                <input type="text" class="form-control" id="dq" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Type, étudiant, email…">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1" for="dst">Statut</label>
                <select class="form-select" id="dst" name="statut">
                    <option value="">Tous</option>
                    <?php foreach ($statut_labels as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statut_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="us-section-card">
    <div class="card-body p-0">
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Étudiant</th>
                        <th>Type</th>
                        <th>Statut</th>
                        <th>Demandée</th>
                        <th>Validée</th>
                        <th>Livrée</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Aucune demande.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $row): ?>
                            <?php
                            $sid = (int) ($row['id'] ?? 0);
                            $st = (string) ($row['statut'] ?? '');
                            $label = $statut_labels[$st] ?? $st;
                            ?>
                            <tr>
                                <td class="text-muted small">#<?= $sid ?></td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($row['etudiant_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['etudiant_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td class="small"><?= htmlspecialchars((string) ($row['type_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['demandee_le'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['validee_le'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['livree_le'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <form method="post" action="<?= $this->url('/documents/updateStatut/' . $sid) ?>" class="border rounded p-2 bg-light">
                                        <label class="form-label small mb-1">Nouveau statut</label>
                                        <select name="statut" class="form-select form-select-sm mb-2" required>
                                            <?php foreach ($statut_labels as $k => $lab): ?>
                                                <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label class="form-label small mb-1">Motif (si rejet)</label>
                                        <textarea name="note_validation" class="form-control form-control-sm mb-2" rows="2" placeholder="Obligatoire pour « Rejeté »"><?= htmlspecialchars((string) ($row['note_validation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                        <button type="submit" class="btn btn-sm btn-primary w-100">Appliquer</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
