<?php
// Include helpers for rendering UI components
require_once __DIR__ . '/../../shared/helpers.php';

$clubs = $clubs ?? [];
$pendingClubs = $pendingClubs ?? [];
$stats = $stats ?? [];
$q = (string) ($q ?? '');
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<!-- Module Navigation Tabs -->
<div class="d-flex gap-2 mb-4 us-module-tabs">
    <a href="<?= $this->url('/evenements/manageClubs') ?>" class="nav-tab active" aria-current="page">
        <i class="fa-solid fa-objects-column me-2"></i>Clubs
    </a>
    <a href="<?= $this->url('/evenements/manage') ?>" class="nav-tab">
        <i class="fa-solid fa-calendar-days me-2"></i>Événements
    </a>
</div>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Gestion des clubs'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Créer et organiser les clubs de l'université.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-outline-secondary btn-sm">Événements</a>
        <a href="<?= $this->url('/evenements/createClubForm') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Nouveau club</a>
    </div>
</div>

<?php 
// Display alerts
if ($success !== '') {
    echo renderSuccessAlert($success);
}
if ($error !== '') {
    echo renderErrorAlert($error);
}
?>

<!-- Statistics Dashboard -->
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
    echo renderStatGrid($statCards);
    ?>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <!-- Search and Filter Form -->
        <form method="get" action="<?= $this->url('/evenements/manageClubs') ?>" class="row g-2 align-items-end mb-4 us-filter-shell">
            <div class="col-lg-8">
                <label class="form-label text-muted small mb-1" for="q">Recherche</label>
                <input class="form-control" id="q" name="q" placeholder="Nom ou email contact..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-outline-secondary w-100">Réinitialiser</a>
            </div>
        </form>

        <!-- Pending Clubs Section -->
        <div class="mb-5">
            <h5 class="mb-3"><i class="bi bi-exclamation-circle text-warning me-2"></i>Demandes de clubs en attente</h5>
            <div class="table-responsive us-table-wrap">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Propriétaire</th>
                            <th>Email contact</th>
                            <th>Description</th>
                            <th class="text-end">Modération</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingClubs)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Aucune demande en attente.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingClubs as $club): ?>
                                <?php $clubId = (int) ($club['id'] ?? 0); ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($club['owner_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($club['email_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars((string) ($club['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                                            <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>">Modifier</a>
                                            <form method="post" action="<?= $this->url('/evenements/approveClub/' . $clubId) ?>" class="d-inline">
                                                <button class="btn btn-outline-success btn-sm" type="submit">Approuver</button>
                                            </form>
                                            <form method="post" action="<?= $this->url('/evenements/rejectClub/' . $clubId) ?>" class="d-inline">
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Rejeter</button>
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

        <!-- Active Clubs Section -->
        <div>
            <h5 class="mb-3"><i class="bi bi-collection text-primary me-2"></i>Clubs existants</h5>
            <div class="table-responsive us-table-wrap">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Propriétaire</th>
                            <th>Email contact</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($clubs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Aucun club trouvé.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clubs as $club): ?>
                                <?php
                                $clubId = (int) ($club['id'] ?? 0);
                                $validation = (string) ($club['statut_validation'] ?? 'en_attente');
                                $badge = $validation === 'approuve' ? 'success' : ($validation === 'rejete' ? 'danger' : 'secondary');
                                $actif = (int) ($club['actif'] ?? 0);
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($club['owner_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($club['email_contact'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars((string) ($club['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge bg-<?= $badge ?>">
                                            <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $validation)), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <?php if ($actif): ?>
                                            <span class="badge bg-success ms-1">Actif</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary ms-1">Inactif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                                            <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>">Modifier</a>
                                            <form method="post" action="<?= $this->url('/evenements/deleteClub/' . $clubId) ?>" class="d-inline">
                                                <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Supprimer ce club ?');">Supprimer</button>
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
</div>
