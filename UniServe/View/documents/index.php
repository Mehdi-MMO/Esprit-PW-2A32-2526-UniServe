<div class="container-fluid py-4 min-vh-100">

    <?php
    // ===== NOTIFICATIONS FLASH =====
    if (!empty($_SESSION['notif_etudiant'])):
        $notif = $_SESSION['notif_etudiant'];
        $cert  = htmlspecialchars($notif['cert'], ENT_QUOTES, 'UTF-8');
        unset($_SESSION['notif_etudiant']);
    ?>
        <?php if ($notif['type'] === 'accepte'): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><strong>Bonne nouvelle !</strong> Votre demande pour <strong><?= $cert ?></strong> a été <strong>acceptée</strong>.</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-danger alert-dismissible fade show mx-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-x-circle-fill fs-5"></i>
                <div><strong>Demande refusée.</strong> Votre demande pour <strong><?= $cert ?></strong> n'a pas été retenue.</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['notif_quiz'])):
        $nq = $_SESSION['notif_quiz'];
        unset($_SESSION['notif_quiz']); ?>
        <?php if ($nq['statut'] === 'accepte'): ?>
            <div class="alert alert-success alert-dismissible fade show mx-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-trophy-fill fs-5 text-warning"></i>
                <div><strong>Félicitations !</strong> Score : <strong><?= (int) $nq['score'] ?>/5</strong> — Certification <strong>validée</strong> !</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php else: ?>
            <div class="alert alert-warning alert-dismissible fade show mx-3 mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-emoji-frown fs-5"></i>
                <div><strong>Score insuffisant :</strong> <strong><?= (int) $nq['score'] ?>/5</strong> — minimum 3/5 requis. Demande refusée.</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show mx-3 mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        <div>Votre demande a bien été soumise ! L'administration vous contactera.</div>
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <h1 class="text-center mb-2"><?= htmlspecialchars($title) ?></h1>
    <p class="text-center text-secondary mb-5">Consultez vos cours, vos certificats et soumettez une demande de passage.</p>

    <div class="px-3">

        <!-- ===== TABLEAU COURS ===== -->
        <div class="card shadow mb-4">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <h5 class="m-0"><i class="bi bi-journals me-2 text-info"></i>Liste des Cours</h5>
                <span class="badge bg-info text-dark"><?= count($cours) ?> cours</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Titre</th>
                                <th>Description</th>
                                <th>Formateur</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cours as $c): ?>
                            <tr class="cours-row" style="cursor:pointer;"
                                data-cours='<?= htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                                onclick="openCoursModal(JSON.parse(this.dataset.cours))">
                                <td class="fw-bold ps-4 text-info">
                                    <i class="bi bi-book-open me-1"></i><?= htmlspecialchars($c['titre']) ?>
                                </td>
                                <td class="small text-muted">
                                    <?= mb_strlen($c['description'] ?? '') > 60
                                        ? htmlspecialchars(mb_substr($c['description'], 0, 60)) . '…'
                                        : htmlspecialchars($c['description'] ?? '') ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <i class="bi bi-person me-1"></i><?= htmlspecialchars($c['formateur']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-1">
                                        <i class="bi bi-eye me-1"></i>Voir le cours
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($cours)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">Aucun cours disponible.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== QUIZ EN ATTENTE (envoyés par admin) ===== -->
        <?php
        $quizzesEnAttente = [];
        foreach (($demandes ?? []) as $d) {
            if (!empty($d['quiz']) && $d['quiz']['statut'] === 'en_attente') {
                $quizzesEnAttente[] = $d;
            }
        }
        ?>
        <?php if (!empty($quizzesEnAttente)): ?>
        <div class="card shadow mb-4 border-success border-2">
            <div class="card-header bg-success bg-opacity-10 d-flex align-items-center justify-content-between">
                <h5 class="m-0 text-success fw-bold">
                    <i class="bi bi-pencil-square me-2"></i>Quiz à Passer
                </h5>
                <span class="badge bg-success">
                    <?= count($quizzesEnAttente) ?> quiz disponible<?= count($quizzesEnAttente) > 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-success">
                            <tr>
                                <th class="ps-4">Certificat</th>
                                <th>Cours lié</th>
                                <th>Date demandée</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($quizzesEnAttente as $d): ?>
                            <tr>
                                <td class="fw-bold ps-4"><?= htmlspecialchars($d['nom_certificat']) ?></td>
                                <td>
                                    <?php if (!empty($d['titre_cours'])): ?>
                                        <span class="badge bg-info text-dark"><?= htmlspecialchars($d['titre_cours']) ?></span>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($d['date_souhaitee']) ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-success fw-bold btn-sm px-3"
                                            data-quiz='<?= htmlspecialchars(json_encode($d['quiz'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'
                                            onclick="openPasserQuizModal(JSON.parse(this.dataset.quiz))">
                                        <i class="bi bi-play-circle me-1"></i>Passer le Quiz
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- ===== TABLEAU CERTIFICATS ===== -->
        <div class="card shadow mb-4">
            <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                <h5 class="m-0"><i class="bi bi-patch-check me-2 text-warning"></i>Mes Certificats</h5>
                <span class="badge bg-warning text-dark">
                    <?= count($certificats) ?> certificat<?= count($certificats) !== 1 ? 's' : '' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Nom</th>
                                <th>Cours Lié</th>
                                <th>Organisation</th>
                                <th>Date</th>
                                <th>Fichier</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificats as $cert): ?>
                            <tr>
                                <td class="fw-bold ps-4"><?= htmlspecialchars($cert['nom_certificat']) ?></td>
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
                                           target="_blank" class="btn btn-info btn-sm">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    <?php else: ?><span class="text-muted">—</span><?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($certificats)): ?>
                            <tr><td colspan="5" class="text-center text-muted py-4">Aucun certificat disponible.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ===== MODALE DÉTAIL COURS ===== -->
