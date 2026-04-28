<style>
@media print {
    .btn, form, .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
}
.service-card {
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
    border: none;
    border-radius: 16px;
    overflow: hidden;
}
.service-card.clickable:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.13) !important;
    cursor: pointer;
}
.service-card img {
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
#serviceDemandesModal .modal-dialog { max-width: 860px; }
.sd-toolbar { background: #f8f9fa; border-bottom: 1px solid #e9ecef; padding: 12px 16px; }
</style>

<!-- SECTION KPIs -->
<div class="row mb-4 mt-2 no-print">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <h5 class="card-title"><i class="bi bi-box-seam fs-3 d-block mb-2 text-center"></i> Mes Demandes</h5>
                <h2 class="mb-0 fw-bold"><?= $stats['total'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <h5 class="card-title"><i class="bi bi-hourglass-split fs-3 d-block mb-2 text-center"></i> En attente</h5>
                <h2 class="mb-0 fw-bold"><?= $stats['attente'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <h5 class="card-title"><i class="bi bi-check-circle fs-3 d-block mb-2 text-center"></i> Traitées</h5>
                <h2 class="mb-0 fw-bold"><?= $stats['traitees'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <h5 class="card-title"><i class="bi bi-list-stars fs-3 d-block mb-2 text-center"></i> Services Dispo</h5>
                <h2 class="mb-0 fw-bold"><?= $stats['services'] ?></h2>
            </div>
        </div>
    </div>
</div>

<!-- SECTION SERVICES -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h1 class="h3">Services Disponibles</h1>
    <button class="btn btn-primary no-print shadow-sm" onclick="printReleve()">
        <i class="bi bi-file-earmark-pdf me-2"></i>Exporter mon relevé
    </button>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-5">
    <?php if (empty($services)): ?>
        <div class="col-12"><p class="text-center py-4 text-muted">Aucun service disponible.</p></div>
    <?php else: ?>
        <?php foreach ($services as $service): ?>
            <div class="col">
                <div class="card h-100 shadow-sm service-card <?= $service['actif'] ? 'clickable' : 'opacity-75' ?>"
                     <?= $service['actif'] ? 'onclick="openServiceModal(' . $service['id'] . ', \'' . htmlspecialchars(addslashes($service['nom']), ENT_QUOTES) . '\')"' : '' ?>>
                    <div class="position-relative">
                        <?php
                            $imageMap = [
                                'Bulletin de notes' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?auto=format&fit=crop&w=400&q=80',
                                'Attestation de scolarité' => 'https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&w=400&q=80',
                                'Réclamation administrative' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?auto=format&fit=crop&w=400&q=80',
                                'Aide financière' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&w=400&q=80'
                            ];
                            $defaultImage = 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=400&q=80';
                            $imageUrl = $imageMap[$service['nom']] ?? $defaultImage;
                        ?>
                        <img src="<?= $imageUrl ?>"
                             class="card-img-top" alt="<?= htmlspecialchars($service['nom']) ?>"
                             style="height: 180px; object-fit: cover;">
                        <?php if (!$service['actif']): ?>
                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-50"></div>
                            <span class="position-absolute top-50 start-50 translate-middle badge bg-danger fs-6 px-3 py-2">Indisponible</span>
                        <?php else: ?>
                            <?php
                                $count = count(array_filter($demandes, fn($d) => $d['service_id'] == $service['id']));
                            ?>
                            <span class="position-absolute top-0 end-0 m-2 badge bg-primary rounded-pill"><?= $count ?> demande<?= $count > 1 ? 's' : '' ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title fw-bold text-primary mb-2"><?= htmlspecialchars($service['nom']) ?></h5>
                        <p class="card-text text-secondary flex-grow-1 small"><?= htmlspecialchars($service['description']) ?></p>
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <?php if ($service['actif']): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-medium">
                                    <i class="bi bi-check-circle me-1"></i> Disponible
                                </span>
                                <span class="text-primary small fw-semibold">Gérer mes demandes <i class="bi bi-arrow-right"></i></span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-medium">
                                    <i class="bi bi-x-circle me-1"></i> Indisponible
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ===================== MODAL DEMANDES PAR SERVICE ===================== -->
<div class="modal fade" id="serviceDemandesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content border-0 shadow-lg" style="border-radius:16px; overflow:hidden;">

      <!-- Header -->
      <div class="modal-header bg-primary text-white border-0 py-3">
        <div>
          <h5 class="modal-title fw-bold mb-0" id="sdModalTitle">Mes demandes</h5>
          <small class="opacity-75" id="sdModalSub">Gérez vos demandes pour ce service</small>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <!-- Toolbar : Recherche + Filtre + Tri + Nouvelle Demande -->
      <div class="sd-toolbar d-flex flex-wrap align-items-center gap-2 no-print">
        <input type="text" id="sdSearch" class="form-control form-control-sm" placeholder="🔍 Rechercher un titre..." style="max-width:220px;" oninput="sdFilter()">
        <select id="sdFilterStatus" class="form-select form-select-sm" style="width:160px;" onchange="sdFilter()">
            <option value="all">Tous les statuts</option>
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="traite">Traité</option>
            <option value="rejete">Rejeté</option>
        </select>
        <select id="sdSortDate" class="form-select form-select-sm" style="width:160px;" onchange="sdSort()">
            <option value="desc">Plus récent d'abord</option>
            <option value="asc">Plus ancien d'abord</option>
        </select>
        <div class="ms-auto">
            <button class="btn btn-primary btn-sm" id="sdNewDemandeBtn">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle Demande
            </button>
        </div>
      </div>

      <!-- Body : Tableau -->
      <div class="modal-body p-0" style="max-height: 55vh; overflow-y: auto;">
        <table class="table table-hover align-middle mb-0" id="sdTable">
            <thead class="table-light sticky-top">
                <tr>
                    <th class="ps-4">Titre</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th class="text-end pe-4 no-print">Actions</th>
                </tr>
            </thead>
            <tbody id="sdTableBody"></tbody>
        </table>
      </div>

      <!-- Footer -->
      <div class="modal-footer bg-light border-0">
        <small class="text-muted me-auto" id="sdCount"></small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>

    </div>
  </div>
</div>

<!-- Modal Détails Description -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="modalTitle">Détails de la demande</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4" id="modalDesc" style="font-size: 1.05rem; line-height: 1.7;"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de Confirmation de Suppression -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white border-0 py-2">
        <h5 class="modal-title fs-6"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmation</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center p-4">
        <i class="bi bi-trash text-danger display-4 d-block mb-3"></i>
        <p class="mb-1 fw-bold fs-5">Êtes-vous sûr ?</p>
        <p class="text-muted small mb-0">Cette action est irréversible.</p>
      </div>
      <div class="modal-footer border-0 justify-content-center bg-light">
        <button type="button" class="btn btn-light px-4 border" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">Oui, supprimer</button>
      </div>
    </div>
  </div>
</div>

<script>
const userDemandes = <?= json_encode($demandes) ?>;
const BASE_URL = '<?= $this->url('') ?>';
let currentServiceId = null;
let currentFiltered = [];

// ─── Labels & classes statut ──────────────────────────────────────────────────
const statusConfig = {
    en_attente: { label: 'En attente', cls: 'warning',  icon: 'hourglass-split' },
    en_cours:   { label: 'En cours',   cls: 'info',     icon: 'arrow-repeat'    },
    traite:     { label: 'Traité',     cls: 'success',  icon: 'check-circle'    },
    rejete:     { label: 'Rejeté',     cls: 'danger',   icon: 'x-circle'        },
};

function getStatusBadge(statut) {
    const cfg = statusConfig[statut] || { label: statut, cls: 'secondary', icon: 'circle' };
    return `<span class="badge bg-${cfg.cls}-subtle text-${cfg.cls} border border-${cfg.cls}-subtle rounded-pill px-2 py-1 fw-medium">
                <i class="bi bi-${cfg.icon} me-1"></i>${cfg.label}
            </span>`;
}

// ─── Ouvrir le modal ──────────────────────────────────────────────────────────
function openServiceModal(serviceId, serviceName) {
    currentServiceId = serviceId;
    document.getElementById('sdModalTitle').innerText = serviceName;

    // Bouton Nouvelle Demande → redirige vers le formulaire avec le service pré-sélectionné
    const btn = document.getElementById('sdNewDemandeBtn');
    btn.onclick = function() {
        window.location.href = BASE_URL + '/demandes/create?service_id=' + serviceId;
    };

    // Reset toolbar
    document.getElementById('sdSearch').value = '';
    document.getElementById('sdFilterStatus').value = 'all';
    document.getElementById('sdSortDate').value = 'desc';

    sdRenderRows(userDemandes.filter(d => d.service_id == serviceId));
    new bootstrap.Modal(document.getElementById('serviceDemandesModal')).show();
}

// ─── Rendre les lignes ────────────────────────────────────────────────────────
function sdRenderRows(data) {
    currentFiltered = [...data];
    const tbody = document.getElementById('sdTableBody');
    tbody.innerHTML = '';

    if (data.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="text-center py-5 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            Aucune demande pour ce service.
        </td></tr>`;
        document.getElementById('sdCount').innerText = '';
        return;
    }

    data.forEach(d => {
        const dateObj = new Date(d.date_creation);
        const dateStr = dateObj.toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
        const badge   = getStatusBadge(d.statut);

        // Actions
        let actions = `<button class="btn btn-sm btn-outline-info me-1" onclick="showDetails('${escJs(d.titre)}','${escJs(nl2br(d.description))}')" title="Voir"><i class="bi bi-eye"></i></button>`;
        if (d.statut === 'en_attente') {
            actions += `<a href="${BASE_URL}/demandes/edit/${d.id}" class="btn btn-sm btn-outline-primary me-1" title="Modifier"><i class="bi bi-pencil"></i></a>`;
        }
        actions += `<form action="${BASE_URL}/demandes/delete/${d.id}" method="POST" class="d-inline" id="deleteForm_${d.id}">
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Supprimer" onclick="confirmDelete('deleteForm_${d.id}')"><i class="bi bi-trash"></i></button>
                    </form>`;

        const tr = document.createElement('tr');
        tr.setAttribute('data-statut', d.statut);
        tr.setAttribute('data-date', new Date(d.date_creation).getTime());
        tr.setAttribute('data-titre', d.titre.toLowerCase());
        tr.innerHTML = `
            <td class="fw-medium ps-4">${escHtml(d.titre)}</td>
            <td class="text-muted small">${dateStr}</td>
            <td>${badge}</td>
            <td class="text-end pe-4 no-print">${actions}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('sdCount').innerText = `${data.length} demande${data.length > 1 ? 's' : ''} affichée${data.length > 1 ? 's' : ''}`;
}

// ─── Filtre + Tri ─────────────────────────────────────────────────────────────
function sdFilter() {
    const term    = document.getElementById('sdSearch').value.toLowerCase();
    const statut  = document.getElementById('sdFilterStatus').value;
    const sortDir = document.getElementById('sdSortDate').value;

    let data = userDemandes.filter(d => d.service_id == currentServiceId);

    if (term)           data = data.filter(d => d.titre.toLowerCase().includes(term));
    if (statut !== 'all') data = data.filter(d => d.statut === statut);

    data = sdSortData(data, sortDir);
    sdRenderRows(data);
}

function sdSort() {
    sdFilter();
}

function sdSortData(data, dir) {
    return [...data].sort((a, b) => {
        const da = new Date(a.date_creation).getTime();
        const db = new Date(b.date_creation).getTime();
        return dir === 'desc' ? db - da : da - db;
    });
}

// ─── Voir la description complète ────────────────────────────────────────────
function showDetails(title, desc) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalDesc').innerHTML = desc;
    const dm = new bootstrap.Modal(document.getElementById('detailsModal'));
    dm.show();
}

// ─── Utilitaires ─────────────────────────────────────────────────────────────
function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}
function escJs(str) {
    return String(str).replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"');
}
function nl2br(str) {
    return String(str).replace(/\n/g, '<br>');
}

// ─── Suppression ─────────────────────────────────────────────────────────────
let formToSubmitId = null;
function confirmDelete(formId) {
    formToSubmitId = formId;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (formToSubmitId) {
        document.getElementById(formToSubmitId).submit();
    }
});

// --- Impression / Export PDF ---
function printReleve() {
    const now = new Date().toLocaleDateString('fr-FR', {day:'2-digit',month:'long',year:'numeric'});

    const palettes = [
        { bg:'#1e3a8a', light:'#eff6ff', border:'#bfdbfe', text:'#1e3a8a' },
        { bg:'#5b21b6', light:'#f5f3ff', border:'#ddd6fe', text:'#5b21b6' },
        { bg:'#0f766e', light:'#f0fdfa', border:'#99f6e4', text:'#0f766e' },
        { bg:'#92400e', light:'#fffbeb', border:'#fde68a', text:'#92400e' }
    ];

    const statusMap = {
        en_attente: { label:'En attente', bg:'#fef9c3', color:'#854d0e', border:'#fde047' },
        en_cours:   { label:'En cours',   bg:'#e0f2fe', color:'#075985', border:'#7dd3fc' },
        traite:     { label:'Trait&eacute;',     bg:'#dcfce7', color:'#14532d', border:'#86efac' },
        rejete:     { label:'Rejet&eacute;',     bg:'#fee2e2', color:'#7f1d1d', border:'#fca5a5' },
    };

    const byService = {};
    userDemandes.forEach(d => {
        const key = d.service_nom || 'Inconnu';
        if (!byService[key]) byService[key] = [];
        byService[key].push(d);
    });

    const total   = userDemandes.length;
    const attente = userDemandes.filter(d => d.statut === 'en_attente').length;
    const traite  = userDemandes.filter(d => d.statut === 'traite').length;
    const rejete  = userDemandes.filter(d => d.statut === 'rejete').length;

    let servicesHtml = '';
    let pIdx = 0;

    for (const [nom, demandes] of Object.entries(byService)) {
        const pal = palettes[pIdx % palettes.length]; pIdx++;
        let rows = '';

        demandes.forEach((d, i) => {
            const st = statusMap[d.statut] || { label: d.statut, bg:'#f3f4f6', color:'#374151', border:'#d1d5db' };
            const dt = new Date(d.date_creation).toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
            const rowBg = i % 2 === 0 ? '#ffffff' : pal.light;
            rows += '<tr style="background:'+rowBg+';">'
                  + '<td style="padding:10px; border-bottom:1px solid '+pal.border+'; font-size:14px; width:60%; color:#333;">'+escHtml(d.titre)+'</td>'
                  + '<td style="padding:10px; border-bottom:1px solid '+pal.border+'; font-size:14px; width:20%; color:#555;">'+dt+'</td>'
                  + '<td style="padding:10px; border-bottom:1px solid '+pal.border+'; text-align:center; width:20%;">'
                  +   '<span style="background:'+st.bg+'; color:'+st.color+'; border:1px solid '+st.border+'; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:bold; display:inline-block;">'+st.label+'</span>'
                  + '</td>'
                  + '</tr>';
        });

        servicesHtml +=
            '<div style="margin-bottom:30px; border:1px solid '+pal.border+'; border-radius:8px; overflow:hidden;">'
          +   '<div style="background:'+pal.bg+'; padding:12px 15px; color:#fff; font-size:16px; font-weight:bold; display:flex; justify-content:space-between;">'
          +     '<span>'+escHtml(nom)+'</span>'
          +     '<span style="background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:4px; font-size:14px;">'+demandes.length+' demande(s)</span>'
          +   '</div>'
          +   '<table style="width:100%; border-collapse:collapse; table-layout:fixed;">'
          +     '<thead>'
          +       '<tr style="background:'+pal.light+';">'
          +         '<th style="padding:8px 10px; text-align:left; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">TITRE DE LA DEMANDE</th>'
          +         '<th style="padding:8px 10px; text-align:left; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">DATE</th>'
          +         '<th style="padding:8px 10px; text-align:center; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">STATUT</th>'
          +       '</tr>'
          +     '</thead>'
          +     '<tbody>'+rows+'</tbody>'
          +   '</table>'
          + '</div>';
    }

    const html = `<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Relev&eacute; UniServe</title>
<style>
  body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f9fafb; color: #111; }
  .page { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
  @media print {
    body { background: #fff !important; padding: 0 !important; }
    .page { box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
    .no-print { display: none !important; }
    .stat-box { border: 1px solid #000 !important; }
  }
  .header { border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
  .title-main { font-size: 24px; font-weight: bold; color: #1e3a8a; margin: 0; }
  .title-sub { font-size: 14px; color: #6b7280; margin-top: 5px; }
  .stats-grid { display: flex; gap: 15px; margin-bottom: 30px; }
  .stat-box { flex: 1; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #e5e7eb; background: #f9fafb; }
  .stat-val { font-size: 24px; font-weight: bold; margin-bottom: 5px; }
  .stat-lbl { font-size: 12px; text-transform: uppercase; color: #6b7280; font-weight: bold; }
</style>
</head>
<body>
  <div class="page">
    <div class="header">
      <div>
        <h1 class="title-main">Relev&eacute; de mes Demandes</h1>
        <div class="title-sub">Portail &Eacute;tudiant UniServe</div>
      </div>
      <div style="text-align:right; color:#4b5563; font-size:14px;">
        G&eacute;n&eacute;r&eacute; le ` + now + `
      </div>
    </div>
    
    <div class="stats-grid">
      <div class="stat-box" style="border-color:#bfdbfe; background:#eff6ff;">
        <div class="stat-val" style="color:#1d4ed8;">` + total + `</div>
        <div class="stat-lbl" style="color:#1d4ed8;">Total</div>
      </div>
      <div class="stat-box" style="border-color:#fde047; background:#fef9c3;">
        <div class="stat-val" style="color:#a16207;">` + attente + `</div>
        <div class="stat-lbl" style="color:#a16207;">En attente</div>
      </div>
      <div class="stat-box" style="border-color:#86efac; background:#dcfce7;">
        <div class="stat-val" style="color:#15803d;">` + traite + `</div>
        <div class="stat-lbl" style="color:#15803d;">Trait&eacute;s</div>
      </div>
      <div class="stat-box" style="border-color:#fca5a5; background:#fee2e2;">
        <div class="stat-val" style="color:#b91c1c;">` + rejete + `</div>
        <div class="stat-lbl" style="color:#b91c1c;">Rejet&eacute;s</div>
      </div>
    </div>
    
    <h2 style="font-size:16px; color:#4b5563; margin-bottom:20px; text-transform:uppercase; letter-spacing:1px;">D&eacute;tail par service</h2>
    
    ` + servicesHtml + `
    
    <div class="no-print" style="text-align:center; margin-top:40px; padding-top:20px; border-top:1px solid #e5e7eb;">
      <button onclick="window.print()" style="background:#2563eb; color:#fff; border:none; padding:10px 20px; border-radius:6px; font-size:16px; font-weight:bold; cursor:pointer;">
        Imprimer / Enregistrer en PDF
      </button>
    </div>
  </div>
</body>
</html>`;

    const popup = window.open('', '_blank');
    popup.document.write(html);
    popup.document.close();
}
</script>
