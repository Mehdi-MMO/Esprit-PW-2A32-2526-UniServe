<?php
// Include helpers for rendering UI components
require_once __DIR__ . '/../../shared/helpers.php';

$clubs = $clubs ?? [];
$pendingClubs = $pendingClubs ?? [];
$stats = $stats ?? [];
$q = (string) ($q ?? '');
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');

$clubValidationLabel = static function (string $v): string {
    return match ($v) {
        'approuve' => 'Approuvé',
        'rejete' => 'Rejeté',
        'en_attente' => 'En attente',
        default => $v,
    };
};

$clubValidationBadgeClass = static function (string $v): string {
    return match ($v) {
        'approuve' => 'us-club-val us-club-val--ok',
        'rejete' => 'us-club-val us-club-val--no',
        default => 'us-club-val us-club-val--wait',
    };
};
?>

<?php
$clubsEventsSubnavActive = 'clubs';
require __DIR__ . '/../shared/clubs_events_subnav.php';
?>

<div class="back-page-header mb-4">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="back-page-title"><?= htmlspecialchars((string) ($title ?? 'Gestion des clubs'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-page-sub">Créer et organiser les clubs de l'université.</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/evenements/createClubForm') ?>" class="btn btn-primary" style="border-radius:10px; font-weight:600;">
            <i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nouveau club
        </a>
    </div>
</div>

<?php
if ($success !== '') {
    echo renderSuccessAlert($success);
}
if ($error !== '') {
    echo renderErrorAlert($error);
}
?>

<?php if (!empty($stats)): ?>
    <div class="back-stats-row mb-4">
        <span class="back-stat-pill" style="background:#e0f2fe;color:#0369a1;border-color:#bae6fd;">
            <i class="bi bi-diagram-3-fill"></i><?= $stats['total'] ?? 0 ?> Clubs total
        </span>
        <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
            <i class="bi bi-hourglass-split"></i><?= $stats['pending'] ?? 0 ?> En attente
        </span>
        <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
            <i class="bi bi-check-circle-fill"></i><?= $stats['active'] ?? 0 ?> Clubs actifs
        </span>
    </div>
<?php endif; ?>

<form method="get" action="<?= $this->url('/evenements/manageClubs') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nom ou email du contact..." style="max-width:300px; border-radius:10px; font-size:.9rem; font-weight:600;" autocomplete="off">
    <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search me-1"></i> Filtrer</button>
    <?php if ($q !== ''): ?>
        <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
    <?php endif; ?>
</form>

<div class="mb-4">
    <h2 class="h5 mb-3 d-flex align-items-center gap-2" style="color:var(--brand);">
        <i class="bi bi-inbox text-warning"></i>
        Demandes en attente
    </h2>
    <?php if (empty($pendingClubs)): ?>
        <div class="text-muted small">Aucune demande en attente.</div>
    <?php else: ?>
        <div class="back-table-wrap">
            <table class="back-table">
                <thead>
                    <tr>
                        <th scope="col">Nom</th>
                        <th scope="col">Propriétaire</th>
                        <th scope="col">Email contact</th>
                        <th scope="col">Description</th>
                        <th scope="col" class="text-end">Modération</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingClubs as $club): ?>
                        <?php $clubId = (int) ($club['id'] ?? 0); ?>
                        <tr>
                            <td class="fw-semibold text-nowrap" style="color:var(--brand);"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-medium text-secondary"><?= htmlspecialchars((string) ($club['owner_nom'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="small"><a href="mailto:<?= htmlspecialchars((string) ($club['email_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($club['email_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                            <td class="text-muted small"><?= htmlspecialchars((string) ($club['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end align-items-center">
                                    <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>" style="border-radius:6px; width:32px; height:32px;" title="Modifier">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form method="post" action="<?= $this->url('/evenements/approveClub/' . $clubId) ?>" class="m-0 d-inline">
                                        <button class="btn btn-success btn-sm d-flex align-items-center justify-content-center" type="submit" style="border-radius:6px; width:32px; height:32px;" title="Approuver">
                                            <i class="bi bi-check-lg" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="<?= $this->url('/evenements/rejectClub/' . $clubId) ?>" class="m-0 d-inline">
                                        <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" type="submit" style="border-radius:6px; width:32px; height:32px;" title="Rejeter">
                                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<div>
    <h2 class="h5 mb-3 d-flex align-items-center gap-2" style="color:var(--brand);">
        <i class="bi bi-collection text-primary"></i>
        Clubs existants
    </h2>
    <div class="back-table-wrap">
        <table class="back-table">
            <thead>
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Propriétaire</th>
                    <th scope="col">Email contact</th>
                    <th scope="col">Description</th>
                    <th scope="col">Statut</th>
                    <th scope="col" class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clubs)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            Aucun club ne correspond à votre recherche.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clubs as $club): ?>
                        <?php
                        $clubId = (int) ($club['id'] ?? 0);
                        $validation = (string) ($club['statut_validation'] ?? 'en_attente');
                        $actif = (int) ($club['actif'] ?? 0);
                        $vLabel = $clubValidationLabel($validation);
                        
                        $vBg = '#fef9c3'; $vColor = '#854d0e'; $vBorder = '#fde047';
                        if ($validation === 'approuve') { $vBg = '#dcfce7'; $vColor = '#166534'; $vBorder = '#86efac'; }
                        if ($validation === 'rejete') { $vBg = '#fee2e2'; $vColor = '#991b1b'; $vBorder = '#fca5a5'; }
                        ?>
                        <tr>
                            <td class="fw-semibold" style="color:var(--brand);"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="fw-medium text-secondary"><?= htmlspecialchars((string) ($club['owner_nom'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="small">
                                <?php $em = (string) ($club['email_contact'] ?? ''); ?>
                                <?php if ($em !== ''): ?>
                                    <a href="mailto:<?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small"><?= htmlspecialchars((string) ($club['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <span class="badge" style="background:<?= $vBg ?>;color:<?= $vColor ?>;border:1px solid <?= $vBorder ?>;border-radius:6px;padding:5px 8px;">
                                        <?= htmlspecialchars($vLabel, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                    <?php if ($actif): ?>
                                        <span class="badge" style="background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:6px;padding:5px 8px;">Actif</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#f3f4f6;color:#4b5563;border:1px solid #e5e7eb;border-radius:6px;padding:5px 8px;">Inactif</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end align-items-center">
                                    <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>" title="Modifier" style="border-radius:6px; width:32px; height:32px;">
                                        <i class="bi bi-pencil" aria-hidden="true"></i>
                                    </a>
                                    <form method="post" action="<?= $this->url('/evenements/deleteClub/' . $clubId) ?>" class="m-0 d-inline" onsubmit="return confirm('Supprimer ce club et tous ses événements associés ?');">
                                        <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" type="submit" title="Supprimer" style="border-radius:6px; width:32px; height:32px;">
                                            <i class="bi bi-trash3-fill" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
