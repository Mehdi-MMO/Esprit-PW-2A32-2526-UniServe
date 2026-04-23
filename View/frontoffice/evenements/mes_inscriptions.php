<?php
$inscriptions = $inscriptions ?? [];
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
        <div class="us-kicker mb-1">Espace personnel</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Mes inscriptions'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Retrouvez tous les evenements auxquels vous etes inscrit.</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements') ?>">Voir les evenements</a>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Evenement</th>
                        <th>Club</th>
                        <th>Date debut</th>
                        <th>Statut evenement</th>
                        <th>Votre presence</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inscriptions)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune inscription pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inscriptions as $inscription): ?>
                            <?php
                            $eventStatus = (string) ($inscription['evenement_statut'] ?? 'planifie');
                            $eventId = (int) ($inscription['id'] ?? 0);
                            ?>
                            <tr>
                                <td>
                                    <a class="text-decoration-none fw-semibold" href="<?= $this->url('/evenements/show/' . $eventId) ?>">
                                        <?= htmlspecialchars((string) ($inscription['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars((string) ($inscription['club_nom'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($inscription['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-<?= $statusClass($eventStatus) ?>"><?= htmlspecialchars(ucfirst($eventStatus), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars((string) ucfirst((string) ($inscription['inscription_statut'] ?? 'inscrit')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <form method="post" action="<?= $this->url('/evenements/unregister/' . $eventId) ?>" class="d-inline">
                                        <button class="btn btn-outline-danger btn-sm" type="submit">Annuler</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
