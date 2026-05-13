<?php
require_once __DIR__ . '/../../shared/helpers.php';

$services = $services ?? [];
$flash = $flash ?? null;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Demandes de service</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Services'), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted mb-0">Les entrées correspondent à la table <code>categories_service</code> (catalogue visible aux étudiants).</p>
    </div>
    <a href="<?= $this->url('/services/createForm') ?>" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>Nouveau service</a>
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
                        <th>Description</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($services === []): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">Aucun service.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($services as $s): ?>
                            <?php
                            $sid = (int) ($s['id'] ?? 0);
                            $actif = (int) ($s['actif'] ?? 0) === 1;
                            ?>
                            <tr>
                                <td class="fw-medium"><?= htmlspecialchars((string) ($s['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small"><?= nl2br(htmlspecialchars((string) ($s['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></td>
                                <td>
                                    <?php if ($actif): ?>
                                        <span class="badge text-bg-success">Actif</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-secondary">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= $this->url('/services/editForm/' . $sid) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <form action="<?= $this->url('/services/delete/' . $sid) ?>" method="post" class="d-inline" onsubmit="return confirm('Supprimer ou désactiver ce service ?');">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
