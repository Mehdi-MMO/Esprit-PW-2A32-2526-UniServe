<?php
declare(strict_types=1);

require_once __DIR__ . '/../../shared/helpers.php';

$rdvs = $rdvs ?? [];
$stats = $stats ?? [];
$statut_labels = $statut_labels ?? [];
$teacher_notice = !empty($teacher_notice ?? false);
$flash = $flash ?? null;

$hub_bq = (string) ($hub_bq ?? '');
$hub_bpage = (int) ($hub_bpage ?? 1);
$hub_bureaux = $hub_bureaux ?? [];
$hub_bureau_total_filtered = (int) ($hub_bureau_total_filtered ?? 0);
$hub_bureau_total_pages = (int) ($hub_bureau_total_pages ?? 1);
$hub_bureau_from = (int) ($hub_bureau_from ?? 0);
$hub_bureau_to = (int) ($hub_bureau_to ?? 0);
$hub_taux_succes = (int) ($hub_taux_succes ?? 0);
$hub_campus_pins = $hub_campus_pins ?? [];

$humanType = static function (string $typeService): string {
    $t = trim(str_replace(['_', '-'], ' ', $typeService));

    return $t !== '' ? $t : '—';
};

$bureauIcon = static function (string $typeService): string {
    $t = strtolower($typeService);
    if (str_contains($t, 'sport')) {
        return 'fa-trophy';
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
    if (str_contains($t, 'rh') || str_contains($t, 'personnel')) {
        return 'fa-people-group';
    }

    return 'fa-building';
};

$hubQuery = static function (array $replace) use ($hub_bq): string {
    $base = array_merge(['bq' => $hub_bq, 'bpage' => 1], $replace);
    $out = [];
    foreach ($base as $k => $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        if ($k === 'bpage' && (int) $v <= 1) {
            continue;
        }
        $out[$k] = $v;
    }

    return http_build_query($out);
};

$fmtCell = static function (string $d): string {
    $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d);
    if ($dt === false) {
        $ts = strtotime($d);

        return $ts !== false ? date('d/m/Y H:i', $ts) : htmlspecialchars($d, ENT_QUOTES, 'UTF-8');
    }

    return $dt->format('d/m/Y H:i');
};

