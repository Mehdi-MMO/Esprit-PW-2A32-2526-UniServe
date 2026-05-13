<?php
$categories = $categories ?? [];
$demande = $demande ?? [];
$error = $error ?? null;
$pieces = $pieces ?? [];

$cid = (int) ($demande['categorie_id'] ?? 0);
$titre = (string) ($demande['titre'] ?? '');
$description = (string) ($demande['description'] ?? '');
$sid = (int) ($demande['id'] ?? 0);
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Modifier</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Uniquement tant que la demande est <strong>en attente</strong>.</p>
    </div>
    <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-secondary">Retour</a>
</div>

<?php if ($error !== null && $error !== ''): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card" style="max-width: 720px;">
    <div class="card-body p-4">
        <form method="post" action="<?= $this->url('/demandes/update/' . $sid) ?>" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="categorie_id" class="form-label">Catégorie <span class="text-danger">*</span></label>
                <select class="form-select" id="categorie_id" name="categorie_id" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($categories as $c): ?>
                        <?php $optId = (int) ($c['id'] ?? 0); ?>
                        <option value="<?= $optId ?>" <?= $optId === $cid ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($c['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="titre" class="form-label">Titre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="titre" name="titre" required maxlength="150" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?></textarea>
                <?php if (!empty($demande_ai_description_enabled)) {
                    include __DIR__ . '/../../shared/demande_description_ai.inc.php';
                } ?>
            </div>
            <?php if ($pieces !== []): ?>
                <div class="mb-3">
                    <div class="form-label">Fichiers déjà joints</div>
                    <ul class="list-group list-group-flush border rounded">
                        <?php foreach ($pieces as $pj): ?>
                            <?php $pid = (int) ($pj['id'] ?? 0); ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <a href="<?= $this->url('/demandes/downloadPiece/' . $pid) ?>"><?= htmlspecialchars((string) ($pj['nom_fichier'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a>
                                <form method="post" action="<?= $this->url('/demandes/deletePiece/' . $pid) ?>" class="mb-0" onsubmit="return confirm('Supprimer cette pièce ?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Retirer</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="pieces" class="form-label">Ajouter des pièces jointes</label>
                <input type="file" class="form-control" id="pieces" name="pieces[]" multiple accept=".pdf,.doc,.docx,image/jpeg,image/png">
                <div class="form-text">PDF, Word ou images, 5 Mo max par fichier (12 fichiers max au total).</div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
