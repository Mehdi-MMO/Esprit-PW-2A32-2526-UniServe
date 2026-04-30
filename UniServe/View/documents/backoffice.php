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
                    <div class="fs-2 fw-bold"><?= (int) $nb_en_attente ?></div>
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
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 flex-wrap gap-2">
        <h5 class="mb-0">
            <i class="bi bi-send me-2 text-warning"></i>Demandes de Passage de Certification
            <?php if ($nb_en_attente > 0): ?>
                <span class="badge bg-warning text-dark ms-2"><?= (int) $nb_en_attente ?> en attente</span>
            <?php endif; ?>
        </h5>
        <div class="btn-group btn-group-sm" role="group">
            <button type="button" class="btn btn-outline-secondary active" onclick="filterDemandes('all', this)">Toutes</button>
            <button type="button" class="btn btn-outline-warning"   onclick="filterDemandes('en_attente', this)">En attente</button>
            <button type="button" class="btn btn-outline-info"      onclick="filterDemandes('quiz_envoye', this)">Quiz envoyé</button>
            <button type="button" class="btn btn-outline-success"   onclick="filterDemandes('accepte', this)">Acceptées</button>
            <button type="button" class="btn btn-outline-danger"    onclick="filterDemandes('refuse', this)">Refusées</button>
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
                    <th>Quiz</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($demandes)): ?>
                <tr><td colspan="10" class="text-center text-muted py-4">Aucune demande reçue.</td></tr>
                <?php endif; ?>

                <?php foreach ($demandes as $i => $d):
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
                            <a href="/UniServe/public/uploads/certificats/<?= htmlspecialchars($d['fichier_path']) ?>"
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
                        <?php if ($quiz): ?>
                            <?php if ($quiz['statut'] === 'en_attente'): ?>
                                <span class="badge bg-info text-dark"><i class="bi bi-hourglass me-1"></i>En cours</span>
                            <?php elseif ($quiz['statut'] === 'accepte'): ?>
                                <span class="badge bg-success"><i class="bi bi-check me-1"></i>Réussi (<?= (int) $quiz['score'] ?>/5)</span>
                            <?php elseif ($quiz['statut'] === 'refuse'): ?>
                                <span class="badge bg-danger"><i class="bi bi-x me-1"></i>Échoué (<?= (int) $quiz['score'] ?>/5)</span>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-primary btn-sm ms-1"
                                    data-quiz='<?= htmlspecialchars(json_encode($quiz, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                                    onclick="voirQuizModal(JSON.parse(this.dataset.quiz))">
                                <i class="bi bi-eye"></i>
                            </button>
                        <?php else: ?>
                            <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($d['statut'] === 'en_attente'): ?>
                        <div class="d-flex gap-1 flex-wrap">
                            <button type="button" class="btn btn-success btn-sm"
                                    onclick="openDecisionModal(<?= (int) $d['id'] ?>, 'accepter', '<?= htmlspecialchars($d['nom_certificat'], ENT_QUOTES) ?>')"
                                    title="Accepter"><i class="bi bi-check-lg"></i></button>
                            <button type="button" class="btn btn-danger btn-sm"
                                    onclick="openDecisionModal(<?= (int) $d['id'] ?>, 'refuser', '<?= htmlspecialchars($d['nom_certificat'], ENT_QUOTES) ?>')"
                                    title="Refuser"><i class="bi bi-x-lg"></i></button>
                            <button type="button" class="btn btn-primary btn-sm"
                                    onclick="envoyerQuiz(<?= (int) $d['id'] ?>, '<?= htmlspecialchars($d['nom_certificat'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['titre_cours'] ?? $d['nom_certificat'], ENT_QUOTES) ?>')"
                                    title="Envoyer un Quiz IA">
                                <i class="bi bi-robot me-1"></i>Quiz IA
                            </button>
                            <?php if (!empty($d['notes'])): ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm"
                                    data-bs-toggle="tooltip" title="<?= htmlspecialchars($d['notes'], ENT_QUOTES) ?>">
                                <i class="bi bi-chat-left-text"></i>
                            </button>
                            <?php endif; ?>
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

