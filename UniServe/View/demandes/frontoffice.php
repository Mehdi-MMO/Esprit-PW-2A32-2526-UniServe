<style>
@media print {
    .btn, form, .no-print { display: none !important; }
    .card { border: none !important; box-shadow: none !important; }
    .badge { border: 1px solid #000; color: #000 !important; background: transparent !important; }
}
.search-bar { max-width: 300px; }
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

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <button class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Imprimer mon relevé</button>
    </div>
</div>

<!-- SECTION SERVICES -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h1 class="h3">Services Disponibles</h1>
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
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">Aucun service disponible.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($services as $service): ?>
                        <tr class="<?= $service['actif'] ? '' : 'table-secondary text-muted' ?>">
                            <td class="fw-medium"><?= htmlspecialchars($service['nom']) ?></td>
                            <td><?= htmlspecialchars($service['description']) ?></td>
                            <td>
                                <?php if ($service['actif']): ?>
                                    <span class="badge bg-success">Disponible</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Indisponible</span>
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

<hr class="mb-5">

<!-- SECTION DEMANDES -->
<div class="d-flex justify-content-between align-items-center mb-3 mt-4">
    <h1 class="h3">Mes Demandes</h1>
    <a href="<?= $this->url('/demandes/create') ?>" class="btn btn-primary no-print"><i class="bi bi-plus-lg me-2"></i>Nouvelle Demande</a>
</div>

<div class="d-flex justify-content-end gap-2 mb-3 no-print">
    <select id="filterStatus" class="form-select" style="width: 200px;">
        <option value="all">Tous les statuts</option>
        <option value="en_attente">En attente</option>
        <option value="en_cours">En cours</option>
        <option value="traite">Traité</option>
        <option value="rejete">Rejeté</option>
    </select>
    <input type="text" id="searchDemande" class="form-control search-bar" placeholder="Rechercher un titre...">
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="demandesTable">
                <thead class="table-light">
                    <tr>
                        <th>Titre</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Statut</th>
                        <th class="text-end no-print">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Aucune demande trouvée.</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($demandes as $demande): 
                            $rowClass = '';
                            if ($demande['statut'] === 'rejete') $rowClass = 'table-danger text-muted';
                            if ($demande['statut'] === 'traite') $rowClass = 'table-success';
                            if ($demande['statut'] === 'en_attente') $rowClass = 'table-warning';
                        ?>
                        <tr class="demande-row <?= $rowClass ?>" data-statut="<?= $demande['statut'] ?>">
                            <td class="fw-medium"><?= htmlspecialchars($demande['titre']) ?></td>
                            <td><?= htmlspecialchars($demande['service_nom']) ?></td>
                            <td><?= date('d/m/Y', strtotime($demande['date_creation'])) ?></td>
                            <td class="fw-bold">
                                <?php
                                $statusClasses = [
                                    'en_attente' => 'text-warning',
                                    'en_cours' => 'text-info',
                                    'traite' => 'text-success',
                                    'rejete' => 'text-danger'
                                ];
                                $statusLabels = [
                                    'en_attente' => 'En attente',
                                    'en_cours' => 'En cours',
                                    'traite' => 'Traité',
                                    'rejete' => 'Rejeté'
                                ];
                                $class = $statusClasses[$demande['statut']] ?? 'text-secondary';
                                $label = $statusLabels[$demande['statut']] ?? $demande['statut'];
                                ?>
                                <span class="<?= $class ?>"><i class="bi bi-circle-fill me-1" style="font-size:0.6rem;"></i> <?= $label ?></span>
                            </td>
                            <td class="text-end no-print">
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="showDetails('<?= htmlspecialchars(addslashes($demande['titre'])) ?>', '<?= htmlspecialchars(addslashes(nl2br($demande['description']))) ?>')">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <?php if ($demande['statut'] === 'en_attente'): ?>
                                    <a href="<?= $this->url('/demandes/edit/' . $demande['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                <?php endif; ?>

                                <form action="<?= $this->url('/demandes/delete/' . $demande['id']) ?>" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette demande ?');">
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
