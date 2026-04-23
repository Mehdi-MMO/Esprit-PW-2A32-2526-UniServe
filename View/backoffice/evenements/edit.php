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

$event = $event ?? [];
$clubs = $clubs ?? [];
$error = (string) ($error ?? '');
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Gestion des evenements</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Modifier un evenement'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Mettre a jour les details et le statut de l evenement.</p>
    </div>
    <a href="<?= $this->url('/evenements/manage') ?>" class="btn btn-outline-secondary btn-sm">Retour</a>
</div>

<div class="us-section-card">
    <div class="card-body p-3 p-md-4">
        <?php if ($error !== ''): ?>
            <div class="alert alert-danger py-2 small" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $this->url('/evenements/edit/' . (int) ($event['id'] ?? 0)) ?>">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label text-muted small" for="titre">Titre *</label>
                    <input class="form-control" id="titre" name="titre" required value="<?= e((string) ($event['titre'] ?? '')) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label text-muted small" for="club_id">Club</label>
                    <?php $selectedClub = (int) ($event['club_id'] ?? 0); ?>
                    <select class="form-select" id="club_id" name="club_id">
                        <option value="">General</option>
                        <?php foreach ($clubs as $club): ?>
                            <?php $clubId = (int) ($club['id'] ?? 0); ?>
                            <option value="<?= $clubId ?>" <?= $selectedClub === $clubId ? 'selected' : '' ?>>
                                <?= e((string) ($club['nom'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label text-muted small" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4"><?= e((string) ($event['description'] ?? '')) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="lieu">Lieu</label>
                    <input class="form-control" id="lieu" name="lieu" value="<?= e((string) ($event['lieu'] ?? '')) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted small" for="date_debut">Date debut *</label>
                    <input class="form-control" id="date_debut" name="date_debut" type="datetime-local" required value="<?= e(dt((string) ($event['date_debut'] ?? ''))) ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-muted small" for="date_fin">Date fin *</label>
                    <input class="form-control" id="date_fin" name="date_fin" type="datetime-local" required value="<?= e(dt((string) ($event['date_fin'] ?? ''))) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label text-muted small" for="capacite">Capacite</label>
                    <input class="form-control" id="capacite" name="capacite" type="number" min="1" value="<?= e((string) ($event['capacite'] ?? '')) ?>">
                </div>

                <div class="col-md-6">
                    <?php $statut = (string) ($event['statut'] ?? 'planifie'); ?>
                    <label class="form-label text-muted small" for="statut">Statut *</label>
                    <select class="form-select" id="statut" name="statut" required>
                        <option value="planifie" <?= $statut === 'planifie' ? 'selected' : '' ?>>Planifie</option>
                        <option value="ouvert" <?= $statut === 'ouvert' ? 'selected' : '' ?>>Ouvert</option>
                        <option value="complet" <?= $statut === 'complet' ? 'selected' : '' ?>>Complet</option>
                        <option value="termine" <?= $statut === 'termine' ? 'selected' : '' ?>>Termine</option>
                        <option value="annule" <?= $statut === 'annule' ? 'selected' : '' ?>>Annule</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4 pt-2 border-top">
                <button class="btn btn-primary px-4" type="submit">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
