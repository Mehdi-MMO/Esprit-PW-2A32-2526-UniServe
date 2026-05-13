<?php
require_once __DIR__ . '/../../shared/helpers.php';

$demandes = $demandes ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
$teacher_notice = !empty($teacher_notice ?? false);
$pieces_by_demande = $pieces_by_demande ?? [];

if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
} else {
    $flash = null;
}
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Services administratifs</div>
        <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Demandes'), ENT_QUOTES, 'UTF-8') ?></h1>
        <?php if ($teacher_notice): ?>
            <p class="text-muted mb-0">Les demandes de service en ligne sont réservées aux étudiants. Pour toute question, contactez la scolarité.</p>
        <?php else: ?>
            <p class="text-muted mb-0">Suivez vos demandes et créez-en de nouvelles.</p>
        <?php endif; ?>
    </div>
    <?php if (!$teacher_notice): ?>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="<?= $this->url('/services') ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-list-ul me-2" aria-hidden="true"></i>Types de demandes</a>
            <a href="<?= $this->url('/demandes/createForm') ?>" class="btn btn-primary"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>Nouvelle demande</a>
        </div>
    <?php endif; ?>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php elseif (($flash['type'] ?? '') === 'warning'): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <?= htmlspecialchars((string) ($flash['message'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($teacher_notice): ?>
    <div class="us-card p-4 text-muted">
        <p class="mb-0">Vous pouvez utiliser les autres modules du menu (documents, rendez-vous, événements selon votre profil).</p>
    </div>
<?php else: ?>

<?php
$kpi = [
    ['title' => 'Total', 'value' => $stats['total'] ?? 0, 'border' => 'primary'],
    ['title' => 'En attente', 'value' => $stats['en_attente'] ?? 0, 'border' => 'warning'],
    ['title' => 'En cours', 'value' => $stats['en_cours'] ?? 0, 'border' => 'info'],
    ['title' => 'Traitées', 'value' => $stats['traite'] ?? 0, 'border' => 'success'],
    ['title' => 'Rejetées', 'value' => $stats['rejete'] ?? 0, 'border' => 'danger'],
];
?>
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-4">
    <?php foreach ($kpi as $cell): ?>
        <div class="col">
            <div class="card h-100 border-top border-4 border-<?= htmlspecialchars($cell['border'], ENT_QUOTES, 'UTF-8') ?> shadow-sm">
                <div class="card-body py-3 text-center">
                    <div class="small text-muted text-uppercase"><?= htmlspecialchars($cell['title'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="h4 mb-0 fw-bold"><?= (int) $cell['value'] ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="us-section-card">
    <div class="card-body p-0">
        <div class="table-responsive us-table-wrap">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Catégorie</th>
                        <th>Titre</th>
                        <th>Pièces</th>
                        <th>Statut</th>
                        <th>Soumise</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">Aucune demande pour le moment.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $row): ?>
                            <?php
                            $sid = (int) ($row['id'] ?? 0);
                            $st = (string) ($row['statut'] ?? '');
                            $label = $statut_labels[$st] ?? $st;
                            ?>
                            <tr>
                                <td class="text-muted small"><?= htmlspecialchars((string) ($row['categorie_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars((string) ($row['titre'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="small">
                                    <?php $pList = $pieces_by_demande[$sid] ?? []; ?>
                                    <?php if ($pList === []): ?>
                                        <span class="text-muted">—</span>
                                    <?php else: ?>
                                        <?php foreach ($pList as $pj): ?>
                                            <?php $pid = (int) ($pj['id'] ?? 0); ?>
                                            <div><a href="<?= $this->url('/demandes/downloadPiece/' . $pid) ?>"><?= htmlspecialchars((string) ($pj['nom_fichier'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a></div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td class="small text-muted"><?= htmlspecialchars((string) ($row['soumise_le'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-end">
                                    <?php if ($st === 'en_attente'): ?>
                                        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/demandes/editForm/' . $sid) ?>">Modifier</a>
                                        <form method="post" action="<?= $this->url('/demandes/delete/' . $sid) ?>" class="d-inline" onsubmit="return confirm('Supprimer cette demande ?');">
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
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