<div class="modal fade" id="coursDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background:linear-gradient(135deg,#0dcaf0,#0891b2);color:#fff;">
                <h5 class="modal-title fw-bold"><i class="bi bi-book-open me-2"></i><span id="modalCoursTitre">—</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Formateur -->
                <div class="d-flex align-items-center gap-3 mb-4 p-3 rounded-3 bg-light">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-info text-white"
                         style="width:52px;height:52px;min-width:52px;font-size:1.4rem;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-bold" style="letter-spacing:.05em;">Formateur</div>
                        <div class="fw-bold fs-5" id="modalCoursFormateur">—</div>
                    </div>
                </div>

                <!-- Description courte -->
                <div class="mb-4" id="modalDescriptionWrapper">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-card-text me-1 text-info"></i>Description
                    </h6>
                    <div id="modalCoursDescription" class="p-3 rounded-3 border border-info border-opacity-25 bg-light"
                         style="line-height:1.6;"></div>
                </div>

                <!-- Contenu détaillé -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-file-text me-1 text-info"></i>Contenu du cours
                    </h6>
                    <div id="modalCoursContenu" class="p-3 rounded-3 border border-info border-opacity-25 bg-light"
                         style="line-height:1.8;white-space:pre-wrap;min-height:60px;max-height:280px;overflow-y:auto;"></div>
                </div>

                <!-- Image -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-image me-1 text-info"></i>Illustration
                    </h6>
                    <div id="modalCoursImageBox" class="rounded-3 d-flex flex-column align-items-center justify-content-center"
                         style="min-height:160px;background:linear-gradient(135deg,#e0f7fa,#b2ebf2);border:2px dashed #0dcaf0;">
                        <!-- rempli par JS -->
                    </div>
                </div>

                <!-- Fichiers -->
                <div class="mb-2">
                    <h6 class="text-uppercase text-muted fw-bold mb-2" style="font-size:.72rem;letter-spacing:.07em;">
                        <i class="bi bi-folder2-open me-1 text-info"></i>Fichiers & Ressources
                    </h6>
                    <div id="modalCoursFichiersBox" class="rounded-3 border p-2" style="background:#f8f9fa;">
                        <!-- rempli par JS -->
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x me-1"></i>Fermer
                </button>
                <button type="button" class="btn btn-success fw-bold px-4" onclick="openQuizDemandeModal()">
                    <i class="bi bi-pencil-square me-2"></i>Demander à passer un Quiz
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODALE DEMANDE DE QUIZ (formulaire date/heure) ===== -->
<div class="modal fade" id="quizDemandeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-calendar-check me-2"></i>Demande de Quiz</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="/UniServe/documents/demanderCertification" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="nom_certificat" id="qd_nom_certificat" value="Quiz Evaluation">
                    <input type="hidden" name="titre_cours"    id="qd_titre_cours">
                    <input type="hidden" name="organisation"   value="UniServe">

                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4">
                        <i class="bi bi-info-circle-fill"></i>
                        <div>Demande de quiz pour le cours : <strong id="qd_cours_nom">—</strong></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-calendar-event me-1 text-success"></i>
                            Date d'examen souhaitée <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" name="date_obtention"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-clock me-1 text-secondary"></i>Heure préférée
                        </label>
                        <select class="form-select" name="heure_preferee">
                            <option value="">— Indifférent —</option>
                            <option value="08:00">08:00</option><option value="09:00">09:00</option>
                            <option value="10:00">10:00</option><option value="11:00">11:00</option>
                            <option value="13:00">13:00</option><option value="14:00">14:00</option>
                            <option value="15:00">15:00</option><option value="16:00">16:00</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-paperclip me-1 text-secondary"></i>Justificatif (optionnel)
                        </label>
                        <input type="file" class="form-control" name="certif_file"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">PDF, JPG ou PNG — 5 Mo max</small>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold">
                            <i class="bi bi-chat-left-text me-1 text-secondary"></i>Notes
                        </label>
                        <textarea class="form-control" name="notes" rows="2"
                                  placeholder="Informations complémentaires…"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="backToCoursModal()">
                        <i class="bi bi-arrow-left me-1"></i>Retour
                    </button>
                    <button type="submit" class="btn btn-success fw-bold px-4">
                        <i class="bi bi-send me-2"></i>Soumettre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODALE PASSER LE QUIZ ===== -->
