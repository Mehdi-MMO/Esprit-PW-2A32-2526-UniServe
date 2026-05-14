

<?php
$badgeStyles = [
    'reserve'   => ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047','dot'=>'#eab308','label'=>'En attente'],
    'confirme'  => ['bg'=>'#dcfce7','color'=>'#166534','border'=>'#86efac','dot'=>'#22c55e','label'=>'Confirmé'],
    'annule'    => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','dot'=>'#ef4444','label'=>'Annulé'],
    'termine'   => ['bg'=>'#f3f4f6','color'=>'#4b5563','border'=>'#e5e7eb','dot'=>'#9ca3af','label'=>'Terminé'],
];

$bureauIcon = static function (string $typeService): string {
    $t = strtolower($typeService);
    if (str_contains($t, 'sport')) return 'bi-person-walking';
    if (str_contains($t, 'stage') || str_contains($t, 'intern')) return 'bi-briefcase';
    if (str_contains($t, 'finan') || str_contains($t, 'aide_fin')) return 'bi-coin';
    if (str_contains($t, 'psy') || str_contains($t, 'soutien')) return 'bi-heart-pulse';
    if (str_contains($t, 'admin') || str_contains($t, 'scol')) return 'bi-bank';
    return 'bi-building';
};

$humanType = static function (string $typeService): string {
    $t = trim(str_replace(['_', '-'], ' ', $typeService));
    return $t !== '' ? $t : '—';
};

$buildBureauQuery = static function (array $replace) use ($bureau_page, $bureau_bq): string {
    $base = array_merge([
        'tab' => 'bureaux',
        'bq' => $bureau_bq,
        'bpage' => $bureau_page,
    ], $replace);
    $filtered = [];
    foreach ($base as $k => $v) {
        if ($v === '' || $v === null) continue;
        if ($k === 'bpage' && (int) $v <= 1) continue;
        $filtered[$k] = $v;
    }
    return http_build_query($filtered);
};
?>

<div class="back-page-header">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi <?= $tab === 'bureaux' ? 'bi-building' : 'bi-calendar-check' ?>"></i></div>
        <div>
            <div class="back-page-title"><?= $tab === 'bureaux' ? 'Gestion des Bureaux' : 'Gestion des Rendez-vous' ?></div>
            <div class="back-page-sub"><?= $tab === 'bureaux' ? 'Gérez les bureaux et services disponibles.' : 'Approuvez ou rejetez les demandes de rendez-vous.' ?></div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <?php if ($tab === 'bureaux'): ?>
            <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary" style="font-weight:700;border-radius:12px;padding:8px 16px;">
                <i class="bi bi-calendar-event me-1"></i> Gérer les rendez-vous
            </a>
            <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:8px 16px;font-size:.85rem;font-weight:700;color:var(--brand);display:inline-flex;align-items:center;">
                <i class="bi bi-list-ul me-2"></i><?= $nb_bureaux ?> bureaux
            </span>
        <?php else: ?>
            <a href="<?= $this->url('/rendezvous?tab=bureaux') ?>" class="btn btn-outline-primary" style="font-weight:700;border-radius:12px;padding:8px 16px;">
                <i class="bi bi-building me-1"></i> Gérer les bureaux
            </a>
            <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:8px 16px;font-size:.85rem;font-weight:700;color:var(--brand);display:inline-flex;align-items:center;">
                <i class="bi bi-list-ul me-2"></i><?= $stats['total'] ?> rendez-vous
            </span>
        <?php endif; ?>
    </div>
</div>

<?php if ($tab !== 'bureaux'): ?>

