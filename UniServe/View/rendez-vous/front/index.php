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
    'pending'   => ['label'=>'En attente',  'icon'=>'bi-hourglass-split', 'bg'=>'#fff8e1','color'=>'#b45309','border'=>'#fde68a'],
    'confirmed' => ['label'=>'Confirmé',    'icon'=>'bi-check-circle-fill','bg'=>'#dcfce7','color'=>'#166534','border'=>'#bbf7d0'],
    'cancelled' => ['label'=>'Annulé',      'icon'=>'bi-x-circle-fill',   'bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fecaca'],
];

$statusPalettes = [
    'confirmed' => ['light'=>'#dcfce7','main'=>'#166534','bar'=>'#22c55e','soft'=>'rgba(34,197,94,0.10)'],
    'pending'   => ['light'=>'#fef9c3','main'=>'#854d0e','bar'=>'#eab308','soft'=>'rgba(234,179,8,0.10)'],
    'cancelled' => ['light'=>'#fee2e2','main'=>'#991b1b','bar'=>'#ef4444','soft'=>'rgba(239,68,68,0.10)'],
];

// Récupérer la notification
$notification = $_SESSION['notification'] ?? null;
if ($notification) {
    unset($_SESSION['notification']);
}

// Récupérer le terme de recherche
$search_front = $_GET['search_front'] ?? '';

// Filtrer les bureaux
$allBureaux = [];
while ($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)) $allBureaux[] = $b;

$filteredBureaux = $allBureaux;
if (!empty($search_front)) {
    $filteredBureaux = array_filter($filteredBureaux, function($b) use ($search_front) {
        return stripos($b['nom'], $search_front) !== false || 
               stripos($b['localisation'], $search_front) !== false;
    });
}

$allRdv = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) $allRdv[] = $r;

// Statistiques
$statsTotal = count($allRdv);
$statsConfirmes = count(array_filter($allRdv, fn($r) => strtolower($r['statut']) === 'confirmed'));
$taux = $statsTotal > 0 ? round(($statsConfirmes / $statsTotal) * 100) : 0;
?>

<style>
/* ── Bureaux ── */
.bureau-card {
    background:#fff;border-radius:18px;overflow:hidden;
    border:1px solid rgba(0,0,0,0.07);
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    transition:transform .22s ease,box-shadow .22s ease;height:100%;
}
.bureau-card:hover { transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,0.12); }
.bureau-card-header { padding:22px 22px 16px;position:relative; }
.bureau-card-icon {
    width:54px;height:54px;border-radius:15px;display:inline-grid;
    place-items:center;font-size:1.4rem;flex-shrink:0;
    box-shadow:0 3px 10px rgba(0,0,0,0.12);
}
.bureau-card-body { padding:0 22px 20px; }
.bureau-detail-row { display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;margin-bottom:8px; }
.bureau-detail-icon { width:32px;height:32px;border-radius:9px;display:inline-grid;place-items:center;flex-shrink:0;font-size:.9rem; }
.bureau-detail-text { font-size:.82rem;font-weight:500;color:#4b5563;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.bureau-btn {
    display:flex;align-items:center;justify-content:center;gap:7px;
    width:100%;padding:10px;border-radius:12px;
    font-size:.85rem;font-weight:700;letter-spacing:.3px;
    border:none;cursor:pointer;transition:all .18s;
    text-decoration:none;margin-top:16px;
}
.bureau-btn:hover { filter:brightness(0.92);transform:translateY(-1px); }
.disponible-dot { display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:999px; }

