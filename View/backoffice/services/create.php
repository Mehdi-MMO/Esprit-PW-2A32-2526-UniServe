<?php
$old = $old ?? ['nom' => '', 'description' => '', 'actif' => '1'];
$error = $error ?? null;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0"><?= htmlspecialchars((string) ($title ?? 'Nouveau service'), ENT_QUOTES, 'UTF-8') ?></h1>
            <a href="<?= $this->url('/services') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
        </div>

        <?php if ($error !== null): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="us-section-card card-body">
            <form method="post" action="<?= $this->url('/services/create') ?>" id="formService">
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom du service</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars((string) $old['nom'], ENT_QUOTES, 'UTF-8') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars((string) $old['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    <div class="form-text">Au moins 10 caractères.</div>
                </div>
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="actif" name="actif" value="1" <?= ($old['actif'] ?? '') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="actif">Service actif (visible aux étudiants)</label>
                </div>
                <div class="d-flex gap-2 justify-content-end">
                    <a href="<?= $this->url('/services') ?>" class="btn btn-light">Annuler</a>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>
</div>
