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

<div class="back-page-header mb-4">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
        <div>
            <div class="back-page-title"><?= htmlspecialchars((string) ($title ?? 'Documents académiques'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-page-sub">Validation et suivi des demandes de documents officiels.</div>
        </div>
    </div>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<div class="back-stats-row mb-4">
    <span class="back-stat-pill" style="background:#e0e7ff;color:#4338ca;border-color:#c7d2fe;">
        <i class="bi bi-file-earmark-text-fill"></i><?= $stats['total'] ?? 0 ?> Total
    </span>
    <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
        <i class="bi bi-hourglass-split"></i><?= $stats['en_attente'] ?? 0 ?> En attente
    </span>
    <span class="back-stat-pill" style="background:#e0f2fe;color:#0369a1;border-color:#bae6fd;">
        <i class="bi bi-search"></i><?= $stats['en_validation'] ?? 0 ?> En validation
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= $stats['valide'] ?? 0 ?> Validé
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-box-seam-fill"></i><?= $stats['livre'] ?? 0 ?> Livré
    </span>
    <span class="back-stat-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
        <i class="bi bi-x-circle-fill"></i><?= $stats['rejete'] ?? 0 ?> Rejeté
    </span>
</div>

<form method="get" action="<?= $this->url('/documents') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Type, étudiant, email..." style="max-width:300px; border-radius:10px; font-size:.9rem; font-weight:600;">
    <select class="form-select" name="statut" style="max-width:180px; border-radius:10px; font-size:.9rem; font-weight:600;">
        <option value="">Tous statuts</option>
        <?php foreach ($statut_labels as $k => $lab): ?>
            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statut_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search me-1"></i> Filtrer</button>
    <?php if ($q !== '' || $statut_filter !== ''): ?>
        <a href="<?= $this->url('/documents') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
    <?php endif; ?>
</form>

<div class="back-table-wrap">
    <table class="back-table">
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
                                    <div class="back-student-cell">
                                        <?php
                                        $etuNom = (string) ($row['etudiant_nom'] ?? 'U');
                                        $initials = profile_avatar_initial($etuNom);
                                        $photoPath = (string) ($row['etudiant_photo'] ?? '');
                                        ?>
                                        <?php if ($photoPath !== ''): ?>
                                            <img src="<?= htmlspecialchars(profile_photo_public_url($photoPath), ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="back-avatar" style="object-fit:cover; border:none; padding:0;">
                                        <?php else: ?>
                                            <div class="back-avatar"><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="back-student-name">
                                                <?= htmlspecialchars($etuNom, ENT_QUOTES, 'UTF-8') ?>
                                            </div>
                                            <div class="text-muted small" style="font-size:0.75rem;"><?= htmlspecialchars((string) ($row['etudiant_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small"><?= htmlspecialchars((string) ($row['type_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php
                                    $stBadgeBg = '#f3f4f6'; $stBadgeColor = '#4b5563'; $stBadgeBorder = '#e5e7eb';
                                    if ($st === 'en_attente') { $stBadgeBg = '#fef9c3'; $stBadgeColor = '#854d0e'; $stBadgeBorder = '#fde047'; }
                                    if ($st === 'en_validation') { $stBadgeBg = '#e0f2fe'; $stBadgeColor = '#0369a1'; $stBadgeBorder = '#bae6fd'; }
                                    if ($st === 'valide' || $st === 'livre') { $stBadgeBg = '#dcfce7'; $stBadgeColor = '#166534'; $stBadgeBorder = '#86efac'; }
                                    if ($st === 'rejete') { $stBadgeBg = '#fee2e2'; $stBadgeColor = '#991b1b'; $stBadgeBorder = '#fca5a5'; }
                                    ?>
                                    <span class="badge" style="background:<?= $stBadgeBg ?>;color:<?= $stBadgeColor ?>;border:1px solid <?= $stBadgeBorder ?>;border-radius:6px;padding:5px 8px;">
                                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['demandee_le'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['validee_le'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['livree_le'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end" style="min-width:180px;">
                                    <form method="post" action="<?= $this->url('/documents/updateStatut/' . $sid) ?>" class="d-flex flex-column gap-1">
                                        <div class="d-flex gap-1 align-items-center justify-content-end">
                                            <select name="statut" class="form-select form-select-sm" required style="width:120px; border-radius:6px; font-weight:600;">
                                                <?php foreach ($statut_labels as $k => $lab): ?>
                                                    <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary d-flex align-items-center justify-content-center" style="border-radius:6px; width:32px; height:32px;" title="Appliquer">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </div>
                                        <textarea name="note_validation" class="form-control form-control-sm mt-1" rows="1" placeholder="Motif (si rejeté)" style="border-radius:6px; font-size:0.8rem;"><?= htmlspecialchars((string) ($row['note_validation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
    </table>
</div>
