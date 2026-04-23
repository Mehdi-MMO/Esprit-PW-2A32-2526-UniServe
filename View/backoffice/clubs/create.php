<?php
function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

$old = $old ?? [];
$error = (string) ($error ?? '');
$isActif = (int) ($old['actif'] ?? 1) === 1;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Gestion des clubs</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Creer un club'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Ajouter un nouveau club universitaire.</p>
    </div>
    <a href="<?= $this->url('/evenements/manageClubs') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $this->url('/evenements/createClub') ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label text-muted small" for="nom">Nom *</label>
                    <input class="form-control" id="nom" name="nom" required value="<?= e((string) ($old['nom'] ?? '')) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small" for="email_contact">Email contact</label>
                    <input class="form-control" id="email_contact" name="email_contact" type="email" value="<?= e((string) ($old['email_contact'] ?? '')) ?>">
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="5"><?= e((string) ($old['description'] ?? '')) ?></textarea>
                </div>

                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" id="actif" name="actif" type="checkbox" value="1" <?= $isActif ? 'checked' : '' ?>>
                        <label class="form-check-label" for="actif">Club actif</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-2 border-top">
                <button class="btn btn-primary px-4" type="submit">Creer le club</button>
            </div>
        </form>
    </div>
</div>