<div class="modal fade" id="passerQuizModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header px-4 py-3"
                 style="background:linear-gradient(135deg,#198754,#0f5132);color:#fff;">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-pencil-square me-2"></i>Quiz — <span id="quizCoursNomModal">—</span>
                    </h5>
                    <small class="opacity-75">Répondez à toutes les questions puis cliquez sur <strong>Valider</strong></small>
                </div>
                <div class="ms-auto text-center">
                    <div id="quizTimerBadge"
                         class="rounded-3 px-3 py-2 fw-bold fs-5"
                         style="background:rgba(255,255,255,.2);min-width:90px;letter-spacing:.05em;">
                        ⏱ 02:00
                    </div>
                    <div class="small opacity-75 mt-1">Temps restant</div>
                </div>
            </div>

            <div class="px-4 pt-3 pb-0">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <small class="text-muted fw-semibold">Progression</small>
                    <small class="text-muted"><span id="answeredCount">0</span> / <span id="totalCount">5</span> répondues</small>
                </div>
                <div class="progress" style="height:6px;">
                    <div id="quizProgressBar" class="progress-bar bg-success" style="width:0%;transition:width .3s;"></div>
                </div>
            </div>

            <form action="/UniServe/documents/passerQuiz" method="POST" id="quizForm">
                <input type="hidden" name="quiz_id" id="quizIdInput">

                <div class="modal-body px-4 py-3" id="quizQuestionsContainer"
                     style="max-height:60vh;overflow-y:auto;">
                    <!-- rempli par JS -->
                </div>

                <div class="modal-footer px-4 py-3 justify-content-between align-items-center"
                     style="border-top:2px solid #dee2e6;">
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <i class="bi bi-info-circle-fill text-success"></i>
                        Score minimum : <strong class="text-success">3 / 5</strong> pour valider la certification
                    </div>
                    <button type="submit" class="btn btn-success fw-bold px-5 py-2" id="quizValidateBtn">
                        <i class="bi bi-check2-circle me-2"></i>Valider mes réponses
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<style>
.cours-row:hover { background: rgba(13,202,240,.07) !important; }

/* Quiz option styling */
.quiz-option-label {
    display:flex; align-items:center; gap:.75rem;
    padding:.75rem 1rem; border:2px solid #dee2e6;
    border-radius:.5rem; cursor:pointer;
    transition:all .18s ease; user-select:none;
    background:#fff;
}
.quiz-option-label:hover {
    border-color:#198754;
    background:rgba(25,135,84,.06);
}
input[type=radio]:checked + .quiz-option-label {
    border-color:#198754;
    background:rgba(25,135,84,.12);
    color:#0f5132;
    font-weight:600;
}
input[type=radio]:checked + .quiz-option-label .quiz-letter {
    background:#198754;
    color:#fff;
}

