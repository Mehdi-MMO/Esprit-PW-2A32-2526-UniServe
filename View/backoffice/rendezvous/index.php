<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/helpers.php';

$rdvs = $rdvs ?? [];
$tab = (string) ($tab ?? 'rdv');
$dashboard_stats = $dashboard_stats ?? ['total' => 0, 'reserve' => 0, 'confirme' => 0, 'annule' => 0, 'termine' => 0];
$nb_rdvs = (int) ($nb_rdvs ?? 0);
$nb_bureaux = (int) ($nb_bureaux ?? 0);
$nb_bureaux_filtered = (int) ($nb_bureaux_filtered ?? $nb_bureaux);
$bureau_bq = (string) ($bureau_bq ?? '');
$bureau_kpi = $bureau_kpi ?? ['total' => 0, 'actifs' => 0, 'inactifs' => 0, 'rdv_total' => 0];
$bureaux = $bureaux ?? [];
$bureau_page = (int) ($bureau_page ?? 1);
$bureau_total_pages = (int) ($bureau_total_pages ?? 1);
$bureau_from = (int) ($bureau_from ?? 0);
$bureau_to = (int) ($bureau_to ?? 0);
$statut_filter = (string) ($statut_filter ?? '');
$q = (string) ($q ?? '');
$sort = (string) ($sort ?? 'date_desc');
$statut_labels = $statut_labels ?? [];
$pagination = $pagination ?? ['page' => 1, 'per_page' => 10, 'total' => 0, 'total_pages' => 1];
$flash = $flash ?? null;

$ds = $dashboard_stats;
$statCards = [
    ['title' => 'Total', 'value' => $ds['total'] ?? 0, 'color' => 'primary', 'icon' => 'fa-solid fa-calendar-days'],
    ['title' => 'En attente', 'value' => $ds['reserve'] ?? 0, 'color' => 'warning', 'icon' => 'fa-solid fa-hourglass-half'],
    ['title' => 'Confirmés', 'value' => $ds['confirme'] ?? 0, 'color' => 'success', 'icon' => 'fa-solid fa-circle-check'],
    ['title' => 'Annulés', 'value' => $ds['annule'] ?? 0, 'color' => 'danger', 'icon' => 'fa-solid fa-ban'],
];

$buildRdvQuery = static function (array $replace) use ($q, $statut_filter, $sort, $pagination): string {
    $base = array_merge([
        'tab' => 'rdv',
        'q' => $q,
        'statut' => $statut_filter,
        'sort' => $sort,
        'page' => (int) ($pagination['page'] ?? 1),
    ], $replace);
    $filtered = [];
    foreach ($base as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        if ($k === 'page' && (int) $v <= 1) {
            continue;
        }
        $filtered[$k] = $v;
    }

    return http_build_query($filtered);
};

$buildBureauQuery = static function (array $replace) use ($bureau_page, $bureau_bq): string {
    $base = array_merge([
        'tab' => 'bureaux',
        'bq' => $bureau_bq,
        'bpage' => $bureau_page,
    ], $replace);
    $filtered = [];
    foreach ($base as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        if ($k === 'bpage' && (int) $v <= 1) {
            continue;
        }
        $filtered[$k] = $v;
    }

    return http_build_query($filtered);
};

$exportQs = [];
if ($statut_filter !== '') {
    $exportQs['statut'] = $statut_filter;
}
if ($q !== '') {
    $exportQs['q'] = $q;
}
if ($sort !== '') {
    $exportQs['sort'] = $sort;
}
$exportHref = $this->url('/rendezvous/exportPrint') . ($exportQs !== [] ? ('?' . http_build_query($exportQs)) : '');

$exportBureauxQs = [];
if ($bureau_bq !== '') {
    $exportBureauxQs['bq'] = $bureau_bq;
}
$exportBureauxHref = $this->url('/rendezvous/exportBureauxPrint') . ($exportBureauxQs !== [] ? ('?' . http_build_query($exportBureauxQs)) : '');

$bk = $bureau_kpi;

$fmtRdv = static function (string $d): string {
    $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d);
    if ($dt === false) {
        $ts = strtotime($d);

        return $ts !== false ? date('d/m/Y H:i:s', $ts) : $d;
    }

    return $dt->format('d/m/Y H:i:s');
};

