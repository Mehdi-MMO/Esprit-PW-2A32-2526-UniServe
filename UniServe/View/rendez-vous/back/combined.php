<?php
$activeTab = $_GET['tab'] ?? 'appointments';

$badgeStyles = [
    'pending'   => ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047','dot'=>'#eab308','label'=>'En attente'],
    'confirmed' => ['bg'=>'#dcfce7','color'=>'#166534','border'=>'#86efac','dot'=>'#22c55e','label'=>'Confirmé'],
    'cancelled' => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','dot'=>'#ef4444','label'=>'Annulé'],
];

// Icône intelligente selon nom du bureau
function bureauIcon(string $nom): string {
    $n = mb_strtolower($nom);
    if (str_contains($n,'scolar'))  return 'bi-mortarboard-fill';
    if (str_contains($n,'finance')) return 'bi-cash-coin';
    if (str_contains($n,'intern') || str_contains($n,'stage')) return 'bi-briefcase-fill';
    if (str_contains($n,'academ')) return 'bi-book-fill';
    if (str_contains($n,'registr')) return 'bi-file-earmark-text-fill';
    if (str_contains($n,'info'))   return 'bi-pc-display';
    return 'bi-building-fill';
}

// Récupérer les paramètres de filtre (pour rendez-vous)
$search_back = $_GET['search_back'] ?? '';
$status_back = $_GET['status_back'] ?? 'all';
$sort_back = $_GET['sort_back'] ?? 'date_desc';

// Récupérer les paramètres de filtre pour bureaux
$search_bureau = $_GET['search_bureau'] ?? '';

// Récupérer tous les rendez-vous
$allRdvs = [];
$tempStmt = $stmt;
while ($row = $tempStmt->fetch(PDO::FETCH_ASSOC)) {
    $allRdvs[] = $row;
}

// Filtrer par recherche (nom, sujet, bureau)
if (!empty($search_back)) {
    $allRdvs = array_filter($allRdvs, function($r) use ($search_back) {
        return stripos($r['nom_etudiant'], $search_back) !== false || 
               stripos($r['objet'], $search_back) !== false ||
               stripos($r['bureau_nom'] ?? '', $search_back) !== false;
    });
}

// Filtrer par statut
if ($status_back !== 'all') {
    $allRdvs = array_filter($allRdvs, fn($r) => strtolower($r['statut']) === $status_back);
}

// Trier
usort($allRdvs, function($a, $b) use ($sort_back) {
    if ($sort_back === 'date_asc') return strcmp($a['date_rdv'], $b['date_rdv']);
    if ($sort_back === 'date_desc') return strcmp($b['date_rdv'], $a['date_rdv']);
    if ($sort_back === 'nom_asc') return strcmp($a['nom_etudiant'], $b['nom_etudiant']);
    return strcmp($b['date_rdv'], $a['date_rdv']);
});

// Créer un statement filtré
$stmt = new class($allRdvs) {
    private $data; private $i=0;
    public function __construct($d) { $this->data = array_values($d); }
    public function fetch($m=null) { return $this->i < count($this->data) ? $this->data[$this->i++] : false; }
    public function fetchAll($m=null) { return $this->data; }
};

// Calculer statistiques rendez-vous
$total = count($allRdvs);
$pending = count(array_filter($allRdvs, fn($r) => strtolower($r['statut']) === 'pending'));
$confirmed = count(array_filter($allRdvs, fn($r) => strtolower($r['statut']) === 'confirmed'));
$cancelled = count(array_filter($allRdvs, fn($r) => strtolower($r['statut']) === 'cancelled'));

// Récupérer tous les bureaux
$allBureaux = [];
while ($rowB = $stmtBureaux->fetch(PDO::FETCH_ASSOC)) $allBureaux[] = $rowB;

// Filtrer les bureaux par recherche
$filteredBureaux = $allBureaux;
if (!empty($search_bureau)) {
    $filteredBureaux = array_filter($filteredBureaux, function($b) use ($search_bureau) {
        return stripos($b['nom'], $search_bureau) !== false;
    });
}

// Calculer statistiques des bureaux (les plus réservés)
$bureauStats = [];
foreach ($allRdvs as $rdv) {
    $bureauNom = $rdv['bureau_nom'] ?? 'N/A';
    if (!isset($bureauStats[$bureauNom])) {
        $bureauStats[$bureauNom] = 0;
    }
    $bureauStats[$bureauNom]++;
}
arsort($bureauStats);
$topBureaux = array_slice($bureauStats, 0, 5, true);

