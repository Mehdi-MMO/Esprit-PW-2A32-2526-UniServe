<?php
require_once __DIR__ . '/../../shared/helpers.php';

$demandes = $demandes ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
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

<div class="back-page-header">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-journal-text"></i></div>
        <div>
            <div class="back-page-title"><?= htmlspecialchars((string) ($title ?? 'Demandes de service'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-page-sub">Traitement des demandes (étudiants et enseignants) par catégorie.</div>
        </div>
    </div>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert" style="border-left:4px solid #22c55e;border-radius:12px;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-check-circle-fill" style="font-size:1.5rem;color:#22c55e;"></i>
                <div><?= htmlspecialchars((string) ($flash['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif (($flash['type'] ?? '') === 'warning'): ?>
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-left:4px solid #eab308;border-radius:12px;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:1.5rem;color:#eab308;"></i>
                <div><?= htmlspecialchars((string) ($flash['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php else: ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="border-left:4px solid #ef4444;border-radius:12px;">
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-x-circle-fill" style="font-size:1.5rem;color:#ef4444;"></i>
                <div><?= htmlspecialchars((string) ($flash['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="back-stats-row mb-4">
    <span class="back-stat-pill" style="background:#e0f2fe;color:#0369a1;border-color:#bae6fd;">
        <i class="bi bi-inbox-fill"></i><?= $stats['total'] ?? 0 ?> Total
    </span>
    <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
        <i class="bi bi-hourglass-split"></i><?= $stats['en_attente'] ?? 0 ?> En attente
    </span>
    <span class="back-stat-pill" style="background:#e0e7ff;color:#4338ca;border-color:#c7d2fe;">
        <i class="bi bi-arrow-repeat"></i><?= $stats['en_cours'] ?? 0 ?> En cours
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= $stats['traite'] ?? 0 ?> Traitées
    </span>
    <span class="back-stat-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
        <i class="bi bi-x-circle-fill"></i><?= $stats['rejete'] ?? 0 ?> Rejetées
    </span>
</div>

<form method="get" action="<?= $this->url('/demandes') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Titre, étudiant, catégorie..." style="max-width:300px; border-radius:10px; font-size:.9rem; font-weight:600;">
    <select class="form-select" name="statut" style="max-width:200px; border-radius:10px; font-size:.9rem; font-weight:600;">
        <option value="">Tous les statuts</option>
        <?php foreach ($statut_labels as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statut_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search me-1"></i> Filtrer</button>
    <?php if ($q !== '' || $statut_filter !== ''): ?>
        <a href="<?= $this->url('/demandes') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
    <?php endif; ?>
</form>

<div class="back-table-wrap">
    <table class="back-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Étudiant</th>
                <th>Catégorie</th>
                <th>Titre</th>
                <th>Pièces</th>
                <th>Statut</th>
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
                <?php
                $badgeStyles = [
                    'en_attente'   => ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047'],
                    'en_cours'     => ['bg'=>'#e0e7ff','color'=>'#4338ca','border'=>'#c7d2fe'],
                    'traite'       => ['bg'=>'#dcfce7','color'=>'#166534','border'=>'#86efac'],
                    'rejete'       => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5'],
                ];
                ?>
                <?php foreach ($demandes as $row): ?>
                    <?php
                    $sid = (int) ($row['id'] ?? 0);
                    $st = (string) ($row['statut'] ?? '');
                    $label = $statut_labels[$st] ?? $st;
                    $bs = $badgeStyles[$st] ?? ['bg'=>'#f3f4f6','color'=>'#4b5563','border'=>'#e5e7eb'];
                    $photoUrl = null;
                    if (function_exists('profile_photo_public_url')) {
                        // Warning: photo might not be fetched in the query.
                        // We will just use the initials if not.
                        $photoUrl = profile_photo_public_url((string) ($row['etudiant_photo'] ?? ''), $this);
                    }
                    $nom = $row['etudiant_nom'] ?? 'Inconnu';
                    $initials = strtoupper(mb_substr($nom, 0, 1));
                    if (str_contains($nom, ' ')) {
                        $parts = explode(' ', $nom);
                        $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts)-1], 0, 1));
                    }
                    ?>
                    <tr>
                        <td class="text-muted small fw-bold">#<?= $sid ?></td>
                        <td>
                            <div class="back-student-cell">
                                <?php if ($photoUrl !== null && $row['etudiant_photo'] ?? '' !== ''): ?>
                                    <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="back-avatar" style="object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <div class="back-avatar"><?= $initials ?></div>
                                <?php endif; ?>
                                <div>
                                    <div class="back-student-name"><?= htmlspecialchars((string) ($row['etudiant_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="small text-muted" style="font-size:0.75rem;"><?= htmlspecialchars((string) ($row['etudiant_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="small fw-semibold text-secondary"><?= htmlspecialchars((string) ($row['categorie_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="fw-semibold" style="color:var(--brand);"><?= htmlspecialchars((string) ($row['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small">
                            <?php
                            $pList = $pieces_by_demande[$sid] ?? [];
                            ?>
                            <?php if ($pList === []): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-1">
                                <?php foreach ($pList as $pj): ?>
                                    <?php $pid = (int) ($pj['id'] ?? 0); ?>
                                    <div class="d-flex align-items-center gap-1 bg-light rounded px-2 py-1" style="width: max-content;">
                                        <i class="bi bi-paperclip text-muted"></i>
                                        <a href="<?= $this->url('/demandes/downloadPiece/' . $pid) ?>" class="text-decoration-none small text-truncate" style="max-width:120px;" title="<?= htmlspecialchars((string) ($pj['nom_fichier'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars((string) ($pj['nom_fichier'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                        </a>
                                        <form method="post" action="<?= $this->url('/demandes/deletePiece/' . $pid) ?>" class="m-0 ms-1" onsubmit="return confirm('Supprimer cette pièce ?');">
                                            <button type="submit" class="btn btn-link btn-sm text-danger p-0 border-0 lh-1" title="Supprimer"><i class="bi bi-x-circle-fill"></i></button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge" style="background:<?= $bs['bg'] ?>;color:<?= $bs['color'] ?>;border:1px solid <?= $bs['border'] ?>;border-radius:6px;padding:5px 8px;">
                                <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        </td>
                        <td class="small text-muted fw-medium">
                            <i class="bi bi-clock me-1"></i><?= htmlspecialchars((string) ($row['soumise_le'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-2 align-items-end">
                                <form method="post" action="<?= $this->url('/demandes/updateStatut/' . $sid) ?>" class="d-flex gap-1 align-items-center">
                                    <select name="statut" class="form-select form-select-sm" style="width: 130px; font-weight:600; font-size:0.8rem; border-radius:6px;">
                                        <?php foreach ($statut_labels as $k => $lab): ?>
                                            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm" style="background:var(--brand);color:#fff;border-radius:6px;" title="Mettre à jour"><i class="bi bi-check2"></i></button>
                                </form>
                                <div class="d-flex gap-1 justify-content-end w-100">
                                    <?php if ($demande_staff_ai_check_enabled): ?>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary us-demande-staff-ai-check d-flex align-items-center justify-content-center"
                                            style="border-radius:6px; flex:1;"
                                            data-check-url="<?= htmlspecialchars($this->url('/demandes/aiStaffCheck/' . $sid), ENT_QUOTES, 'UTF-8') ?>"
                                            data-demande-label="<?= htmlspecialchars('#' . $sid . ' — ' . (string) ($row['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            title="Assistant IA">
                                            <i class="bi bi-robot" aria-hidden="true"></i> IA
                                        </button>
                                    <?php endif; ?>
                                    <form method="post" action="<?= $this->url('/demandes/adminDelete/' . $sid) ?>" class="m-0" style="flex:1;" onsubmit="return confirm('Supprimer définitivement cette demande ?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100 d-flex align-items-center justify-content-center" style="border-radius:6px;" title="Supprimer">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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
                    <p class="small text-break" style="white-space: pre-line;" id="usDemandeStaffAiPoints"></p>
                    <h3 class="h6">Éléments manquants / à clarifier</h3>
                    <p class="small text-break" style="white-space: pre-line;" id="usDemandeStaffAiManquants"></p>
                    <h3 class="h6">Piste de traitement</h3>
                    <p class="small text-break mb-0" style="white-space: pre-line;" id="usDemandeStaffAiEtape"></p>
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

    function staffAiAsPlainText(v) {
        if (v == null || v === '') {
            return '—';
        }
        if (Array.isArray(v)) {
            var lines = v.map(function (x) {
                return (x == null) ? '' : String(x).trim();
            }).filter(Boolean);
            return lines.length ? lines.join('\n• ') : '—';
        }
        return String(v);
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
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: new URLSearchParams()
            })
                .then(function (r) {
                    return r.text().then(function (t) {
                        var t0 = (typeof t === 'string' ? t : '').replace(/^\uFEFF/, '');
                        var brace = t0.indexOf('{');
                        if (brace > 0) {
                            t0 = t0.substring(brace);
                        }
                        var j = null;
                        try {
                            j = t0 ? JSON.parse(t0) : null;
                        } catch (e) {
                            j = null;
                        }
                        return { ok: r.ok, status: r.status, body: j, raw: t };
                    });
                })
                .then(function (res) {
                    loading.classList.add('d-none');
                    if (res.ok && res.body && res.body.ok) {
                        if (verdictEl) {
                            verdictEl.textContent = verdictLabel(res.body.verdict);
                            verdictEl.className = 'badge ' + verdictBadgeClass(res.body.verdict);
                        }
                        if (pointsEl) pointsEl.textContent = staffAiAsPlainText(res.body.points_cles);
                        if (manquantsEl) manquantsEl.textContent = staffAiAsPlainText(res.body.elements_manquants);
                        if (etapeEl) etapeEl.textContent = staffAiAsPlainText(res.body.suggestion_prochaine_etape);
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