$bureauIcon = static function (string $typeService): string {
    $t = strtolower($typeService);
    if (str_contains($t, 'sport')) {
        return 'fa-person-running';
    }
    if (str_contains($t, 'stage') || str_contains($t, 'intern')) {
        return 'fa-briefcase';
    }
    if (str_contains($t, 'finan') || str_contains($t, 'aide_fin')) {
        return 'fa-coins';
    }
    if (str_contains($t, 'psy') || str_contains($t, 'soutien')) {
        return 'fa-heart-pulse';
    }
    if (str_contains($t, 'admin') || str_contains($t, 'scol')) {
        return 'fa-landmark';
    }

    return 'fa-building';
};

$humanType = static function (string $typeService): string {
    $t = trim(str_replace(['_', '-'], ' ', $typeService));

    return $t !== '' ? $t : '—';
};

$badgeTone = static function (string $st): string {
    return match ($st) {
        'reserve' => 'warning',
        'confirme' => 'success',
        'annule' => 'danger',
        'termine' => 'secondary',
        default => 'secondary',
    };
};
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
    <div>
        <div class="us-kicker mb-1">Administration</div>
        <h1 class="h3 mb-1 d-flex align-items-center gap-2">
            <i class="fa-regular fa-calendar-check text-primary" aria-hidden="true"></i>
            <?= htmlspecialchars((string) ($title ?? 'Gestion des rendez-vous'), ENT_QUOTES, 'UTF-8') ?>
        </h1>
        <p class="text-muted mb-0">Gérez les rendez-vous et les bureaux depuis cette page.</p>
    </div>
</div>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<ul class="nav nav-pills us-rdv-admin-tabs gap-2 mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'rdv' ? 'active' : '' ?>"
           href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildRdvQuery(['tab' => 'rdv', 'page' => 1]), ENT_QUOTES, 'UTF-8') ?>">
            Rendez-vous <span class="us-tab-count"><?= $nb_rdvs ?></span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'bureaux' ? 'active' : '' ?>"
           href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildBureauQuery(['tab' => 'bureaux', 'bpage' => 1]), ENT_QUOTES, 'UTF-8') ?>">
            Bureaux <span class="us-tab-count"><?= $nb_bureaux ?></span>
        </a>
    </li>
</ul>