$palettes = [
    ['bar'=>'#0b2a5a','bg'=>'rgba(11,42,90,.07)','icon'=>'#0b2a5a','border'=>'rgba(11,42,90,.18)'],
    ['bar'=>'#3ecfb2','bg'=>'rgba(62,207,178,.10)','icon'=>'#1a9e86','border'=>'rgba(62,207,178,.28)'],
    ['bar'=>'#9f7a2f','bg'=>'rgba(159,122,47,.09)','icon'=>'#9f7a2f','border'=>'rgba(159,122,47,.25)'],
    ['bar'=>'#6366f1','bg'=>'rgba(99,102,241,.08)','icon'=>'#4f46e5','border'=>'rgba(99,102,241,.20)'],
    ['bar'=>'#ef4444','bg'=>'rgba(239,68,68,.07)','icon'=>'#dc2626','border'=>'rgba(239,68,68,.18)'],
];
?>

<style>
/* ── Shared ── */
.bk-header {
    background:#fff;border-radius:16px;padding:22px 26px;
    margin-bottom:24px;border:1px solid var(--border);
    box-shadow:0 1px 8px rgba(11,42,90,.06);
    display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;
}
.bk-header-left { display:flex;align-items:center;gap:14px; }
.bk-icon {
    width:48px;height:48px;border-radius:14px;flex-shrink:0;
    display:grid;place-items:center;font-size:1.25rem;color:#fff;
    box-shadow:0 4px 14px rgba(11,42,90,.22);
}
.bk-title { font-size:1.1rem;font-weight:800;color:var(--brand);margin-bottom:2px; }
.bk-sub   { font-size:.78rem;color:var(--text-muted); }

