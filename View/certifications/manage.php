<?php
require_once __DIR__ . '/../shared/helpers.php';

$cours = $cours ?? [];
$certificats = $certificats ?? [];
$demandes = $demandes ?? [];
$nb_en_attente = $nb_en_attente ?? 0;
$flash = $flash ?? null;
?>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <div class="us-kicker mb-1">DOCAC — administration</div>
        <h1 class="h3 mb-0"><?= htmlspecialchars((string) ($title ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="text-muted small mb-0">Gestion du catalogue cours / certificats et traitement des demandes. Les étudiants utilisent le portail <a href="<?= $this->url('/certifications') ?>">Certifications</a> (front-office).</p>
    </div>
    <a class="btn btn-outline-secondary btn-sm" href="<?= $this->url('/backoffice/dashboard') ?>">Tableau de bord</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fs-4 fw-bold"><?= count($cours) ?></div><div class="text-muted small">Cours</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fs-4 fw-bold"><?= count($certificats) ?></div><div class="text-muted small">Certificats</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fs-4 fw-bold text-warning"><?= (int) $nb_en_attente ?></div><div class="text-muted small">En attente</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="fs-4 fw-bold"><?= count($demandes) ?></div><div class="text-muted small">Demandes</div></div></div></div>
</div>

<div class="card shadow-sm mb-4" id="demandes">
    <div class="card-header fw-semibold">Demandes de certification</div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>#</th><th>Demandeur</th><th>Certificat</th><th>Cours</th><th>Statut</th><th>Quiz</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($demandes as $i => $d): ?>
                    <?php
                    $quiz = $d['quiz'] ?? null;
                    $did = (int) ($d['id'] ?? 0);
                    $statut = (string) ($d['statut'] ?? '');
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($d['demandeur_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars((string) ($d['nom_certificat'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="small"><?= htmlspecialchars((string) ($d['titre_cours'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge text-bg-secondary"><?= htmlspecialchars($statut, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td class="small">
                            <?php if (is_array($quiz)): ?>
                                <?= htmlspecialchars((string) ($quiz['statut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                <?php if ($quiz['score'] !== null): ?>(<?= (int) $quiz['score'] ?>/5)<?php endif; ?>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td style="min-width: 220px;">
                            <?php
                            $terminal = in_array($statut, ['accepte', 'refuse'], true);
                            ?>
                            <?php if (!empty($d['fichier_path'])): ?>
                                <a class="btn btn-sm btn-outline-secondary mb-1" href="<?= $this->url('/certifications/download/demande/' . rawurlencode((string) $d['fichier_path'])) ?>">Pièce jointe</a>
                            <?php endif; ?>
                            <?php if (!$terminal): ?>
                                <form method="post" class="small">
                                    <input type="hidden" name="id" value="<?= $did ?>">
                                    <label class="form-label mb-0 text-muted">Commentaire (optionnel)</label>
                                    <textarea class="form-control form-control-sm mb-1" name="commentaire" rows="2" placeholder="Visible avec la décision"></textarea>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php if ($statut === 'en_attente'): ?>
                                            <button type="submit" class="btn btn-sm btn-primary" formaction="<?= $this->url('/certifications/envoyerQuiz') ?>" onclick="return confirm('Générer le quiz peut prendre 1–3 minutes. Continuer ?');">Envoyer quiz</button>
                                        <?php endif; ?>
                                        <?php if ($statut === 'quiz_envoye'): ?>
                                            <span class="align-self-center small text-muted me-1">Quiz envoyé — décision manuelle possible.</span>
                                        <?php endif; ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success" formaction="<?= $this->url('/certifications/accepterDemande') ?>">Accepter</button>
                                        <button type="submit" class="btn btn-sm btn-outline-danger" formaction="<?= $this->url('/certifications/refuserDemande') ?>">Refuser</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($demandes === []): ?><tr><td colspan="7" class="text-muted p-3">Aucune demande.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Nouveau cours</div>
            <div class="card-body">
                <form method="post" action="<?= $this->url('/certifications/storeCours') ?>" enctype="multipart/form-data" class="small">
                    <div class="mb-2"><input class="form-control form-control-sm" name="titre" placeholder="Titre unique" required></div>
                    <div class="mb-2"><textarea class="form-control form-control-sm" name="description" rows="2"></textarea></div>
                    <div class="mb-2"><input class="form-control form-control-sm" name="formateur" placeholder="Formateur"></div>
                    <div class="mb-2"><textarea class="form-control form-control-sm" name="contenu" rows="2"></textarea></div>
                    <div class="mb-2"><input class="form-control form-control-sm" type="file" name="cours_image" accept="image/*"></div>
                    <div class="mb-2"><input class="form-control form-control-sm" type="file" name="cours_fichiers[]" multiple></div>
                    <button class="btn btn-primary btn-sm" type="submit">Créer</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Nouveau certificat</div>
            <div class="card-body">
                <form method="post" action="<?= $this->url('/certifications/storeCertificat') ?>" enctype="multipart/form-data" class="small">
                    <div class="mb-2"><input class="form-control form-control-sm" name="nom_certificat" required></div>
                    <div class="mb-2"><input class="form-control form-control-sm" type="date" name="date_obtention" required></div>
                    <div class="mb-2"><input class="form-control form-control-sm" name="organisation" required></div>
                    <div class="mb-2"><input class="form-control form-control-sm" name="titre_cours" required></div>
                    <div class="mb-2"><input class="form-control form-control-sm" type="file" name="certif_file"></div>
                    <button class="btn btn-primary btn-sm" type="submit">Ajouter</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mt-4 mb-4">
    <div class="card-header fw-semibold">Cours — édition</div>
    <div class="card-body">
        <?php foreach ($cours as $c): ?>
            <?php
            $titre = (string) ($c['titre'] ?? '');
            $fichJson = htmlspecialchars(json_encode($c['fichiers'] ?? [], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 align-items-start">
                    <div class="fw-semibold"><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php if (!empty($c['image_path'])): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= $this->url('/certifications/download/cours/' . rawurlencode((string) $c['image_path'])) ?>">Image</a>
                        <?php endif; ?>
                        <form method="post" action="<?= $this->url('/certifications/deleteCours') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce cours ?');">
                            <input type="hidden" name="titre" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </div>
                <details class="mt-2">
                    <summary class="small text-primary" style="cursor:pointer;">Modifier ce cours</summary>
                    <form method="post" action="<?= $this->url('/certifications/editCours') ?>" enctype="multipart/form-data" class="row g-2 small mt-2">
                        <input type="hidden" name="old_titre" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="fichiers_existants" value="<?= $fichJson ?>">
                        <div class="col-md-6"><input class="form-control form-control-sm" name="titre" value="<?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-6"><input class="form-control form-control-sm" name="formateur" value="<?= htmlspecialchars((string) ($c['formateur'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"></div>
                        <div class="col-12"><textarea class="form-control form-control-sm" name="description" rows="2"><?= htmlspecialchars((string) ($c['description'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        <div class="col-12"><textarea class="form-control form-control-sm" name="contenu" rows="2"><?= htmlspecialchars((string) ($c['contenu'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea></div>
                        <div class="col-md-6"><input class="form-control form-control-sm" type="file" name="cours_image" accept="image/*"></div>
                        <div class="col-md-6"><input class="form-control form-control-sm" type="file" name="cours_fichiers[]" multiple></div>
                        <div class="col-12"><button type="submit" class="btn btn-sm btn-primary">Enregistrer</button></div>
                    </form>
                </details>
            </div>
        <?php endforeach; ?>
        <?php if ($cours === []): ?><p class="text-muted mb-0">Aucun cours.</p><?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header fw-semibold">Certificats — édition</div>
    <div class="card-body">
        <?php foreach ($certificats as $cert): ?>
            <?php $cid = (int) ($cert['id'] ?? 0); ?>
            <div class="border rounded p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars((string) ($cert['nom_certificat'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="small text-muted"><?= htmlspecialchars((string) ($cert['date_obtention'] ?? ''), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars((string) ($cert['titre_cours'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                    </div>
                    <div class="d-flex gap-1 flex-wrap">
                        <?php if (!empty($cert['fichier_path'])): ?>
                            <a class="btn btn-sm btn-outline-secondary" href="<?= $this->url('/certifications/download/cert/' . rawurlencode((string) $cert['fichier_path'])) ?>">Fichier</a>
                        <?php endif; ?>
                        <form method="post" action="<?= $this->url('/certifications/deleteCertificat') ?>" class="d-inline" onsubmit="return confirm('Supprimer ce certificat ?');">
                            <input type="hidden" name="id" value="<?= $cid ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </div>
                <details class="mt-2">
                    <summary class="small text-primary" style="cursor:pointer;">Modifier</summary>
                    <form method="post" action="<?= $this->url('/certifications/editCertificat') ?>" enctype="multipart/form-data" class="row g-2 small mt-2">
                        <input type="hidden" name="id" value="<?= $cid ?>">
                        <div class="col-md-6"><label class="form-label">Nom</label><input class="form-control form-control-sm" name="nom_certificat" value="<?= htmlspecialchars((string) ($cert['nom_certificat'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Date</label><input class="form-control form-control-sm" type="date" name="date_obtention" value="<?= htmlspecialchars((string) ($cert['date_obtention'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Organisme</label><input class="form-control form-control-sm" name="organisation" value="<?= htmlspecialchars((string) ($cert['organisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-md-6"><label class="form-label">Cours lié</label><input class="form-control form-control-sm" name="titre_cours" value="<?= htmlspecialchars((string) ($cert['titre_cours'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" required></div>
                        <div class="col-12"><label class="form-label">Nouveau fichier (optionnel)</label><input class="form-control form-control-sm" type="file" name="certif_file" accept=".pdf,image/*"></div>
                        <div class="col-12"><button type="submit" class="btn btn-sm btn-primary">Enregistrer</button></div>
                    </form>
                </details>
            </div>
        <?php endforeach; ?>
        <?php if ($certificats === []): ?><p class="text-muted mb-0">Aucun certificat.</p><?php endif; ?>
    </div>
</div>
