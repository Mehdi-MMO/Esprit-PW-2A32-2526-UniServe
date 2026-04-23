<style>
@media print {
    .btn, form, .no-print, .sidebar, .top-header { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
    .ms-sidebar { margin-left: 0 !important; padding: 0 !important; }
    body { background-color: white !important; }
}
.search-bar { max-width: 300px; }
</style>

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
        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Imprimer le rapport</button>
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
                        <tr class="<?= $service['actif'] ? '' : 'table-secondary text-muted' ?>">
                            <td class="fw-medium"><?= htmlspecialchars($service['nom']) ?></td>
                            <td><?= htmlspecialchars($service['description']) ?></td>
                            <td>
                                <?php if ($service['actif']): ?>
                                    <span class="badge bg-success">Actif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end no-print">
                                <a href="<?= $this->url('/services/edit/' . $service['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <form action="<?= $this->url('/services/delete/' . $service['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce service ?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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
                            if ($demande['statut'] === 'rejete') $rowClass = 'table-danger text-muted';
                            if ($demande['statut'] === 'traite') $rowClass = 'table-success';
                            if ($demande['statut'] === 'en_attente') $rowClass = 'table-warning';
                        ?>
                        <tr class="demande-row <?= $rowClass ?>" data-statut="<?= $demande['statut'] ?>">
                            <td class="fw-medium search-target"><?= htmlspecialchars($demande['titre']) ?></td>
                            <td><?= htmlspecialchars($demande['service_nom']) ?></td>
                            <td class="search-target"><?= htmlspecialchars($demande['user_prenom'] . ' ' . $demande['user_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($demande['date_creation'])) ?></td>
                            <td>
                                <form action="<?= $this->url('/demandes/updateStatut/' . $demande['id']) ?>" method="POST" class="d-inline no-print">
                                    <select name="statut" class="form-select form-select-sm d-inline-block w-auto fw-bold" onchange="this.form.submit()">
                                        <option value="en_attente" <?= $demande['statut'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                        <option value="en_cours" <?= $demande['statut'] === 'en_cours' ? 'selected' : '' ?>>En cours</option>
                                        <option value="traite" <?= $demande['statut'] === 'traite' ? 'selected' : '' ?>>Traité</option>
                                        <option value="rejete" <?= $demande['statut'] === 'rejete' ? 'selected' : '' ?>>Rejeté</option>
                                    </select>
                                </form>
                                <span class="d-none d-print-block fw-bold">
                                    <?php
                                    $statusLabels = ['en_attente' => 'En attente', 'en_cours' => 'En cours', 'traite' => 'Traité', 'rejete' => 'Rejeté'];
                                    echo $statusLabels[$demande['statut']] ?? $demande['statut'];
                                    ?>
                                </span>
                            </td>
                            <td class="text-end no-print">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="showDetails('<?= htmlspecialchars(addslashes($demande['titre'])) ?>', '<?= htmlspecialchars(addslashes(nl2br($demande['description']))) ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <form action="<?= $this->url('/demandes/delete_back/' . $demande['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
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

<script>
function showDetails(title, desc) {
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalDesc').innerHTML = desc;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

document.getElementById('searchDemande').addEventListener('input', filterTable);
document.getElementById('filterStatus').addEventListener('change', filterTable);

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
</script>