<!-- ===== TABLE COURS ===== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
        <h5 class="mb-0">
            <i class="bi bi-journal-bookmark me-2 text-primary"></i>Liste des Cours
            <span class="badge bg-primary ms-2"><?= count($cours) ?></span>
        </h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addCoursModal">
            <i class="bi bi-plus-lg me-1"></i>Ajouter un Cours
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
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
                        Aucun cours enregistré — cliquez sur "Ajouter un Cours" pour commencer
                    </td></tr>
                <?php endif; ?>
                <?php foreach ($cours as $i => $c):
                    $coursJson  = htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                    $nbFichiers = count($c['fichiers'] ?? []);
                    $hasContenu = !empty(trim($c['contenu'] ?? ''));
                ?>
                <tr>
                    <td class="ps-4 text-muted"><?= $i + 1 ?></td>
                    <td>
                        <button type="button" class="btn btn-link p-0 fw-bold text-primary text-decoration-none"
                                data-cours='<?= $coursJson ?>'
                                onclick="openCoursDetailBO(JSON.parse(this.dataset.cours))">
                            <i class="bi bi-book-open me-1 text-info"></i><?= htmlspecialchars($c['titre']) ?>
                        </button>
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
                            <button type="button" class="btn btn-outline-primary btn-sm"
                                    data-cours='<?= $coursJson ?>'
                                    onclick="openCoursDetailBO(JSON.parse(this.dataset.cours))"
                                    title="Voir">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm"
                                    data-cours='<?= $coursJson ?>'
                                    onclick="openEditCours(JSON.parse(this.dataset.cours))"
                                    title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/UniServe/backofficeDocuments/deleteCours" method="POST"
                                  onsubmit="return confirm('Supprimer le cours \'<?= htmlspecialchars($c['titre'], ENT_QUOTES) ?>\' ?\nCette action est irréversible.')"
                                  class="d-inline">
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
                    <th>#</th><th>Nom du certificat</th><th>Cours lié</th>
                    <th>Organisation</th><th>Date d'obtention</th><th>Fichier</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($certificats as $i => $cert): ?>
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
                            <a href="/UniServe/public/uploads/certificats/<?= htmlspecialchars($cert['fichier_path']) ?>"
                               target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-file-earmark-text"></i> Voir
                            </a>
                        <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-warning btn-sm"
                                data-cert='<?= htmlspecialchars(json_encode($cert, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                                onclick="openEditCertif(JSON.parse(this.dataset.cert))">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="/UniServe/backofficeDocuments/deleteCertificat" method="POST"
                                onsubmit="return confirm('Supprimer ce certificat ?')">
                                <input type="hidden" name="id" value="<?= (int) $cert['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
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

<!-- ===== MODALE DÉCISION (Accepter / Refuser direct) ===== -->
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
                <label class="form-label fw-bold">
                    Commentaire pour l'étudiant <small class="text-muted">(optionnel)</small>
                </label>
                <textarea name="commentaire" id="decisionCommentaire" class="form-control" rows="3"
                          placeholder="Ex : Créneau confirmé au 15 mai à 10h00…"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn" id="decisionSubmitBtn">Confirmer</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE ENVOYER QUIZ IA ===== -->
