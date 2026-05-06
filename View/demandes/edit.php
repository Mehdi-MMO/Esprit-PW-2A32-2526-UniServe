<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h1 class="h3 mb-0 text-gray-800">Modifier la Demande</h1>
            <a href="<?= $this->url('/demandes/frontoffice') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title text-primary mb-0"><i class="bi bi-pencil-square me-2"></i>Mise à jour de la demande</h5>
                <p class="text-muted small mt-1">Modifiez les informations de votre demande ci-dessous.</p>
            </div>
            <div class="card-body p-4">
                <form action="<?= $this->url('/demandes/update/' . $demande->getId()) ?>" method="POST" id="formDemande" novalidate>
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold text-secondary small text-uppercase">Type de service</label>
                        <!-- Le service est fixé : on l'affiche en lecture seule -->
                        <input type="hidden" name="service_id" value="<?= $demande->getServiceId() ?>">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-secondary bg-secondary bg-opacity-10 text-secondary">
                                <i class="bi bi-diagram-2"></i>
                            </span>
                            <div class="form-control border-start-0 border-secondary bg-secondary bg-opacity-10 text-muted d-flex align-items-center" style="pointer-events:none;">
                                <?= htmlspecialchars($demande->getServiceNom() ?? '') ?>
                                <span class="badge bg-secondary ms-auto small">Verrouillé</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label fw-semibold text-secondary small text-uppercase">Adresse Email</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" value="<?= htmlspecialchars($demande->getEmail() ?? '') ?>" placeholder="votre.email@exemple.com" required>
                                <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="telephone" class="form-label fw-semibold text-secondary small text-uppercase">Téléphone</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="tel" class="form-control border-start-0 ps-0" id="telephone" name="telephone" value="<?= htmlspecialchars($demande->getTelephone() ?? '') ?>" placeholder="Ex: 12345678" maxlength="8" pattern="[0-9]{8}" required>
                                <div class="invalid-feedback">Le numéro doit contenir exactement 8 chiffres.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="titre" class="form-label fw-semibold text-secondary small text-uppercase">Sujet de la demande</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-fonts text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="titre" name="titre" value="<?= htmlspecialchars($demande->getTitre() ?? '') ?>" required>
                            <div class="invalid-feedback">Le sujet est requis.</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold text-secondary small text-uppercase">Description détaillée</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="bi bi-text-paragraph text-muted"></i></span>
                            <textarea class="form-control border-start-0 ps-0" id="description" name="description" rows="6" required><?= htmlspecialchars($demande->getDescription() ?? '') ?></textarea>
                            <div class="invalid-feedback">La description est requise et doit contenir au moins 15 caractères.</div>
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25 mb-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $this->url('/demandes/frontoffice') ?>" class="btn btn-light px-4">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> Mettre à jour la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formDemande');
    const titre = document.getElementById('titre');
    const description = document.getElementById('description');
    const email = document.getElementById('email');
    const telephone = document.getElementById('telephone');

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

    const validateTitre = () => validateField(titre, titre.value.trim() !== '');
    const validateDesc = () => validateField(description, description.value.trim().length >= 15);
    const validateEmail = () => validateField(email, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()));
    const validatePhone = () => validateField(telephone, /^[0-9]{8}$/.test(telephone.value.trim()));

    // Contrôle de saisie constant (live validation)
    titre.addEventListener('input', validateTitre);
    description.addEventListener('input', validateDesc);
    email.addEventListener('input', validateEmail);
    telephone.addEventListener('input', validatePhone);

    // Validation finale à la soumission
    form.addEventListener('submit', function(event) {
        const vT = validateTitre();
        const vD = validateDesc();
        const vE = validateEmail();
        const vP = validatePhone();
        
        if (!(vT && vD && vE && vP)) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
});
</script>
