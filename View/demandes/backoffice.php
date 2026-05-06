<style>
@media print {
    .btn, form, .no-print, .sidebar, .top-header { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
    .ms-sidebar { margin-left: 0 !important; padding: 0 !important; }
    body { background-color: white !important; }
}
.search-bar { max-width: 300px; }
.btn-outline-magic {
    color: #6366f1;
    border-color: #6366f1;
}
.btn-outline-magic:hover {
    background-color: #6366f1;
    color: white;
}
</style>

<!-- Alertes Flash -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?= $_SESSION['flash']['type'] ?> alert-dismissible fade show shadow-sm border-0 mb-4 no-print" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-<?= $_SESSION['flash']['type'] === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill' ?> me-2 fs-5"></i>
            <div><?= $_SESSION['flash']['message'] ?></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<!-- SECTION KPIs -->
<div class="row mb-4 mt-2 no-print">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <h5 class="card-title"><i class="bi bi-box-seam fs-3 d-block mb-2 text-center"></i> Total Demandes</h5>
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
        <div class="card bg-danger text-white shadow-sm h-100">
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <h5 class="card-title"><i class="bi bi-x-circle fs-3 d-block mb-2 text-center"></i> Rejetées</h5>
                <h2 class="mb-0 fw-bold"><?= $stats['rejetees'] ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <button class="btn btn-primary shadow-sm" onclick="printReleve()">
            <i class="bi bi-file-earmark-pdf me-2"></i>Exporter le rapport global
        </button>
    </div>
</div>

<!-- SECTION SERVICES -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h1 class="h3">Gestion des Services</h1>
    <a href="<?= $this->url('/services/create') ?>" class="btn btn-primary no-print"><i class="bi bi-plus-lg me-2"></i>Nouveau Service</a>
</div>

<div class="card shadow-sm mb-5">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Statut</th>
                        <th class="text-end no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Aucun service trouvé.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                        <tr class="<?= $service->getActif() ? '' : 'table-secondary text-muted' ?>">
                            <td class="fw-medium"><?= htmlspecialchars($service->getNom() ?? '') ?></td>
                            <td><?= htmlspecialchars($service->getDescription() ?? '') ?></td>
                            <td>
                                <?php if ($service->getActif()): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end no-print">
                                <a href="<?= $this->url('/services/edit/' . $service->getId()) ?>" class="btn btn-sm btn-warning text-dark me-1" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="<?= $this->url('/services/delete/' . $service->getId()) ?>" method="POST" class="d-inline" id="deleteService_<?= $service->getId() ?>">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('deleteService_<?= $service->getId() ?>')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<hr class="mb-5">

<!-- SECTION DEMANDES -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3">Toutes les demandes</h1>
    <div class="d-flex gap-2 no-print">
        <select id="sortDate" class="form-select form-select-sm" style="width: 150px;">
            <option value="desc">Plus récent</option>
            <option value="asc">Plus ancien</option>
        </select>
        <select id="filterStatus" class="form-select form-select-sm" style="width: 150px;">
            <option value="all">Tous les statuts</option>
            <option value="en_attente">En attente</option>
            <option value="en_cours">En cours</option>
            <option value="traite">Traité</option>
            <option value="rejete">Rejeté</option>
        </select>
        <input type="text" id="searchDemande" class="form-control form-control-sm search-bar" placeholder="Rechercher étudiant ou titre...">
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="demandesTable">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Service</th>
                        <th>Utilisateur</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Aucune demande trouvée.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $demande): 
                            $rowClass = '';
                            if ($demande->getStatut() === 'rejete') $rowClass = 'table-danger text-muted';
                            if ($demande->getStatut() === 'traite') $rowClass = 'table-success';
                            if ($demande->getStatut() === 'en_attente') $rowClass = 'table-warning';
                        ?>
                        <tr class="demande-row <?= $rowClass ?>" data-statut="<?= $demande->getStatut() ?>" data-date="<?= strtotime($demande->getDateCreation() ?? '') ?>">
                            <td class="fw-medium search-target"><?= htmlspecialchars($demande->getTitre() ?? '') ?></td>
                            <td><?= htmlspecialchars($demande->getServiceNom() ?? '') ?></td>
                            <td class="search-target"><?= htmlspecialchars($demande->getUserPrenom() . ' ' . $demande->getUserNom()) ?></td>
                            <td><?= date('d/m/Y', strtotime($demande->getDateCreation() ?? '')) ?></td>
                            <td>
                                <form action="<?= $this->url('/demandes/updateStatut/' . $demande->getId()) ?>" method="POST" class="d-inline no-print">
                                    <select name="statut" class="form-select form-select-sm d-inline-block w-auto fw-bold" onchange="this.form.submit()">
                                        <option value="en_attente" <?= $demande->getStatut() === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="en_cours" <?= $demande->getStatut() === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                        <option value="traite" <?= $demande->getStatut() === 'traite' ? 'selected' : '' ?>>Traité</option>
                                        <option value="rejete" <?= $demande->getStatut() === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                                    </select>
                                </form>
                                <span class="d-none d-print-block fw-bold">
                                    <?php
                                    $statusLabels = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'traite' => 'Traité', 'rejete' => 'Rejeté'];
                                    echo $statusLabels[$demande->getStatut()] ?? $demande->getStatut();
                                    ?>
                                </span>
                            </td>
                            <td class="text-end no-print">
                                <?php if ($demande->getStatut() === 'en_attente'): ?>
                                    <a href="<?= $this->url('/demandes/aiValidate/' . $demande->getId()) ?>" class="btn btn-sm btn-outline-magic me-1" title="Vérifier par IA (Spam/Charabia)">
                                        <i class="bi bi-robot"></i>
                                    </a>
                                <?php endif; ?>
                                <button type="button" class="btn btn-sm btn-info text-white me-1" onclick="showDetails('<?= htmlspecialchars(addslashes($demande->getTitre() ?? '')) ?>', '<?= htmlspecialchars(addslashes(nl2br($demande->getDescription() ?? ''))) ?>')" title="Détails">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <form action="<?= $this->url('/demandes/delete_back/' . $demande->getId()) ?>" method="POST" class="d-inline" id="deleteDemande_<?= $demande->getId() ?>">
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('deleteDemande_<?= $demande->getId() ?>')" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
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

