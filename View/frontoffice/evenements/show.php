<?php
$event = $event ?? [];
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
$registrations = (int) ($registrations ?? 0);
$isRegistered = (bool) ($isRegistered ?? false);

$status = (string) ($event['statut'] ?? 'planifie');
$statusClass = match ($status) {
    'planifie' => 'secondary',
    'ouvert' => 'success',
    'complet' => 'warning',
    'termine' => 'dark',
    'annule' => 'danger',
    default => 'secondary',
};
$eventId = (int) ($event['id'] ?? 0);
$capacite = isset($event['capacite']) ? (int) $event['capacite'] : 0;
$canRegister = !$isRegistered && !in_array($status, ['annule', 'termine', 'complet'], true) && ($capacite <= 0 || $registrations < $capacite);
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 us-page-header">
    <div>
        <div class="us-kicker mb-1">Événements</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($event['titre'] ?? 'Détail événement'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Consultez les informations et gérez votre inscription.</p>
    </div>
    <div class="d-flex flex-wrap gap-2 justify-content-end">
        <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/evenements') ?>">Retour aux événements</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/clubs') ?>">Voir les clubs</a>
    </div>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="us-section-card">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <span class="badge bg-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($status), ENT_QUOTES, 'UTF-8') ?></span>
                    <span class="text-muted small">Club: <?= htmlspecialchars((string) ($event['club_nom'] ?? 'General'), ENT_QUOTES, 'UTF-8') ?></span>
                </div>

                <p class="mb-4"><?= nl2br(htmlspecialchars((string) ($event['description'] ?? 'Aucune description disponible.'), ENT_QUOTES, 'UTF-8')) ?></p>

                <div class="row g-2 text-muted small">
                    <div class="col-md-6"><strong>Lieu:</strong> <?= htmlspecialchars((string) ($event['lieu'] ?? 'A definir'), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="col-md-6"><strong>Debut:</strong> <?= htmlspecialchars((string) ($event['date_debut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="col-md-6"><strong>Fin:</strong> <?= htmlspecialchars((string) ($event['date_fin'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="col-md-6">
                        <strong>Inscriptions:</strong> <?= $registrations ?>
                        <?php if ($capacite > 0): ?>
                            / <?= $capacite ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="us-section-card">
            <div class="card-body p-3 p-md-4">
                <h2 class="h6 mb-3">Action rapide</h2>

                <?php if ($isRegistered): ?>
                    <form method="post" action="<?= $this->url('/evenements/unregister/' . $eventId) ?>">
                        <button type="submit" class="btn btn-outline-danger w-100 py-2">Annuler inscription</button>
                    </form>
                <?php elseif ($canRegister): ?>
                    <form method="post" action="<?= $this->url('/evenements/register/' . $eventId) ?>">
                        <button type="submit" class="btn btn-success w-100 py-2">S inscrire</button>
                    </form>
                <?php else: ?>
                    <button type="button" class="btn btn-secondary w-100 py-2" disabled>Inscriptions indisponibles</button>
                <?php endif; ?>

                <a class="btn btn-link w-100 mt-2" href="<?= $this->url('/evenements/mesInscriptions') ?>">Voir mes inscriptions</a>
            </div>
        </div>
    </div>
</div>
