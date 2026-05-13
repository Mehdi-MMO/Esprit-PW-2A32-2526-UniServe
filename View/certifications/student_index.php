<?php
require_once __DIR__ . '/../shared/helpers.php';

$cours = $cours ?? [];
$certificats = $certificats ?? [];
$demandes = $demandes ?? [];
$quiz_notif = $quiz_notif ?? null;
$flash = $flash ?? null;
?>

<?php if ($flash !== null): ?>
    <?php if (($flash['type'] ?? '') === 'success'): ?>
        <?= renderSuccessAlert((string) ($flash['message'] ?? '')) ?>
    <?php else: ?>
        <?= renderErrorAlert((string) ($flash['message'] ?? '')) ?>
    <?php endif; ?>
<?php endif; ?>

<?php if (is_array($quiz_notif)): ?>
    <?php if (($quiz_notif['statut'] ?? '') === 'accepte'): ?>
        <div class="alert alert-success">Félicitations — score <?= (int) ($quiz_notif['score'] ?? 0) ?>/5. Certification validée.</div>
    <?php else: ?>
        <div class="alert alert-warning">Score <?= (int) ($quiz_notif['score'] ?? 0) ?>/5 — minimum 3/5 requis.</div>
    <?php endif; ?>
<?php endif; ?>

