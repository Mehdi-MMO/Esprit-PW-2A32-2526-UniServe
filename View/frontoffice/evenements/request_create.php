<?php
function e(string $v): string
{
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function dt(string $v): string
{
    $trimmed = trim($v);
    if ($trimmed === '') {
        return '';
    }

    return str_replace(' ', 'T', substr($trimmed, 0, 16));
}

$old = $old ?? [];
$clubs = $clubs ?? [];
$error = (string) ($error ?? '');
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Demande evenement</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Soumettre un evenement'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Votre evenement sera examine par l administration.</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/evenements') ?>">Retour</a>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $this->url('/evenements/createEventRequest') ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label text-muted small" for="titre">Titre *</label>
                    <input class="form-control" id="titre" name="titre" required value="<?= e((string) ($old['titre'] ?? '')) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small" for="club_id">Club (optionnel)</label>
                    <?php $selectedClub = (int) ($old['club_id'] ?? 0); ?>
                    <select class="form-select" id="club_id" name="club_id">
                        <option value="">General</option>
                        <?php foreach ($clubs as $club): ?>
                            <?php $clubId = (int) ($club['id'] ?? 0); ?>
                            <option value="<?= $clubId ?>" <?= $clubId === $selectedClub ? 'selected' : '' ?>>
                                <?= e((string) ($club['nom'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= e((string) ($old['description'] ?? '')) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="lieu">Lieu</label>
                    <div class="input-group">
                        <input class="form-control" id="lieu" name="lieu" value="<?= e((string) ($old['lieu'] ?? '')) ?>">
                        <button
                            type="button"
                            class="btn btn-outline-primary"
                            data-map-picker-btn="1"
                            data-map-target-input="lieu"
                        >
                            Choisir sur carte
                        </button>
                    </div>
                    <div class="form-text">Cliquez sur la carte pour pre-remplir le lieu.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted small" for="date_debut">Date debut *</label>
                    <input class="form-control" id="date_debut" name="date_debut" type="datetime-local" required value="<?= e(dt((string) ($old['date_debut'] ?? ''))) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted small" for="date_fin">Date fin *</label>
                    <input class="form-control" id="date_fin" name="date_fin" type="datetime-local" required value="<?= e(dt((string) ($old['date_fin'] ?? ''))) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="capacite">Capacite</label>
                    <input class="form-control" id="capacite" name="capacite" type="number" min="1" value="<?= e((string) ($old['capacite'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="prix_ticket">Prix ticket</label>
                    <div class="input-group">
                        <span class="input-group-text">USD</span>
                        <input class="form-control" id="prix_ticket" name="prix_ticket" type="number" min="0" step="0.01" value="<?= e((string) ($old['prix_ticket'] ?? '0')) ?>">
                    </div>
                    <div class="form-text">Devise principale: USD. Conversion TND affichee dans les pages evenement.</div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-2 border-top">
                <button class="btn btn-primary px-4" type="submit">Envoyer la demande</button>
            </div>
        </form>
    </div>
</div>
