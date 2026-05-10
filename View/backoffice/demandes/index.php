<?php
require_once __DIR__ . '/../../shared/helpers.php';

$demandes = $demandes ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
$staff_list = $staff_list ?? [];
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
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Demandes de service'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Traitement des demandes étudiantes par catégorie.</p>
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
    ['title' => 'Total', 'value' => $stats['total'] ?? 0, 'color' => 'primary', 'icon' => 'fa-solid fa-inbox'],
    ['title' => 'En attente', 'value' => $stats['en_attente'] ?? 0, 'color' => 'warning', 'icon' => 'fa-solid fa-hourglass-half'],
    ['title' => 'En cours', 'value' => $stats['en_cours'] ?? 0, 'color' => 'info', 'icon' => 'fa-solid fa-spinner'],
    ['title' => 'Traitées', 'value' => $stats['traite'] ?? 0, 'color' => 'success', 'icon' => 'fa-solid fa-circle-check'],
    ['title' => 'Rejetées', 'value' => $stats['rejete'] ?? 0, 'color' => 'danger', 'icon' => 'fa-solid fa-circle-xmark'],
];
echo '<div class="mb-4">' . renderStatGrid($statCards) . '</div>';
?>

<div class="us-section-card mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= $this->url('/demandes') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1" for="bq">Recherche</label>
                <input type="text" class="form-control" id="bq" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Titre, étudiant, catégorie…">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1" for="bst">Statut</label>
                <select class="form-select" id="bst" name="statut">
                    <option value="">Tous</option>
                    <?php foreach ($statut_labels as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statut_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
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
                        <th>Catégorie</th>
                        <th>Titre</th>
                        <th>Statut</th>
                        <th>Assigné</th>
                        <th>Soumise</th>
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
                                <td class="small"><?= htmlspecialchars((string) ($row['categorie_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($row['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="small"><?= htmlspecialchars((string) ($row['assigne_nom'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['soumise_le'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <form method="post" action="<?= $this->url('/demandes/updateStatut/' . $sid) ?>" class="d-flex flex-wrap gap-1 justify-content-end align-items-center mb-2">
                                        <select name="statut" class="form-select form-select-sm" style="width: auto; min-width: 9rem;">
                                            <?php foreach ($statut_labels as $k => $lab): ?>
                                                <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Statut</button>
                                    </form>
                                    <form method="post" action="<?= $this->url('/demandes/assign/' . $sid) ?>" class="d-flex flex-wrap gap-1 justify-content-end align-items-center mb-2">
                                        <select name="assigne_a" class="form-select form-select-sm" style="width: auto; max-width: 11rem;">
                                            <option value="">Non assigné</option>
                                            <?php foreach ($staff_list as $su): ?>
                                                <?php $uid = (int) ($su['id'] ?? 0); ?>
                                                <option value="<?= $uid ?>" <?= (int) ($row['assigne_a'] ?? 0) === $uid ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars(trim(($su['prenom'] ?? '') . ' ' . ($su['nom'] ?? '')), ENT_QUOTES, 'UTF-8') ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Assigner</button>
                                    </form>
                                    <form method="post" action="<?= $this->url('/demandes/adminDelete/' . $sid) ?>" onsubmit="return confirm('Supprimer définitivement cette demande ?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
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

<p class="text-muted small mt-3 mb-0">Les catégories sont définies dans la base (<code>categories_service</code>), voir le dump <code>uniserve_full.sql</code>.</p>
