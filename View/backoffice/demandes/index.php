<?php
require_once __DIR__ . '/../../shared/helpers.php';

$demandes = $demandes ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
$staff_list = $staff_list ?? [];
$statut_filter = (string) ($statut_filter ?? '');
$pieces_by_demande = $pieces_by_demande ?? [];
$demande_staff_ai_check_enabled = !empty($demande_staff_ai_check_enabled ?? false);

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
        <p class="text-muted mb-0">Traitement des demandes (étudiants et enseignants) par catégorie.</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php elseif (($flash['type'] ?? '') === 'warning'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string) ($flash['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
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
                        <th>Pièces</th>
                        <th>Statut</th>
                        <th>Assigné</th>
                        <th>Soumise</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">Aucune demande.</td>
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
                                <td class="small">
                                    <?php
                                    $pList = $pieces_by_demande[$sid] ?? [];
                                    ?>
                                    <?php if ($pList === []): ?>
                                        <span class="text-muted">—</span>
                                    <?php else: ?>
                                        <?php foreach ($pList as $pj): ?>
                                            <?php $pid = (int) ($pj['id'] ?? 0); ?>
                                            <div class="mb-1">
                                                <a href="<?= $this->url('/demandes/downloadPiece/' . $pid) ?>"><?= htmlspecialchars((string) ($pj['nom_fichier'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                                                <form method="post" action="<?= $this->url('/demandes/deletePiece/' . $pid) ?>" class="d-inline ms-1" onsubmit="return confirm('Supprimer cette pièce ?');">
                                                    <button type="submit" class="btn btn-link btn-sm text-danger p-0 align-baseline" title="Supprimer">×</button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
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
                                    <?php if ($demande_staff_ai_check_enabled): ?>
                                        <div class="d-flex justify-content-end mb-2">
                                            <button type="button"
                                                class="btn btn-sm btn-outline-secondary us-demande-staff-ai-check"
                                                data-check-url="<?= htmlspecialchars($this->url('/demandes/aiStaffCheck/' . $sid), ENT_QUOTES, 'UTF-8') ?>"
                                                data-demande-label="<?= htmlspecialchars('#' . $sid . ' — ' . (string) ($row['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fa-solid fa-robot me-1" aria-hidden="true"></i>Vérification IA
                                            </button>
                                        </div>
                                    <?php endif; ?>
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

<?php if ($demande_staff_ai_check_enabled): ?>
<div class="modal fade" id="usDemandeStaffAiModal" tabindex="-1" aria-labelledby="usDemandeStaffAiLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="usDemandeStaffAiLabel">Vérification IA</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3" id="usDemandeStaffAiMeta"></p>
                <div id="usDemandeStaffAiLoading" class="d-none text-center py-4 text-muted">
                    <i class="fa-solid fa-circle-notch fa-spin fa-2x mb-2" aria-hidden="true"></i>
                    <div>Analyse en cours…</div>
                </div>
                <div id="usDemandeStaffAiError" class="alert alert-danger d-none" role="alert"></div>
                <div id="usDemandeStaffAiResult" class="d-none">
                    <div class="mb-3"><span class="badge" id="usDemandeStaffAiVerdict"></span></div>
                    <h3 class="h6">Points clés</h3>
                    <p class="small" id="usDemandeStaffAiPoints"></p>
                    <h3 class="h6">Éléments manquants / à clarifier</h3>
                    <p class="small" id="usDemandeStaffAiManquants"></p>
                    <h3 class="h6">Piste de traitement</h3>
                    <p class="small mb-0" id="usDemandeStaffAiEtape"></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('usDemandeStaffAiModal');
    if (!modalEl || typeof bootstrap === 'undefined') {
        return;
    }
    var modal = new bootstrap.Modal(modalEl);
    var meta = document.getElementById('usDemandeStaffAiMeta');
    var loading = document.getElementById('usDemandeStaffAiLoading');
    var errBox = document.getElementById('usDemandeStaffAiError');
    var result = document.getElementById('usDemandeStaffAiResult');
    var verdictEl = document.getElementById('usDemandeStaffAiVerdict');
    var pointsEl = document.getElementById('usDemandeStaffAiPoints');
    var manquantsEl = document.getElementById('usDemandeStaffAiManquants');
    var etapeEl = document.getElementById('usDemandeStaffAiEtape');
    if (!loading || !errBox || !result) {
        return;
    }

    function verdictBadgeClass(v) {
        v = (v || '').toLowerCase();
        if (v === 'clair') return 'text-bg-success';
        if (v === 'insuffisant' || v === 'douteux') return 'text-bg-danger';
        return 'text-bg-warning';
    }
    function verdictLabel(v) {
        v = (v || '').toLowerCase();
        if (v === 'clair') return 'Clair';
        if (v === 'insuffisant') return 'Insuffisant';
        if (v === 'douteux') return 'Douteux';
        if (v === 'a_preciser') return 'À préciser';
        return v || '—';
    }

    document.querySelectorAll('.us-demande-staff-ai-check').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var url = btn.getAttribute('data-check-url');
            var label = btn.getAttribute('data-demande-label') || '';
            if (!url) return;
            if (meta) meta.textContent = label;
            errBox.classList.add('d-none');
            errBox.textContent = '';
            result.classList.add('d-none');
            loading.classList.remove('d-none');
            modal.show();

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: '{}'
            })
                .then(function (r) {
                    return r.text().then(function (t) {
                        var t0 = (typeof t === 'string' ? t : '').replace(/^\uFEFF/, '');
                        var j = null;
                        try {
                            j = t0 ? JSON.parse(t0) : null;
                        } catch (e) {
                            j = null;
                        }
                        return { ok: r.ok, status: r.status, body: j, raw: t0 };
                    });
                })
                .then(function (res) {
                    loading.classList.add('d-none');
                    if (res.ok && res.body && res.body.ok) {
                        if (verdictEl) {
                            verdictEl.textContent = verdictLabel(res.body.verdict);
                            verdictEl.className = 'badge ' + verdictBadgeClass(res.body.verdict);
                        }
                        if (pointsEl) pointsEl.textContent = res.body.points_cles || '—';
                        if (manquantsEl) manquantsEl.textContent = res.body.elements_manquants || '—';
                        if (etapeEl) etapeEl.textContent = res.body.suggestion_prochaine_etape || '—';
                        result.classList.remove('d-none');
                    } else {
                        var msg;
                        if (res.body && res.body.error) {
                            msg = res.body.error;
                        } else if (res.ok && !res.body && (!res.raw || res.raw.trim() === '')) {
                            msg = 'Réponse vide du serveur (souvent un encodage JSON bloqué côté PHP). Réessayez après mise à jour.';
                        } else if (res.ok && !res.body && res.raw && /^\s*</.test(res.raw)) {
                            msg = 'Réponse HTML au lieu de JSON (session expirée ou URL incorrecte). Rechargez la page puis réessayez.';
                        } else {
                            msg = 'Réponse ' + (res.status || '') + (res.raw && res.raw.length < 200 ? ' : ' + res.raw : '');
                        }
                        errBox.textContent = msg || 'Erreur serveur.';
                        errBox.classList.remove('d-none');
                    }
                })
                .catch(function () {
                    loading.classList.add('d-none');
                    errBox.textContent = 'Erreur réseau.';
                    errBox.classList.remove('d-none');
                });
        });
    });
});
</script>
<?php endif; ?>

<p class="text-muted small mt-3 mb-0">Catalogue des types de demande : menu <a href="<?= $this->url('/services') ?>">Services (catégories)</a> (table <code>categories_service</code>).</p>