<div class="us-card p-4 mb-4" id="us-parcours-intro">
    <nav class="small text-muted mb-2" aria-label="Fil d'Ariane">
        <a href="<?= $this->url('/frontoffice/dashboard') ?>" class="text-decoration-none">Accueil</a>
        <span class="mx-1" aria-hidden="true">/</span>
        <span class="text-body-secondary">Certifications</span>
    </nav>
    <h1 class="h4 mb-1"><?= htmlspecialchars((string) ($title ?? ''), ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted small mb-0">
        Consultez les <strong>cours</strong> et le <strong>catalogue de certifications</strong>, puis déposez une <strong>demande</strong> et passez le quiz si le personnel vous l’envoie.
        La création ou la modification des cours et du catalogue certificats se fait dans le <strong>back-office</strong> (personnel uniquement).
    </p>
</div>

<div class="card shadow-sm mb-4" id="us-parcours-cours" style="scroll-margin-top: 5.5rem;">
    <div class="card-header fw-semibold">Cours (<?= count($cours) ?>) — consultation</div>
    <div class="card-body">
        <?php foreach ($cours as $c): ?>
            <?php
            $titre = (string) ($c['titre'] ?? '');
            $fichiers = is_array($c['fichiers'] ?? null) ? $c['fichiers'] : [];
            ?>
            <div class="border rounded p-3 mb-3">
                <div class="fw-semibold"><?= htmlspecialchars($titre, ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (trim((string) ($c['formateur'] ?? '')) !== ''): ?>
                    <div class="small text-muted mb-2"><?= htmlspecialchars((string) ($c['formateur'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if (trim((string) ($c['description'] ?? '')) !== ''): ?>
                    <p class="small mb-2"><?= nl2br(htmlspecialchars((string) ($c['description'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></p>
                <?php endif; ?>
                <?php if (trim((string) ($c['contenu'] ?? '')) !== ''): ?>
                    <details class="small mb-2">
                        <summary class="text-primary" style="cursor:pointer;">Contenu du cours</summary>
                        <div class="mt-2 border-top pt-2"><?= nl2br(htmlspecialchars((string) ($c['contenu'] ?? ''), ENT_QUOTES, 'UTF-8')) ?></div>
                    </details>
                <?php endif; ?>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php if (!empty($c['image_path'])): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?= $this->url('/certifications/download/cours/' . rawurlencode((string) $c['image_path'])) ?>">Image</a>
                    <?php endif; ?>
                    <?php foreach ($fichiers as $f): ?>
                        <?php
                        if (!is_array($f)) {
                            continue;
                        }
                        $fn = (string) ($f['path'] ?? '');
                        $label = (string) ($f['nom'] ?? $fn);
                        if ($fn === '') {
                            continue;
                        }
                        ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?= $this->url('/certifications/download/cours/' . rawurlencode($fn)) ?>"><?= htmlspecialchars($label !== '' ? $label : 'Fichier', ENT_QUOTES, 'UTF-8') ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($cours === []): ?>
            <p class="text-muted mb-0">Aucun cours publié pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4" id="us-parcours-certificats" style="scroll-margin-top: 5.5rem;">
    <div class="card-header fw-semibold">Catalogue certifications (<?= count($certificats) ?>) — lecture seule</div>
    <div class="card-body">
        <p class="small text-muted mb-3">Référence pour vos demandes (titre exact utile pour le champ « certificat visé »). Les pièces du catalogue ne sont téléchargeables que par le personnel.</p>
        <?php foreach ($certificats as $cert): ?>
            <div class="border rounded p-3 mb-3">
                <div class="fw-semibold"><?= htmlspecialchars((string) ($cert['nom_certificat'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-muted">
                    <?= htmlspecialchars((string) ($cert['date_obtention'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    — <?= htmlspecialchars((string) ($cert['organisation'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <?php if (trim((string) ($cert['titre_cours'] ?? '')) !== ''): ?>
                        — cours lié : <?= htmlspecialchars((string) ($cert['titre_cours'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($certificats === []): ?>
            <p class="text-muted mb-0">Aucune entrée dans le catalogue.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4" id="us-parcours-demande">
    <div class="card-header fw-semibold">Nouvelle demande de certification</div>
    <div class="card-body">
        <?php if ($cours === []): ?>
            <p class="alert alert-warning small mb-0">Aucun cours n’est encore publié dans le catalogue DOCAC : le personnel doit créer au moins un cours (back-office) avant que vous puissiez déposer une demande liée au quiz.</p>
        <?php else: ?>
        <form method="post" action="<?= $this->url('/certifications/demanderCertification') ?>" enctype="multipart/form-data" class="row g-2 small">
            <div class="col-md-6"><label class="form-label">Certificat visé <span class="text-danger">*</span></label><input class="form-control" name="nom_certificat" required maxlength="255" placeholder="Nom exact visé par votre demande"></div>
            <div class="col-md-6">
                <label class="form-label">Cours du catalogue (lié au quiz) <span class="text-danger">*</span></label>
                <select class="form-select" name="titre_cours" required>
                    <option value="" disabled selected>— Choisir un cours —</option>
                    <?php foreach ($cours as $co): ?>
                        <?php
                        $ctitre = trim((string) ($co['titre'] ?? ''));
                        if ($ctitre === '') {
                            continue;
                        }
                        ?>
                        <option value="<?= htmlspecialchars($ctitre, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($ctitre, ENT_QUOTES, 'UTF-8') ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">Le quiz envoyé par le staff est généré à partir de <strong>ce cours</strong> (titre identique en base DOCAC).</div>
            </div>
            <div class="col-md-6"><label class="form-label">Organisme</label><input class="form-control" name="organisation" value="UniServe"></div>
            <div class="col-md-6"><label class="form-label">Date souhaitée <span class="text-danger">*</span></label><input class="form-control" type="date" name="date_souhaitee" required></div>
            <div class="col-md-6"><label class="form-label">Heure préférée</label><input class="form-control" name="heure_preferee" placeholder="ex. 14h"></div>
            <div class="col-12"><label class="form-label">Notes</label><textarea class="form-control" name="notes" rows="2"></textarea></div>
            <div class="col-12"><label class="form-label">Justificatif (PDF / image)</label><input class="form-control" type="file" name="certif_file" accept=".pdf,image/*"></div>
            <div class="col-12"><button class="btn btn-primary" type="submit">Soumettre la demande</button></div>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mb-4" id="mes-demandes">
    <div class="card-header fw-semibold">Mes demandes &amp; quiz</div>
    <div class="card-body">
        <?php foreach ($demandes as $d): ?>
            <?php
            $q = $d['quiz'] ?? null;
            $did = (int) ($d['id'] ?? 0);
            ?>
            <div class="border rounded p-3 mb-3">
                <div class="fw-semibold"><?= htmlspecialchars((string) ($d['nom_certificat'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="small text-muted">Statut : <?= htmlspecialchars((string) ($d['statut'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <?php if (!empty($d['fichier_path'])): ?>
                    <a class="small" href="<?= $this->url('/certifications/download/demande/' . rawurlencode((string) $d['fichier_path'])) ?>">Télécharger le justificatif</a>
                <?php endif; ?>

                <?php if (is_array($q) && (string) ($q['statut'] ?? '') === 'en_attente' && !empty($q['questions'])): ?>
                    <form method="post" action="<?= $this->url('/certifications/passerQuiz') ?>" class="mt-3">
                        <input type="hidden" name="quiz_id" value="<?= (int) ($q['id'] ?? 0) ?>">
                        <?php foreach ($q['questions'] as $i => $question): ?>
                            <fieldset class="mb-3 border-0 p-0">
                                <legend class="form-label small fw-semibold mb-1"><?= htmlspecialchars((string) ($question['question'] ?? ''), ENT_QUOTES, 'UTF-8') ?></legend>
                                <?php foreach (($question['options'] ?? []) as $j => $opt): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="answer_<?= (int) $i ?>" value="<?= (int) $j ?>" id="q<?= $did ?>_<?= (int) $i ?>_<?= (int) $j ?>"<?= $j === 0 ? ' required' : '' ?>>
                                        <label class="form-check-label small" for="q<?= $did ?>_<?= (int) $i ?>_<?= (int) $j ?>"><?= htmlspecialchars((string) $opt, ENT_QUOTES, 'UTF-8') ?></label>
                                    </div>
                                <?php endforeach; ?>
                            </fieldset>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-sm btn-primary">Valider le quiz</button>
                    </form>
                <?php elseif (is_array($q)): ?>
                    <div class="small mt-2">Quiz : <?= htmlspecialchars((string) ($q['statut'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                        <?php if ($q['score'] !== null): ?> — score <?= (int) $q['score'] ?>/5<?php endif; ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if ($demandes === []): ?>
            <p class="text-muted mb-0">Aucune demande.</p>
        <?php endif; ?>
    </div>
</div>
