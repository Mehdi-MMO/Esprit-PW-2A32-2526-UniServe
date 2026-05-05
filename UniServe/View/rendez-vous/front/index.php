<?php
function getBureauIcon(string $nom): string {
    $nom = mb_strtolower($nom);
    if (str_contains($nom, 'scolar'))                                  return 'bi-mortarboard-fill';
    if (str_contains($nom, 'finance') || str_contains($nom, 'compt')) return 'bi-cash-coin';
    if (str_contains($nom, 'stage')   || str_contains($nom, 'intern'))return 'bi-briefcase-fill';
    if (str_contains($nom, 'academ'))                                  return 'bi-book-fill';
    if (str_contains($nom, 'registr'))                                 return 'bi-file-earmark-text-fill';
    if (str_contains($nom, 'biblio'))                                  return 'bi-journals';
    if (str_contains($nom, 'sport'))                                   return 'bi-trophy-fill';
    if (str_contains($nom, 'info')    || str_contains($nom, 'tech'))  return 'bi-pc-display';
    if (str_contains($nom, 'rh')      || str_contains($nom, 'human')) return 'bi-people-fill';
    if (str_contains($nom, 'droit')   || str_contains($nom, 'jurid')) return 'bi-shield-fill';
    if (str_contains($nom, 'medecin') || str_contains($nom, 'sante')) return 'bi-heart-pulse-fill';
    return 'bi-building-fill';
}

$palettes = [
    ['light'=>'#e8eef8','main'=>'#0b2a5a','dark'=>'#07193a','bar'=>'#0b2a5a','soft'=>'rgba(11,42,90,0.06)'],
    ['light'=>'#e0f7f3','main'=>'#1a9e86','dark'=>'#127a68','bar'=>'#3ecfb2','soft'=>'rgba(62,207,178,0.08)'],
    ['light'=>'#f7f0e0','main'=>'#9f7a2f','dark'=>'#7a5c1e','bar'=>'#c49a3c','soft'=>'rgba(159,122,47,0.07)'],
    ['light'=>'#eeeef9','main'=>'#4f46e5','dark'=>'#3730a3','bar'=>'#6366f1','soft'=>'rgba(99,102,241,0.07)'],
    ['light'=>'#fdeaea','main'=>'#dc2626','dark'=>'#b91c1c','bar'=>'#ef4444','soft'=>'rgba(239,68,68,0.06)'],
    ['light'=>'#e0f5ec','main'=>'#065f46','dark'=>'#044034','bar'=>'#10b981','soft'=>'rgba(16,185,129,0.07)'],
];

$rdvPalettes = [
    ['light'=>'#e8eef8','main'=>'#0b2a5a','bar'=>'#0b2a5a','soft'=>'rgba(11,42,90,0.05)'],
    ['light'=>'#e0f7f3','main'=>'#1a9e86','bar'=>'#3ecfb2','soft'=>'rgba(62,207,178,0.07)'],
    ['light'=>'#f7f0e0','main'=>'#9f7a2f','bar'=>'#c49a3c','soft'=>'rgba(159,122,47,0.06)'],
    ['light'=>'#eeeef9','main'=>'#4f46e5','bar'=>'#6366f1','soft'=>'rgba(99,102,241,0.06)'],
    ['light'=>'#fdeaea','main'=>'#dc2626','bar'=>'#ef4444','soft'=>'rgba(239,68,68,0.05)'],
    ['light'=>'#e0f5ec','main'=>'#065f46','bar'=>'#10b981','soft'=>'rgba(16,185,129,0.06)'],
];

$statusConfig = [
    'pending'   => ['label'=>'En attente',  'icon'=>'bi-hourglass-split',  'bg'=>'#fff8e1','color'=>'#b45309','border'=>'#fde68a'],
    'confirmed' => ['label'=>'Confirmé',    'icon'=>'bi-check-circle-fill','bg'=>'#dcfce7','color'=>'#166534','border'=>'#bbf7d0'],
    'cancelled' => ['label'=>'Annulé',      'icon'=>'bi-x-circle-fill',   'bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fecaca'],
];

$statusPalettes = [
    'confirmed' => ['light'=>'#dcfce7','main'=>'#166534','bar'=>'#22c55e','soft'=>'rgba(34,197,94,0.10)'],
    'pending'   => ['light'=>'#fef9c3','main'=>'#854d0e','bar'=>'#eab308','soft'=>'rgba(234,179,8,0.10)'],
    'cancelled' => ['light'=>'#fee2e2','main'=>'#991b1b','bar'=>'#ef4444','soft'=>'rgba(239,68,68,0.10)'],
];

$notification = $_SESSION['notification'] ?? null;
if ($notification) unset($_SESSION['notification']);

$search_front = $_GET['search_front'] ?? '';