.quiz-question-block {
    border:1px solid #e9ecef;
    border-radius:.75rem;
    padding:1.25rem;
    margin-bottom:1rem;
    background:#fafafa;
    transition:border-color .2s;
}
.quiz-question-block.answered {
    border-color:#198754;
    background:#f0fff4;
}

.quiz-letter {
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; min-width:28px;
    border-radius:50%; border:2px solid #dee2e6;
    font-weight:700; font-size:.8rem;
    transition:all .18s ease;
}

.timer-urgent {
    background:rgba(220,53,69,.9) !important;
    animation:pulse 1s infinite;
}
@keyframes pulse {
    0%,100% { transform:scale(1); }
    50%      { transform:scale(1.05); }
}
</style>

<script>
let currentCours   = null;
let quizTimer      = null;
let answeredCount  = 0;
let totalQuestions = 0;

// Helper : extrait les fichiers depuis n'importe quel format
function parseFichiers(data) {
    if (Array.isArray(data.fichiers))      return data.fichiers;
    if (typeof data.fichiers_json === 'string' && data.fichiers_json.length > 0) {
        try { return JSON.parse(data.fichiers_json) || []; } catch(e) { return []; }
    }
    if (Array.isArray(data.fichiers_json)) return data.fichiers_json;
    return [];
}

