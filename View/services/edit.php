<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h1 class="h3 mb-0 text-gray-800">Modifier le Service</h1>
            <a href="<?= $this->url('/demandes/backoffice') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title text-primary mb-0"><i class="bi bi-pencil-square me-2"></i>Mise à jour du service</h5>
                <p class="text-muted small mt-1">Modifiez les informations du service existant ci-dessous.</p>
            </div>
            <div class="card-body p-4">
                <form action="<?= $this->url('/services/update/' . $service->getId()) ?>" method="POST" id="formService" novalidate>
                    
                    <div class="mb-4">
                        <label for="nom" class="form-label fw-semibold text-secondary small text-uppercase">Nom du service</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-tag text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="nom" name="nom" value="<?= htmlspecialchars($service->getNom() ?? '') ?>" required>
                            <div class="invalid-feedback">Le nom du service est requis.</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold text-secondary small text-uppercase">Description détaillée</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="bi bi-card-text text-muted"></i></span>
                            <textarea class="form-control border-start-0 ps-0" id="description" name="description" rows="4" required><?= htmlspecialchars($service->getDescription() ?? '') ?></textarea>
                            <div class="invalid-feedback">La description est requise et doit contenir au moins 10 caractères.</div>
                        </div>
                    </div>
                    
                    <div class="mb-4 p-3 bg-light rounded-3 d-flex justify-content-between align-items-center border">
                        <div>
                            <span class="fw-semibold d-block text-dark">Disponibilité du service</span>
                            <small class="text-muted">Rendre ce service visible pour les étudiants immédiatement.</small>
                        </div>
                        <div class="form-check form-switch fs-4 mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="actif" name="actif" <?= $service->getActif() ? 'checked' : '' ?> style="cursor: pointer;">
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25 mb-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $this->url('/demandes/backoffice') ?>" class="btn btn-light px-4">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formService');
    const nom = document.getElementById('nom');
    const description = document.getElementById('description');

    const validateField = (field, condition) => {
        if (!field) return true;
        if (condition) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            return true;
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            return false;
        }
    };

    const validateNom = () => validateField(nom, nom.value.trim() !== '');
    const validateDesc = () => validateField(description, description.value.trim().length >= 10);

    // Contrôle de saisie constant (live validation)
    nom.addEventListener('input', validateNom);
    description.addEventListener('input', validateDesc);

    // Validation finale à la soumission
    form.addEventListener('submit', function(event) {
        const vN = validateNom();
        const vD = validateDesc();
        
        if (!(vN && vD)) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
});
</script>
