<div class="container-fluid py-4 min-vh-100">
    <!-- STATS -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-3 p-3 bg-primary bg-opacity-10">
                        <i class="bi bi-journal-bookmark-fill text-primary fs-4"></i>
                    </div>
                    <div>
                        <div class="fs-2 fw-bold"><?= count($cours ?? []) ?></div>
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
                        <div class="fs-2 fw-bold"><?= count($certificats ?? []) ?></div>
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
                        <div class="fs-2 fw-bold"><?= count(array_filter($demandes ?? [], fn($d) => $d['statut'] === 'en_attente')) ?></div>
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
                        <div class="fs-2 fw-bold"><?= count($demandes ?? []) ?></div>
                        <div class="text-muted small">Demandes totales</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CERTIFICATION REQUESTS -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="bi bi-send me-2 text-warning"></i>Demandes de Passage de Certification
                    <?php $pending = count(array_filter($demandes ?? [], fn($d) => $d['statut'] === 'en_attente')); ?>
                    <?php if ($pending > 0): ?>
                        <span class="badge bg-warning text-dark ms-2"><?= $pending ?> en attente</span>
                    <?php endif; ?>
                </h5>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <div class="input-group input-group-sm" style="max-width:200px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Rechercher…"
                               oninput="searchTable('demandesTable', this.value)">
                    </div>
                </div>
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
                        <th>Justificatif</th>
                        <th>Soumis le</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($demandes)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Aucune demande reçue.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($demandes ?? [] as $i => $d):
                        $quiz = $d['quiz'] ?? null;
                    ?>
                    <tr class="demande-row" data-statut="<?= htmlspecialchars($d['statut']) ?>">
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($d['nom_certificat']) ?></td>
                        <td>
                            <?php if (!empty($d['titre_cours'])): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($d['titre_cours']) ?></span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($d['organisation']) ?></td>
                        <td><?= htmlspecialchars($d['date_souhaitee']) ?></td>
                        <td>
                            <?php if (!empty($d['fichier_path'])): ?>
                                <a href="/public/uploads/certificats/<?= htmlspecialchars($d['fichier_path']) ?>"
                                   target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="bi bi-file-earmark-text"></i> Voir
                                </a>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td class="text-muted small"><?= date('d/m/Y H:i', strtotime($d['soumise_le'])) ?></td>
                        <td>
                            <?php if ($d['statut'] === 'en_attente'): ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i>En attente</span>
                            <?php elseif ($d['statut'] === 'quiz_envoye'): ?>
                                <span class="badge bg-info text-dark"><i class="bi bi-pencil-square me-1"></i>Quiz envoyé</span>
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
                            <div class="d-flex gap-1 flex-wrap">
                                <form method="POST" action="/backofficeDocuments/envoyerQuiz" style="display:inline;">
                                    <input type="hidden" name="demande_id" value="<?= (int) $d['id'] ?>">
                                    <button type="submit" class="btn btn-primary btn-sm"
                                            title="Envoyer un Quiz IA">
                                        <i class="bi bi-robot me-1"></i>Quiz IA
                                    </button>
                                </form>
                            </div>
                            <?php elseif ($d['statut'] === 'quiz_envoye'): ?>
                                <span class="text-info small fst-italic">En attente du quiz</span>
                            <?php else: ?>
                                <span class="text-muted small">
                                    Traitée le <?= !empty($d['traitee_le']) ? date('d/m/Y', strtotime($d['traitee_le'])) : '—' ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- COURSES TABLE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="bi bi-journal-bookmark me-2 text-primary"></i>Liste des Cours
                    <span class="badge bg-primary ms-2"><?= count($cours ?? []) ?></span>
                </h5>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <div class="input-group input-group-sm" style="max-width:200px;">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" placeholder="Rechercher…"
                               oninput="searchTable('coursTableBO', this.value)">
                    </div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCoursModal">
                        <i class="bi bi-plus-lg me-1"></i>Ajouter un Cours
                    </button>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="coursTableBO">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Titre</th>
                        <th>Formateur</th>
                        <th class="text-center">Contenu IA</th>
                        <th class="text-center">Fichiers</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cours)): ?>
                        <tr><td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-journal-x fs-3 d-block mb-2 opacity-50"></i>
                            Aucun cours enregistré
                        </td></tr>
                    <?php endif; ?>
                    <?php foreach ($cours ?? [] as $i => $c):
                        $nbFichiers = count($c['fichiers'] ?? []);
                        $hasContenu = !empty(trim($c['contenu'] ?? ''));
                    ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td>
                            <div class="fw-bold text-primary">
                                <i class="bi bi-book-open me-1 text-info"></i><?= htmlspecialchars($c['titre']) ?>
                            </div>
                            <?php if (!empty($c['description'])): ?>
                                <div class="small text-muted mt-1">
                                    <?= htmlspecialchars(mb_strimwidth($c['description'], 0, 70, '…')) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary">
                                <i class="bi bi-person me-1"></i><?= htmlspecialchars($c['formateur']) ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ($hasContenu): ?>
                                <span class="badge bg-success" title="L'IA pourra générer des questions précises">
                                    <i class="bi bi-check-circle me-1"></i><?= mb_strlen($c['contenu']) ?> car.
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark" title="Sans contenu, l'IA génèrera des questions génériques">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Vide
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($nbFichiers > 0): ?>
                                <span class="badge bg-info text-dark">
                                    <i class="bi bi-paperclip me-1"></i><?= $nbFichiers ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-outline-primary btn-sm" title="Voir">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="/backofficeDocuments/deleteCours" style="display:inline;"
                                      onsubmit="return confirm('Supprimer ce cours ?')">
                                    <input type="hidden" name="titre" value="<?= htmlspecialchars($c['titre']) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- CERTIFICATES TABLE -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0">
                    <i class="bi bi-patch-check me-2 text-warning"></i>Certificats Enregistrés
                    <span class="badge bg-warning text-dark ms-2"><?= count($certificats ?? []) ?></span>
                </h5>
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#addCertificatModal">
                    <i class="bi bi-plus-lg me-1"></i>Ajouter
                </button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nom</th>
                        <th>Cours Associé</th>
                        <th>Organisation</th>
                        <th>Date</th>
                        <th>Fichier</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($certificats)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Aucun certificat enregistré.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($certificats ?? [] as $i => $cert): ?>
                    <tr>
                        <td class="text-muted"><?= $i + 1 ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($cert['nom_certificat']) ?></td>
                        <td>
                            <?php if (!empty($cert['titre_cours'])): ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($cert['titre_cours']) ?></span>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($cert['organisation']) ?></td>
                        <td><?= htmlspecialchars($cert['date_obtention']) ?></td>
                        <td>
                            <?php if (!empty($cert['fichier_path'])): ?>
                                <a href="/public/uploads/certificats/<?= htmlspecialchars($cert['fichier_path']) ?>"
                                   target="_blank" class="btn btn-info btn-sm">
                                    <i class="bi bi-file-earmark-text"></i>
                                </a>
                            <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-outline-warning btn-sm" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" action="/backofficeDocuments/deleteCertificat" style="display:inline;"
                                      onsubmit="return confirm('Supprimer ?')">
                                    <input type="hidden" name="id" value="<?= (int) $cert['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function searchTable(tableId, value) {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(value.toLowerCase()) ? '' : 'none';
    });
}
</script>