<div class="modal fade" id="envoyerQuizModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="/UniServe/backofficeDocuments/envoyerQuiz" method="POST" class="modal-content"
              onsubmit="document.getElementById('eqSubmitBtn').disabled=true;
                        document.getElementById('eqSubmitBtn').innerHTML='<span class=\'spinner-border spinner-border-sm me-2\'></span>Génération en cours…';">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);color:#fff;">
                <h5 class="modal-title fw-bold"><i class="bi bi-robot me-2"></i>Générer un Quiz par IA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="eqDemandeId">
                <div class="alert alert-primary d-flex align-items-start gap-2 mb-3">
                    <i class="bi bi-info-circle-fill mt-1"></i>
                    <div>
                        L'IA <strong>qwen2.5:14b</strong> (Ollama local) va générer automatiquement
                        <strong>5 questions QCM</strong> sur le cours
                        <strong id="eqCoursNom">—</strong> pour la certification
                        <strong id="eqCertifNom">—</strong>.
                    </div>
                </div>
                <div class="p-3 rounded-3 border bg-light">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Quiz personnalisé selon le contenu du cours et ses PDF</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>5 questions avec 4 choix de réponses</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Score minimum : <strong>3/5</strong> pour valider la certification</span>
                    </div>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-clock me-1"></i>
                    La génération peut prendre <strong>30s à 2min</strong> selon votre machine.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary fw-bold px-4" id="eqSubmitBtn">
                    <i class="bi bi-robot me-2"></i>Générer et envoyer le Quiz
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE VOIR QUIZ ===== -->
<div class="modal fade" id="voirQuizModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-clipboard-data me-2"></i>Aperçu du Quiz</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="voirQuizBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODALE DÉTAIL COURS (backoffice) ===== -->
<div class="modal fade" id="coursDetailBOModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0d6efd,#0a58ca);color:#fff;">
                <h5 class="modal-title fw-bold"><i class="bi bi-book-open me-2"></i><span id="boCoursTitre">—</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3 bg-light">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white"
                         style="width:52px;height:52px;min-width:52px;font-size:1.4rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold">Formateur</div>
                        <div class="fw-bold fs-5" id="boCoursFormateur">—</div>
                    </div>
                </div>

                <div class="mb-4" id="boCoursDescriptionWrapper">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-card-text me-1 text-primary"></i>Description
                    </h6>
                    <div id="boCoursDescription" class="p-3 rounded-3 border bg-light"></div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-robot me-1 text-primary"></i>Contenu du cours
                        <span class="badge bg-info text-dark ms-1" style="font-size:.6rem;">IA</span>
                    </h6>
                    <div id="boCoursContenu" class="p-3 rounded-3 border bg-light"
                         style="line-height:1.8;white-space:pre-wrap;min-height:60px;max-height:300px;overflow-y:auto;font-size:.9rem;"></div>
                </div>

                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-image me-1 text-primary"></i>Illustration
                    </h6>
                    <div id="boCoursImage" class="rounded-3 d-flex flex-column align-items-center justify-content-center"
                         style="min-height:160px;background:linear-gradient(135deg,#e3f2fd,#bbdefb);border:2px dashed #0d6efd;">
                    </div>
                </div>

                <div class="mb-2">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-folder2-open me-1 text-primary"></i>Fichiers & Ressources
                    </h6>
                    <div id="boCoursFichiers" class="rounded-3 border p-2" style="background:#f8f9fa;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODALE AJOUTER COURS ===== -->
