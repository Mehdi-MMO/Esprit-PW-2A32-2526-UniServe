<?php
$clubs = $clubs ?? [];
$pendingClubs = $pendingClubs ?? [];
$q = (string) ($q ?? '');
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Gestion des clubs'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Creer et organiser les clubs de l universite.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-outline-secondary btn-sm">Evenements</a>
        <a href="<?= $this->url('/evenements/createClubForm') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Nouveau club</a>
    </div>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <form method="get" action="<?= $this->url('/evenements/manageClubs') ?>" class="row g-2 align-items-end mb-3 us-filter-shell">
            <div class="col-lg-8">
                <label class="form-label text-muted small mb-1" for="q">Recherche</label>
                <input class="form-control" id="q" name="q" placeholder="Nom ou email contact..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-outline-secondary w-100">Reinitialiser</a>
            </div>
        </form>

        <h2 class="h6 mb-3">Demandes de clubs en attente</h2>
        <div class="table-responsive us-table-wrap mb-4">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Proprietaire</th>
                        <th>Email contact</th>
                        <th>Description</th>
                        <th class="text-end">Moderation</th>
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
                                    <div class="us-action-group">
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

        <h2 class="h6 mb-3">Clubs existants</h2>
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Proprietaire</th>
                        <th>Email contact</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clubs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun club trouve.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clubs as $club): ?>
                            <?php
                            $clubId = (int) ($club['id'] ?? 0);
                            $validation = (string) ($club['statut_validation'] ?? 'en_attente');
                            $badge = $validation === 'approuve' ? 'success' : ($validation === 'rejete' ? 'danger' : 'secondary');
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
                                </td>
                                <td class="text-end">
                                    <div class="us-action-group">
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
