<!-- ===== STATS ===== -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                    <i class="bi bi-journal-bookmark-fill text-primary fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= count($cours) ?></div>
                    <div class="text-muted small">Cours enregistrés</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-success bg-opacity-10">
                    <i class="bi bi-patch-check-fill text-success fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= count($certificats) ?></div>
                    <div class="text-muted small">Certificats enregistrés</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-warning bg-opacity-10">
                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= $nb_en_attente ?></div>
                    <div class="text-muted small">Demandes en attente</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 p-3 bg-info bg-opacity-10">
                    <i class="bi bi-send-check-fill text-info fs-4"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold"><?= count($demandes) ?></div>
                    <div class="text-muted small">Demandes totales</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== DEMANDES DE CERTIFICATION ===== -->
<div class="card border-0 shadow-sm mb-4" id="demandes">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            <i class="bi bi-send me-2 text-warning"></i>Demandes de Passage de Certification
            <?php if ($nb_en_attente > 0): ?>
                <span class="badge bg-warning text-dark ms-2"><?= $nb_en_attente ?> en attente</span>
            <?php endif; ?>
        </h5>
        <!-- Filter tabs -->
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary active" onclick="filterDemandes('all', this)">Toutes</button>
            <button type="button" class="btn btn-outline-warning" onclick="filterDemandes('en_attente', this)">En attente</button>
            <button type="button" class="btn btn-outline-success" onclick="filterDemandes('accepte', this)">Acceptées</button>
            <button type="button" class="btn btn-outline-danger" onclick="filterDemandes('refuse', this)">Refusées</button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="demandesTable">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Certificat souhaité</th>
                    <th>Cours lié</th>
                    <th>Organisme</th>
                    <th>Date souhaitée</th>
                    <th>Heure</th>
                    <th>Justificatif</th>
                    <th>Soumis le</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($demandes)): ?>
                <tr><td colspan="10" class="text-center text-muted py-4">Aucune demande reçue.</td></tr>
                <?php endif; ?>
                <?php foreach ($demandes as $i => $d): ?>
                <tr class="demande-row" data-statut="<?= htmlspecialchars($d['statut']) ?>">
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($d['nom_certificat']) ?></td>
                    <td>
                        <?php if (!empty($d['titre_cours'])): ?>
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($d['titre_cours']) ?></span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($d['organisation']) ?></td>
                    <td><?= htmlspecialchars($d['date_souhaitee']) ?></td>
                    <td><?= !empty($d['heure_preferee']) ? htmlspecialchars($d['heure_preferee']) : '<span class="text-muted">—</span>' ?></td>
                    <td>
                        <?php if (!empty($d['fichier_path'])): ?>
                            <a href="/UniServe/public/uploads/certificats/<?= htmlspecialchars($d['fichier_path']) ?>"
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-file-earmark-text"></i> Voir
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($d['soumise_le'])) ?></td>
                    <td>
                        <?php if ($d['statut'] === 'en_attente'): ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En attente</span>
                        <?php elseif ($d['statut'] === 'accepte'): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Acceptée</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Refusée</span>
                        <?php endif; ?>
                        <?php if (!empty($d['commentaire_admin'])): ?>
                            <br><small class="text-muted fst-italic"><?= htmlspecialchars($d['commentaire_admin']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($d['statut'] === 'en_attente'): ?>
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="openDecisionModal(<?= (int)$d['id'] ?>, 'accepter', '<?= htmlspecialchars($d['nom_certificat'], ENT_QUOTES) ?>')">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button type="button" class="btn btn-danger btn-sm"
                                    onclick="openDecisionModal(<?= (int)$d['id'] ?>, 'refuser', '<?= htmlspecialchars($d['nom_certificat'], ENT_QUOTES) ?>')">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <?php if (!empty($d['notes'])): ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    title="<?= htmlspecialchars($d['notes'], ENT_QUOTES) ?>">
                                <i class="bi bi-chat-left-text"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                        <?php else: ?>
                            <span class="text-muted small">Traitée le <?= $d['traitee_le'] ? date('d/m/Y', strtotime($d['traitee_le'])) : '—' ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== TABLE COURS ===== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-journal-bookmark me-2 text-primary"></i>Liste des Cours</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCoursModal">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un Cours
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Titre</th>
                    <th>Description</th>
                    <th>Formateur</th>
                    <th>Certificats liés</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cours as $i => $c): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td class="fw-bold text-primary"><?= htmlspecialchars($c['titre']) ?></td>
                    <td class="text-muted small">
                        <?= strlen($c['description']) > 60
                            ? substr(htmlspecialchars($c['description']), 0, 60) . '…'
                            : htmlspecialchars($c['description']) ?>
                    </td>
                    <td><?= htmlspecialchars($c['formateur']) ?></td>
                    <td>
                        <?php $nb = count(array_filter($certificats, fn($cert) => $cert['titre_cours'] === $c['titre'])); ?>
                        <span class="badge bg-secondary"><?= $nb ?> certificat<?= $nb > 1 ? 's' : '' ?></span>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm"
                                onclick="openEditCours(<?= htmlspecialchars(json_encode($c)) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/UniServe/backofficeDocuments/deleteCours" method="POST"
                                onsubmit="return confirm('Supprimer ce cours et ses certificats liés ?')">
                                <input type="hidden" name="titre" value="<?= htmlspecialchars($c['titre']) ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cours)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Aucun cours enregistré</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== TABLE CERTIFICATS ===== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0"><i class="bi bi-patch-check me-2 text-success"></i>Liste des Certificats</h5>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addCertifModal">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un Certificat
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nom du certificat</th>
                    <th>Cours lié</th>
                    <th>Organisation</th>
                    <th>Date d'obtention</th>
                    <th>Fichier</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificats as $i => $cert): ?>
                <tr>
                    <td class="text-muted"><?= $i + 1 ?></td>
                    <td class="fw-bold"><?= htmlspecialchars($cert['nom_certificat']) ?></td>
                    <td><span class="badge bg-info text-dark"><?= htmlspecialchars($cert['titre_cours'] ?? '') ?></span></td>
                    <td><?= htmlspecialchars($cert['organisation']) ?></td>
                    <td><?= htmlspecialchars($cert['date_obtention']) ?></td>
                    <td>
                        <?php if (!empty($cert['fichier_path'])): ?>
                            <a href="/UniServe/public/uploads/certificats/<?= htmlspecialchars($cert['fichier_path']) ?>"
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-file-earmark-text"></i> Voir
                            </a>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm"
                                onclick="openEditCertif(<?= htmlspecialchars(json_encode($cert)) ?>)">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/UniServe/backofficeDocuments/deleteCertificat" method="POST"
                                onsubmit="return confirm('Supprimer ce certificat ?')">
                                <input type="hidden" name="id" value="<?= (int)$cert['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($certificats)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">Aucun certificat enregistré</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===== MODALE DÉCISION (Accepter / Refuser) ===== -->