<div class="modal fade" id="addCoursModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="/UniServe/backofficeDocuments/storeCours" method="POST"
              enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Nouveau Cours</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" placeholder="Titre du cours (clé unique)" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Formateur <span class="text-danger">*</span></label>
                        <input type="text" name="formateur" placeholder="Nom du formateur" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description courte</label>
                        <input type="text" name="description" placeholder="Résumé en une ligne…" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-robot me-1 text-primary"></i>
                            Contenu du cours
                            <small class="text-danger fw-normal">(utilisé par l'IA pour générer le quiz)</small>
                        </label>
                        <textarea name="contenu" id="add_cours_contenu" class="form-control" rows="6"
                                  placeholder="Collez ici le contenu détaillé du cours : chapitres, définitions, formules, protocoles, concepts clés, exemples...
Plus ce champ est riche, plus les questions du quiz seront précises et pertinentes."></textarea>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Copiez-collez directement le texte de vos PDF, slides ou notes de cours.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-image me-1 text-info"></i>Image du cours
                            <small class="text-muted fw-normal">(JPG, PNG — max 5 Mo)</small>
                        </label>
                        <input type="file" name="cours_image" class="form-control"
                               accept=".jpg,.jpeg,.png,.gif,.webp"
                               onchange="previewImage(this,'add_img_preview')">
                        <div class="mt-2" id="add_img_preview"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-paperclip me-1 text-secondary"></i>Fichiers / Documents
                            <small class="text-muted fw-normal">(PDF, Word, PPT — max 20 Mo)</small>
                        </label>
                        <input type="file" name="cours_fichiers[]" id="add_cours_fichiers" class="form-control" multiple
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                               onchange="extractPdfToContenu(this, 'add_cours_contenu')">
                        <small class="text-muted">Vous pouvez sélectionner plusieurs fichiers.</small>
                        <div id="add_pdf_status" class="mt-1"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-primary fw-bold">
                    <i class="bi bi-check-lg me-1"></i>Enregistrer le cours
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===== MODALE MODIFIER COURS ===== -->
<div class="modal fade" id="editCoursModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="/UniServe/backofficeDocuments/editCours" method="POST"
              enctype="multipart/form-data" class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Modifier le Cours</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="old_titre"          id="edit_cours_old_titre">
                <input type="hidden" name="fichiers_existants" id="edit_cours_fichiers_existants">

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Titre <span class="text-danger">*</span></label>
                        <input type="text" name="titre" id="edit_cours_titre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Formateur <span class="text-danger">*</span></label>
                        <input type="text" name="formateur" id="edit_cours_formateur" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Description courte</label>
                        <input type="text" name="description" id="edit_cours_desc" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-robot me-1 text-primary"></i>
                            Contenu du cours
                            <small class="text-danger fw-normal">(utilisé par l'IA)</small>
                        </label>
                        <textarea name="contenu" id="edit_cours_contenu" class="form-control" rows="6"
                                  placeholder="Collez ici le contenu détaillé : chapitres, définitions, formules, protocoles, concepts clés…"></textarea>
                        <div class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Plus ce champ est riche, plus les questions du quiz seront précises.
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-image me-1 text-info"></i>Image du cours
                        </label>
                        <div id="edit_img_current" class="mb-2"></div>
                        <input type="file" name="cours_image" class="form-control"
                               accept=".jpg,.jpeg,.png,.gif,.webp"
                               onchange="previewImage(this,'edit_img_preview')">
                        <div class="mt-2" id="edit_img_preview"></div>
                        <small class="text-muted">Laissez vide pour garder l'image actuelle.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">
                            <i class="bi bi-paperclip me-1 text-secondary"></i>Fichiers / Documents
                        </label>
                        <div id="edit_fichiers_current" class="mb-2"></div>
                        <input type="file" name="cours_fichiers[]" id="edit_cours_fichiers" class="form-control" multiple
                               accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                               onchange="extractPdfToContenu(this, 'edit_cours_contenu')">
                        <small class="text-muted">Nouveaux fichiers s'ajoutent aux existants.</small>
                        <div id="edit_pdf_status" class="mt-1"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" class="btn btn-warning fw-bold">
                    <i class="bi bi-check-lg me-1"></i>Mettre à jour
                </button>
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
                <small class="text-muted">PDF, JPG ou PNG — 5 Mo max</small>
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
// ── Helpers ────────────────────────────────────────────────────────
function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function parseFichiers(data) {
    if (Array.isArray(data.fichiers))      return data.fichiers;
    if (typeof data.fichiers_json === 'string' && data.fichiers_json.length > 0) {
        try { return JSON.parse(data.fichiers_json) || []; } catch(e) { return []; }
    }
    if (Array.isArray(data.fichiers_json)) return data.fichiers_json;
    return [];
}

function previewImage(input, targetId) {
    const target = document.getElementById(targetId);
    target.innerHTML = '';
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            target.innerHTML = `<img src="${e.target.result}"
                class="rounded-2 border mt-1"
                style="max-height:120px;max-width:100%;object-fit:cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ── Edit cours ─────────────────────────────────────────────────────
function openEditCours(data) {
    document.getElementById('edit_cours_old_titre').value = data.titre || '';
    document.getElementById('edit_cours_titre').value     = data.titre || '';
    document.getElementById('edit_cours_formateur').value = data.formateur || '';
    document.getElementById('edit_cours_desc').value      = data.description || '';
    document.getElementById('edit_cours_contenu').value   = data.contenu || '';
    document.getElementById('edit_img_preview').innerHTML = '';
    document.getElementById('edit_pdf_status').innerHTML  = '';

    const fichiers = parseFichiers(data);
    document.getElementById('edit_cours_fichiers_existants').value =
        fichiers.length > 0 ? JSON.stringify(fichiers) : '';

    const imgBox = document.getElementById('edit_img_current');
    if (data.image_path) {
        imgBox.innerHTML = `
            <div class="d-flex align-items-center gap-2 mb-1">
                <img src="/UniServe/public/uploads/cours/${data.image_path}"
                     class="rounded-2 border" style="max-height:80px;max-width:120px;object-fit:cover;">
                <small class="text-muted">Image actuelle</small>
            </div>`;
    } else {
        imgBox.innerHTML = '<small class="text-muted fst-italic">Aucune image actuellement.</small>';
    }

    const filesBox = document.getElementById('edit_fichiers_current');
    if (fichiers.length > 0) {
        filesBox.innerHTML = '<div class="mb-1"><small class="text-muted fw-bold">Fichiers actuels :</small></div>' +
            fichiers.map(f => `
                <span class="badge bg-secondary me-1 mb-1 p-2">
                    <i class="bi bi-file-earmark me-1"></i>${escapeHtml(f.nom || f.path)}
                </span>`).join('');
    } else {
        filesBox.innerHTML = '<small class="text-muted fst-italic">Aucun fichier actuellement.</small>';
    }

    new bootstrap.Modal(document.getElementById('editCoursModal')).show();
}

// ── Edit certif ────────────────────────────────────────────────────
function openEditCertif(data) {
    document.getElementById('edit_certif_id').value   = data.id;
    document.getElementById('edit_certif_nom').value  = data.nom_certificat;
    document.getElementById('edit_certif_org').value  = data.organisation;
    document.getElementById('edit_certif_date').value = data.date_obtention;
    const sel = document.getElementById('edit_certif_cours');
    for (let o of sel.options) o.selected = (o.value === data.titre_cours);
    new bootstrap.Modal(document.getElementById('editCertifModal')).show();
}

// ── Décision (accepter / refuser) ──────────────────────────────────
function openDecisionModal(id, action, nomCertificat) {
    const form   = document.getElementById('decisionForm');
    const header = document.getElementById('decisionModalHeader');
    const title  = document.getElementById('decisionModalTitle');
    const desc   = document.getElementById('decisionDesc');
    const btn    = document.getElementById('decisionSubmitBtn');
    document.getElementById('decisionId').value = id;

    if (action === 'accepter') {
        form.action = '/UniServe/backofficeDocuments/accepterDemande';
        header.className = 'modal-header bg-success text-white';
        title.textContent = 'Accepter la demande';
        desc.innerHTML = `Accepter la demande pour : <strong>${escapeHtml(nomCertificat)}</strong>`;
        btn.className = 'btn btn-success'; btn.textContent = '✔ Accepter';
    } else {
        form.action = '/UniServe/backofficeDocuments/refuserDemande';
        header.className = 'modal-header bg-danger text-white';
        title.textContent = 'Refuser la demande';
        desc.innerHTML = `Refuser la demande pour : <strong>${escapeHtml(nomCertificat)}</strong>`;
        btn.className = 'btn btn-danger'; btn.textContent = '✖ Refuser';
    }
    document.getElementById('decisionCommentaire').value = '';
    new bootstrap.Modal(document.getElementById('decisionModal')).show();
}

// ── Envoyer Quiz IA ────────────────────────────────────────────────
function envoyerQuiz(id, nomCertif, coursTitre) {
    document.getElementById('eqDemandeId').value      = id;
    document.getElementById('eqCertifNom').textContent = nomCertif;
    document.getElementById('eqCoursNom').textContent  = coursTitre;
    const btn = document.getElementById('eqSubmitBtn');
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-robot me-2"></i>Générer et envoyer le Quiz';
    new bootstrap.Modal(document.getElementById('envoyerQuizModal')).show();
}

// ── Voir Quiz (admin) ──────────────────────────────────────────────
function voirQuizModal(quiz) {
    const body = document.getElementById('voirQuizBody');
    let scoreHtml = '';
    if (quiz.score !== null && quiz.score !== undefined) {
        const cls = quiz.score >= 3 ? 'success' : 'danger';
        scoreHtml = `<div class="alert alert-${cls} mb-3">
            <strong>Score obtenu : ${quiz.score}/5</strong>
            ${quiz.score >= 3 ? ' — Réussi ✔' : ' — Échoué ✖'}
        </div>`;
    }
    body.innerHTML = scoreHtml + (quiz.questions || []).map((q, i) => `
        <div class="mb-3 p-3 rounded-3 border">
            <p class="fw-bold mb-2"><span class="badge bg-primary me-2">${i+1}</span>${escapeHtml(q.question)}</p>
            <ul class="list-unstyled mb-0">
                ${q.options.map((opt, j) => `
                    <li class="py-1 ${j === q.correct ? 'text-success fw-bold' : ''}">
                        ${j === q.correct ? '✔ ' : ''}<strong>${['A','B','C','D'][j]}.</strong> ${escapeHtml(opt)}
                    </li>`).join('')}
            </ul>
        </div>`).join('');
    new bootstrap.Modal(document.getElementById('voirQuizModal')).show();
}

// ── Détail cours BO ────────────────────────────────────────────────
function openCoursDetailBO(cours) {
    document.getElementById('boCoursTitre').textContent     = cours.titre || '—';
    document.getElementById('boCoursFormateur').textContent = cours.formateur || '—';

    const descWrapper = document.getElementById('boCoursDescriptionWrapper');
    const descBox     = document.getElementById('boCoursDescription');
    if (cours.description && cours.description.trim() !== '') {
        descBox.textContent       = cours.description;
        descWrapper.style.display = '';
    } else {
        descWrapper.style.display = 'none';
    }

    const contenuBox = document.getElementById('boCoursContenu');
    if (cours.contenu && cours.contenu.trim() !== '') {
        contenuBox.textContent = cours.contenu;
        contenuBox.classList.remove('text-muted','fst-italic');
    } else {
        contenuBox.innerHTML = `<span class="text-muted fst-italic">
            <i class="bi bi-exclamation-triangle me-1 text-warning"></i>
            Aucun contenu défini — l'IA générera des questions génériques.
        </span>`;
    }

    const imgBox = document.getElementById('boCoursImage');
    if (cours.image_path) {
        imgBox.style.background = 'none';
        imgBox.style.border     = 'none';
        imgBox.style.minHeight  = 'auto';
        imgBox.innerHTML = `<img src="/UniServe/public/uploads/cours/${cours.image_path}"
            class="rounded-3 w-100" style="max-height:240px;object-fit:cover;"
            onerror="this.parentElement.innerHTML='<div class=text-muted py-4>Image introuvable</div>'">`;
    } else {
        imgBox.style.background = 'linear-gradient(135deg,#e3f2fd,#bbdefb)';
        imgBox.style.border     = '2px dashed #0d6efd';
        imgBox.style.minHeight  = '160px';
        imgBox.innerHTML = `
            <i class="bi bi-image text-primary" style="font-size:2.5rem;opacity:.4;"></i>
            <div class="text-muted small mt-2">Aucune illustration</div>`;
    }

    const filesBox = document.getElementById('boCoursFichiers');
    const fichiers = parseFichiers(cours);
    if (fichiers.length > 0) {
        const icons = {pdf:'bi-file-earmark-pdf text-danger', doc:'bi-file-earmark-word text-primary',
                       docx:'bi-file-earmark-word text-primary', ppt:'bi-file-earmark-ppt text-warning',
                       pptx:'bi-file-earmark-ppt text-warning'};
        filesBox.innerHTML = fichiers.map(f => {
            const nom  = f.nom || f.path || '';
            const ext  = nom.split('.').pop().toLowerCase();
            const icon = icons[ext] || 'bi-file-earmark text-secondary';
            return `<a href="/UniServe/public/uploads/cours/${f.path}" target="_blank"
                class="d-flex align-items-center gap-2 p-2 rounded-2 text-decoration-none text-dark mb-1"
                style="background:#fff;border:1px solid #dee2e6;"
                onmouseover="this.style.background='#f0f9ff'"
                onmouseout="this.style.background='#fff'">
                <i class="bi ${icon} fs-5"></i>
                <span class="small fw-semibold">${escapeHtml(nom)}</span>
                <i class="bi bi-download ms-auto text-muted small"></i>
            </a>`;
        }).join('');
    } else {
        filesBox.innerHTML = `
            <div class="d-flex align-items-center gap-3 p-2 text-muted fst-italic">
                <i class="bi bi-cloud-upload text-primary" style="font-size:1.6rem;opacity:.5;"></i>
                <span>Aucun fichier disponible pour ce cours.</span>
            </div>`;
    }

    new bootstrap.Modal(document.getElementById('coursDetailBOModal')).show();
}

// ── Filter demandes ────────────────────────────────────────────────
function filterDemandes(statut, btn) {
    document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('#demandesTable .demande-row').forEach(row => {
        row.style.display = (statut === 'all' || row.dataset.statut === statut) ? '' : 'none';
    });
}

// ── Bootstrap tooltips ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
});

// ── PDF → Contenu auto-extraction (côté navigateur via PDF.js) ────
async function extractPdfToContenu(input, textareaId) {
    const textarea = document.getElementById(textareaId);
    const statusId = input.id === 'add_cours_fichiers' ? 'add_pdf_status' : 'edit_pdf_status';
    const status   = document.getElementById(statusId);

    const pdfFiles = Array.from(input.files).filter(f => f.name.toLowerCase().endsWith('.pdf'));
    if (pdfFiles.length === 0) return;

    status.innerHTML = '<span class="text-info"><i class="bi bi-hourglass-split me-1"></i>Extraction du texte PDF en cours…</span>';

    try {
        if (!window.pdfjsLib) {
            await loadScript('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js');
            window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }

        let allText = '';
        for (const file of pdfFiles.slice(0, 2)) {
            const arrayBuffer = await file.arrayBuffer();
            const pdf         = await window.pdfjsLib.getDocument({ data: arrayBuffer }).promise;
            let fileText      = `=== ${file.name} ===\n`;
            for (let p = 1; p <= Math.min(pdf.numPages, 20); p++) {
                const page    = await pdf.getPage(p);
                const content = await page.getTextContent();
                const pageText = content.items.map(i => i.str).join(' ');
                if (pageText.trim()) fileText += pageText + '\n';
            }
            allText += fileText + '\n';
        }
        allText = allText.trim();

        if (allText.length > 20) {
            const existing = textarea.value.trim();
            textarea.value = existing ? existing + '\n\n' + allText : allText;
            status.innerHTML = `<span class="text-success"><i class="bi bi-check-circle me-1"></i>
                ${allText.length} caractères extraits du PDF et ajoutés au contenu !</span>`;
        } else {
            status.innerHTML = '<span class="text-warning"><i class="bi bi-exclamation-triangle me-1"></i>PDF scanné ou protégé — copiez le texte manuellement.</span>';
        }
    } catch (err) {
        console.error('PDF extraction error:', err);
        status.innerHTML = '<span class="text-danger"><i class="bi bi-x-circle me-1"></i>Erreur lors de la lecture du PDF.</span>';
    }
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        if (document.querySelector(`script[src="${src}"]`)) { resolve(); return; }
        const s = document.createElement('script');
        s.src = src; s.onload = resolve; s.onerror = reject;
        document.head.appendChild(s);
    });
}
</script>