/* ── Tabs ── */
.bk-tabs { display:flex;gap:4px;margin-bottom:22px;background:#fff;padding:5px;border-radius:12px;border:1px solid var(--border); width:fit-content; }
.bk-tab  {
    display:inline-flex;align-items:center;gap:7px;padding:8px 18px;border-radius:9px;
    font-size:.82rem;font-weight:700;color:var(--text-muted);
    text-decoration:none;transition:all .18s;
}
.bk-tab:hover { color:var(--brand);background:rgba(11,42,90,.05); }
.bk-tab.active { background:var(--brand);color:#fff;box-shadow:0 3px 10px rgba(11,42,90,.22); }
.bk-tab-badge {
    background:rgba(255,255,255,.22);color:inherit;border-radius:999px;
    padding:1px 7px;font-size:.7rem;font-weight:800;
}
.bk-tab:not(.active) .bk-tab-badge { background:rgba(11,42,90,.09);color:var(--brand); }

/* ── Table ── */
.bk-card { background:#fff;border-radius:16px;border:1px solid var(--border);box-shadow:0 1px 8px rgba(11,42,90,.06);overflow:hidden; }
.bk-table { width:100%;border-collapse:collapse; }
.bk-table thead tr { background:linear-gradient(90deg,#0b2a5a,#1565c0); }
.bk-table thead th {
    color:rgba(255,255,255,.9);font-size:.7rem;font-weight:700;
    text-transform:uppercase;letter-spacing:.6px;
    padding:13px 16px;white-space:nowrap;border:none;
}
.bk-table thead th:first-child { padding-left:22px; }
.bk-table tbody tr { border-bottom:1px solid rgba(11,42,90,.055);transition:background .14s; }
.bk-table tbody tr:last-child { border-bottom:none; }
.bk-table tbody tr:hover { background:rgba(11,42,90,.022); }
.bk-table td { padding:13px 16px;font-size:.855rem;vertical-align:middle;border:none; }
.bk-table td:first-child { padding-left:22px; }

.bk-avatar {
    width:34px;height:34px;border-radius:10px;
    background:linear-gradient(135deg,#e8eef8,#c7d8f5);
    color:#0b2a5a;font-size:.78rem;font-weight:800;
    display:grid;place-items:center;flex-shrink:0;
}
.bk-bureau-tag {
    display:inline-flex;align-items:center;gap:5px;
    background:rgba(11,42,90,.06);color:#0b2a5a;
    border-radius:8px;padding:3px 9px;font-size:.74rem;font-weight:600;
}
.bk-date-box {
    width:28px;height:28px;border-radius:7px;
    background:rgba(11,42,90,.06);color:#0b2a5a;
    display:grid;place-items:center;font-size:.75rem;flex-shrink:0;
}
.status-pill {
    display:inline-flex;align-items:center;gap:5px;
    padding:4px 11px;border-radius:999px;
    font-size:.7rem;font-weight:800;letter-spacing:.15px;border:1.5px solid;
}
.s-dot { width:6px;height:6px;border-radius:50%; }

.bk-btn {
    display:inline-flex;align-items:center;gap:5px;
    padding:5px 12px;border-radius:8px;font-size:.76rem;font-weight:700;
    border:1.5px solid;text-decoration:none;transition:all .15s;
}
.bk-btn:hover { transform:translateY(-1px);filter:brightness(.92); }
.bk-approve { background:#dcfce7;color:#166534;border-color:#86efac; }
.bk-reject  { background:#fee2e2;color:#991b1b;border-color:#fca5a5; }
.bk-edit    { background:rgba(11,42,90,.07);color:#0b2a5a;border-color:rgba(11,42,90,.18); }
.bk-delete  { background:#fee2e2;color:#991b1b;border-color:#fca5a5; }

.bk-bureau-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(270px,1fr));gap:16px; }
.bk-bureau-card {
    background:#fff;border-radius:14px;border:1px solid var(--border);
    overflow:hidden;transition:transform .18s,box-shadow .18s,border-color .18s;
}
.bk-bureau-card:hover { transform:translateY(-3px);box-shadow:0 10px 24px rgba(11,42,90,.10);border-color:rgba(11,42,90,.20); }
.bk-bureau-bar { height:4px; }
.bk-bureau-body { padding:18px 20px; }
.bk-bureau-icon-wrap {
    width:44px;height:44px;border-radius:12px;flex-shrink:0;
    display:grid;place-items:center;font-size:1.1rem;
}
.bk-bureau-name { font-size:.92rem;font-weight:800;color:var(--text); }
.bk-bureau-status {
    font-size:.65rem;font-weight:700;letter-spacing:.2px;
    padding:2px 8px;border-radius:999px;border:1px solid;margin-top:2px;display:inline-block;
}
.bk-info-row { display:flex;align-items:center;gap:8px;font-size:.8rem;color:var(--text-muted); }
.bk-info-icon {
    width:24px;height:24px;border-radius:6px;
    background:rgba(11,42,90,.06);color:#0b2a5a;
    display:grid;place-items:center;font-size:.65rem;flex-shrink:0;
}
.bk-bureau-actions { display:flex;gap:8px;margin-top:14px; }
.bk-bureau-actions .bk-btn { flex:1;justify-content:center;border-radius:9px; }

.bk-empty { text-align:center;padding:56px 20px; }
.bk-empty-icon {
    width:54px;height:54px;border-radius:16px;
    background:rgba(11,42,90,.06);color:#0b2a5a;
    display:grid;place-items:center;font-size:1.4rem;margin:0 auto 12px;
}

/* Statistiques bureaux - Design simple et professionnel */
.stats-card-simple {
    background:#fff;border-radius:12px;padding:12px 16px;
    margin-bottom:20px;border:1px solid var(--border);
    display:flex;align-items:center;justify-content:space-between;
    flex-wrap:wrap;gap:12px;
}
.stats-title-simple {
    display:flex;align-items:center;gap:8px;
}
.stats-title-simple i { font-size:1rem;color:#eab308; }
.stats-title-simple span { font-weight:600;color:var(--brand);font-size:.8rem; }
.stats-list-simple {
    display:flex;align-items:center;gap:12px;flex-wrap:wrap;
}
.stats-item-simple {
    display:flex;align-items:center;gap:6px;
    background:#f8f9fa;padding:5px 14px;border-radius:20px;
}
.stats-name-simple { font-size:.75rem;font-weight:500;color:#374151; }
.stats-count-simple { 
    background:var(--brand);color:#fff;padding:2px 8px;
    border-radius:20px;font-size:.65rem;font-weight:700;
}

/* Impression PDF */
@media print {
    .sidebar, .top-header, .bk-tabs, .bk-actions, .btn, button, form, .btn-outline-danger, .bk-bureau-actions {
        display: none !important;
    }
    .ms-sidebar { margin-left: 0 !important; }
    body { padding: 0; margin: 0; }
    .bk-table th, .bk-table td { border: 1px solid #ddd !important; }
}
</style>

<?php
$rows = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = $row;
}
?>

<!-- Alertes -->
<?php if (!empty($error) && $error === 'has_rdv'): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    Impossible de supprimer : <strong><?= (int)$count ?> rendez-vous actif(s)</strong> liés à ce bureau.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($success)):
    $msgs=['created'=>'Bureau ajouté.','updated'=>'Bureau mis à jour.','deleted'=>'Bureau supprimé.'];
?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msgs[$success] ?? 'Opération réussie.') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($activeTab === 'appointments'): ?>
<!-- === ONGLET RENDEZ-VOUS === -->
<div class="bk-header">
    <div class="bk-header-left">
        <div class="bk-icon" style="background:linear-gradient(135deg,#0b2a5a,#1565c0);">
            <i class="bi bi-calendar-check"></i>
        </div>
        <div>
            <div class="bk-title">Gestion des Rendez-vous</div>
            <div class="bk-sub">Gérez les rendez-vous depuis cette page.</div>
        </div>
    </div>
</div>

<!-- Barre d'outils filtres -->
<div style="background:#fff;border-radius:12px;padding:15px;margin-bottom:20px;border:1px solid var(--border);">
    <form method="GET" action="index.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="page" value="back">
        <input type="hidden" name="module" value="appointments">
        <input type="hidden" name="tab" value="appointments">
        
        <div style="display:flex;flex-direction:column;gap:5px;flex:2;">
            <label style="font-size:11px;font-weight:700;">🔍 Rechercher (nom, sujet, bureau)</label>
            <input type="text" name="search_back" class="form-control form-control-sm" placeholder="Nom, sujet ou bureau..." style="min-width:250px;" value="<?= htmlspecialchars($search_back) ?>">
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px;">
            <label style="font-size:11px;font-weight:700;">📊 Statut</label>
            <select name="status_back" class="form-select form-select-sm" style="width:130px;">
                <option value="all" <?= $status_back === 'all' ? 'selected' : '' ?>>Tous</option>
                <option value="pending" <?= $status_back === 'pending' ? 'selected' : '' ?>>En attente</option>
                <option value="confirmed" <?= $status_back === 'confirmed' ? 'selected' : '' ?>>Confirmé</option>
                <option value="cancelled" <?= $status_back === 'cancelled' ? 'selected' : '' ?>>Annulé</option>
            </select>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px;">
            <label style="font-size:11px;font-weight:700;">📋 Trier par</label>
            <select name="sort_back" class="form-select form-select-sm" style="width:160px;">
                <option value="date_desc" <?= $sort_back === 'date_desc' ? 'selected' : '' ?>>Date (récent → ancien)</option>
                <option value="date_asc" <?= $sort_back === 'date_asc' ? 'selected' : '' ?>>Date (ancien → récent)</option>
                <option value="nom_asc" <?= $sort_back === 'nom_asc' ? 'selected' : '' ?>>Nom (A→Z)</option>
            </select>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px;">
            <label style="font-size:11px;font-weight:700;">&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-sm">Appliquer</button>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px;margin-left:auto;">
            <label style="font-size:11px;font-weight:700;">&nbsp;</label>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.print()"><i class="bi bi-file-pdf"></i> Export PDF</button>
        </div>
    </form>
</div>

<!-- Cartes statistiques rendez-vous -->
<div style="display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;">
    <div style="flex:1;background:#f8f9fa;border-radius:12px;padding:15px;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#0b2a5a;"><?= $total ?></div>
        <div style="font-size:12px;color:#667085;">Total</div>
    </div>
    <div style="flex:1;background:#fef9c3;border-radius:12px;padding:15px;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#eab308;"><?= $pending ?></div>
        <div style="font-size:12px;color:#854d0e;">En attente</div>
    </div>
    <div style="flex:1;background:#dcfce7;border-radius:12px;padding:15px;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#22c55e;"><?= $confirmed ?></div>
        <div style="font-size:12px;color:#166534;">Confirmés</div>
    </div>
    <div style="flex:1;background:#fee2e2;border-radius:12px;padding:15px;text-align:center;">
        <div style="font-size:28px;font-weight:800;color:#ef4444;"><?= $cancelled ?></div>
        <div style="font-size:12px;color:#991b1b;">Annulés</div>
    </div>
</div>

<!-- Onglets -->
<div class="bk-tabs">
    <a class="bk-tab active" href="index.php?page=back&module=appointments&tab=appointments&search_back=<?= urlencode($search_back) ?>&status_back=<?= urlencode($status_back) ?>&sort_back=<?= urlencode($sort_back) ?>">
        <i class="bi bi-calendar-check"></i>Rendez-vous
        <span class="bk-tab-badge"><?= count($rows) ?></span>
    </a>
    <a class="bk-tab" href="index.php?page=back&module=appointments&tab=offices">
        <i class="bi bi-building"></i>Bureaux
        <span class="bk-tab-badge"><?= count($allBureaux) ?></span>
    </a>
</div>

<!-- TABLEAU RENDEZ-VOUS -->
<div class="bk-card">
    <div class="table-responsive">
        <table class="bk-table">
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
            <?php if (empty($rows)): ?>
                <tr><td colspan="6">
                    <div class="bk-empty">
                        <div class="bk-empty-icon"><i class="bi bi-calendar-x"></i></div>
                        <div style="font-weight:700;margin-bottom:4px;">Aucun rendez-vous trouvé</div>
                        <div style="font-size:.8rem;color:var(--text-muted);">Aucune demande ne correspond à vos critères.</div>
                    </div>
                </td></tr>
            <?php else: foreach ($rows as $row):
                $statut = strtolower($row['statut']);
                $bs = $badgeStyles[$statut] ?? $badgeStyles['pending'];
                $initials = strtoupper(mb_substr($row['nom_etudiant'], 0, 1));
                
                $params = [];
                if (!empty($search_back)) $params['search_back'] = $search_back;
                if ($status_back !== 'all') $params['status_back'] = $status_back;
                if ($sort_back !== 'date_desc') $params['sort_back'] = $sort_back;
                $query = http_build_query(array_merge(['page'=>'back','module'=>'appointments','tab'=>'appointments'], $params));
            ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bk-avatar"><?= $initials ?></div>
                            <span style="font-weight:700;color:#111827;"><?= htmlspecialchars($row['nom_etudiant']) ?></span>
                        </div>
                    </td>
                    <td>
                        <span class="bk-bureau-tag">
                            <i class="bi bi-building-fill" style="font-size:.65rem;"></i>
                            <?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td style="color:#374151;max-width:200px;">
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($row['objet']) ?>">
                            <?= htmlspecialchars($row['objet']) ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bk-date-box"><i class="bi bi-calendar-event-fill"></i></div>
                            <div>
                                <div style="font-size:.82rem;font-weight:600;color:#374151;"><?= date('d/m/Y', strtotime($row['date_rdv'])) ?></div>
                                <div style="font-size:.72rem;color:var(--text-muted);"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($row['heure_rdv']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-pill" style="background:<?= $bs['bg'] ?>;color:<?= $bs['color'] ?>;border-color:<?= $bs['border'] ?>;">
                            <span class="s-dot" style="background:<?= $bs['dot'] ?>;"></span>
                            <?= $bs['label'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($statut === 'pending'): ?>
                        <div class="d-flex gap-2">
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=confirmed&<?= $query ?>"
                               class="bk-btn bk-approve"><i class="bi bi-check-lg"></i>Approuver</a>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=cancelled&<?= $query ?>"
                               class="bk-btn bk-reject"><i class="bi bi-x-lg"></i>Rejeter</a>
                        </div>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-style:italic;font-size:.8rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<!-- === ONGLET BUREAUX === -->

<div class="bk-header">
    <div class="bk-header-left">
        <div class="bk-icon" style="background:linear-gradient(135deg,#0b2a5a,#1565c0);">
            <i class="bi bi-building"></i>
        </div>
        <div>
            <div class="bk-title">Gestion des Bureaux</div>
            <div class="bk-sub">Gérez les bureaux disponibles pour les rendez-vous.</div>
        </div>
    </div>
    <div>
        <a href="index.php?page=back&module=offices&action=create" class="btn btn-primary btn-sm px-3">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un bureau
        </a>
    </div>
</div>

<!-- Barre de recherche pour bureaux -->
<div style="background:#fff;border-radius:12px;padding:15px;margin-bottom:20px;border:1px solid var(--border);">
    <form method="GET" action="index.php" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="page" value="back">
        <input type="hidden" name="module" value="appointments">
        <input type="hidden" name="tab" value="offices">
        
        <div style="display:flex;flex-direction:column;gap:5px;flex:2;">
            <label style="font-size:11px;font-weight:700;">🔍 Rechercher un bureau par nom</label>
            <input type="text" name="search_bureau" class="form-control form-control-sm" placeholder="Nom du bureau..." style="min-width:250px;" value="<?= htmlspecialchars($search_bureau) ?>">
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px;">
            <label style="font-size:11px;font-weight:700;">&nbsp;</label>
            <button type="submit" class="btn btn-primary btn-sm">Rechercher</button>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px;margin-left:auto;">
            <label style="font-size:11px;font-weight:700;">&nbsp;</label>
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="window.print()"><i class="bi bi-file-pdf"></i> Export PDF</button>
        </div>
    </form>
</div>

<!-- Statistiques des bureaux - DESIGN SIMPLE SANS STICKERS -->
<?php if (!empty($topBureaux)): ?>
<div class="stats-card-simple">
    <div class="stats-title-simple">
        <i class="bi bi-bar-chart-fill"></i>
        <span>Les bureaux les plus réservés</span>
    </div>
    <div class="stats-list-simple">
        <?php foreach ($topBureaux as $bureauNom => $count): ?>
        <div class="stats-item-simple">
            <span class="stats-name-simple"><?= htmlspecialchars(mb_substr($bureauNom, 0, 25)) . (mb_strlen($bureauNom) > 25 ? '...' : '') ?></span>
            <span class="stats-count-simple"><?= $count ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Onglets -->
<div class="bk-tabs">
    <a class="bk-tab" href="index.php?page=back&module=appointments&tab=appointments">
        <i class="bi bi-calendar-check"></i>Rendez-vous
        <span class="bk-tab-badge"><?= count($rows) ?></span>
    </a>
    <a class="bk-tab active" href="index.php?page=back&module=appointments&tab=offices">
        <i class="bi bi-building"></i>Bureaux
        <span class="bk-tab-badge"><?= count($filteredBureaux) ?></span>
    </a>
</div>

<?php if (empty($filteredBureaux)): ?>
<div class="bk-card">
    <div class="bk-empty">
        <div class="bk-empty-icon"><i class="bi bi-building"></i></div>
        <div style="font-weight:700;margin-bottom:4px;">Aucun bureau trouvé</div>
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:16px;">Aucun bureau ne correspond à votre recherche.</div>
        <a href="index.php?page=back&module=offices&action=create" class="btn btn-primary btn-sm px-4">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un bureau
        </a>
    </div>
</div>
<?php else: ?>
<div class="bk-bureau-grid">
<?php foreach ($filteredBureaux as $i => $rowB):
    $p = $palettes[$i % count($palettes)];
    $icon = bureauIcon($rowB['nom']);
?>
    <div class="bk-bureau-card">
        <div class="bk-bureau-bar" style="background:linear-gradient(90deg,<?= $p['bar'] ?>,<?= $p['bar'] ?>88);"></div>
        <div class="bk-bureau-body">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="bk-bureau-icon-wrap" style="background:<?= $p['bg'] ?>;border:1px solid <?= $p['border'] ?>;">
                    <i class="bi <?= $icon ?>" style="color:<?= $p['icon'] ?>;"></i>
                </div>
                <div>
                    <div class="bk-bureau-name"><?= htmlspecialchars($rowB['nom']) ?></div>
                    <span class="bk-bureau-status" style="background:<?= $p['bg'] ?>;color:<?= $p['icon'] ?>;border-color:<?= $p['border'] ?>;">
                        Actif
                    </span>
                </div>
            </div>

            <div style="height:1px;background:var(--border);margin-bottom:12px;"></div>

            <div class="d-flex flex-column gap-2 mb-3">
                <div class="bk-info-row">
                    <div class="bk-info-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <span class="text-truncate" title="<?= htmlspecialchars($rowB['localisation']) ?>">
                        <?= htmlspecialchars($rowB['localisation']) ?>
                    </span>
                </div>
                <div class="bk-info-row">
                    <div class="bk-info-icon"><i class="bi bi-person-fill"></i></div>
                    <span class="text-truncate">
                        <?= !empty($rowB['responsable']) ? htmlspecialchars($rowB['responsable']) : '<em>Non assigné</em>' ?>
                    </span>
                </div>
            </div>

            <div class="bk-bureau-actions">
                <a href="index.php?page=back&module=offices&action=edit&id=<?= $rowB['id'] ?>"
                   class="bk-btn bk-edit">
                    <i class="bi bi-pencil-fill"></i>Modifier
                </a>
                <a href="index.php?page=back&module=offices&action=delete&id=<?= $rowB['id'] ?>"
                   class="bk-btn bk-delete"
                   onclick="return confirm('Supprimer « <?= htmlspecialchars(addslashes($rowB['nom'])) ?> » ?')">
                    <i class="bi bi-trash-fill"></i>
                </a>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>