// ── Pagination params ──
$perPageB  = 6;
$perPageR  = 6;
$pageB     = max(1, (int)($_GET['page_b'] ?? 1));
$pageR     = max(1, (int)($_GET['page_r'] ?? 1));

// ── Collecter & filtrer bureaux ──
$allBureaux = [];
while ($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)) $allBureaux[] = $b;

$filteredBureaux = $allBureaux;
if (!empty($search_front)) {
    $filteredBureaux = array_filter($filteredBureaux, function($b) use ($search_front) {
        return stripos($b['nom'], $search_front) !== false
            || stripos($b['localisation'], $search_front) !== false;
    });
}
$filteredBureaux = array_values($filteredBureaux);
$totalB          = count($filteredBureaux);
$totalPagesB     = max(1, (int)ceil($totalB / $perPageB));
$pageB           = min($pageB, $totalPagesB);
$pagedBureaux    = array_slice($filteredBureaux, ($pageB - 1) * $perPageB, $perPageB);

// ── Collecter rdv ──
$allRdv = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $allRdv[] = $r;
$totalR      = count($allRdv);
$totalPagesR = max(1, (int)ceil($totalR / $perPageR));
$pageR       = min($pageR, $totalPagesR);
$pagedRdv    = array_slice($allRdv, ($pageR - 1) * $perPageR, $perPageR);

// ── Stats ──
$statsTotal     = $totalR;
$statsConfirmes = count(array_filter($allRdv, fn($r) => strtolower($r['statut']) === 'confirmed'));
$taux           = $statsTotal > 0 ? round(($statsConfirmes / $statsTotal) * 100) : 0;

// ── Helper URL ──
// page_b et page_r ne se croisent jamais pour éviter l'ouverture automatique non souhaitée
function frontUrl(array $extra = []): string {
    $param = (string)array_key_first($extra);
    $base  = [];
    // Toujours conserver la recherche
    if (!empty($_GET['search_front'])) $base['search_front'] = $_GET['search_front'];
    // Pagination bureaux : on n'inclut JAMAIS page_r
    // Pagination RDV     : on n'inclut JAMAIS page_b
    if ($param === 'page_b' && isset($_GET['page_b'])) {
        // rien à ajouter, page_b sera dans $extra
    } elseif ($param === 'page_r' && isset($_GET['page_r'])) {
        // rien à ajouter, page_r sera dans $extra
    }
    return 'index.php?' . http_build_query(array_merge($base, $extra));
}

// ── Helper pagination ──
function frontPager(int $current, int $total, string $param): string {
    if ($total <= 1) return '';
    $h  = '<div class="front-pagination">';
    // Précédent
    $h .= '<a href="' . frontUrl([$param => max(1, $current - 1)]) . '" class="fp-btn' . ($current <= 1 ? ' disabled' : '') . '">'
        . '<i class="bi bi-chevron-left"></i></a>';
    // Numéros de page (avec ellipsis si > 7 pages)
    for ($i = 1; $i <= $total; $i++) {
        if ($total > 7 && abs($i - $current) > 2 && $i !== 1 && $i !== $total) {
            if ($i === 2 || $i === $total - 1) { $h .= '<span class="fp-btn" style="pointer-events:none;border:none;color:#9ca3af;">…</span>'; }
            continue;
        }
        $h .= '<a href="' . frontUrl([$param => $i]) . '" class="fp-btn' . ($i === $current ? ' active' : '') . '">' . $i . '</a>';
    }
    // Suivant
    $h .= '<a href="' . frontUrl([$param => min($total, $current + 1)]) . '" class="fp-btn' . ($current >= $total ? ' disabled' : '') . '">'
        . '<i class="bi bi-chevron-right"></i></a>';
    $h .= '</div>';
    return $h;
}
?>

