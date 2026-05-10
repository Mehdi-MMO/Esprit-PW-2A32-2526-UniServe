<?php
$bureaux = $bureaux ?? [];
$rdv = $rdv ?? [];
$error = $error ?? null;

$bid = (int) ($rdv['bureau_id'] ?? 0);
$motif = (string) ($rdv['motif'] ?? '');
$rid = (int) ($rdv['id'] ?? 0);

$fmtLocal = static function (?string $sqlDt): string {
    if ($sqlDt === null || $sqlDt === '') {
        return '';
    }
    $t = strtotime($sqlDt);

    return $t !== false ? date('Y-m-d\TH:i', $t) : '';
};
$d1 = $fmtLocal((string) ($rdv['date_debut'] ?? ''));
$d2 = $fmtLocal((string) ($rdv['date_fin'] ?? ''));
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Modification</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Réservation encore au statut <strong>réservé</strong> uniquement.</p>
    </div>
    <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-secondary">Retour</a>
</div>

<?php if ($error !== null && $error !== ''): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="us-section-card" style="max-width: 720px;">
    <div class="card-body p-4">
        <form method="post" action="<?= $this->url('/rendezvous/update/' . $rid) ?>">
            <div class="mb-3">
                <label for="bureau_id" class="form-label">Bureau <span class="text-danger">*</span></label>
                <select class="form-select" id="bureau_id" name="bureau_id" required>
                    <option value="">— Choisir —</option>
                    <?php foreach ($bureaux as $b): ?>
                        <?php $optId = (int) ($b['id'] ?? 0); ?>
                        <option value="<?= $optId ?>" <?= $optId === $bid ? 'selected' : '' ?>>
                            <?= htmlspecialchars((string) ($b['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="motif" class="form-label">Motif</label>
                <input type="text" class="form-control" id="motif" name="motif" maxlength="255" value="<?= htmlspecialchars($motif, ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="date_debut" class="form-label">Début <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required value="<?= htmlspecialchars($d1, ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-6">
                    <label for="date_fin" class="form-label">Fin <span class="text-danger">*</span></label>
                    <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required value="<?= htmlspecialchars($d2, ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
