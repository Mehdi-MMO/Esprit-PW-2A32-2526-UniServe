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
        <p class="text-muted mb-0">Retrouvez tous les événements auxquels vous êtes inscrit.</p>
    </div>
    <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements') ?>">Voir les événements</a>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Événement</th>
                        <th>Club</th>
                        <th>Date de début</th>
                        <th>Statut (événement)</th>
                        <th>Votre présence</th>
                        <th class="text-end">Actions</th>
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
                            $detailUrl = $this->url('/evenements/show/' . $eventId);
                            $canUnregister = !in_array($eventStatus, ['termine', 'annule'], true);
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string) ($inscription['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($inscription['club_nom'] ?? 'Général'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($inscription['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge bg-<?= $statusClass($eventStatus) ?>"><?= htmlspecialchars(ucfirst($eventStatus), ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars((string) ucfirst((string) ($inscription['inscription_statut'] ?? 'inscrit')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <?php
                                        $mapRaw = trim((string) ($inscription['lieu'] ?? ''));
                                        $mapOk = $mapRaw !== '' && !preg_match('/^(à|a)\s*d[ée]finir\.?$/iu', $mapRaw);
                                        if ($mapOk) {
                                            $map_address = $mapRaw;
                                            $map_actions_class = 'justify-content-end';
                                            require __DIR__ . '/../../shared/event_map_actions.php';
                                        }
                                        ?>
                                        <div class="d-flex gap-2 justify-content-end flex-wrap">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($detailUrl, ENT_QUOTES, 'UTF-8') ?>">Modifier</a>
                                        <?php if ($canUnregister): ?>
                                            <form method="post" action="<?= $this->url('/evenements/unregister/' . $eventId) ?>" class="d-inline" onsubmit="return confirm('Retirer votre inscription à cet événement ?');">
                                                <button class="btn btn-outline-danger btn-sm" type="submit">Supprimer</button>
                                            </form>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Inscription figée : événement terminé ou annulé.">Supprimer</button>
                                        <?php endif; ?>
                                    </div>
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