// ── Modal détail cours ─────────────────────────────────────────
function openCoursModal(cours) {
    currentCours = cours;
    document.getElementById('modalCoursTitre').textContent     = cours.titre || '—';
    document.getElementById('modalCoursFormateur').textContent = cours.formateur || '—';

    // Description
    const descWrapper = document.getElementById('modalDescriptionWrapper');
    const descBox     = document.getElementById('modalCoursDescription');
    if (cours.description && cours.description.trim() !== '') {
        descBox.textContent       = cours.description;
        descWrapper.style.display = '';
    } else {
        descWrapper.style.display = 'none';
    }

    // Contenu du cours
    const contenuBox = document.getElementById('modalCoursContenu');
    if (cours.contenu && cours.contenu.trim() !== '') {
        contenuBox.textContent = cours.contenu;
        contenuBox.classList.remove('text-muted','fst-italic');
    } else {
        contenuBox.innerHTML = '<span class="text-muted fst-italic">Aucun contenu détaillé disponible pour ce cours.</span>';
    }

    // Image
    const imgBox = document.getElementById('modalCoursImageBox');
    if (cours.image_path) {
        imgBox.style.background = 'none';
        imgBox.style.border     = 'none';
        imgBox.innerHTML = `<img src="/UniServe/public/uploads/cours/${cours.image_path}"
            class="rounded-3 w-100" style="max-height:220px;object-fit:cover;"
            onerror="this.parentElement.innerHTML='<div class=text-muted py-4>Image introuvable</div>'">`;
    } else {
        imgBox.style.background = 'linear-gradient(135deg,#e0f7fa,#b2ebf2)';
        imgBox.style.border     = '2px dashed #0dcaf0';
        imgBox.innerHTML = `
            <i class="bi bi-image text-info" style="font-size:2.5rem;opacity:.4;"></i>
            <div class="text-muted small mt-2">Aucune illustration pour ce cours</div>`;
    }

    // Fichiers
    const filesBox = document.getElementById('modalCoursFichiersBox');
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
                <span class="small fw-semibold">${nom}</span>
                <i class="bi bi-download ms-auto text-muted small"></i>
            </a>`;
        }).join('');
    } else {
        filesBox.innerHTML = `
            <div class="d-flex align-items-center gap-3 p-2 text-muted fst-italic">
                <i class="bi bi-cloud-upload text-info" style="font-size:1.6rem;opacity:.5;"></i>
                <span>Aucun fichier disponible pour ce cours.</span>
            </div>`;
    }

    new bootstrap.Modal(document.getElementById('coursDetailModal')).show();
}

function openQuizDemandeModal() {
    if (!currentCours) return;
    document.getElementById('qd_titre_cours').value     = currentCours.titre;
    document.getElementById('qd_cours_nom').textContent = currentCours.titre;
    document.getElementById('qd_nom_certificat').value  = 'Quiz — ' + currentCours.titre;
    bootstrap.Modal.getInstance(document.getElementById('coursDetailModal')).hide();
    setTimeout(() => new bootstrap.Modal(document.getElementById('quizDemandeModal')).show(), 350);
}

function backToCoursModal() {
    bootstrap.Modal.getInstance(document.getElementById('quizDemandeModal')).hide();
    setTimeout(() => new bootstrap.Modal(document.getElementById('coursDetailModal')).show(), 350);
}

// ── Quiz : passage par l'étudiant ──────────────────────────────
function openPasserQuizModal(quiz) {
    if (!quiz || !Array.isArray(quiz.questions) || quiz.questions.length === 0) {
        alert('Quiz invalide ou vide.');
        return;
    }

    document.getElementById('quizIdInput').value             = quiz.id;
    document.getElementById('quizCoursNomModal').textContent = quiz.cours_titre || '—';

    totalQuestions = quiz.questions.length;
    answeredCount  = 0;
    document.getElementById('totalCount').textContent    = totalQuestions;
    document.getElementById('answeredCount').textContent = 0;
    document.getElementById('quizProgressBar').style.width = '0%';

    const container = document.getElementById('quizQuestionsContainer');
    container.innerHTML = '';

    quiz.questions.forEach((q, i) => {
        const block = document.createElement('div');
        block.className = 'quiz-question-block';
        block.id = `qblock_${i}`;
        block.innerHTML = `
            <p class="fw-bold mb-3 d-flex align-items-start gap-2">
                <span class="badge rounded-pill text-bg-success px-2 py-1" style="min-width:26px;">${i + 1}</span>
                <span>${escapeHtml(q.question)}</span>
            </p>
            <div class="d-flex flex-column gap-2">
                ${q.options.map((opt, j) => `
                    <div>
                        <input type="radio" name="answer_${i}" id="q${i}o${j}" value="${j}"
                               class="visually-hidden" required
                               onchange="onAnswerChange(${i}, ${totalQuestions})">
                        <label for="q${i}o${j}" class="quiz-option-label">
                            <span class="quiz-letter">${['A','B','C','D'][j]}</span>
                            <span>${escapeHtml(opt)}</span>
                        </label>
                    </div>
                `).join('')}
            </div>`;
        container.appendChild(block);
    });

    // Reset le bouton de validation
    const btn = document.getElementById('quizValidateBtn');
    btn.classList.remove('btn-warning');
    btn.classList.add('btn-success');
    btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Valider mes réponses';

    // Timer 2 minutes
    startTimer(120);
    new bootstrap.Modal(document.getElementById('passerQuizModal')).show();
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function onAnswerChange(qIndex, total) {
    document.getElementById(`qblock_${qIndex}`).classList.add('answered');
    answeredCount = 0;
    for (let i = 0; i < total; i++) {
        if (document.querySelector(`input[name="answer_${i}"]:checked`)) answeredCount++;
    }
    document.getElementById('answeredCount').textContent = answeredCount;
    const pct = Math.round((answeredCount / total) * 100);
    document.getElementById('quizProgressBar').style.width = pct + '%';

    const btn = document.getElementById('quizValidateBtn');
    if (answeredCount === total) {
        btn.classList.remove('btn-success');
        btn.classList.add('btn-warning');
        btn.innerHTML = '<i class="bi bi-check2-circle me-2"></i>Valider mes réponses ✓';
    }
}

function startTimer(seconds) {
    clearInterval(quizTimer);
    const badge = document.getElementById('quizTimerBadge');
    let remaining = seconds;

    function tick() {
        const m = String(Math.floor(remaining / 60)).padStart(2,'0');
        const s = String(remaining % 60).padStart(2,'0');
        badge.textContent = `⏱ ${m}:${s}`;
        if (remaining <= 30) badge.classList.add('timer-urgent');
        if (remaining <= 0) {
            clearInterval(quizTimer);
            document.getElementById('quizForm').submit();
        }
        remaining--;
    }
    tick();
    quizTimer = setInterval(tick, 1000);
}

document.getElementById('passerQuizModal').addEventListener('hidden.bs.modal', () => {
    clearInterval(quizTimer);
});

document.getElementById('quizForm').addEventListener('submit', function(e) {
    const answered = parseInt(document.getElementById('answeredCount').textContent);
    const total    = parseInt(document.getElementById('totalCount').textContent);
    if (answered < total) {
        e.preventDefault();
        const missing = total - answered;
        if (confirm(`⚠️ Vous n'avez pas répondu à ${missing} question(s).\nVoulez-vous quand même valider ?`)) {
            clearInterval(quizTimer);
            this.submit();
        }
    } else {
        clearInterval(quizTimer);
    }
});
</script>