<?php
require_once __DIR__ . '/../../shared/helpers.php';

$rdvs = $rdvs ?? [];
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
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Rendez-vous'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Gestion des créneaux réservés par les étudiants auprès des bureaux.</p>
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
    ['title' => 'Total', 'value' => $stats['total'] ?? 0, 'color' => 'primary', 'icon' => 'fa-solid fa-calendar-days'],
    ['title' => 'Réservé', 'value' => $stats['reserve'] ?? 0, 'color' => 'warning', 'icon' => 'fa-solid fa-bookmark'],
    ['title' => 'Confirmé', 'value' => $stats['confirme'] ?? 0, 'color' => 'info', 'icon' => 'fa-solid fa-circle-check'],
    ['title' => 'Terminé', 'value' => $stats['termine'] ?? 0, 'color' => 'success', 'icon' => 'fa-solid fa-flag-checkered'],
    ['title' => 'Annulé', 'value' => $stats['annule'] ?? 0, 'color' => 'danger', 'icon' => 'fa-solid fa-ban'],
    ['title' => 'Bureaux actifs', 'value' => $stats['bureaux_actifs'] ?? 0, 'color' => 'secondary', 'icon' => 'fa-solid fa-building'],
];
echo '<div class="mb-4">' . renderStatGrid($statCards) . '</div>';
?>

<div class="us-section-card mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= $this->url('/rendezvous') ?>" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1" for="rq">Recherche</label>
                <input type="text" class="form-control" id="rq" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Motif, étudiant, bureau…">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1" for="rst">Statut</label>
                <select class="form-select" id="rst" name="statut">
                    <option value="">Tous</option>
                    <?php foreach ($statut_labels as $k => $lab): ?>
                        <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statut_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-secondary">Réinitialiser</a>
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
                        <th>Bureau</th>
                        <th>Motif</th>
                        <th>Début</th>
                        <th>Fin</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rdvs)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Aucun rendez-vous.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rdvs as $row): ?>
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
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($row['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['bureau_localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['motif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small"><?= htmlspecialchars((string) ($row['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small"><?= htmlspecialchars((string) ($row['date_fin'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="text-end">
                                    <form method="post" action="<?= $this->url('/rendezvous/updateStatut/' . $sid) ?>" class="d-flex flex-wrap gap-1 justify-content-end align-items-center mb-2">
                                        <select name="statut" class="form-select form-select-sm" style="width: auto; min-width: 9rem;">
                                            <?php foreach ($statut_labels as $k => $lab): ?>
                                                <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-sm btn-primary">Statut</button>
                                    </form>
                                    <form method="post" action="<?= $this->url('/rendezvous/adminDelete/' . $sid) ?>" onsubmit="return confirm('Supprimer définitivement ce rendez-vous ?');">
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

<p class="text-muted small mt-3 mb-0">Les bureaux sont définis dans <code>bureaux</code> (voir <code>uniserve_full.sql</code>). Les chevauchements sur un même bureau sont refusés côté serveur.</p>
