<?php
$club = $club ?? [];
$events = $events ?? [];
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

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 us-page-header">
    <div>
        <div class="us-kicker mb-1">Club</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($club['nom'] ?? 'Club'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Informations du club et événements associés.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/evenements/clubs') ?>">Retour aux clubs</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements') ?>">Voir les événements</a>
    </div>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card mb-3">
    <div class="card-body p-3 p-md-4">
        <p class="mb-2"><?= nl2br(htmlspecialchars((string) ($club['description'] ?? 'Aucune description disponible.'), ENT_QUOTES, 'UTF-8')) ?></p>
        <div class="small text-muted">Email contact: <?= htmlspecialchars((string) ($club['email_contact'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <h2 class="h5 mb-3">Événements du club</h2>

        <div class="row g-3">
            <?php if (empty($events)): ?>
                <div class="col-12">
                    <div class="text-muted py-3">Ce club n’a pas encore d’événement public à afficher.</div>
                </div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <?php
                    $eventId = (int) ($event['id'] ?? 0);
                    $status = (string) ($event['statut'] ?? 'planifie');
                    ?>
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h3 class="h6 mb-0"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <span class="badge bg-<?= $statusClass($status) ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-calendar-event me-1"></i><?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (trim((string) ($event['lieu'] ?? '')) !== ''): ?>
                                        <br><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars((string) ($event['lieu'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    <?php endif; ?>
                                </p>
                                <?php
                                $mapRaw = trim((string) ($event['lieu'] ?? ''));
                                $mapOk = $mapRaw !== '' && !preg_match('/^(à|a)\s*d[ée]finir\.?$/iu', $mapRaw);
                                if ($mapOk) {
                                    $map_address = $mapRaw;
                                    $map_actions_class = 'mb-2';
                                    require __DIR__ . '/../../shared/event_map_actions.php';
                                }
                                ?>
                                <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/show/' . $eventId) ?>">Voir l’événement</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
