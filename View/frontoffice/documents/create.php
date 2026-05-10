<?php
$types = $types ?? [];
$old = $old ?? ['type_document_id' => ''];
$error = $error ?? null;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Scolarité</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Nouvelle demande'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Choisissez le type de document officiel à obtenir.</p>
    </div>
    <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== null && $error !== ''): ?>
            <div class="alert alert-danger py-2 small mb-3"><?= htmlspecialchars((string) $error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if (empty($types)): ?>
            <p class="text-muted mb-0">Aucun type de document n’est disponible pour le moment.</p>
        <?php else: ?>
            <form method="post" action="<?= $this->url('/documents/create') ?>" class="mx-auto" style="max-width: 520px;">
                <div class="mb-3">
                    <label class="form-label" for="type_document_id">Type de document *</label>
                    <select class="form-select" id="type_document_id" name="type_document_id" required>
                        <option value="">— Sélectionner —</option>
                        <?php foreach ($types as $t): ?>
                            <?php $tid = (int) ($t['id'] ?? 0); ?>
                            <option value="<?= $tid ?>" <?= (string) ($old['type_document_id'] ?? '') === (string) $tid ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string) ($t['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Envoyer la demande</button>
            </form>
        <?php endif; ?>
    </div>
</div>