<div class="modal fade" id="decisionModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="decisionForm" method="POST" class="modal-content">
            <div class="modal-header" id="decisionModalHeader">
                <h5 class="modal-title" id="decisionModalTitle">Décision</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="decisionId">
                <p id="decisionDesc" class="mb-3"></p>
                <label class="form-label fw-bold">Commentaire pour l'étudiant <small class="text-muted">(optionnel)</small></label>
                <textarea name="commentaire" id="decisionCommentaire" class="form-control" rows="3"
                          placeholder="Ex : Créneau confirmé au 15 mai à 10h00 / Motif de refus…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn" id="decisionSubmitBtn">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE AJOUTER COURS ===== -->
<div class="modal fade" id="addCoursModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/UniServe/backofficeDocuments/storeCours" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouveau Cours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" placeholder="Titre (Clé unique)" class="form-control mb-3" required>
                <label class="form-label fw-bold">Formateur <span class="text-danger">*</span></label>
                <input type="text" name="formateur" placeholder="Nom du formateur" class="form-control mb-3" required>
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" placeholder="Description du cours" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE MODIFIER COURS ===== -->
<div class="modal fade" id="editCoursModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/UniServe/backofficeDocuments/editCours" method="POST" class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier le Cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="old_titre" id="edit_cours_old_titre">
                <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                <input type="text" name="titre" id="edit_cours_titre" class="form-control mb-3" required>
                <label class="form-label fw-bold">Formateur <span class="text-danger">*</span></label>
                <input type="text" name="formateur" id="edit_cours_formateur" class="form-control mb-3" required>
                <label class="form-label fw-bold">Description</label>
                <textarea name="description" id="edit_cours_desc" class="form-control" rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE AJOUTER CERTIFICAT ===== -->
