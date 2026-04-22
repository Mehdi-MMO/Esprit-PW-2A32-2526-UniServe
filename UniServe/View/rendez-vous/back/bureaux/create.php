<div class="row justify-content-center">
    <div class="col-lg-7 col-xl-6">

        <!-- En-tête -->
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="index.php?page=back&module=offices" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <h1 class="h4 fw-bold mb-0" style="color:var(--brand)">
                    <i class="bi bi-plus-circle me-2"></i>Ajouter un Bureau
                </h1>
                <p class="text-muted small mb-0">Les champs marqués * sont obligatoires.</p>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="us-card p-4">
            <form action="index.php?page=back&module=offices&action=store"
                  method="POST"
                  onsubmit="return validateBureauForm()">

                <!-- Nom -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nom du bureau <span class="text-danger">*</span></label>
                    <input type="text" name="nom" id="nom"
                           class="form-control"
                           placeholder="Ex : Scolarité"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                    <div class="invalid-feedback" id="err_nom"></div>
                </div>

                <!-- Localisation -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Localisation <span class="text-danger">*</span></label>
                    <input type="text" name="localisation" id="localisation"
                           class="form-control"
                           placeholder="Ex : Bâtiment A, RDC"
                           value="<?= htmlspecialchars($_POST['localisation'] ?? '') ?>">
                    <div class="invalid-feedback" id="err_localisation"></div>
                </div>

                <!-- Responsable -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Responsable</label>
                    <input type="text" name="responsable"
                           class="form-control"
                           placeholder="Ex : M. Trabelsi"
                           value="<?= htmlspecialchars($_POST['responsable'] ?? '') ?>">
                </div>

                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-floppy me-1"></i>Enregistrer
                    </button>
                    <a href="index.php?page=back&module=offices" class="btn btn-outline-secondary px-4">Annuler</a>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
function validateBureauForm() {
    var valid = true;

    var nom = document.getElementById('nom');
    var errNom = document.getElementById('err_nom');
    if (nom.value.trim() === '') {
        nom.classList.add('is-invalid');
        errNom.innerText = "Le nom du bureau est obligatoire.";
        valid = false;
    } else if (nom.value.trim().length < 2) {
        nom.classList.add('is-invalid');
        errNom.innerText = "Le nom doit contenir au moins 2 caractères.";
        valid = false;
    } else {
        nom.classList.remove('is-invalid');
        errNom.innerText = "";
    }

    var loc = document.getElementById('localisation');
    var errLoc = document.getElementById('err_localisation');
    if (loc.value.trim() === '') {
        loc.classList.add('is-invalid');
        errLoc.innerText = "La localisation est obligatoire.";
        valid = false;
    } else {
        loc.classList.remove('is-invalid');
        errLoc.innerText = "";
    }

    return valid;
}
</script>
