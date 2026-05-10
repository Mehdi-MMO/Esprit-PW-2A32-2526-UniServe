<?php
$event = $event ?? [];
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

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Inscriptions</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($event['titre'] ?? 'Événement'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Suivi des participants et gestion de présence.</p>
    </div>
    <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-surface-muted px-3 py-2 mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
    <?php $status = (string) ($event['statut'] ?? 'planifie'); ?>
    <div class="small text-muted">Statut de l’événement : <span class="badge bg-<?= $statusClass($status) ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span></div>
    <div class="small text-muted">Total inscrits: <strong><?= count($inscriptions) ?></strong></div>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Participant</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Matricule</th>
                        <th>Statut</th>
                        <th class="text-end">Presence</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($inscriptions)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucune inscription enregistree.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inscriptions as $inscription): ?>
                            <?php
                            $userId = (int) ($inscription['utilisateur_id'] ?? 0);
                            $statutInscription = (string) ($inscription['statut'] ?? 'inscrit');
                            ?>
                            <tr>
                                <td class="fw-semibold">
                                    <?= htmlspecialchars((string) ($inscription['prenom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                    <?= htmlspecialchars((string) ($inscription['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td><?= htmlspecialchars((string) ($inscription['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ucfirst((string) ($inscription['role'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($inscription['matricule'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ucfirst($statutInscription), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <?php if ($statutInscription === 'present'): ?>
                                        <span class="badge bg-success">Present</span>
                                    <?php else: ?>
                                        <form method="post" action="<?= $this->url('/evenements/checkIn/' . (int) ($event['id'] ?? 0) . '/' . $userId) ?>" class="d-inline">
                                            <button class="btn btn-outline-success btn-sm" type="submit">Check-in</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