/* ── RDV Cards ── */
.rdv-card {
    background:#fff;border-radius:18px;overflow:hidden;
    border:1px solid rgba(0,0,0,0.07);
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    transition:transform .22s ease,box-shadow .22s ease;height:100%;
}
.rdv-card:hover { transform:translateY(-5px);box-shadow:0 12px 32px rgba(0,0,0,0.12); }
.rdv-card-header { padding:18px 20px 14px; }
.rdv-card-body { padding:0 20px 18px; }
.rdv-detail-row { display:flex;align-items:center;gap:9px;padding:8px 11px;border-radius:10px;margin-bottom:7px; }
.rdv-detail-icon { width:30px;height:30px;border-radius:8px;display:inline-grid;place-items:center;flex-shrink:0;font-size:.85rem; }
.rdv-detail-text { font-size:.81rem;font-weight:500;color:#4b5563;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.rdv-status-badge {
    display:inline-flex;align-items:center;gap:6px;
    padding:5px 12px;border-radius:999px;
    font-size:.72rem;font-weight:800;letter-spacing:.3px;
    border:1px solid;
}

/* ── Boutons hero ── */
.btn-hero {
    display:inline-flex;align-items:center;gap:9px;
    padding:13px 32px;border-radius:50px;
    font-size:.95rem;font-weight:700;letter-spacing:.3px;
    border:none;cursor:pointer;
    transition:transform .22s ease,box-shadow .22s ease,filter .22s;
    text-decoration:none;
    position:relative;overflow:hidden;
}
.btn-hero::after {
    content:'';position:absolute;inset:0;
    background:rgba(255,255,255,0);
    transition:background .22s;
}
.btn-hero:hover { transform:translateY(-3px); }
.btn-hero:hover::after { background:rgba(255,255,255,0.10); }
.btn-hero:active { transform:translateY(0px); }