<!-- Filtres simples -->
<form method="get" action="<?= $this->url('/rendezvous') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="text" name="q" class="form-control" style="max-width:280px; border-radius:10px; font-size:.9rem; font-weight:600;" placeholder="Rechercher étudiant, bureau, motif..." value="<?= htmlspecialchars($q ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <select name="statut" class="form-select" style="max-width:200px; border-radius:10px; font-size:.9rem; font-weight:600;" onchange="this.form.submit()">
        <option value="">Tous les statuts</option>
        <option value="reserve"  <?= ($statut_filter ?? '') === 'reserve' ? 'selected' : '' ?>>En attente</option>
        <option value="confirme" <?= ($statut_filter ?? '') === 'confirme' ? 'selected' : '' ?>>Confirmé</option>
        <option value="annule"   <?= ($statut_filter ?? '') === 'annule' ? 'selected' : '' ?>>Annulé</option>
        <option value="termine"  <?= ($statut_filter ?? '') === 'termine' ? 'selected' : '' ?>>Terminé</option>
    </select>
    <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search me-1"></i> Filtrer</button>
    <?php if (!empty($statut_filter) || !empty($q)): ?>
        <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Réinitialiser</a>
    <?php endif; ?>
</form>

<div class="back-stats-row">
    <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
        <i class="bi bi-hourglass-split"></i><?= $stats['reserve'] ?? 0 ?> En attente
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= $stats['confirme'] ?? 0 ?> Confirmés
    </span>
    <span class="back-stat-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
        <i class="bi bi-x-circle-fill"></i><?= $stats['annule'] ?? 0 ?> Annulés
    </span>
</div>

<div class="back-table-wrap">
    <div class="table-responsive">
        <table class="back-table">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Bureau</th>
                    <th>Sujet</th>
                    <th>Date &amp; Heure</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rdvs)): ?>
                <tr class="back-empty-row">
                    <td colspan="6">
                        <div class="back-empty-icon"><i class="bi bi-calendar-x"></i></div>
                        <div style="font-weight:600;font-size:.9rem;margin-bottom:4px;">Aucun rendez-vous</div>
                        <div style="font-size:.8rem;">Aucun rendez-vous ne correspond à vos critères.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rdvs as $row):
                    $statut = strtolower($row['statut']);
                    $bs     = $badgeStyles[$statut] ?? $badgeStyles['reserve'];
                    $prenom = $row['etudiant_prenom'] ?? ($row['prenom_etudiant'] ?? '');
                    $nom = $row['etudiant_nom_seul'] ?? ($row['nom_etudiant'] ?? '');
                    $fullname = trim($prenom . ' ' . $nom);
                    if ($fullname === '') $fullname = htmlspecialchars(trim((string) ($row['etudiant_nom'] ?? 'Étudiant Inconnu')));
                    $initials = strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
                    if ($initials === '') $initials = 'E';
                    $photoUrl = null;
                    if (function_exists('profile_photo_public_url')) {
                        $photoUrl = profile_photo_public_url((string) ($row['etudiant_photo'] ?? ''), $this);
                    }
                ?>
                <tr>
                    <td>
                        <div class="back-student-cell">
                            <?php if ($photoUrl !== null): ?>
                                <img src="<?= htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar" class="back-avatar" style="object-fit: cover; border-radius: 50%;">
                            <?php else: ?>
                                <div class="back-avatar"><?= $initials ?></div>
                            <?php endif; ?>
                            <div class="back-student-name"><?= htmlspecialchars($fullname) ?></div>
                        </div>
                    </td>
                    <td>
                        <span class="back-bureau-badge">
                            <i class="bi bi-building-fill" style="font-size:.7rem;"></i>
                            <?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td style="color:#374151;font-size:.85rem;max-width:180px;">
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($row['motif'] ?? '') ?>">
                            <?= htmlspecialchars($row['motif'] ?? 'Aucun motif') ?>
                        </div>
                    </td>
                    <td>
                        <div class="back-date-cell">
                            <div class="back-date-icon"><i class="bi bi-calendar-event-fill"></i></div>
                            <div>
                                <div class="back-date-text"><?= date('d/m/Y', strtotime($row['date_debut'])) ?></div>
                                <div class="back-date-time"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($row['date_debut'])) ?> - <?= date('H:i', strtotime($row['date_fin'])) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-pill" style="background:<?= $bs['bg'] ?>;color:<?= $bs['color'] ?>;border-color:<?= $bs['border'] ?>;">
                            <span class="status-pill-dot" style="background:<?= $bs['dot'] ?>;"></span>
                            <?= $bs['label'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="back-actions">
                        <?php if ($statut === 'reserve'): ?>
                            <form action="<?= $this->url('/rendezvous/updateStatut/' . $row['id']) ?>" method="post" style="display:inline;">
                                <input type="hidden" name="statut" value="confirme">
                                <button type="submit" class="back-btn back-btn-approve">
                                    <i class="bi bi-check-lg"></i> Approuver
                                </button>
                            </form>
                            <form action="<?= $this->url('/rendezvous/updateStatut/' . $row['id']) ?>" method="post" style="display:inline;">
                                <input type="hidden" name="statut" value="annule">
                                <button type="submit" class="back-btn back-btn-reject">
                                    <i class="bi bi-x-lg"></i> Rejeter
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:.8rem;font-style:italic;margin-right:10px;">—</span>
                        <?php endif; ?>
                            <form action="<?= $this->url('/rendezvous/delete/' . $row['id']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Confirmer la suppression définitive de ce RDV ?');">
                                <button type="submit" class="back-btn back-btn-delete" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
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

<?php else: ?>

<div class="back-stats-row mb-4">
    <span class="back-stat-pill" style="background:#e0f2fe;color:#0369a1;border-color:#bae6fd;">
        <i class="bi bi-building"></i><?= $bureau_kpi['total'] ?? 0 ?> Bureaux
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= $bureau_kpi['actifs'] ?? 0 ?> Actifs
    </span>
    <span class="back-stat-pill" style="background:#f3f4f6;color:#4b5563;border-color:#e5e7eb;">
        <i class="bi bi-pause-circle-fill"></i><?= $bureau_kpi['inactifs'] ?? 0 ?> Inactifs
    </span>
    <span class="back-stat-pill" style="background:#f3e8ff;color:#7e22ce;border-color:#d8b4fe;">
        <i class="bi bi-calendar-check"></i><?= $bureau_kpi['rdv_total'] ?? 0 ?> RDVs Liés
    </span>
</div>

<form method="get" action="<?= $this->url('/rendezvous') ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">
    <input type="hidden" name="tab" value="bureaux">
    <input type="text" name="bq" class="form-control" style="max-width:300px; border-radius:10px; font-size:.9rem; font-weight:600;" placeholder="Recherche bureau..." value="<?= htmlspecialchars($bureau_bq, ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="btn btn-primary" style="border-radius:10px; font-weight:600;"><i class="bi bi-search"></i></button>
    <?php if (!empty($bureau_bq)): ?>
        <a href="<?= $this->url('/rendezvous?tab=bureaux') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Effacer</a>
    <?php endif; ?>
    
    <div class="ms-auto d-flex gap-2">
        <?php
        $exportBureauxQs = [];
        if ($bureau_bq !== '') $exportBureauxQs['bq'] = $bureau_bq;
        $exportBureauxHref = $this->url('/rendezvous/exportBureauxPrint') . ($exportBureauxQs !== [] ? ('?' . http_build_query($exportBureauxQs)) : '');
        ?>
        <a class="btn btn-outline-danger" style="border-radius:10px; font-weight:600; font-size:0.9rem;" href="<?= htmlspecialchars($exportBureauxHref, ENT_QUOTES, 'UTF-8') ?>" target="_blank">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF
        </a>
        <a href="<?= $this->url('/bureaux/createForm') ?>" class="btn btn-primary" style="border-radius:10px; font-weight:600; font-size:0.9rem;">
            <i class="bi bi-plus-lg me-1"></i> Nouveau bureau
        </a>
    </div>
</form>

<div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 g-3">
    <?php if ($bureaux === []): ?>
        <div class="col-12 text-center text-muted py-5">Aucun bureau.</div>
    <?php else: ?>
        <?php foreach ($bureaux as $b): ?>
            <?php
            $bid = (int) ($b['id'] ?? 0);
            $actif = (int) ($b['actif'] ?? 0) === 1;
            $ts = (string) ($b['type_service'] ?? '');
            $bi = $bureauIcon($ts);
            ?>
            <div class="col">
                <div class="card h-100 shadow-sm border-0" style="border-radius:16px; overflow:hidden; background:#fff;">
                    <div class="card-body d-flex flex-column p-4">
                        <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                            <div class="d-flex align-items-center gap-3 min-w-0">
                                <div style="width:48px;height:48px;border-radius:12px;background:rgba(11,42,90,0.06);color:var(--brand);display:flex;align-items:center;justify-content:center;font-size:1.5rem;">
                                    <i class="bi <?= htmlspecialchars($bi, ENT_QUOTES, 'UTF-8') ?>"></i>
                                </div>
                                <h2 class="h6 mb-0 text-truncate fw-bold" style="color:var(--brand);"><?= htmlspecialchars((string) ($b['nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></h2>
                            </div>
                            <?php if ($actif): ?>
                                <span class="badge" style="background:#dcfce7;color:#166534;border-radius:6px;font-size:.7rem;">Actif</span>
                            <?php else: ?>
                                <span class="badge" style="background:#f3f4f6;color:#4b5563;border-radius:6px;font-size:.7rem;">Inactif</span>
                            <?php endif; ?>
                        </div>
                        <div class="small text-muted mb-2 d-flex gap-2 align-items-center">
                            <i class="bi bi-geo-alt-fill text-secondary"></i>
                            <span class="text-truncate"><?= htmlspecialchars(trim((string) ($b['localisation'] ?? '')) !== '' ? (string) $b['localisation'] : '—', ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="small mb-4 d-flex gap-2 align-items-center">
                            <i class="bi bi-tags-fill text-secondary"></i>
                            <span class="text-muted text-truncate"><?= htmlspecialchars($humanType($ts), ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <div class="mt-auto d-flex gap-2">
                            <a class="btn btn-sm flex-grow-1" style="background:var(--brand);color:#fff;border-radius:8px;font-weight:600;" href="<?= $this->url('/bureaux/editForm/' . $bid) ?>">
                                <i class="bi bi-pencil-square me-1"></i>Modifier
                            </a>
                            <form method="post" action="<?= $this->url('/bureaux/delete/' . $bid) ?>" class="flex-shrink-0" onsubmit="return confirm('Supprimer ce bureau ?');">
                                <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:8px;" title="Supprimer"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($bureau_total_pages > 1): ?>
    <div class="d-flex justify-content-end mt-4">
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
