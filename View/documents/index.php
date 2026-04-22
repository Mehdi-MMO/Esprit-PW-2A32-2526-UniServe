<div class="container-fluid py-4 bg-dark text-white min-vh-100">
    <h1 class="text-center mb-2"><?= $title ?></h1>
    <p class="text-center text-secondary mb-5">Consultez vos cours, vos certificats et soumettez une demande de passage.</p>

    <div class="px-3">

        <!-- ===== TABLEAU COURS ===== -->
        <div class="card bg-secondary text-white shadow mb-4">
            <div class="card-header bg-dark border-bottom border-secondary d-flex align-items-center justify-content-between">
                <h5 class="m-0"><i class="bi bi-journals me-2 text-info"></i>Liste des Cours</h5>
                <span class="badge bg-info text-dark"><?= count($cours) ?> cours</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">Titre</th>
                                <th>Description</th>
                                <th>Formateur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cours as $c): ?>
                            <tr>
                                <td class="fw-bold ps-4"><?= htmlspecialchars($c['titre']) ?></td>
                                <td class="small text-info">
                                    <?= strlen($c['description']) > 60
                                        ? substr(htmlspecialchars($c['description']), 0, 60) . '…'
                                        : htmlspecialchars($c['description']) ?>
                                </td>
                                <td><?= htmlspecialchars($c['formateur']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($cours)): ?>
                            <tr><td colspan="3" class="text-center text-muted py-4">Aucun cours disponible.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===== TABLEAU CERTIFICATS ===== -->
        <div class="card bg-secondary text-white shadow mb-4">
            <div class="card-header bg-dark border-bottom border-secondary d-flex align-items-center justify-content-between">
                <h5 class="m-0"><i class="bi bi-patch-check me-2 text-warning"></i>Mes Certificats</h5>
                <span class="badge bg-warning text-dark"><?= count($certificats) ?> certificat<?= count($certificats) !== 1 ? 's' : '' ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0">
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
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($cert['organisation']) ?></td>
                                <td><?= htmlspecialchars($cert['date_obtention']) ?></td>
                                <td>
                                    <?php if (!empty($cert['fichier_path'])): ?>
                                        <a href="/UniServe/public/uploads/certificats/<?= htmlspecialchars($cert['fichier_path']) ?>"
                                           target="_blank" class="btn btn-info btn-sm">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
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

        <!-- ===== FORMULAIRE DEMANDE DE CERTIFICATION ===== -->
        <div class="card bg-secondary text-white shadow mb-4">
            <div class="card-header bg-dark border-bottom border-secondary">
                <h5 class="m-0"><i class="bi bi-send me-2 text-success"></i>Demande de Passage de Certification</h5>
                <p class="text-secondary small mb-0 mt-1">Choisissez le certificat que vous souhaitez passer et votre créneau préféré.</p>
            </div>
            <div class="card-body">

                <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill"></i>
                    Votre demande a bien été soumise ! L'administration vous contactera pour confirmation.
                </div>
                <?php endif; ?>

                <form action="/UniServe/documents/demanderCertification" method="POST" enctype="multipart/form-data">

                    <div class="row g-3">

                        <!-- Certificat souhaité -->
                        <div class="col-md-6">
                            <label for="nom_certificat" class="form-label fw-semibold">
                                <i class="bi bi-award me-1 text-warning"></i>Certificat souhaité <span class="text-danger">*</span>
                            </label>
                            <select class="form-select bg-dark text-white border-secondary" id="nom_certificat" name="nom_certificat" required>
                                <option value="" disabled selected>— Choisissez un certificat —</option>
                                <optgroup label="Réseaux &amp; Infrastructure">
                                    <option value="Cisco CCNA">Cisco CCNA</option>
                                    <option value="CompTIA Network+">CompTIA Network+</option>
                                    <option value="CompTIA Security+">CompTIA Security+</option>
                                </optgroup>
                                <optgroup label="Cloud">
                                    <option value="AWS Certified Solutions Architect">AWS Certified Solutions Architect</option>
                                    <option value="Microsoft Azure Fundamentals AZ-900">Microsoft Azure Fundamentals (AZ-900)</option>
                                    <option value="Google Associate Cloud Engineer">Google Associate Cloud Engineer</option>
                                </optgroup>
                                <optgroup label="Développement">
                                    <option value="Python Institute PCEP">Python Institute PCEP</option>
                                    <option value="Oracle Java SE 11 Developer">Oracle Java SE 11 Developer</option>
                                    <option value="MongoDB Developer">MongoDB Developer</option>
                                </optgroup>
                                <optgroup label="Gestion de projet">
                                    <option value="PMI PMP">PMI Project Management Professional (PMP)</option>
                                    <option value="Scrum Master PSM I">Scrum Master PSM I</option>
                                </optgroup>
                                <option value="Autre">Autre (préciser dans les notes)</option>
                            </select>
                        </div>

                        <!-- Cours associé -->
                        <div class="col-md-6">
                            <label for="titre_cours" class="form-label fw-semibold">
                                <i class="bi bi-book me-1 text-info"></i>Cours associé
                            </label>
                            <select class="form-select bg-dark text-white border-secondary" id="titre_cours" name="titre_cours">
                                <option value="">— Aucun cours associé —</option>
                                <?php foreach ($cours as $c): ?>
                                    <option value="<?= htmlspecialchars($c['titre']) ?>">
                                        <?= htmlspecialchars($c['titre']) ?> — <?= htmlspecialchars($c['formateur']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Organisme -->
                        <div class="col-md-6">
                            <label for="organisation" class="form-label fw-semibold">
                                <i class="bi bi-building me-1 text-secondary"></i>Organisme / Centre d'examen <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control bg-dark text-white border-secondary"
                                   id="organisation" name="organisation"
                                   placeholder="ex : Pearson VUE, Udemy, Coursera…"
                                   required>
                        </div>

                        <!-- Date souhaitée -->
                        <div class="col-md-3">
                            <label for="date_obtention" class="form-label fw-semibold">
                                <i class="bi bi-calendar-event me-1 text-warning"></i>Date souhaitée <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   class="form-control bg-dark text-white border-secondary"
                                   id="date_obtention" name="date_obtention"
                                   min="<?= date('Y-m-d') ?>"
                                   required>
                        </div>

                        <!-- Heure préférée -->
                        <div class="col-md-3">
                            <label for="heure_preferee" class="form-label fw-semibold">
                                <i class="bi bi-clock me-1 text-secondary"></i>Heure préférée
                            </label>
                            <select class="form-select bg-dark text-white border-secondary" id="heure_preferee" name="heure_preferee">
                                <option value="">— Indifférent —</option>
                                <option value="08:00">08:00</option>
                                <option value="09:00">09:00</option>
                                <option value="10:00">10:00</option>
                                <option value="11:00">11:00</option>
                                <option value="13:00">13:00</option>
                                <option value="14:00">14:00</option>
                                <option value="15:00">15:00</option>
                                <option value="16:00">16:00</option>
                            </select>
                        </div>

                        <div class="col-12"><hr class="border-secondary my-1"></div>

                        <!-- Fichier justificatif -->
                        <div class="col-md-6">
                            <label for="certif_file" class="form-label fw-semibold">
                                <i class="bi bi-paperclip me-1 text-secondary"></i>Justificatif <small class="text-secondary">(PDF, JPG, PNG — max 5 Mo)</small>
                            </label>
                            <input type="file"
                                   class="form-control bg-dark text-white border-secondary"
                                   id="certif_file" name="certif_file"
                                   accept=".pdf,.jpg,.jpeg,.png">
                        </div>

                        <!-- Notes -->
                        <div class="col-md-6">
                            <label for="notes" class="form-label fw-semibold">
                                <i class="bi bi-chat-left-text me-1 text-secondary"></i>Notes / Précisions
                            </label>
                            <textarea class="form-control bg-dark text-white border-secondary"
                                      id="notes" name="notes" rows="2"
                                      placeholder="Contraintes horaires, besoins particuliers, nom complet si Autre…"></textarea>
                        </div>

                    </div>

                    <div class="d-flex align-items-center gap-3 mt-4 flex-wrap">
                        <button type="submit" class="btn btn-success px-4 fw-bold">
                            <i class="bi bi-send me-2"></i>Soumettre la demande
                        </button>
                        <span class="text-secondary small">
                            Les champs marqués <span class="text-danger">*</span> sont obligatoires.
                        </span>
                    </div>

                </form>
            </div>
        </div>

    </div>
</div>