$recentSlice = !$teacher_notice ? array_slice($rdvs, 0, 3) : [];
?>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<?php if ($teacher_notice): ?>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header mb-4">
        <div>
            <div class="us-kicker mb-1">Planification</div>
            <h1 class="h3 mb-1"><?= htmlspecialchars((string) ($title ?? 'Rendez-vous'), ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="text-muted mb-0">La réservation en ligne est réservée aux étudiants. Pour un rendez-vous administratif, contactez un bureau.</p>
        </div>
    </div>
    <div class="us-card p-4 text-muted">
        <p class="mb-0">Utilisez les autres entrées du menu selon votre profil.</p>
    </div>
<?php else: ?>

    <section class="us-rdv-hero mb-4">
        <div class="us-rdv-hero-inner">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="us-kicker text-white-50 mb-2">UniServe · Rendez-vous</div>
                    <h1 class="h2 mb-3">Réservez votre rendez-vous en quelques secondes</h1>
                    <p class="lead mb-0">Choisissez un bureau, sélectionnez une date et évitez les files d’attente.</p>
                </div>
                <div class="col-lg-4 d-flex flex-column flex-sm-row flex-lg-column gap-2 align-items-stretch align-items-lg-end">
                    <a href="#bureaux-disponibles" class="btn btn-light btn-lg">Réserver maintenant</a>
                    <div class="dropdown">
                        <button class="btn btn-outline-light btn-lg dropdown-toggle w-100" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Rendez-vous récents
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <?php if ($recentSlice === []): ?>
                                <li><span class="dropdown-item-text text-muted">Aucun rendez-vous pour l’instant.</span></li>
                            <?php else: ?>
                                <?php foreach ($recentSlice as $rr): ?>
                                    <?php
                                    $rid = (int) ($rr['id'] ?? 0);
                                    $rst = (string) ($rr['statut'] ?? '');
                                    $canEdit = $rst === 'reserve';
                                    ?>
                                    <li>
                                        <?php if ($canEdit): ?>
                                            <a class="dropdown-item" href="<?= $this->url('/rendezvous/editForm/' . $rid) ?>">
                                                <?= htmlspecialchars((string) ($rr['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                <span class="d-block small text-muted"><?= htmlspecialchars($fmtCell((string) ($rr['date_debut'] ?? '')), ENT_QUOTES, 'UTF-8') ?></span>
                                            </a>
                                        <?php else: ?>
                                            <a class="dropdown-item" href="<?= $this->url('/rendezvous?focus=' . $rid) ?>#mes-rendez-vous">
                                                <?= htmlspecialchars((string) ($rr['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                                <span class="d-block small text-muted"><?= htmlspecialchars($statut_labels[$rst] ?? $rst, ENT_QUOTES, 'UTF-8') ?></span>
                                            </a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item fw-semibold" href="#mes-rendez-vous">Voir tous mes rendez-vous</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
        <div class="col">
            <div class="card us-rdv-kpi-card us-rdv-kpi-card--total h-100">
                <div class="card-body py-3">
                    <div class="us-rdv-kpi-label mb-1">Total RDV</div>
                    <div class="us-rdv-kpi-value"><?= (int) ($stats['total'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card us-rdv-kpi-card us-rdv-kpi-card--ok h-100">
                <div class="card-body py-3">
                    <div class="us-rdv-kpi-label mb-1">Confirmés</div>
                    <div class="us-rdv-kpi-value"><?= (int) ($stats['confirme'] ?? 0) ?></div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card us-rdv-kpi-card us-rdv-kpi-card--rate h-100">
                <div class="card-body py-3">
                    <div class="us-rdv-kpi-label mb-1">Taux succès</div>
                    <div class="us-rdv-kpi-value"><?= (int) $hub_taux_succes ?>%</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card us-rdv-kpi-card us-rdv-kpi-card--bureaux h-100">
                <div class="card-body py-3">
                    <div class="us-rdv-kpi-label mb-1">Bureaux actifs</div>
                    <div class="us-rdv-kpi-value"><?= (int) ($stats['bureaux_actifs'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>

    <section class="us-campus-map-wrap mb-4" aria-labelledby="carte-campus-titre">
        <div class="us-campus-map-head d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 id="carte-campus-titre" class="h5 mb-0">Carte du campus</h2>
                <p class="text-muted small mb-0">Cliquez sur un bureau pour réserver</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="us-map-reset">Vue globale</button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#us-map-legend" aria-expanded="false">Légende</button>
                <div class="btn-group btn-group-sm" role="group" aria-label="Zoom décoratif">
                    <button type="button" class="btn btn-outline-secondary" id="us-map-zoom-in" title="Zoom +">+</button>
                    <button type="button" class="btn btn-outline-secondary" id="us-map-zoom-out" title="Zoom −">−</button>
                </div>
            </div>
        </div>
        <div class="collapse px-3 pb-2" id="us-map-legend">
            <div class="small text-muted d-flex flex-wrap gap-3 py-2">
                <span><span class="rounded-circle d-inline-block me-1" style="width:0.75rem;height:0.75rem;background:#4f46e5"></span> Catégorie A</span>
                <span><span class="rounded-circle d-inline-block me-1" style="width:0.75rem;height:0.75rem;background:#0284c7"></span> Catégorie B</span>
                <span><span class="rounded-circle d-inline-block me-1" style="width:0.75rem;height:0.75rem;background:#0d9488"></span> Catégorie C</span>
            </div>
        </div>
        <div class="us-campus-map" id="us-campus-map" data-scale="1">
            <div class="us-campus-map-pins" id="us-campus-map-pins">
                <?php foreach ($hub_campus_pins as $pin): ?>
                    <?php
                    $pid = (int) ($pin['id'] ?? 0);
                    $tone = (int) ($pin['tone'] ?? 0);
                    $px = (float) ($pin['x'] ?? 0);
                    $py = (float) ($pin['y'] ?? 0);
                    $pnom = (string) ($pin['nom'] ?? '');
                    ?>
                    <button type="button"
                            class="us-map-pin us-map-pin--<?= $tone ?>"
                            style="left: <?= $px ?>%; top: <?= $py ?>%;"
                            data-bureau-id="<?= $pid ?>"
                            title="<?= htmlspecialchars($pnom, ENT_QUOTES, 'UTF-8') ?>"
                            aria-label="Bureau <?= htmlspecialchars($pnom, ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <form method="get" action="<?= $this->url('/rendezvous') ?>" class="us-hub-toolbar p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-lg">
                <label class="form-label small text-muted mb-1" for="hub-bq">Recherche</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" id="hub-bq" name="bq" value="<?= htmlspecialchars($hub_bq, ENT_QUOTES, 'UTF-8') ?>" placeholder="Rechercher un bureau par nom ou localisation…">
                </div>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">Rechercher</button>
                <a class="btn btn-outline-danger" href="<?= $this->url('/rendezvous/exportMesPrint') ?>" target="_blank" rel="noopener">
                    <i class="fa-regular fa-file-pdf me-1"></i>Export PDF
                </a>
            </div>
        </div>
    </form>

    <section id="bureaux-disponibles" class="mb-2">
        <h2 class="h4 mb-3">Bureaux disponibles</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-4">
            <?php if ($hub_bureaux === []): ?>
                <div class="col-12 text-center text-muted py-5">Aucun bureau ne correspond à votre recherche.</div>
            <?php else: ?>
                <?php foreach ($hub_bureaux as $b): ?>
                    <?php
                    $bid = (int) ($b['id'] ?? 0);
                    $theme = $bid % 6;
                    $ts = (string) ($b['type_service'] ?? '');
                    $fa = $bureauIcon($ts);
                    ?>
                    <div class="col">
                        <article class="card us-hub-bureau-card shadow-sm h-100 border-0" id="bureau-card-<?= $bid ?>" data-bureau-id="<?= $bid ?>">
                            <div class="us-hub-card-top us-hub-card-top--<?= $theme ?>">
                                <div class="d-flex align-items-start gap-2 min-w-0">
                                    <span class="fs-4 flex-shrink-0" aria-hidden="true"><i class="fa-solid <?= htmlspecialchars($fa, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                    <h3 class="text-truncate"><?= htmlspecialchars((string) ($b['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h3>
                                </div>
                                <span class="us-hub-disponible"><i class="fa-solid fa-circle me-1 small" style="opacity:.85"></i>Disponible</span>
                            </div>
                            <div class="us-hub-card-body d-flex flex-column">
                                <div class="mb-2">
                                    <div class="us-hub-meta-label">Localisation</div>
                                    <div class="small fw-semibold"><?= htmlspecialchars(trim((string) ($b['localisation'] ?? '')) !== '' ? (string) $b['localisation'] : '—', ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                                <div class="mb-3">
                                    <div class="us-hub-meta-label">Responsable</div>
                                    <div class="small text-muted"><?= htmlspecialchars($humanType($ts), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                                <a class="btn btn-primary w-100 mt-auto" href="<?= $this->url('/rendezvous/createForm?bureau_id=' . $bid) ?>">Prendre rendez-vous</a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($hub_bureau_total_pages > 1): ?>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 mb-5">
            <p class="text-muted small mb-0">
                Page <?= $hub_bpage ?> sur <?= $hub_bureau_total_pages ?> — <?= $hub_bureau_total_filtered ?> bureau(x) au total.
            </p>
            <nav aria-label="Pagination bureaux">
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($hub_bpage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $hubQuery(['bq' => $hub_bq, 'bpage' => $hub_bpage - 1]), ENT_QUOTES, 'UTF-8') ?>">Préc.</a>
                        </li>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $hub_bureau_total_pages; $i++): ?>
                        <li class="page-item <?= $i === $hub_bpage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $hubQuery(['bq' => $hub_bq, 'bpage' => $i]), ENT_QUOTES, 'UTF-8') ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <?php if ($hub_bpage < $hub_bureau_total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= htmlspecialchars($this->url('/rendezvous') . '?' . $hubQuery(['bq' => $hub_bq, 'bpage' => $hub_bpage + 1]), ENT_QUOTES, 'UTF-8') ?>">Suiv.</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php else: ?>
        <p class="text-muted small mt-2 mb-5">
            <?php if ($hub_bureau_total_filtered > 0): ?>
                <?= $hub_bureau_total_filtered ?> bureau(x) au total.
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <section id="mes-rendez-vous" class="mt-5">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
            <h2 class="h4 mb-0">Mes rendez-vous</h2>
        </div>
        <div class="us-section-card">
            <div class="card-body p-0">
                <div class="table-responsive us-table-wrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Bureau</th>
                                <th>Motif</th>
                                <th>Début</th>
                                <th>Fin</th>
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
                                    $tone = match ($st) {
                                        'reserve' => 'warning',
                                        'confirme' => 'success',
                                        'annule' => 'danger',
                                        'termine' => 'secondary',
                                        default => 'secondary',
                                    };
                                    ?>
                                    <tr id="rdv-<?= $sid ?>">
                                        <td>
                                            <div class="fw-semibold"><?= htmlspecialchars((string) ($row['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                            <div class="small text-muted"><?= htmlspecialchars((string) ($row['bureau_localisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                        </td>
                                        <td><?= htmlspecialchars((string) ($row['motif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="small"><?= htmlspecialchars($fmtCell((string) ($row['date_debut'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="small"><?= htmlspecialchars($fmtCell((string) ($row['date_fin'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="badge rounded-pill text-bg-<?= htmlspecialchars($tone, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td class="text-end">
                                            <?php if ($st === 'reserve'): ?>
                                                <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/rendezvous/editForm/' . $sid) ?>">Modifier</a>
                                            <?php endif; ?>
                                            <?php if ($st === 'reserve' || $st === 'confirme'): ?>
                                                <form method="post" action="<?= $this->url('/rendezvous/cancel/' . $sid) ?>" class="d-inline" onsubmit="return confirm('Annuler ce rendez-vous ?');">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Annuler</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if ($st !== 'reserve' && $st !== 'confirme'): ?>
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
    </section>

    <?php
    $focusRdvId = isset($_GET['focus']) ? (int) $_GET['focus'] : 0;
    ?>
    <?php if ($focusRdvId > 0): ?>
        <script>
        (function () {
            var id = <?= json_encode($focusRdvId, JSON_THROW_ON_ERROR) ?>;
            var row = document.getElementById('rdv-' + id);
            if (!row) { return; }
            row.classList.add('table-warning');
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
        })();
        </script>
    <?php endif; ?>

    <script>
    (function () {
        var map = document.getElementById('us-campus-map');
        var pinsWrap = document.getElementById('us-campus-map-pins');
        if (!map || !pinsWrap) { return; }

        function clearActivePins() {
            pinsWrap.querySelectorAll('.us-map-pin.is-active').forEach(function (p) { p.classList.remove('is-active'); });
        }

        function highlightBureau(id) {
            clearActivePins();
            document.querySelectorAll('.us-hub-bureau-card.is-highlight').forEach(function (c) { c.classList.remove('is-highlight'); });
            var pin = pinsWrap.querySelector('.us-map-pin[data-bureau-id="' + id + '"]');
            if (pin) { pin.classList.add('is-active'); }
            var card = document.getElementById('bureau-card-' + id);
            if (card) {
                card.classList.add('is-highlight');
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        pinsWrap.addEventListener('click', function (e) {
            var t = e.target.closest('.us-map-pin');
            if (!t) { return; }
            var id = t.getAttribute('data-bureau-id');
            if (!id) { return; }
            highlightBureau(id);
        });

        var btnReset = document.getElementById('us-map-reset');
        if (btnReset) {
            btnReset.addEventListener('click', function () {
                clearActivePins();
                document.querySelectorAll('.us-hub-bureau-card.is-highlight').forEach(function (c) { c.classList.remove('is-highlight'); });
                map.dataset.scale = '1';
                pinsWrap.style.transform = 'scale(1)';
                map.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        }

        var sc = 1;
        var zIn = document.getElementById('us-map-zoom-in');
        var zOut = document.getElementById('us-map-zoom-out');
        function applyZoom() {
            pinsWrap.style.transform = 'scale(' + sc + ')';
            map.dataset.scale = String(sc);
        }
        if (zIn) {
            zIn.addEventListener('click', function () {
                sc = Math.min(1.35, sc + 0.08);
                applyZoom();
            });
        }
        if (zOut) {
            zOut.addEventListener('click', function () {
                sc = Math.max(0.85, sc - 0.08);
                applyZoom();
            });
        }
    })();
    </script>

<?php endif; ?>
