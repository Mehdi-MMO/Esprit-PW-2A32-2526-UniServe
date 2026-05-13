<?php
$events = $events ?? [];
$myEvents = $myEvents ?? [];
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
$role = (string) ($_SESSION['user']['role'] ?? '');
$canSubmitRequest = in_array($role, ['etudiant', 'enseignant'], true);

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
        <div class="us-kicker mb-1">Vie universitaire</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Événements à venir'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Découvrez les prochains événements et inscrivez-vous en ligne.</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canSubmitRequest): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/evenements/createEventRequestForm') ?>">Soumettre un événement</a>
        <?php endif; ?>
        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/mesInscriptions') ?>">Mes inscriptions</a>
        <a class="btn btn-primary btn-sm" href="<?= $this->url('/evenements/clubs') ?>">Voir les clubs</a>
    </div>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($canSubmitRequest): ?>
    <div class="us-section-card mb-3">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 mb-3">Mes événements soumis</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Club</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myEvents)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-3">Aucun événement soumis.</td></tr>
                        <?php else: ?>
                            <?php foreach ($myEvents as $event): ?>
                                <?php
                                $eventId = (int) ($event['id'] ?? 0);
                                $status = (string) ($event['statut'] ?? 'planifie');
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['club_nom'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-<?= $statusClass($status) ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-end">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/editForm/' . $eventId) ?>">Modifier</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <?php if (empty($events)): ?>
        <div class="col-12">
            <div class="us-card p-4 us-empty-state">
                <div class="fw-semibold mb-1">Aucun événement à venir</div>
                <div class="text-muted">Revenez prochainement pour consulter les nouvelles activités.</div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($events as $event): ?>
            <?php
            $eventId = (int) ($event['id'] ?? 0);
            $status = (string) ($event['statut'] ?? 'planifie');
            $registrations = (int) ($event['inscriptions_count'] ?? 0);
            $capacite = isset($event['capacite']) ? (int) $event['capacite'] : 0;
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                            <h2 class="h6 mb-0"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                            <span class="badge bg-<?= $statusClass($status) ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p class="text-muted small mb-2">
                            Club: <?= htmlspecialchars((string) ($event['club_nom'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <ul class="list-unstyled small text-muted mb-3">
                            <li><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars((string) ($event['lieu'] ?? 'A definir'), ENT_QUOTES, 'UTF-8') ?></li>
                            <li><i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
                            <li>
                                <i class="bi bi-people me-1"></i>
                                <?= $registrations ?> inscrit(s)
                                <?php if ($capacite > 0): ?>
                                    / <?= $capacite ?>
                                <?php endif; ?>
                            </li>
                        </ul>
                        <?php
                        $mapRaw = trim((string) ($event['lieu'] ?? ''));
                        $mapOk = $mapRaw !== '' && !preg_match('/^(à|a)\s*d[ée]finir\.?$/iu', $mapRaw);
                        if ($mapOk) {
                            $map_address = $mapRaw;
                            $map_actions_class = 'mb-2';
                            require __DIR__ . '/../../shared/event_map_actions.php';
                        }
                        ?>
                        <div class="mt-auto">
                            <a class="btn btn-outline-primary w-100" href="<?= $this->url('/evenements/show/' . $eventId) ?>">Voir le détail</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
