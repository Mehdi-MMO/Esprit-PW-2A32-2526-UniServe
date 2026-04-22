<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">

        <div class="mb-4">
            <h1 class="h4 fw-bold mb-1" style="color:var(--brand)">
                <i class="bi bi-calendar-plus me-2"></i>Nouveau Rendez-vous
            </h1>
            <p class="text-muted small">Remplissez le formulaire pour soumettre votre demande.</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($errors['general']) ?>
        </div>
        <?php endif; ?>

        <div class="us-card p-4">
            <form action="index.php?action=store_booking" method="POST" onsubmit="return validateBookForm()">

                <!-- Nom étudiant -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nom de l'étudiant <span class="text-danger">*</span></label>
                    <input type="text" name="nom_etudiant" id="nom_etudiant"
                           class="form-control <?= !empty($errors['nom_etudiant']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['nom_etudiant'] ?? '') ?>"
                           placeholder="Ex : Ali Ben Salah">
                    <div class="invalid-feedback" id="err_nom">
                        <?= htmlspecialchars($errors['nom_etudiant'] ?? '') ?>
                    </div>
                </div>

                <!-- Bureau -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Bureau <span class="text-danger">*</span></label>
                    <select name="id_bureau" id="id_bureau"
                            class="form-select <?= !empty($errors['id_bureau']) ? 'is-invalid' : '' ?>">
                        <option value="">-- Sélectionnez un bureau --</option>
                        <?php while ($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)): ?>
                            <option value="<?= $b['id'] ?>"
                                <?= (($_POST['id_bureau'] ?? '') == $b['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($b['nom']) ?> — <?= htmlspecialchars($b['localisation']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="invalid-feedback" id="err_bureau">
                        <?= htmlspecialchars($errors['id_bureau'] ?? '') ?>
                    </div>
                </div>

                <!-- Sujet -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Sujet <span class="text-danger">*</span></label>
                    <input type="text" name="objet" id="objet"
                           class="form-control <?= !empty($errors['objet']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['objet'] ?? '') ?>"
                           placeholder="Ex : Demande de bourse">
                    <div class="invalid-feedback" id="err_objet">
                        <?= htmlspecialchars($errors['objet'] ?? '') ?>
                    </div>
                </div>

                <!-- Date -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="text" name="date_rdv" id="date_rdv"
                           class="form-control <?= !empty($errors['date_rdv']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['date_rdv'] ?? '') ?>"
                           placeholder="AAAA-MM-JJ">
                    <div class="form-text text-muted">Format : AAAA-MM-JJ &nbsp;(ex : 2026-06-15)</div>
                    <div class="invalid-feedback" id="err_date">
                        <?= htmlspecialchars($errors['date_rdv'] ?? '') ?>
                    </div>
                </div>

                <!-- Heure -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Heure <span class="text-danger">*</span></label>
                    <input type="text" name="heure_rdv" id="heure_rdv"
                           class="form-control <?= !empty($errors['heure_rdv']) ? 'is-invalid' : '' ?>"
                           value="<?= htmlspecialchars($_POST['heure_rdv'] ?? '') ?>"
                           placeholder="HH:MM">
                    <div class="form-text text-muted">Format : HH:MM &nbsp;(ex : 09:30)</div>
                    <div class="invalid-feedback" id="err_heure">
                        <?= htmlspecialchars($errors['heure_rdv'] ?? '') ?>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-lg me-1"></i>Soumettre
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary px-4">Annuler</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function validateBookForm() {
    var valid = true;

    // Réinitialiser toutes les classes is-invalid
    ['nom_etudiant','id_bureau','objet','date_rdv','heure_rdv'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.classList.remove('is-invalid');
    });

    // Nom étudiant
    var nom = document.getElementById('nom_etudiant').value.trim();
    if (nom === '') {
        setError('nom_etudiant', 'err_nom', "Le nom de l'étudiant est obligatoire.");
        valid = false;
    } else if (nom.length < 2) {
        setError('nom_etudiant', 'err_nom', "Le nom doit contenir au moins 2 caractères.");
        valid = false;
    }

    // Bureau
    var bureau = document.getElementById('id_bureau').value;
    if (bureau === '') {
        setError('id_bureau', 'err_bureau', "Veuillez sélectionner un bureau.");
        valid = false;
    }

    // Sujet
    var objet = document.getElementById('objet').value.trim();
    if (objet === '') {
        setError('objet', 'err_objet', "Le sujet est obligatoire.");
        valid = false;
    }

    // Date AAAA-MM-JJ
    var date = document.getElementById('date_rdv').value.trim();
    var dateRegex = /^\d{4}-\d{2}-\d{2}$/;
    if (date === '') {
        setError('date_rdv', 'err_date', "La date est obligatoire.");
        valid = false;
    } else if (!dateRegex.test(date)) {
        setError('date_rdv', 'err_date', "Format requis : AAAA-MM-JJ");
        valid = false;
    } else {
        var parts = date.split('-');
        var d = new Date(parts[0], parts[1] - 1, parts[2]);
        if (d.getFullYear() != parts[0] || (d.getMonth()+1) != parts[1] || d.getDate() != parts[2]) {
            setError('date_rdv', 'err_date', "La date saisie n'est pas valide.");
            valid = false;
        }
    }

    // Heure HH:MM
    var heure = document.getElementById('heure_rdv').value.trim();
    var heureRegex = /^\d{2}:\d{2}$/;
    if (heure === '') {
        setError('heure_rdv', 'err_heure', "L'heure est obligatoire.");
        valid = false;
    } else if (!heureRegex.test(heure)) {
        setError('heure_rdv', 'err_heure', "Format requis : HH:MM");
        valid = false;
    } else {
        var h = parseInt(heure.split(':')[0]);
        var m = parseInt(heure.split(':')[1]);
        if (h < 0 || h > 23 || m < 0 || m > 59) {
            setError('heure_rdv', 'err_heure', "Heure invalide (00:00 – 23:59).");
            valid = false;
        }
    }

    return valid;
}

function setError(fieldId, errId, msg) {
    var field = document.getElementById(fieldId);
    var errEl = document.getElementById(errId);
    if (field) field.classList.add('is-invalid');
    if (errEl) errEl.innerText = msg;
}
</script>
