<?php
require_once __DIR__ . '/../../shared/helpers.php';

$bureau = $bureau ?? [];
$error = $error ?? null;
$bid = (int) ($bureau['id'] ?? 0);
$actifChecked = (int) ($bureau['actif'] ?? 0) === 1;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Bureaux</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Modifier le bureau'), ENT_QUOTES, 'UTF-8') ?></h1>
    </div>
    <a href="<?= $this->url('/rendezvous?tab=bureaux') ?>" class="btn btn-outline-secondary">Retour à la liste</a>
</div>

<?php if ($error !== null && $error !== ''): ?>
    <?= renderErrorAlert($error) ?>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-4">
        <form method="post" action="<?= $this->url('/bureaux/update/' . $bid) ?>" class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="nom">Nom du bureau</label>
                <input type="text" class="form-control" id="nom" name="nom" required maxlength="120"
                       value="<?= htmlspecialchars((string) ($bureau['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label" for="type_service">Type de service (identifiant)</label>
                <input type="text" class="form-control" id="type_service" name="type_service" required maxlength="120"
                       value="<?= htmlspecialchars((string) ($bureau['type_service'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <label class="form-label" for="localisation">Localisation</label>
                <input type="text" class="form-control" id="localisation" name="localisation" maxlength="255"
                       value="<?= htmlspecialchars((string) ($bureau['localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="col-12">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="actif" value="1" id="actif" <?= $actifChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="actif">Bureau actif (visible pour les réservations)</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Mettre à jour</button>
            </div>
        </form>
    </div>
</div>
