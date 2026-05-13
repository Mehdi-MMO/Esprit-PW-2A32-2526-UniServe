<?php
$categories = $categories ?? [];
$old = $old ?? ['categorie_id' => '', 'titre' => '', 'description' => ''];
$error = $error ?? null;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Nouvelle demande</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Choisissez une catégorie et décrivez votre besoin.</p>
    </div>
    <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-secondary">Retour</a>
</div>

<?php if ($error !== null && $error !== ''): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card" style="max-width: 720px;">
    <div class="card-body p-4">
        <form method="post" action="<?= $this->url('/demandes/create') ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="categorie_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                <select class="form-select" id="categorie_id" name="categorie_id" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($categories as $c): ?>
                        <?php $cid = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $cid ?>" <?= (int) ($old['categorie_id'] ?? 0) === $cid ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($c['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="titre" name="titre" required maxlength="150" value="<?= htmlspecialchars((string) $old['titre'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="5" required placeholder="Décrivez votre situation et ce dont vous avez besoin."><?= htmlspecialchars((string) $old['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($demande_ai_description_enabled)) {
                    include __DIR__ . '/../../shared/demande_description_ai.inc.php';
                } ?>
            </div>
            <div class="mb-3">
                <label for="pieces" class="form-label">Pièces jointes <span class="text-muted fw-normal">(optionnel)</span></label>
                <input type="file" class="form-control" id="pieces" name="pieces[]" multiple accept=".pdf,.doc,.docx,image/jpeg,image/png">
                <div class="form-text">PDF, Word ou images (JPEG, PNG), jusqu’à 5 Mo par fichier, 12 fichiers max au total par demande.</div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Envoyer la demande</button>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