.btn-hero-primary {
    background:linear-gradient(135deg,#0b2a5a 0%,#1565c0 100%);
    color:#fff;
    box-shadow:0 4px 18px rgba(11,42,90,0.28);
}
.btn-hero-primary:hover {
    box-shadow:0 8px 28px rgba(11,42,90,0.38);
    color:#fff;
}

.btn-hero-teal {
    background:linear-gradient(135deg,#0b2a5a 0%,#1a9e86 100%);
    color:#fff;
    box-shadow:0 4px 18px rgba(26,158,134,0.28);
}
.btn-hero-teal:hover {
    box-shadow:0 8px 28px rgba(26,158,134,0.38);
    color:#fff;
}

/* ── Animations ── */
@keyframes fadeSlideDown {
    from { opacity:0;transform:translateY(-16px); }
    to   { opacity:1;transform:translateY(0); }
}
.anim-fade { animation:fadeSlideDown .32s ease; }

/* ── Statistiques Front ── */
.stats-front-grid {
    display:grid;grid-template-columns:repeat(4,1fr);gap:15px;margin-bottom:25px;
}
.stats-front-card {
    background:#fff;border-radius:16px;padding:18px 12px;text-align:center;
    border:1px solid var(--border);box-shadow:0 2px 8px rgba(0,0,0,0.04);
    transition:transform 0.2s;
}
.stats-front-card:hover { transform:translateY(-3px); }
.stats-front-number { font-size:28px;font-weight:800;color:var(--brand); }
.stats-front-label { font-size:12px;color:var(--text-muted);margin-top:4px; }

/* Barre de recherche */
.search-bar-front {
    background:#fff;border-radius:12px;padding:12px 16px;
    margin-bottom:25px;border:1px solid var(--border);
    display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:10px;
}
.search-form { display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;flex:1; }
.search-input-group { flex:2;min-width:200px; }
.search-label { font-size:11px;font-weight:700;color:var(--brand);margin-bottom:5px;display:block; }

/* Notification */
@keyframes slideInRight {
    from { transform:translateX(100%); opacity:0; }
    to { transform:translateX(0); opacity:1; }
}

/* Impression PDF */
@media print {
    .navbar, .dropdown, .btn-hero, .btn, .search-bar-front, .btn-outline-danger {
        display: none !important;
    }
    body { padding-top: 0 !important; }
    .stats-front-card { border:1px solid #ddd; }
}
</style>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i>Votre demande de rendez-vous a été soumise avec succès !
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Notification de confirmation/annulation -->
<?php if (!empty($notification)): ?>
<div class="alert alert-<?= $notification['type'] === 'success' ? 'success' : 'danger' ?> alert-dismissible fade show mb-4" role="alert" style="border-left: 4px solid <?= $notification['type'] === 'success' ? '#22c55e' : '#ef4444' ?>; border-radius: 12px;">
    <div class="d-flex align-items-center gap-3">
        <div class="flex-shrink-0">
            <i class="bi <?= $notification['type'] === 'success' ? 'bi-check-circle-fill' : 'bi-x-circle-fill' ?>" style="font-size: 1.8rem; color: <?= $notification['type'] === 'success' ? '#22c55e' : '#ef4444' ?>;"></i>
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1" style="font-weight: 700;"><?= htmlspecialchars($notification['title']) ?></h5>
            <p class="mb-0"><?= htmlspecialchars($notification['message']) ?></p>
            <small class="text-muted"><?= htmlspecialchars($notification['nom_etudiant'] ?? '') ?></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="us-hero rounded-3 p-5 mb-4 text-center">
    <h1 class="fw-bold mb-2" style="color:var(--brand)">Réservez votre rendez-vous<br>en quelques secondes</h1>
    <p class="text-muted mb-4">Choisissez un bureau, sélectionnez une date et évitez les files d'attente.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
        <a href="index.php?action=book" class="btn-hero btn-hero-primary">
            <i class="bi bi-calendar-plus" style="font-size:1.1rem"></i>
            Réserver maintenant
        </a>
        <button class="btn-hero btn-hero-teal" onclick="toggleRdv()" id="btnRdv">
            <i class="bi bi-clock-history" style="font-size:1.1rem"></i>
            Rendez-vous récents
            <i class="bi bi-chevron-down" id="iconRdv"></i>
        </button>
    </div>
</div>

<!-- STATISTIQUES FRONT -->
<div class="stats-front-grid">
    <div class="stats-front-card"><div class="stats-front-number"><?= $statsTotal ?></div><div class="stats-front-label">Total RDV</div></div>
    <div class="stats-front-card"><div class="stats-front-number" style="color:#22c55e;"><?= $statsConfirmes ?></div><div class="stats-front-label">Confirmés</div></div>
    <div class="stats-front-card"><div class="stats-front-number" style="color:#eab308;"><?= $taux ?>%</div><div class="stats-front-label">Taux succès</div></div>
    <div class="stats-front-card"><div class="stats-front-number" style="color:#0b2a5a;"><?= count($allBureaux) ?></div><div class="stats-front-label">Bureaux actifs</div></div>
</div>

<!-- BARRE DE RECHERCHE + EXPORT PDF -->
<div class="search-bar-front">
    <form method="GET" action="index.php" class="search-form">
        <div class="search-input-group">
            <label class="search-label">🔍 Rechercher un bureau</label>
            <input type="text" name="search_front" class="form-control form-control-sm" placeholder="Nom ou localisation..." value="<?= htmlspecialchars($search_front) ?>">
        </div>
        <div><button type="submit" class="btn btn-primary btn-sm px-4">Rechercher</button></div>
        <?php if (!empty($search_front)): ?>
        <div><a href="index.php" class="btn btn-outline-secondary btn-sm px-4">Réinitialiser</a></div>
        <?php endif; ?>
    </form>
    <button onclick="window.print()" class="btn btn-outline-danger btn-sm px-4" style="display:flex;align-items:center;gap:6px;">
        <i class="bi bi-file-pdf"></i> Export PDF
    </button>
</div>

<!-- BUREAUX DISPONIBLES -->
<div class="mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h2 class="h5 fw-bold mb-0" style="color:var(--brand)">
            <i class="bi bi-building me-1"></i>Bureaux disponibles
        </h2>
        <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:5px 14px;font-size:.78rem;font-weight:700;color:var(--brand);display:inline-flex;align-items:center;gap:6px;">
            <i class="bi bi-grid-3x3-gap-fill"></i>
            <?= count($filteredBureaux) ?> bureau<?= count($filteredBureaux) > 1 ? 'x' : '' ?> trouvé<?= count($filteredBureaux) > 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if (empty($filteredBureaux)): ?>
    <div class="us-card p-5 text-center">
        <i class="bi bi-building fs-1 d-block mb-2 text-muted" style="opacity:.4;"></i>
        <p class="text-muted mb-0">Aucun bureau ne correspond à votre recherche.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($filteredBureaux as $i => $b):
            $p    = $palettes[$i % count($palettes)];
            $icon = getBureauIcon($b['nom']);
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="bureau-card">
                <div style="height:5px;background:linear-gradient(90deg,<?= $p['bar'] ?>,<?= $p['main'] ?>99);"></div>
                <div class="bureau-card-header" style="background:<?= $p['soft'] ?>;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bureau-card-icon" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;">
                            <i class="bi <?= $icon ?>"></i>
                        </div>
                        <div class="overflow-hidden flex-grow-1">
                            <h6 class="fw-bold mb-1 text-truncate" style="color:#111827;font-size:.95rem;" title="<?= htmlspecialchars($b['nom']) ?>">
                                <?= htmlspecialchars($b['nom']) ?>
                            </h6>
                            <span class="disponible-dot" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;border:1px solid <?= $p['main'] ?>28;">
                                <span style="width:6px;height:6px;border-radius:50%;background:<?= $p['main'] ?>;display:inline-block;box-shadow:0 0 0 2px <?= $p['main'] ?>33;"></span>
                                Disponible
                            </span>
                        </div>
                    </div>
                </div>
                <div class="bureau-card-body pt-3">
                    <div class="bureau-detail-row" style="background:<?= $p['soft'] ?>;">
                        <div class="bureau-detail-icon" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:.68rem;font-weight:600;color:<?= $p['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Localisation</div>
                            <div class="bureau-detail-text" title="<?= htmlspecialchars($b['localisation']) ?>"><?= htmlspecialchars($b['localisation']) ?></div>
                        </div>
                    </div>
                    <?php if (!empty($b['responsable'])): ?>
                    <div class="bureau-detail-row" style="background:<?= $p['soft'] ?>;">
                        <div class="bureau-detail-icon" style="background:<?= $p['light'] ?>;color:<?= $p['main'] ?>;">
                            <i class="bi bi-person-badge-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:.68rem;font-weight:600;color:<?= $p['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Responsable</div>
                            <div class="bureau-detail-text" title="<?= htmlspecialchars($b['responsable']) ?>"><?= htmlspecialchars($b['responsable']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="index.php?action=book&bureau_id=<?= (int)$b['id'] ?>" class="bureau-btn" style="background:<?= $p['main'] ?>;color:#fff;">
                        <i class="bi bi-calendar-plus" style="font-size:1rem;"></i>
                        Prendre rendez-vous
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- RENDEZ-VOUS RÉCENTS (caché par défaut) -->
<div id="sectionRdv" style="display:none;">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
            <h2 class="h5 fw-bold mb-0" style="color:var(--brand)">
                <i class="bi bi-calendar-check me-2"></i>Rendez-vous récents
            </h2>
            <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:4px 12px;font-size:.76rem;font-weight:700;color:var(--brand);">
                <?= count($allRdv) ?> rendez-vous
            </span>
        </div>
        <button onclick="toggleRdv()" style="background:none;border:1px solid #ddd;border-radius:50px;padding:7px 18px;font-size:.83rem;font-weight:600;color:#6b7280;cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .18s;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='none'">
            <i class="bi bi-x-lg"></i> Fermer
        </button>
    </div>

    <?php if (empty($allRdv)): ?>
    <div class="us-card p-5 text-center text-muted mb-5">
        <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
        Aucun rendez-vous pour le moment.
    </div>
    <?php else: ?>
    <div class="row g-4 mb-5">
        <?php foreach ($allRdv as $i => $row):
            $statut = strtolower($row['statut']);
            $sc     = $statusConfig[$statut] ?? $statusConfig['pending'];
            $rp     = $rdvPalettes[$i % count($rdvPalettes)];
            $sp     = $statusPalettes[$statut] ?? $statusPalettes['pending'];
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="rdv-card" style="background:<?= $sp['light'] ?>;border:2px solid <?= $sp['bar'] ?>;">
                <div style="height:6px;background:linear-gradient(90deg,<?= $sp['bar'] ?>,<?= $sp['main'] ?>);"></div>
                <div class="rdv-card-header" style="background:<?= $sp['light'] ?>;">
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <span class="rdv-status-badge" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>;border-color:<?= $sc['border'] ?>;">
                            <i class="bi <?= $sc['icon'] ?>" style="font-size:.8rem;"></i>
                            <?= $sc['label'] ?>
                        </span>
                        <div style="width:36px;height:36px;border-radius:10px;display:inline-grid;place-items:center;background:<?= $sp['bar'] ?>22;color:<?= $sp['main'] ?>;font-size:1rem;flex-shrink:0;">
                            <i class="bi bi-building-fill"></i>
                        </div>
                    </div>
                    <div class="fw-bold mt-2" style="color:<?= $sp['main'] ?>;font-size:.97rem;">
                        <?= htmlspecialchars($row['nom_etudiant']) ?>
                    </div>
                </div>
                <div class="rdv-card-body pt-3">
                    <div class="rdv-detail-row" style="background:<?= $sp['bar'] ?>18;">
                        <div class="rdv-detail-icon" style="background:<?= $sp['bar'] ?>33;color:<?= $sp['main'] ?>;">
                            <i class="bi bi-chat-left-text-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:.66rem;font-weight:600;color:<?= $sp['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Sujet</div>
                            <div class="rdv-detail-text"><?= htmlspecialchars($row['objet']) ?></div>
                        </div>
                    </div>
                    <div class="rdv-detail-row" style="background:<?= $sp['bar'] ?>18;">
                        <div class="rdv-detail-icon" style="background:<?= $sp['bar'] ?>33;color:<?= $sp['main'] ?>;">
                            <i class="bi bi-calendar-event-fill"></i>
                        </div>
                        <div>
                            <div style="font-size:.66rem;font-weight:600;color:<?= $sp['main'] ?>;text-transform:uppercase;letter-spacing:.4px;margin-bottom:1px;">Date & Heure</div>
                            <div class="rdv-detail-text"><?= date('d/m/Y', strtotime($row['date_rdv'])) ?> à <?= htmlspecialchars($row['heure_rdv']) ?></div>
                        </div>
                    </div>
                    <div class="rdv-detail-row" style="background:<?= $sp['bar'] ?>18;">
                        <div class="rdv-detail-icon" style="background:<?= $sp['bar'] ?>33;color:<?= $sp['main'] ?>;">
                            <i class="bi bi-building-fill"></i>
                        </div>
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
    <?php endif; ?>
</div>

<script>
function toggleRdv() {
    var section = document.getElementById('sectionRdv');
    var icon    = document.getElementById('iconRdv');
    var btn     = document.getElementById('btnRdv');
    var open    = section.style.display === 'none';

    if (open) {
        section.style.display = 'block';
        section.classList.remove('anim-fade');
        void section.offsetWidth;
        section.classList.add('anim-fade');
        icon.className = 'bi bi-chevron-up';
        btn.innerHTML  = '<i class="bi bi-clock-history" style="font-size:1.1rem"></i> Masquer les rendez-vous <i class="bi bi-chevron-up" id="iconRdv"></i>';
        setTimeout(function(){ section.scrollIntoView({ behavior:'smooth', block:'start' }); }, 50);
    } else {
        section.style.display = 'none';
        btn.innerHTML  = '<i class="bi bi-clock-history" style="font-size:1.1rem"></i> Rendez-vous récents <i class="bi bi-chevron-down" id="iconRdv"></i>';
    }
}
</script>