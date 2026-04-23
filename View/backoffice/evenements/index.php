<?php
$events = $events ?? [];
$pendingEvents = $pendingEvents ?? [];
$q = (string) ($q ?? '');
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');

$statusClass = static function (string $status): string {
    return match ($status) {
        'planifie' => 'secondary',
        'ouvert' => 'success',
        'complet' => 'warning',
        'termine' => 'dark',
        'annule' => 'danger',
        default => 'secondary',
    };
};
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Gestion des evenements'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Pilotez les evenements, inscriptions et presences.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-outline-primary btn-sm">Gerer les clubs</a>
        <a href="<?= $this->url('/evenements/createForm') ?>" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Nouveau</a>
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
        <form method="get" action="<?= $this->url('/evenements/manage') ?>" class="row g-2 align-items-end mb-3 us-filter-shell">
            <div class="col-lg-8">
                <label class="form-label text-muted small mb-1" for="q">Recherche</label>
                <input class="form-control" id="q" name="q" placeholder="Titre, club, lieu, statut..." value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-outline-secondary w-100">Reinitialiser</a>
            </div>
        </form>

        <h2 class="h6 mb-3">Demandes en attente</h2>
        <div class="table-responsive us-table-wrap mb-4">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Demandeur</th>
                        <th>Proprietaire club</th>
                        <th>Club</th>
                        <th>Date debut</th>
                        <th class="text-end">Moderation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendingEvents)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune demande en attente.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pendingEvents as $event): ?>
                            <?php $eventId = (int) ($event['id'] ?? 0); ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['createur_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['owner_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['club_nom'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <div class="us-action-group">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/editForm/' . $eventId) ?>">Modifier</a>
                                        <form method="post" action="<?= $this->url('/evenements/approveEvent/' . $eventId) ?>" class="d-inline">
                                            <button class="btn btn-outline-success btn-sm" type="submit">Approuver</button>
                                        </form>
                                        <form method="post" action="<?= $this->url('/evenements/rejectEvent/' . $eventId) ?>" class="d-inline">
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

        <h2 class="h6 mb-3">Evenements traites</h2>
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Demandeur</th>
                        <th>Club</th>
                        <th>Lieu</th>
                        <th>Date debut</th>
                        <th>Capacite</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($events)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Aucun evenement trouve.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($events as $event): ?>
                            <?php
                            $eventId = (int) ($event['id'] ?? 0);
                            $status = (string) ($event['statut'] ?? 'planifie');
                            $registrations = (int) ($event['inscriptions_count'] ?? 0);
                            $capacite = isset($event['capacite']) ? (int) $event['capacite'] : 0;
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['createur_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['club_nom'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['lieu'] ?? 'A definir'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?= $registrations ?>
                                    <?php if ($capacite > 0): ?>
                                        / <?= $capacite ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-<?= $statusClass($status) ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="text-end">
                                    <div class="us-action-group">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/editForm/' . $eventId) ?>">Modifier</a>
                                        <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/evenements/inscriptions/' . $eventId) ?>">Inscriptions</a>
                                        <form method="post" action="<?= $this->url('/evenements/delete/' . $eventId) ?>" class="d-inline">
                                            <button class="btn btn-outline-danger btn-sm" type="submit" onclick="return confirm('Supprimer cet evenement ?');">Supprimer</button>
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
