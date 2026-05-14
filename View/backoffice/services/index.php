<?php
require_once __DIR__ . '/../../shared/helpers.php';

$services = $services ?? [];
$flash = $flash ?? null;
?>

<div class="back-page-header mb-4">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-gear-fill"></i></div>
        <div>
            <div class="back-page-title"><?= htmlspecialchars((string) ($title ?? 'Services'), ENT_QUOTES, 'UTF-8') ?></div>
            <div class="back-page-sub">Gérez les différentes catégories de service proposées au sein de l'établissement.</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/services/createForm') ?>" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-plus-lg me-2"></i>Nouveau service</a>
    </div>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<div class="back-table-wrap">
    <table class="back-table">
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
                                        <span class="badge" style="background:#dcfce7;color:#166534;border:1px solid #86efac;border-radius:6px;padding:5px 8px;">Actif</span>
                                    <?php else: ?>
                                        <span class="badge" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:6px;padding:5px 8px;">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column gap-1 justify-content-end align-items-end">
                                        <a href="<?= $this->url('/services/editForm/' . $sid) ?>" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" style="border-radius:6px; width:32px; height:32px;" title="Modifier"><i class="bi bi-pencil"></i></a>
                                        <form action="<?= $this->url('/services/delete/' . $sid) ?>" method="post" class="m-0 d-inline" onsubmit="return confirm('Supprimer ou désactiver ce service ?');">
                                            <button type="submit" class="btn btn-outline-danger btn-sm d-flex align-items-center justify-content-center" style="border-radius:6px; width:32px; height:32px;" title="Supprimer"><i class="bi bi-trash3-fill"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
</div>