<?php if ($tab === 'rdv'): ?>
    <div class="mb-4"><?= renderStatGrid($statCards) ?></div>

    <div class="us-section-card mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= $this->url('/rendezvous') ?>" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="rdv">
                <div class="col-lg-4">
                    <label class="form-label small text-muted mb-1" for="rq">Recherche</label>
                    <input type="text" class="form-control" id="rq" name="q" value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher par nom, sujet ou bureau…">
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1" for="rst">Statut</label>
                    <select class="form-select" id="rst" name="statut">
                        <option value="">Tous</option>
                        <?php foreach ($statut_labels as $k => $lab): ?>
                            <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $statut_filter === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1" for="srt">Trier</label>
                    <select class="form-select" id="srt" name="sort">
                        <option value="date_desc" <?= $sort === 'date_desc' ? 'selected' : '' ?>>Date récente</option>
                        <option value="date_asc" <?= $sort === 'date_asc' ? 'selected' : '' ?>>Date ancienne</option>
                    </select>
                </div>
                <div class="col-lg-4 d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Rechercher</button>
                    <a href="<?= $this->url('/rendezvous') ?>?tab=rdv" class="btn btn-outline-secondary">Réinitialiser</a>
                    <a class="btn btn-outline-dark" href="<?= htmlspecialchars($exportHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                        <i class="fa-regular fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="us-section-card">
        <div class="card-body p-0">
            <div class="table-responsive us-table-wrap">
                <table class="table table-hover align-middle mb-0 us-rdv-admin-table">
                    <thead>
                        <tr>
                            <th>Étudiant</th>
                            <th>Bureau</th>
                            <th>Sujet</th>
                            <th>Date &amp; heure</th>
                            <th>Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rdvs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Aucun rendez-vous.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rdvs as $row): ?>
                                <?php
                                $sid = (int) ($row['id'] ?? 0);
                                $st = (string) ($row['statut'] ?? '');
                                $label = $statut_labels[$st] ?? $st;
                                $prenom = trim((string) ($row['etudiant_prenom'] ?? ''));
                                $ini = $prenom !== '' ? mb_strtoupper(mb_substr($prenom, 0, 1)) : '?';
                                $tone = $badgeTone($st);
                                $d1 = (string) ($row['date_debut'] ?? '');
                                $d2 = (string) ($row['date_fin'] ?? '');
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="us-rdv-avatar" aria-hidden="true"><?= htmlspecialchars($ini, ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="fw-semibold"><?= htmlspecialchars((string) ($row['etudiant_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="fa-solid fa-bookmark text-primary mt-1 small" aria-hidden="true"></i>
                                            <div>
                                                <div class="fw-semibold"><?= htmlspecialchars((string) ($row['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                <?php if (trim((string) ($row['bureau_localisation'] ?? '')) !== ''): ?>
                                                    <div class="small text-muted"><?= htmlspecialchars((string) ($row['bureau_localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars((string) ($row['motif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <div class="d-flex align-items-start gap-2 text-nowrap">
                                            <i class="fa-regular fa-calendar text-muted mt-1 small" aria-hidden="true"></i>
                                            <div class="small">
                                                <div><?= htmlspecialchars($fmtRdv($d1), ENT_QUOTES, 'UTF-8') ?></div>
                                                <div class="text-muted">→ <?= htmlspecialchars($fmtRdv($d2), ENT_QUOTES, 'UTF-8') ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill text-bg-<?= htmlspecialchars($tone, ENT_QUOTES, 'UTF-8') ?> us-rdv-status-pill">
                                            <span class="us-rdv-dot" aria-hidden="true"></span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end gap-2">
                                            <form method="post" action="<?= $this->url('/rendezvous/updateStatut/' . $sid) ?>" class="d-flex flex-wrap gap-1 justify-content-end align-items-center">
                                                <select name="statut" class="form-select form-select-sm" style="width: auto; min-width: 7.5rem;">
                                                    <?php foreach ($statut_labels as $k => $lab): ?>
                                                        <option value="<?= htmlspecialchars($k, ENT_QUOTES, 'UTF-8') ?>" <?= $st === $k ? 'selected' : '' ?>><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-primary">OK</button>
                                            </form>
                                            <form method="post" action="<?= $this->url('/rendezvous/adminDelete/' . $sid) ?>" onsubmit="return confirm('Supprimer définitivement ce rendez-vous ?');">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
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
    </div>

    <?php
    $pg = (int) ($pagination['page'] ?? 1);
    $tp = (int) ($pagination['total_pages'] ?? 1);
    $tot = (int) ($pagination['total'] ?? 0);
    ?>
    <?php if ($tp > 1): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
            <div class="text-muted small">Page <?= $pg ?> sur <?= $tp ?> · <?= $tot ?> rendez-vous</div>
            <nav aria-label="Pagination">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($pg > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildRdvQuery(['page' => $pg - 1]), ENT_QUOTES, 'UTF-8') ?>">Préc.</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $tp; $i++): ?>
                        <li class="page-item <?= $i === $pg ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildRdvQuery(['page' => $i]), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($pg < $tp): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildRdvQuery(['page' => $pg + 1]), ENT_QUOTES, 'UTF-8') ?>">Suiv.</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>

    <?php if (($ds['termine'] ?? 0) > 0): ?>
        <p class="text-muted small mt-3 mb-0"><strong>Terminés :</strong> <?= (int) ($ds['termine'] ?? 0) ?> (inclus dans le total ci-dessus)</p>
    <?php endif; ?>

<?php else: /* tab bureaux */ ?>
    <?php
    $bureauStatCards = [
        ['title' => 'Bureaux', 'value' => $bk['total'] ?? 0, 'color' => 'primary', 'icon' => 'fa-solid fa-building'],
        ['title' => 'Actifs', 'value' => $bk['actifs'] ?? 0, 'color' => 'success', 'icon' => 'fa-solid fa-circle-check'],
        ['title' => 'Inactifs', 'value' => $bk['inactifs'] ?? 0, 'color' => 'secondary', 'icon' => 'fa-solid fa-circle-pause'],
        ['title' => 'RDV (total)', 'value' => $bk['rdv_total'] ?? 0, 'color' => 'info', 'icon' => 'fa-solid fa-calendar-days'],
    ];
    ?>
    <div class="mb-4"><?= renderStatGrid($bureauStatCards) ?></div>

    <div class="us-section-card mb-4">
        <div class="card-body p-3">
            <form method="get" action="<?= $this->url('/rendezvous') ?>" class="row g-2 align-items-end">
                <input type="hidden" name="tab" value="bureaux">
                <div class="col-lg-6">
                    <label class="form-label small text-muted mb-1" for="bbq">Recherche</label>
                    <input type="text" class="form-control" id="bbq" name="bq" value="<?= htmlspecialchars($bureau_bq, ENT_QUOTES, 'UTF-8') ?>" placeholder="Nom, localisation ou type de service…">
                </div>
                <div class="col-lg-6 d-flex flex-wrap gap-2 justify-content-lg-end">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass me-2"></i>Rechercher</button>
                    <a href="<?= $this->url('/rendezvous') ?>?tab=bureaux" class="btn btn-outline-secondary">Réinitialiser</a>
                    <a class="btn btn-outline-danger" href="<?= htmlspecialchars($exportBureauxHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
                        <i class="fa-regular fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <p class="text-muted small mb-0">
            <?php if ($nb_bureaux_filtered > 0): ?>
                Affichage <?= $bureau_from ?>-<?= $bureau_to ?> sur <?= $nb_bureaux_filtered ?> bureau(x)<?= $bureau_bq !== '' ? ' (filtrés)' : '' ?>
                <?php if ($nb_bureaux_filtered !== $nb_bureaux): ?>
                    · <?= $nb_bureaux ?> au catalogue
                <?php endif; ?>
            <?php else: ?>
                Aucun bureau<?= $bureau_bq !== '' ? ' pour cette recherche' : '' ?>
            <?php endif; ?>
        </p>
        <a href="<?= $this->url('/bureaux/createForm') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus me-2"></i>Nouveau bureau</a>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-4 g-3">
        <?php if ($bureaux === []): ?>
            <div class="col-12 text-center text-muted py-5">Aucun bureau.</div>
        <?php else: ?>
            <?php foreach ($bureaux as $b): ?>
                <?php
                $bid = (int) ($b['id'] ?? 0);
                $actif = (int) ($b['actif'] ?? 0) === 1;
                $ts = (string) ($b['type_service'] ?? '');
                $fa = $bureauIcon($ts);
                ?>
                <div class="col">
                    <div class="card h-100 us-bureau-card shadow-sm border-0">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                <div class="d-flex align-items-center gap-2 min-w-0">
                                    <span class="us-bureau-card-icon"><i class="fa-solid <?= htmlspecialchars($fa, ENT_QUOTES, 'UTF-8') ?>" aria-hidden="true"></i></span>
                                    <h2 class="h6 mb-0 text-truncate"><?= htmlspecialchars((string) ($b['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                                </div>
                                <?php if ($actif): ?>
                                    <span class="badge text-bg-success text-nowrap">Actif</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary text-nowrap">Inactif</span>
                                <?php endif; ?>
                            </div>
                            <p class="small text-muted mb-2 d-flex gap-2">
                                <i class="fa-solid fa-location-dot mt-1 flex-shrink-0" aria-hidden="true"></i>
                                <span><?= htmlspecialchars(trim((string) ($b['localisation'] ?? '')) !== '' ? (string) $b['localisation'] : '—', ENT_QUOTES, 'UTF-8') ?></span>
                            </p>
                            <p class="small mb-3 d-flex gap-2">
                                <i class="fa-solid fa-tag mt-1 text-muted flex-shrink-0" aria-hidden="true"></i>
                                <span class="text-muted"><?= htmlspecialchars($humanType($ts), ENT_QUOTES, 'UTF-8') ?></span>
                            </p>
                            <div class="mt-auto d-flex gap-2">
                                <a class="btn btn-primary btn-sm flex-grow-1" href="<?= $this->url('/bureaux/editForm/' . $bid) ?>">
                                    <i class="fa-solid fa-pen me-1"></i>Modifier
                                </a>
                                <form method="post" action="<?= $this->url('/bureaux/delete/' . $bid) ?>" class="flex-shrink-0" onsubmit="return confirm('Supprimer ce bureau ?');">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($bureau_total_pages > 1): ?>
        <div class="d-flex justify-content-end mt-3">
            <nav aria-label="Pagination bureaux">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($bureau_page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildBureauQuery(['bpage' => $bureau_page - 1]), ENT_QUOTES, 'UTF-8') ?>">Préc.</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $bureau_total_pages; $i++): ?>
                        <li class="page-item <?= $i === $bureau_page ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildBureauQuery(['bpage' => $i]), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($bureau_page < $bureau_total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $buildBureauQuery(['bpage' => $bureau_page + 1]), ENT_QUOTES, 'UTF-8') ?>">Suiv.</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>
