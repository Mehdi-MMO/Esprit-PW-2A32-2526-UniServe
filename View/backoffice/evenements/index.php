<?php
// Include helpers for rendering UI components
require_once __DIR__ . '/../../shared/helpers.php';

$events = $events ?? [];
$pendingEvents = $pendingEvents ?? [];
$stats = $stats ?? [];
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

<?php
$clubsEventsSubnavActive = 'events';
require __DIR__ . '/../shared/clubs_events_subnav.php';
?>

<div class="back-page-header mb-4">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-calendar-event-fill"></i></div>
        <div>
            <div class="back-page-title"><?= htmlspecialchars((string) ($title ?? 'Gestion des événements'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-page-sub">Pilotez les événements, inscriptions et présences.</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/evenements/createForm') ?>" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-plus-lg me-1"></i>Nouveau</a>
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
    <div class="back-stats-row mb-4">
        <span class="back-stat-pill" style="background:#e0f2fe;color:#0369a1;border-color:#bae6fd;">
            <i class="bi bi-calendar-event-fill"></i><?= $stats['total'] ?? 0 ?> Événements total
        </span>
        <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
            <i class="bi bi-play-circle-fill"></i><?= $stats['active'] ?? 0 ?> Événements actifs
        </span>
        <span class="back-stat-pill" style="background:#e0e7ff;color:#4338ca;border-color:#c7d2fe;">
            <i class="bi bi-people-fill"></i><?= $stats['total_registrations'] ?? 0 ?> Inscriptions total
        </span>
        <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
            <i class="bi bi-clock-fill"></i><?= $stats['upcoming'] ?? 0 ?> À venir
        </span>
    </div>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <!-- Search and Filter Form -->
<form method="get" action="<?= $this->url('/evenements/manage') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="text" class="form-control" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Titre, club, lieu, statut..." style="max-width:300px; border-radius:10px; font-size:.9rem; font-weight:600;" autocomplete="off">
    <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search me-1"></i> Filtrer</button>
    <?php if ($q !== ''): ?>
        <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
    <?php endif; ?>
</form>

        <!-- Pending Events Section -->
        <div class="mb-5">
            <h5 class="mb-3 d-flex align-items-center gap-2" style="color:var(--brand);"><i class="bi bi-exclamation-circle text-warning"></i>Demandes en attente</h5>
            <?php if (empty($pendingEvents)): ?>
                <div class="text-muted small">Aucune demande en attente.</div>
            <?php else: ?>
                <div class="back-table-wrap">
                    <table class="back-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Demandeur</th>
                                <th>Propriétaire club</th>
                                <th>Club</th>
                                <th>Date début</th>
                                <th class="text-end">Modération</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingEvents as $event): ?>
                                <?php $eventId = (int) ($event['id'] ?? 0); ?>
                                <tr>
                                    <td class="fw-semibold" style="color:var(--brand);"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['createur_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['owner_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['club_nom'] ?? 'Général'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end align-items-center">
                                            <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="<?= $this->url('/evenements/editForm/' . $eventId) ?>" style="border-radius:6px; width:32px; height:32px;" title="Modifier">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </a>
                                            <form method="post" action="<?= $this->url('/evenements/approveEvent/' . $eventId) ?>" class="m-0 d-inline">
                                                <button class="btn btn-success btn-sm d-flex align-items-center justify-content-center" type="submit" style="border-radius:6px; width:32px; height:32px;" title="Approuver">
                                                    <i class="bi bi-check-lg" aria-hidden="true"></i>
                                                </button>
                                            </form>
                                            <form method="post" action="<?= $this->url('/evenements/rejectEvent/' . $eventId) ?>" class="m-0 d-inline">
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

        <!-- Active Events Section -->
        <div>
            <h5 class="mb-3 d-flex align-items-center gap-2" style="color:var(--brand);"><i class="bi bi-calendar-event text-primary"></i>Événements traités</h5>
            <div class="back-table-wrap">
                <table class="back-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Demandeur</th>
                            <th>Club</th>
                            <th>Lieu</th>
                            <th>Date début</th>
                            <th>Inscriptions</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($events)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Aucun événement trouvé.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($events as $event): ?>
                                <?php
                                $eventId = (int) ($event['id'] ?? 0);
                                $status = (string) ($event['statut'] ?? 'planifie');
                                $registrations = (int) ($event['inscriptions_count'] ?? 0);
                                $capacite = isset($event['capacite']) ? (int) $event['capacite'] : 0;
                                
                                $sBg = '#f3f4f6'; $sColor = '#4b5563'; $sBorder = '#e5e7eb';
                                if ($status === 'ouvert') { $sBg = '#dcfce7'; $sColor = '#166534'; $sBorder = '#86efac'; }
                                if ($status === 'complet') { $sBg = '#fef9c3'; $sColor = '#854d0e'; $sBorder = '#fde047'; }
                                if ($status === 'annule') { $sBg = '#fee2e2'; $sColor = '#991b1b'; $sBorder = '#fca5a5'; }
                                if ($status === 'termine') { $sBg = '#e5e7eb'; $sColor = '#111827'; $sBorder = '#d1d5db'; }
                                ?>
                                <tr>
                                    <td class="fw-semibold" style="color:var(--brand);"><?= htmlspecialchars((string) ($event['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['createur_nom'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['club_nom'] ?? 'Général'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['lieu'] ?? 'À définir'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;border-radius:6px;padding:5px 8px;"><?= $registrations ?></span>
                                        <?php if ($capacite > 0): ?>
                                            / <span class="text-muted"><?= $capacite ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge" style="background:<?= $sBg ?>;color:<?= $sColor ?>;border:1px solid <?= $sBorder ?>;border-radius:6px;padding:5px 8px;"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end align-items-center">
                                            <a class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" href="<?= $this->url('/evenements/editForm/' . $eventId) ?>" style="border-radius:6px; width:32px; height:32px;" title="Modifier">
                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                            </a>
                                            <a class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center" href="<?= $this->url('/evenements/inscriptions/' . $eventId) ?>" style="border-radius:6px; width:32px; height:32px;" title="Inscriptions">
                                                <i class="bi bi-people-fill" aria-hidden="true"></i>
                                            </a>
                                            <form method="post" action="<?= $this->url('/evenements/delete/' . $eventId) ?>" class="m-0 d-inline" onsubmit="return confirm('Supprimer cet événement ?');">
                                                <button class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" type="submit" style="border-radius:6px; width:32px; height:32px;" title="Supprimer">
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
    </div>
