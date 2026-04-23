<?php
$clubs = $clubs ?? [];
$myClubs = $myClubs ?? [];
$success = (string) ($success ?? '');
$error = (string) ($error ?? '');
$role = (string) ($_SESSION['user']['role'] ?? '');
$canSubmitRequest = in_array($role, ['etudiant', 'enseignant'], true);
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 us-page-header">
    <div>
        <div class="us-kicker mb-1">Vie associative</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Clubs actifs'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Explorez les clubs actifs et leurs activites.</p>
    </div>
    <div class="d-flex gap-2">
        <?php if ($canSubmitRequest): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/evenements/createClubRequestForm') ?>">Demander un club</a>
        <?php endif; ?>
        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements') ?>">Voir les evenements</a>
    </div>
</div>

<?php if ($success !== ''): ?>
    <div class="alert alert-success py-2 small" role="alert"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-danger py-2 small" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<?php if ($canSubmitRequest): ?>
    <div class="us-section-card mb-3">
        <div class="card-body p-3 p-md-4">
            <h2 class="h6 mb-3">Mes clubs (proprietaire)</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($myClubs)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-3">Aucun club cree.</td></tr>
                        <?php else: ?>
                            <?php foreach ($myClubs as $club): ?>
                                <?php
                                $clubId = (int) ($club['id'] ?? 0);
                                $validation = (string) ($club['statut_validation'] ?? 'en_attente');
                                $badge = $validation === 'approuve' ? 'success' : ($validation === 'rejete' ? 'danger' : 'secondary');
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><span class="badge bg-<?= $badge ?>"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $validation)), ENT_QUOTES, 'UTF-8') ?></span></td>
                                    <td class="text-end">
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/evenements/editClubForm/' . $clubId) ?>">Modifier</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row g-3">
    <?php if (empty($clubs)): ?>
        <div class="col-12">
            <div class="us-card p-4 us-empty-state">
                <div class="fw-semibold mb-1">Aucun club actif</div>
                <div class="text-muted">Les clubs apparaitront ici des qu ils seront actives.</div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($clubs as $club): ?>
            <?php $clubId = (int) ($club['id'] ?? 0); ?>
            <div class="col-md-6 col-xl-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex flex-column">
                        <h2 class="h6 mb-2"><?= htmlspecialchars((string) ($club['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                        <p class="text-muted small flex-grow-1">
                            <?= nl2br(htmlspecialchars((string) ($club['description'] ?? 'Aucune description.'), ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                        <div class="small text-muted mb-3">
                            Contact: <?= htmlspecialchars((string) ($club['email_contact'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </div>
                        <a class="btn btn-outline-primary w-100" href="<?= $this->url('/evenements/clubShow/' . $clubId) ?>">Voir details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
