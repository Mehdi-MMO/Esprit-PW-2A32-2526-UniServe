<?php
require_once __DIR__ . '/../../shared/helpers.php';

$rdvs = $rdvs ?? [];
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
        <div class="us-kicker mb-1">Planification</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Rendez-vous'), ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($teacher_notice): ?>
            <p class="text-muted mb-0">La réservation en ligne est réservée aux étudiants. Pour un rendez-vous administratif, contactez un bureau.</p>
        <?php else: ?>
            <p class="text-muted mb-0">Réservez un créneau auprès d’un bureau et suivez vos rendez-vous.</p>
        <?php endif; ?>
    </div>
    <?php if (!$teacher_notice): ?>
        <a href="<?= $this->url('/rendezvous/createForm') ?>" class="btn btn-primary"><i class="fa-solid fa-calendar-plus me-2"></i>Nouveau rendez-vous</a>
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
        <p class="mb-0">Utilisez les autres entrées du menu selon votre profil.</p>
    </div>
<?php else: ?>

<?php
$kpi = [
    ['title' => 'Total', 'value' => $stats['total'] ?? 0, 'border' => 'primary'],
    ['title' => 'Réservé', 'value' => $stats['reserve'] ?? 0, 'border' => 'warning'],
    ['title' => 'Confirmé', 'value' => $stats['confirme'] ?? 0, 'border' => 'info'],
    ['title' => 'Terminé', 'value' => $stats['termine'] ?? 0, 'border' => 'success'],
    ['title' => 'Annulé', 'value' => $stats['annule'] ?? 0, 'border' => 'danger'],
];
?>
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
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
                            <td colspan="6" class="text-center text-muted py-5">Aucun rendez-vous.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rdvs as $row): ?>
                            <?php
                            $sid = (int) ($row['id'] ?? 0);
                            $st = (string) ($row['statut'] ?? '');
                            $label = $statut_labels[$st] ?? $st;
                            ?>
                            <tr id="rdv-<?= $sid ?>">
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars((string) ($row['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['bureau_localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </td>
                                <td><?= htmlspecialchars((string) ($row['motif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small"><?= htmlspecialchars((string) ($row['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small"><?= htmlspecialchars((string) ($row['date_fin'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="text-end">
                                    <?php if ($st === 'reserve'): ?>
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/rendezvous/editForm/' . $sid) ?>">Modifier</a>
                                    <?php endif; ?>
                                    <?php if ($st === 'reserve' || $st === 'confirme'): ?>
                                        <form method="post" action="<?= $this->url('/rendezvous/cancel/' . $sid) ?>" class="d-inline" onsubmit="return confirm('Annuler ce rendez-vous ?');">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Annuler</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($st !== 'reserve' && $st !== 'confirme'): ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$focusRdvId = isset($_GET['focus']) ? (int) $_GET['focus'] : 0;
?>
<?php if ($focusRdvId > 0): ?>
<script>
(function () {
    var id = <?= json_encode($focusRdvId, JSON_THROW_ON_ERROR) ?>;
    var row = document.getElementById('rdv-' + id);
    if (!row) {
        return;
    }
    row.classList.add('table-warning');
    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
})();
</script>
<?php endif; ?>

<?php endif; ?>
