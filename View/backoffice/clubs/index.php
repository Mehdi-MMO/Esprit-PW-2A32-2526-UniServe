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

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Gestion des clubs'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Créer et organiser les clubs de l'université.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/evenements/createClubForm') ?>" class="btn btn-primary">
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
    <?php
    $statCards = [
        [
            'title' => 'Clubs total',
            'value' => $stats['total'] ?? 0,
            'color' => 'primary',
            'icon' => 'fa-solid fa-objects-column',
        ],
        [
            'title' => 'En attente',
            'value' => $stats['pending'] ?? 0,
            'color' => 'warning',
            'icon' => 'fa-solid fa-hourglass-end',
        ],
        [
            'title' => 'Clubs actifs',
            'value' => $stats['active'] ?? 0,
            'color' => 'success',
            'icon' => 'fa-solid fa-circle-check',
        ],
    ];
    echo '<div class="mb-4">' . renderStatGrid($statCards) . '</div>';
    ?>
<?php endif; ?>

<div class="us-section-card mb-4 us-admin-clubs">
    <div class="card-body p-3 p-md-4">
        <form method="get" action="<?= $this->url('/evenements/manageClubs') ?>" class="row g-3 align-items-end mb-0 us-filter-shell">
            <div class="col-lg-8">
                <label class="form-label text-muted small mb-1 fw-semibold" for="q">Recherche</label>
                <input class="form-control" id="q" name="q" placeholder="Nom ou email du contact…" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" autocomplete="off">
            </div>
            <div class="col-lg-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="bi bi-search me-1" aria-hidden="true"></i>Filtrer
                </button>
                <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-outline-secondary flex-grow-1">Réinitialiser</a>
            </div>
        </form>
    </div>
</div>

<div class="us-section-card mb-4 us-admin-clubs">
    <div class="card-body p-0">
        <div class="us-admin-clubs__section-head px-3 px-md-4 pt-3 pt-md-4 pb-2">
            <h2 class="h6 mb-0 d-flex align-items-center gap-2">
                <span class="us-admin-clubs__icon us-admin-clubs__icon--pending" aria-hidden="true"><i class="bi bi-inbox"></i></span>
                Demandes en attente
            </h2>
            <p class="text-muted small mb-0 mt-1">Clubs créés par les étudiants, en attente de validation staff.</p>
        </div>
        <?php if (empty($pendingClubs)): ?>
            <div class="us-empty-state mx-3 mx-md-4 mb-3 mb-md-4 text-center py-4 px-3">
                <i class="bi bi-check2-circle text-success fs-2 mb-2 d-block" aria-hidden="true"></i>
                <p class="text-muted mb-0 fw-medium">Aucune demande en attente.</p>
                <p class="text-muted small mb-0 mt-1">Les nouvelles demandes apparaîtront ici.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive us-table-wrap mx-3 mx-md-4 mb-3 mb-md-4">
                <table class="table table-hover align-middle mb-0 us-admin-clubs-table">
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
                                <td class="fw-semibold text-nowrap"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($club['owner_nom'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small"><a href="mailto:<?= htmlspecialchars((string) ($club['email_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($club['email_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></td>
                                <td class="us-club-desc text-muted small"><?= htmlspecialchars((string) ($club['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <div class="us-action-group justify-content-end us-club-row-actions">
                                        <a class="btn btn-sm btn-light border" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>" title="Modifier">
                                            <i class="bi bi-pencil" aria-hidden="true"></i><span class="visually-hidden">Modifier</span>
                                        </a>
                                        <form method="post" action="<?= $this->url('/evenements/approveClub/' . $clubId) ?>" class="d-inline">
                                            <button class="btn btn-sm btn-success" type="submit" title="Approuver">
                                                <i class="bi bi-check-lg" aria-hidden="true"></i><span class="visually-hidden">Approuver</span>
                                            </button>
                                        </form>
                                        <form method="post" action="<?= $this->url('/evenements/rejectClub/' . $clubId) ?>" class="d-inline">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Rejeter">
                                                <i class="bi bi-x-lg" aria-hidden="true"></i><span class="visually-hidden">Rejeter</span>
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
</div>

<div class="us-section-card us-admin-clubs">
    <div class="card-body p-0">
        <div class="us-admin-clubs__section-head px-3 px-md-4 pt-3 pt-md-4 pb-2">
            <h2 class="h6 mb-0 d-flex align-items-center gap-2">
                <span class="us-admin-clubs__icon us-admin-clubs__icon--list" aria-hidden="true"><i class="bi bi-collection"></i></span>
                Clubs existants
            </h2>
            <p class="text-muted small mb-0 mt-1">Clubs validés et visibles côté portail (sous réserve du statut actif).</p>
        </div>
        <div class="table-responsive us-table-wrap mx-3 mx-md-4 mb-3 mb-md-4">
            <table class="table table-hover align-middle mb-0 us-admin-clubs-table">
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
                                <i class="bi bi-folder2-open d-block fs-3 mb-2 opacity-50" aria-hidden="true"></i>
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
                            $vClass = $clubValidationBadgeClass($validation);
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($club['owner_nom'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small">
                                    <?php $em = (string) ($club['email_contact'] ?? ''); ?>
                                    <?php if ($em !== ''): ?>
                                        <a href="mailto:<?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($em, ENT_QUOTES, 'UTF-8') ?></a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="us-club-desc text-muted small"><?= htmlspecialchars((string) ($club['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1 align-items-center">
                                        <span class="<?= $vClass ?>"><?= htmlspecialchars($vLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if ($actif): ?>
                                            <span class="us-club-val us-club-val--active">Actif</span>
                                        <?php else: ?>
                                            <span class="us-club-val us-club-val--inactive">Inactif</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="us-action-group justify-content-end us-club-row-actions">
                                        <a class="btn btn-sm btn-light border" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>" title="Modifier le club">
                                            <i class="bi bi-pencil" aria-hidden="true"></i><span class="d-none d-xl-inline ms-1">Modifier</span>
                                        </a>
                                        <form method="post" action="<?= $this->url('/evenements/deleteClub/' . $clubId) ?>" class="d-inline" onsubmit="return confirm('Supprimer ce club et tous ses événements associés ?');">
                                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer le club">
                                                <i class="bi bi-trash" aria-hidden="true"></i><span class="d-none d-xl-inline ms-1">Supprimer</span>
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
</div>
