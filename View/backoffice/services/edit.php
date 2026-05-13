<?php
$service = $service ?? [];
$error = $error ?? null;
$sid = (int) ($service['id'] ?? 0);
$actifChecked = (int) ($service['actif'] ?? 0) === 1;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><?= htmlspecialchars((string) ($title ?? 'Modifier'), ENT_QUOTES, 'UTF-8') ?></h1>
            <a href="<?= $this->url('/services') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="us-section-card card-body">
            <form method="post" action="<?= $this->url('/services/update/' . $sid) ?>" id="formService">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom du service</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars((string) ($service['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars((string) ($service['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="actif" name="actif" value="1" <?= $actifChecked ? 'checked' : '' ?>>
                    <label class="form-check-label" for="actif">Service actif</label>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= $this->url('/services') ?>" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