<div class="modal fade" id="addCertifModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/UniServe/backofficeDocuments/storeCertificat" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouveau Certificat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label fw-bold">Cours associé <span class="text-danger">*</span></label>
                <select name="titre_cours" class="form-select mb-3" required>
                    <option value="">-- Sélectionner un cours --</option>
                    <?php foreach ($cours as $c): ?>
                        <option value="<?= htmlspecialchars($c['titre']) ?>"><?= htmlspecialchars($c['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label fw-bold">Nom du certificat <span class="text-danger">*</span></label>
                <input type="text" name="nom_certificat" placeholder="Ex: Certificat Python Avancé" class="form-control mb-3" required>
                <label class="form-label fw-bold">Organisation <span class="text-danger">*</span></label>
                <input type="text" name="organisation" placeholder="Ex: Coursera, Udemy" class="form-control mb-3" required>
                <label class="form-label fw-bold">Date d'obtention <span class="text-danger">*</span></label>
                <input type="date" name="date_obtention" class="form-control mb-3" required>
                <label class="form-label fw-bold">Fichier du certificat</label>
                <input type="file" name="certif_file" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                <small class="text-muted">PDF, JPG ou PNG — 5 MB max</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE MODIFIER CERTIFICAT ===== -->
<div class="modal fade" id="editCertifModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="/UniServe/backofficeDocuments/editCertificat" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Modifier le Certificat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_certif_id">
                <label class="form-label fw-bold">Cours associé <span class="text-danger">*</span></label>
                <select name="titre_cours" id="edit_certif_cours" class="form-select mb-3" required>
                    <option value="">-- Sélectionner un cours --</option>
                    <?php foreach ($cours as $c): ?>
                        <option value="<?= htmlspecialchars($c['titre']) ?>"><?= htmlspecialchars($c['titre']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label fw-bold">Nom du certificat <span class="text-danger">*</span></label>
                <input type="text" name="nom_certificat" id="edit_certif_nom" class="form-control mb-3" required>
                <label class="form-label fw-bold">Organisation <span class="text-danger">*</span></label>
                <input type="text" name="organisation" id="edit_certif_org" class="form-control mb-3" required>
                <label class="form-label fw-bold">Date d'obtention <span class="text-danger">*</span></label>
                <input type="date" name="date_obtention" id="edit_certif_date" class="form-control mb-3" required>
                <label class="form-label fw-bold">Fichier du certificat</label>
                <input type="file" name="certif_file" class="form-control" accept=".pdf,.jpg,.png,.jpeg">
                <small class="text-muted">Laissez vide pour garder le fichier actuel</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-warning"><i class="bi bi-check-lg me-1"></i>Mettre à jour</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Existing JS (cours / certif modals) ──────────────────────────
function openEditCours(data) {
    document.getElementById('edit_cours_old_titre').value = data.titre;
    document.getElementById('edit_cours_titre').value     = data.titre;
    document.getElementById('edit_cours_formateur').value = data.formateur;
    document.getElementById('edit_cours_desc').value      = data.description;
    new bootstrap.Modal(document.getElementById('editCoursModal')).show();
}

function openEditCertif(data) {
    document.getElementById('edit_certif_id').value   = data.id;
    document.getElementById('edit_certif_nom').value  = data.nom_certificat;
    document.getElementById('edit_certif_org').value  = data.organisation;
    document.getElementById('edit_certif_date').value = data.date_obtention;
    const sel = document.getElementById('edit_certif_cours');
    for (let o of sel.options) {
        o.selected = (o.value === data.titre_cours);
    }
    new bootstrap.Modal(document.getElementById('editCertifModal')).show();
}

// ── Decision modal (accept / refuse) ────────────────────────────
function openDecisionModal(id, action, nomCertificat) {
    const form    = document.getElementById('decisionForm');
    const header  = document.getElementById('decisionModalHeader');
    const title   = document.getElementById('decisionModalTitle');
    const desc    = document.getElementById('decisionDesc');
    const btn     = document.getElementById('decisionSubmitBtn');
    const idInput = document.getElementById('decisionId');

    idInput.value = id;

    if (action === 'accepter') {
        form.action        = '/UniServe/backofficeDocuments/accepterDemande';
        header.className   = 'modal-header bg-success text-white';
        title.textContent  = 'Accepter la demande';
        desc.innerHTML     = `Vous êtes sur le point d'<strong>accepter</strong> la demande pour le certificat :<br><strong>${nomCertificat}</strong>`;
        btn.className      = 'btn btn-success';
        btn.textContent    = '✔ Accepter';
    } else {
        form.action        = '/UniServe/backofficeDocuments/refuserDemande';
        header.className   = 'modal-header bg-danger text-white';
        title.textContent  = 'Refuser la demande';
        desc.innerHTML     = `Vous êtes sur le point de <strong>refuser</strong> la demande pour le certificat :<br><strong>${nomCertificat}</strong>`;
        btn.className      = 'btn btn-danger';
        btn.textContent    = '✖ Refuser';
    }

    document.getElementById('decisionCommentaire').value = '';
    new bootstrap.Modal(document.getElementById('decisionModal')).show();
}

// ── Filter rows by status ────────────────────────────────────────
function filterDemandes(statut, btn) {
    document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    document.querySelectorAll('#demandesTable .demande-row').forEach(row => {
        row.style.display = (statut === 'all' || row.dataset.statut === statut) ? '' : 'none';
    });
}

// ── Bootstrap tooltips ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
});
</script>