<style>
.bureau-card{background:#fff;border-radius:18px;overflow:hidden;border:1px solid rgba(0,0,0,0.07);box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:transform .22s ease,box-shadow .22s ease;height:100%;}
.bureau-card:hover{transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,0.12);}
.bureau-card-header{padding:22px 22px 16px;position:relative;}
.bureau-card-icon{width:54px;height:54px;border-radius:15px;display:inline-grid;place-items:center;font-size:1.4rem;flex-shrink:0;box-shadow:0 3px 10px rgba(0,0,0,0.12);}
.bureau-card-body{padding:0 22px 20px;}
.bureau-detail-row{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;margin-bottom:8px;}
.bureau-detail-icon{width:32px;height:32px;border-radius:9px;display:inline-grid;place-items:center;flex-shrink:0;font-size:.9rem;}
.bureau-detail-text{font-size:.82rem;font-weight:500;color:#4b5563;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.bureau-btn{display:flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:10px;border-radius:12px;font-size:.85rem;font-weight:700;letter-spacing:.3px;border:none;cursor:pointer;transition:all .18s;text-decoration:none;margin-top:16px;}
.bureau-btn:hover{filter:brightness(0.92);transform:translateY(-1px);}
.disponible-dot{display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:999px;}
.rdv-card{background:#fff;border-radius:18px;overflow:hidden;border:1px solid rgba(0,0,0,0.07);box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:transform .22s ease,box-shadow .22s ease;height:100%;}
.rdv-card:hover{transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,0.12);}
.rdv-card-header{padding:18px 20px 14px;}
.rdv-card-body{padding:0 20px 18px;}
.rdv-detail-row{display:flex;align-items:center;gap:9px;padding:8px 11px;border-radius:10px;margin-bottom:7px;}
.rdv-detail-icon{width:30px;height:30px;border-radius:8px;display:inline-grid;place-items:center;flex-shrink:0;font-size:.85rem;}
.rdv-detail-text{font-size:.81rem;font-weight:500;color:#4b5563;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.rdv-status-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:999px;font-size:.72rem;font-weight:800;letter-spacing:.3px;border:1px solid;}
.btn-hero{display:inline-flex;align-items:center;gap:9px;padding:13px 32px;border-radius:50px;font-size:.95rem;font-weight:700;letter-spacing:.3px;border:none;cursor:pointer;transition:transform .22s ease,box-shadow .22s ease;text-decoration:none;}
.btn-hero:hover{transform:translateY(-3px);}
.btn-hero-primary{background:linear-gradient(135deg,#0b2a5a 0%,#1565c0 100%);color:#fff;box-shadow:0 4px 18px rgba(11,42,90,0.28);}
.btn-hero-primary:hover{box-shadow:0 8px 28px rgba(11,42,90,0.38);color:#fff;}
.btn-hero-teal{background:linear-gradient(135deg,#0b2a5a 0%,#1a9e86 100%);color:#fff;box-shadow:0 4px 18px rgba(26,158,134,0.28);}
.btn-hero-teal:hover{box-shadow:0 8px 28px rgba(26,158,134,0.38);color:#fff;}
.stats-front-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:25px;}
.stats-front-card{background:#fff;border-radius:16px;padding:18px 12px;text-align:center;border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,0.04);transition:transform 0.2s;}
.stats-front-card:hover{transform:translateY(-3px);}
.stats-front-number{font-size:28px;font-weight:800;color:var(--brand);}
.stats-front-label{font-size:12px;color:var(--text-muted);margin-top:4px;}
@keyframes fadeSlideDown{from{opacity:0;transform:translateY(-16px);}to{opacity:1;transform:translateY(0);}}
.anim-fade{animation:fadeSlideDown .32s ease;}

/* ── Pagination ── */
.front-pagination{display:flex;align-items:center;justify-content:center;gap:6px;margin-top:28px;flex-wrap:wrap;}
.fp-btn{display:inline-grid;place-items:center;min-width:36px;height:36px;padding:0 4px;border-radius:10px;font-size:.82rem;font-weight:700;border:1.5px solid var(--border);background:#fff;color:var(--text-muted);text-decoration:none;transition:all .15s;}
.fp-btn:hover{border-color:var(--brand);color:var(--brand);background:rgba(11,42,90,.05);}
.fp-btn.active{background:var(--brand);color:#fff;border-color:var(--brand);box-shadow:0 3px 10px rgba(11,42,90,.22);}
.fp-btn.disabled{opacity:.35;pointer-events:none;}
.fp-info{font-size:.78rem;color:var(--text-muted);text-align:center;margin-top:8px;}
.fp-info strong{color:var(--brand);}

@media(max-width:576px){
    .stats-front-grid{grid-template-columns:repeat(2,1fr);}
    .front-pagination{gap:4px;}
    .fp-btn{min-width:32px;height:32px;font-size:.78rem;}
}
@media print{.navbar,.dropdown,.btn-hero,.btn,.search-bar-front,.btn-outline-danger,.front-pagination{display:none!important;}body{padding-top:0!important;}.stats-front-card{border:1px solid #ddd;}}
</style>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>Votre demande de rendez-vous a été soumise avec succès !
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($notification)): ?>
<div class="alert alert-<?= $notification['type']==='success'?'success':'danger' ?> alert-dismissible fade show mb-4" role="alert"
     style="border-left:4px solid <?= $notification['type']==='success'?'#22c55e':'#ef4444' ?>;border-radius:12px;">
    <div class="d-flex align-items-center gap-3">
        <i class="bi <?= $notification['type']==='success'?'bi-check-circle-fill':'bi-x-circle-fill' ?>"
           style="font-size:1.8rem;color:<?= $notification['type']==='success'?'#22c55e':'#ef4444' ?>;"></i>
        <div>
            <h5 class="alert-heading mb-1" style="font-weight:700;"><?= htmlspecialchars($notification['title']) ?></h5>
            <p class="mb-0"><?= htmlspecialchars($notification['message']) ?></p>
        </div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="us-hero rounded-3 p-5 mb-4 text-center">
    <h1 class="fw-bold mb-2" style="color:var(--brand)">Réservez votre rendez-vous<br>en quelques secondes</h1>
    <p class="text-muted mb-4">Choisissez un bureau, sélectionnez une date et évitez les files d'attente.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="index.php?action=book" class="btn-hero btn-hero-primary">
            <i class="bi bi-calendar-plus" style="font-size:1.1rem"></i>Réserver maintenant
        </a>
        <button class="btn-hero btn-hero-teal" onclick="toggleRdv()" id="btnRdv">
            <i class="bi bi-clock-history" style="font-size:1.1rem"></i>
            Rendez-vous récents <i class="bi bi-chevron-down" id="iconRdv"></i>
        </button>
    </div>
</div>

<!-- Stats -->
<div class="stats-front-grid">
    <div class="stats-front-card"><div class="stats-front-number"><?= $statsTotal ?></div><div class="stats-front-label">Total RDV</div></div>
    <div class="stats-front-card"><div class="stats-front-number" style="color:#22c55e;"><?= $statsConfirmes ?></div><div class="stats-front-label">Confirmés</div></div>
    <div class="stats-front-card"><div class="stats-front-number" style="color:#eab308;"><?= $taux ?>%</div><div class="stats-front-label">Taux succès</div></div>
    <div class="stats-front-card"><div class="stats-front-number" style="color:#0b2a5a;"><?= count($allBureaux) ?></div><div class="stats-front-label">Bureaux actifs</div></div>
</div>

<?php
// Préparer les données bureaux pour la carte (JSON)
$bureauxMapData = [];
// Coordonnées fictives de campus — on distribue les bureaux sur un plan réaliste
$campusCoords = [
    [36.8190, 10.1660],
    [36.8195, 10.1668],
    [36.8185, 10.1672],
    [36.8200, 10.1655],
    [36.8182, 10.1650],
    [36.8198, 10.1675],
    [36.8188, 10.1680],
    [36.8205, 10.1663],
];
$palettesMap = ['#0b2a5a','#1a9e86','#9f7a2f','#4f46e5','#dc2626','#065f46','#c2410c','#7c3aed'];
foreach ($allBureaux as $idx => $b) {
    $coords = $campusCoords[$idx % count($campusCoords)];
    $bureauxMapData[] = [
        'id'          => $b['id'],
        'nom'         => $b['nom'],
        'localisation'=> $b['localisation'],
        'lat'         => $coords[0] + ($idx * 0.00015),
        'lng'         => $coords[1] + ($idx * 0.00012),
        'color'       => $palettesMap[$idx % count($palettesMap)],
        'bookUrl'     => 'index.php?action=book&bureau_id=' . (int)$b['id'],
    ];
}
$bureauxMapJson = json_encode($bureauxMapData, JSON_UNESCAPED_UNICODE);
?>

<!-- ══ CARTE INTERACTIVE ══ -->
<div style="background:#fff;border-radius:18px;border:1px solid rgba(11,42,90,0.10);box-shadow:0 2px 14px rgba(11,42,90,0.07);overflow:hidden;margin-bottom:28px;">

    <!-- En-tête carte -->
    <div style="padding:16px 22px;border-bottom:1px solid rgba(11,42,90,0.07);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:12px;">
            <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#0b2a5a,#1a9e86);display:grid;place-items:center;color:#fff;font-size:1rem;flex-shrink:0;">
                <i class="bi bi-map-fill"></i>
            </div>
            <div>
                <div style="font-weight:800;color:#111827;font-size:.95rem;">Carte du campus</div>
                <div style="font-size:.75rem;color:var(--text-muted);">Cliquez sur un bureau pour réserver</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <button onclick="mapFitAll()" style="background:rgba(11,42,90,0.07);border:1.5px solid rgba(11,42,90,0.15);border-radius:8px;padding:6px 14px;font-size:.78rem;font-weight:700;color:#0b2a5a;cursor:pointer;display:flex;align-items:center;gap:5px;">
                <i class="bi bi-arrows-fullscreen"></i>Vue globale
            </button>
            <button onclick="toggleMapLegend()" style="background:rgba(11,42,90,0.07);border:1.5px solid rgba(11,42,90,0.15);border-radius:8px;padding:6px 14px;font-size:.78rem;font-weight:700;color:#0b2a5a;cursor:pointer;display:flex;align-items:center;gap:5px;">
                <i class="bi bi-list-ul"></i>Légende
            </button>
        </div>
    </div>

    <!-- Légende (masquée par défaut) -->
    <div id="mapLegend" style="display:none;padding:14px 22px;border-bottom:1px solid rgba(11,42,90,0.07);background:rgba(11,42,90,0.02);">
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <?php foreach ($bureauxMapData as $bm): ?>
            <button onclick="mapFocusBureau(<?= $bm['id'] ?>)"
                    style="display:inline-flex;align-items:center;gap:7px;padding:5px 12px;border-radius:999px;border:1.5px solid <?= $bm['color'] ?>22;background:<?= $bm['color'] ?>11;cursor:pointer;transition:all .15s;">
                <span style="width:10px;height:10px;border-radius:50%;background:<?= $bm['color'] ?>;display:inline-block;flex-shrink:0;"></span>
                <span style="font-size:.76rem;font-weight:700;color:<?= $bm['color'] ?>;"><?= htmlspecialchars($bm['nom']) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Map -->
    <div id="campusMap" style="height:420px;width:100%;"></div>

    <!-- Info bar -->
    <div style="padding:10px 22px;background:rgba(11,42,90,0.02);border-top:1px solid rgba(11,42,90,0.06);display:flex;align-items:center;gap:8px;">
        <i class="bi bi-info-circle" style="color:#9ca3af;font-size:.85rem;"></i>
        <span style="font-size:.74rem;color:var(--text-muted);">Cliquez sur un marqueur pour voir les détails et réserver un rendez-vous directement.</span>
    </div>
</div>

<!-- Leaflet CSS + JS (CDN, pas de clé requise) -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
(function() {
    var bureaux = <?= $bureauxMapJson ?>;
    if (!bureaux.length) return;

    // Centre de la carte = moyenne des coordonnées
    var avgLat = bureaux.reduce(function(s,b){return s+b.lat;},0) / bureaux.length;
    var avgLng = bureaux.reduce(function(s,b){return s+b.lng;},0) / bureaux.length;

    var map = L.map('campusMap', { zoomControl: true, scrollWheelZoom: false }).setView([avgLat, avgLng], 17);

    // Tuile OpenStreetMap (gratuite, sans clé)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
        maxZoom: 20
    }).addTo(map);

    var markersMap = {};
    var boundsArr  = [];

    bureaux.forEach(function(b) {
        // Icône personnalisée colorée
        var iconHtml = '<div style="'
            + 'width:38px;height:38px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);'
            + 'background:' + b.color + ';border:3px solid #fff;'
            + 'box-shadow:0 3px 10px rgba(0,0,0,0.25);'
            + 'display:grid;place-items:center;">'
            + '<i class="bi bi-building-fill" style="transform:rotate(45deg);color:#fff;font-size:.85rem;"></i>'
            + '</div>';

        var icon = L.divIcon({
            html: iconHtml,
            className: '',
            iconSize:   [38, 38],
            iconAnchor: [19, 38],
            popupAnchor:[0, -42]
        });

        var marker = L.marker([b.lat, b.lng], { icon: icon });

        // Popup avec bouton réserver
        var popupHtml = '<div style="min-width:200px;font-family:inherit;">'
            + '<div style="display:flex;align-items:center;gap:9px;margin-bottom:10px;">'
            + '<div style="width:34px;height:34px;border-radius:10px;background:' + b.color + ';display:grid;place-items:center;flex-shrink:0;">'
            + '<i class="bi bi-building-fill" style="color:#fff;font-size:.85rem;"></i></div>'
            + '<div><div style="font-weight:800;color:#111827;font-size:.88rem;line-height:1.2;">' + b.nom + '</div>'
            + '<div style="font-size:.72rem;color:#6b7280;margin-top:2px;">' + b.localisation + '</div></div>'
            + '</div>'
            + '<div style="height:1px;background:rgba(0,0,0,0.07);margin-bottom:10px;"></div>'
            + '<a href="' + b.bookUrl + '" style="'
            + 'display:flex;align-items:center;justify-content:center;gap:7px;'
            + 'width:100%;padding:9px;border-radius:9px;'
            + 'background:linear-gradient(135deg,' + b.color + ',' + b.color + 'cc);'
            + 'color:#fff;font-size:.8rem;font-weight:700;text-decoration:none;'
            + 'box-shadow:0 3px 10px rgba(0,0,0,0.15);transition:filter .15s;"'
            + ' onmouseover="this.style.filter=\'brightness(.88)\'"'
            + ' onmouseout="this.style.filter=\'none\'">'
            + '<i class="bi bi-calendar-plus"></i>Réserver un rendez-vous'
            + '</a>'
            + '</div>';

        marker.bindPopup(popupHtml, { maxWidth: 240, className: 'campus-popup' });
        marker.addTo(map);
        markersMap[b.id] = marker;
        boundsArr.push([b.lat, b.lng]);
    });

    // Fonctions globales
    window.mapFitAll = function() {
        if (boundsArr.length) map.fitBounds(boundsArr, { padding: [40, 40] });
    };

    window.mapFocusBureau = function(id) {
        var m = markersMap[id];
        if (!m) return;
        map.setView(m.getLatLng(), 19);
        m.openPopup();
        // fermer la légende sur mobile
        document.getElementById('mapLegend').style.display = 'none';
    };

    window.toggleMapLegend = function() {
        var leg = document.getElementById('mapLegend');
        leg.style.display = leg.style.display === 'none' ? 'block' : 'none';
    };

    // Ajuster la vue pour voir tous les marqueurs au chargement
    if (boundsArr.length > 1) map.fitBounds(boundsArr, { padding: [50, 50] });

})();
</script>

<!-- Style popup Leaflet personnalisé -->
<style>
.campus-popup .leaflet-popup-content-wrapper {
    border-radius: 14px !important;
    box-shadow: 0 8px 28px rgba(0,0,0,0.16) !important;
    padding: 0 !important;
    overflow: hidden;
}
.campus-popup .leaflet-popup-content {
    margin: 14px !important;
}
.campus-popup .leaflet-popup-tip-container { margin-top: -1px; }
</style>

<!-- Barre recherche bureaux -->
<form method="GET" action="index.php">
    <div style="background:#fff;border-radius:12px;padding:12px 16px;margin-bottom:25px;border:1px solid rgba(11,42,90,0.10);display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(11,42,90,0.05);">
        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0b2a5a,#1a9e86);display:inline-grid;place-items:center;flex-shrink:0;">
            <i class="bi bi-search" style="color:#fff;font-size:.9rem;"></i>
        </div>
        <input type="text" name="search_front" placeholder="Rechercher un bureau par nom ou localisation..."
               value="<?= htmlspecialchars($search_front) ?>"
               style="border:none;outline:none;flex:1;font-size:.9rem;font-weight:500;color:#111827;background:transparent;">
        <?php if (!empty($search_front)): ?>
        <a href="index.php" style="background:rgba(11,42,90,0.07);border-radius:8px;padding:5px 10px;font-size:.78rem;font-weight:600;color:#0b2a5a;text-decoration:none;">
            <i class="bi bi-x-lg me-1"></i>Effacer
        </a>
        <?php endif; ?>
        <button type="submit" style="background:linear-gradient(135deg,#0b2a5a,#1565c0);color:#fff;border:none;border-radius:8px;padding:7px 18px;font-size:.82rem;font-weight:700;cursor:pointer;white-space:nowrap;">Rechercher</button>
        <div style="width:1px;height:24px;background:rgba(11,42,90,0.12);"></div>
        <button type="button" onclick="window.print()" style="background:#fff;border:1.5px solid rgba(239,68,68,0.35);border-radius:8px;padding:6px 14px;font-size:.8rem;font-weight:700;color:#dc2626;cursor:pointer;display:flex;align-items:center;gap:6px;white-space:nowrap;">
            <i class="bi bi-file-pdf"></i>Export PDF
        </button>
    </div>
</form>

<!-- ══ BUREAUX ══ -->
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h2 class="h5 fw-bold mb-0" style="color:var(--brand)"><i class="bi bi-building me-1"></i>Bureaux disponibles</h2>
        <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:5px 14px;font-size:.78rem;font-weight:700;color:var(--brand);display:inline-flex;align-items:center;gap:6px;">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <?= $totalB ?> bureau<?= $totalB > 1 ? 'x' : '' ?> trouvé<?= $totalB > 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if (empty($filteredBureaux)): ?>
    <div class="us-card p-5 text-center">
        <i class="bi bi-building fs-1 d-block mb-2 text-muted" style="opacity:.4;"></i>
        <p class="text-muted mb-0">Aucun bureau ne correspond à votre recherche.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($pagedBureaux as $i => $b):
            $p    = $palettes[(($pageB - 1) * $perPageB + $i) % count($palettes)];
            $icon = getBureauIcon($b['nom']);
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="bureau-card">
                <div style="height:5px;background:linear-gradient(90deg,<?= $p['bar'] ?>,<?= $p['main'] ?>99);"></div>
                <div class="bureau-card-header" style="background:<?= $p['soft'] ?>;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bureau-card-icon" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;"><i class="bi <?= $icon ?>"></i></div>
                        <div class="overflow-hidden flex-grow-1">
                            <h6 class="fw-bold mb-1 text-truncate" style="color:#111827;font-size:.95rem;" title="<?= htmlspecialchars($b['nom']) ?>"><?= htmlspecialchars($b['nom']) ?></h6>
                            <span class="disponible-dot" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;border:1px solid <?= $p['main'] ?>28;">
                                <span style="width:6px;height:6px;border-radius:50%;background:<?= $p['main'] ?>;display:inline-block;"></span>Disponible
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bureau-card-body pt-3">
                    <div class="bureau-detail-row" style="background:<?= $p['soft'] ?>;">
                        <div class="bureau-detail-icon" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <div style="font-size:.68rem;font-weight:600;color:<?= $p['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Localisation</div>
                            <div class="bureau-detail-text" title="<?= htmlspecialchars($b['localisation']) ?>"><?= htmlspecialchars($b['localisation']) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($b['responsable'])): ?>
                    <div class="bureau-detail-row" style="background:<?= $p['soft'] ?>;">
                        <div class="bureau-detail-icon" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;"><i class="bi bi-person-badge-fill"></i></div>
                        <div>
                            <div style="font-size:.68rem;font-weight:600;color:<?= $p['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Responsable</div>
                            <div class="bureau-detail-text"><?= htmlspecialchars($b['responsable']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="index.php?action=book&bureau_id=<?= (int)$b['id'] ?>" class="bureau-btn" style="background:<?= $p['main'] ?>;color:#fff;">
                        <i class="bi bi-calendar-plus" style="font-size:1rem;"></i>Prendre rendez-vous
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination bureaux -->
    <?php echo frontPager($pageB, $totalPagesB, 'page_b'); ?>
    <?php if ($totalPagesB > 1): ?>
    <div class="fp-info">Page <strong><?= $pageB ?></strong> sur <strong><?= $totalPagesB ?></strong> — <?= $totalB ?> bureau<?= $totalB > 1 ? 'x' : '' ?> au total</div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<!-- ══ RENDEZ-VOUS RÉCENTS ══ -->
<div id="sectionRdv" style="display:none;">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h2 class="h5 fw-bold mb-0" style="color:var(--brand)"><i class="bi bi-calendar-check me-2"></i>Rendez-vous récents</h2>
            <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:4px 12px;font-size:.76rem;font-weight:700;color:var(--brand);"><?= $totalR ?> rendez-vous</span>
        </div>
        <button onclick="toggleRdv()" style="background:none;border:1px solid #ddd;border-radius:50px;padding:7px 18px;font-size:.83rem;font-weight:600;color:#6b7280;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .18s;">
            <i class="bi bi-x-lg"></i>Fermer
        </button>
    </div>

    <!-- Recherche rdv côté client -->
    <div style="background:#fff;border-radius:12px;padding:12px 16px;margin-bottom:22px;border:1px solid rgba(11,42,90,0.10);display:flex;align-items:center;gap:12px;box-shadow:0 2px 8px rgba(11,42,90,0.05);">
        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#0b2a5a,#1a9e86);display:inline-grid;place-items:center;flex-shrink:0;">
            <i class="bi bi-search" style="color:#fff;font-size:.9rem;"></i>
        </div>
        <input type="text" id="searchRdvNom" oninput="searchRdvByNom()" placeholder="Rechercher par nom d'étudiant..."
               style="border:none;outline:none;flex:1;font-size:.9rem;font-weight:500;color:#111827;background:transparent;" autocomplete="off">
        <button onclick="clearSearchRdv()" id="btnClearRdv"
                style="display:none;background:rgba(11,42,90,0.07);border:none;border-radius:8px;padding:5px 10px;font-size:.78rem;font-weight:600;color:#0b2a5a;cursor:pointer;">
            <i class="bi bi-x-lg me-1"></i>Effacer
        </button>
    </div>

    <div id="rdvNoResult" style="display:none;" class="text-center py-4 mb-4">
        <div style="background:#fff;border-radius:16px;padding:30px;border:1px dashed rgba(11,42,90,0.15);">
            <i class="bi bi-search" style="font-size:2rem;color:#0b2a5a;opacity:.3;display:block;margin-bottom:10px;"></i>
            <div style="font-weight:700;color:#374151;margin-bottom:4px;">Aucun résultat</div>
            <div style="font-size:.83rem;color:#9ca3af;">Aucun rendez-vous ne correspond à "<span id="searchTermDisplay"></span>".</div>
        </div>
    </div>

    <?php if (empty($allRdv)): ?>
    <div class="us-card p-5 text-center text-muted mb-5">
        <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>Aucun rendez-vous pour le moment.
    </div>
    <?php else: ?>
    <div class="row g-4 mb-2" id="rdvGrid">
        <?php foreach ($pagedRdv as $i => $row):
            $statut = strtolower($row['statut']);
            $sc     = $statusConfig[$statut] ?? $statusConfig['pending'];
            $sp     = $statusPalettes[$statut] ?? $statusPalettes['pending'];
        ?>
        <div class="col-sm-6 col-lg-4 rdv-item" data-nom="<?= strtolower(htmlspecialchars($row['nom_etudiant'])) ?>">
            <div class="rdv-card" style="background:<?= $sp['light'] ?>;border:2px solid <?= $sp['bar'] ?>;">
                <div style="height:6px;background:linear-gradient(90deg,<?= $sp['bar'] ?>,<?= $sp['main'] ?>);"></div>
                <div class="rdv-card-header" style="background:<?= $sp['light'] ?>;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="rdv-status-badge" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;border-color:<?= $sc['border'] ?>;">
                            <i class="bi <?= $sc['icon'] ?>" style="font-size:.8rem;"></i><?= $sc['label'] ?>
                        </span>
                        <div style="width:36px;height:36px;border-radius:10px;display:inline-grid;place-items:center;background:<?= $sp['bar'] ?>22;color:<?= $sp['main'] ?>;font-size:1rem;flex-shrink:0;">
                            <i class="bi bi-building-fill"></i>
                        </div>
                    </div>
                    <div class="fw-bold mt-2" style="color:<?= $sp['main'] ?>;font-size:.97rem;"><?= htmlspecialchars($row['nom_etudiant']) ?></div>
                </div>
                <div class="rdv-card-body pt-3">
                    <div class="rdv-detail-row" style="background:<?= $sp['bar'] ?>18;">
                        <div class="rdv-detail-icon" style="background:<?= $sp['bar'] ?>33;color:<?= $sp['main'] ?>;"><i class="bi bi-chat-left-text-fill"></i></div>
                        <div>
                            <div style="font-size:.66rem;font-weight:600;color:<?= $sp['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Sujet</div>
                            <div class="rdv-detail-text"><?= htmlspecialchars($row['objet']) ?></div>
                        </div>
                    </div>
                    <div class="rdv-detail-row" style="background:<?= $sp['bar'] ?>18;">
                        <div class="rdv-detail-icon" style="background:<?= $sp['bar'] ?>33;color:<?= $sp['main'] ?>;"><i class="bi bi-calendar-event-fill"></i></div>
                        <div>
                            <div style="font-size:.66rem;font-weight:600;color:<?= $sp['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Date &amp; Heure</div>
                            <div class="rdv-detail-text"><?= date('d/m/Y', strtotime($row['date_rdv'])) ?> à <?= htmlspecialchars($row['heure_rdv']) ?></div>
                        </div>
                    </div>
                    <div class="rdv-detail-row" style="background:<?= $sp['bar'] ?>18;">
                        <div class="rdv-detail-icon" style="background:<?= $sp['bar'] ?>33;color:<?= $sp['main'] ?>;"><i class="bi bi-building-fill"></i></div>
                        <div>
                            <div style="font-size:.66rem;font-weight:600;color:<?= $sp['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Bureau</div>
                            <div class="rdv-detail-text"><?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination RDV -->
    <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:30px;">
        <?php echo frontPager($pageR, $totalPagesR, 'page_r'); ?>
        <?php if ($totalPagesR > 1): ?>
        <div class="fp-info">Page <strong><?= $pageR ?></strong> sur <strong><?= $totalPagesR ?></strong> — <?= $totalR ?> rendez-vous au total</div>
        <?php endif; ?>
    </div>

    <?php endif; ?>
</div>

<script>
// Ouvrir automatiquement UNIQUEMENT si on pagine spécifiquement les rdv
<?php if (isset($_GET['page_r'])): ?>
document.addEventListener('DOMContentLoaded', function() {
    var s = document.getElementById('sectionRdv');
    s.style.display = 'block';
    setTimeout(function(){ s.scrollIntoView({behavior:'smooth',block:'start'}); }, 100);
});
<?php endif; ?>

function toggleRdv() {
    var section = document.getElementById('sectionRdv');
    var open = section.style.display === 'none';
    section.style.display = open ? 'block' : 'none';
    if (open) {
        section.classList.remove('anim-fade'); void section.offsetWidth; section.classList.add('anim-fade');
        setTimeout(function(){ section.scrollIntoView({behavior:'smooth',block:'start'}); }, 50);
        setTimeout(function(){ var s=document.getElementById('searchRdvNom'); if(s) s.focus(); }, 350);
        document.getElementById('btnRdv').innerHTML = '<i class="bi bi-clock-history" style="font-size:1.1rem"></i> Masquer les rendez-vous <i class="bi bi-chevron-up" id="iconRdv"></i>';
    } else {
        clearSearchRdv();
        document.getElementById('btnRdv').innerHTML = '<i class="bi bi-clock-history" style="font-size:1.1rem"></i> Rendez-vous récents <i class="bi bi-chevron-down" id="iconRdv"></i>';
    }
}

function searchRdvByNom() {
    var term  = document.getElementById('searchRdvNom').value.trim().toLowerCase();
    var items = document.querySelectorAll('.rdv-item');
    var visible = 0;
    document.getElementById('btnClearRdv').style.display = term ? 'block' : 'none';
    items.forEach(function(item) {
        var match = term === '' || (item.getAttribute('data-nom') || '').includes(term);
        item.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    document.getElementById('rdvNoResult').style.display = (!visible && term) ? 'block' : 'none';
    if (document.getElementById('searchTermDisplay')) document.getElementById('searchTermDisplay').innerText = term;
}

function clearSearchRdv() {
    var input = document.getElementById('searchRdvNom');
    if (input) { input.value=''; searchRdvByNom(); input.focus(); }
}
</script>