<!-- Modal Détails -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title" id="modalTitle">Détails de la demande</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4" id="modalDesc" style="font-size: 1.1rem; line-height: 1.6;">
      </div>
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
let formToSubmitId = null;
function confirmDelete(formId) {
    formToSubmitId = formId;
    new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (formToSubmitId) {
            document.getElementById(formToSubmitId).submit();
        }
    });
});

function showDetails(title, desc) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalDesc').innerHTML = desc;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

document.getElementById('searchDemande').addEventListener('input', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);
document.getElementById('sortDate').addEventListener('change', sortTable);

function sortTable() {
    const tbody = document.querySelector('#demandesTable tbody');
    const rows = Array.from(tbody.querySelectorAll('.demande-row'));
    const sortOrder = document.getElementById('sortDate').value;

    rows.sort((a, b) => {
        const dateA = parseInt(a.getAttribute('data-date'));
        const dateB = parseInt(b.getAttribute('data-date'));
        return sortOrder === 'desc' ? dateB - dateA : dateA - dateB;
    });

    rows.forEach(row => tbody.appendChild(row));
}

function filterTable() {
    const term = document.getElementById('searchDemande').value.toLowerCase();
    const status = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('.demande-row');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowStatus = row.getAttribute('data-statut');
        const matchSearch = text.includes(term);
        const matchStatus = (status === 'all' || status === rowStatus);
        
        if (matchSearch && matchStatus) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

const allDemandes = <?= json_encode($demandes) ?>;

function escHtml(str) {
    const d = document.createElement('div');
    d.appendChild(document.createTextNode(str));
    return d.innerHTML;
}

// --- Impression / Export PDF ---
function printReleve() {
    const now = new Date().toLocaleDateString('fr-FR', {day:'2-digit',month:'long',year:'numeric'});
    const logoUrl = window.location.origin + '<?= $this->url('/public/images/logo.png') ?>';

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
    allDemandes.forEach(d => {
        const key = d.service_nom || 'Inconnu';
        if (!byService[key]) byService[key] = [];
        byService[key].push(d);
    });

    const total   = allDemandes.length;
    const attente = allDemandes.filter(d => d.statut === 'en_attente').length;
    const traite  = allDemandes.filter(d => d.statut === 'traite').length;
    const rejete  = allDemandes.filter(d => d.statut === 'rejete').length;

    let servicesHtml = '';
    let pIdx = 0;

    for (const [nom, demandes] of Object.entries(byService)) {
        const pal = palettes[pIdx % palettes.length]; pIdx++;
        let rows = '';

        demandes.forEach((d, i) => {
            const st = statusMap[d.statut] || { label: d.statut, bg:'#f3f4f6', color:'#374151', border:'#d1d5db' };
            const dt = new Date(d.date_creation).toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
            const userName = (d.user_prenom + ' ' + d.user_nom).trim();
            const rowBg = i % 2 === 0 ? '#ffffff' : pal.light;
            rows += '<tr style="background:'+rowBg+';">'
                  + '<td style="padding:10px; border-bottom:1px solid '+pal.border+'; font-size:14px; width:25%; color:#333; font-weight:bold;">'+escHtml(userName)+'</td>'
                  + '<td style="padding:10px; border-bottom:1px solid '+pal.border+'; font-size:14px; width:40%; color:#555;">'+escHtml(d.titre)+'</td>'
                  + '<td style="padding:10px; border-bottom:1px solid '+pal.border+'; font-size:14px; width:15%; color:#555;">'+dt+'</td>'
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
          +         '<th style="padding:8px 10px; text-align:left; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">UTILISATEUR</th>'
          +         '<th style="padding:8px 10px; text-align:left; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">TITRE DE LA DEMANDE</th>'
          +         '<th style="padding:8px 10px; text-align:left; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">DATE</th>'
          +         '<th style="padding:8px 10px; text-align:center; font-size:12px; color:'+pal.text+'; border-bottom:2px solid '+pal.border+';">STATUT</th>'
          +       '</tr>'
          +     '</thead>'
          +     '<tbody>'+rows+'</tbody>'
          +   '</table>'
          + '</div>';
    }

    if (!servicesHtml) {
        servicesHtml = '<div style="text-align:center; padding:40px; color:#6b7280;">Aucune demande trouv&eacute;e.</div>';
    }

    const html = `<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport Global UniServe</title>
<style>
  body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f9fafb; color: #111; }
  .page { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
  @media print {
    body { background: #fff !important; padding: 0 !important; }
    .page { box-shadow: none !important; padding: 0 !important; max-width: 100% !important; }
    .no-print { display: none !important; }
    .stat-box { border: 1px solid #000 !important; }
  }
  .header { border-bottom: 2px solid #2563eb; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
  .header-left { display: flex; align-items: center; gap: 15px; }
  .header-logo { height: 60px; width: auto; }
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
      <div class="header-left">
        <img src="` + logoUrl + `" alt="UniServe" class="header-logo">
        <div>
          <h1 class="title-main">Rapport Global des Demandes</h1>
          <div class="title-sub">Administration UniServe</div>
        </div>
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
    
    <h2 style="font-size:16px; color:#4b5563; margin-bottom:20px; text-transform:uppercase; letter-spacing:1px;">Toutes les demandes par service</h2>
    
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
