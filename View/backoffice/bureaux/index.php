<?php
require_once __DIR__ . '/../../shared/helpers.php';

$bureaux = $bureaux ?? [];
$flash = $flash ?? null;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Rendez-vous</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Bureaux'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Bureaux proposés aux étudiants pour la réservation de créneaux.</p>
    </div>
    <a href="<?= $this->url('/bureaux/createForm') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-2"></i>Nouveau bureau</a>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<div class="us-section-card">
    <div class="card-body p-0">
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Localisation</th>
                        <th>Type de service</th>
                        <th>Actif</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bureaux === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">Aucun bureau.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bureaux as $b): ?>
                            <?php
                            $bid = (int) ($b['id'] ?? 0);
                            $actif = (int) ($b['actif'] ?? 0) === 1;
                            ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars((string) ($b['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string) ($b['localisation'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><code class="small"><?= htmlspecialchars((string) ($b['type_service'] ?? ''), ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td>
                                    <?php if ($actif): ?>
                                        <span class="badge text-bg-success">Oui</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/bureaux/editForm/' . $bid) ?>">Modifier</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
