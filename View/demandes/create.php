<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h1 class="h3 mb-0 text-gray-800">Nouvelle Demande</h1>
            <a href="<?= $this->url('/demandes/frontoffice') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
                <i class="bi bi-arrow-left me-1"></i>Retour
            </a>
        </div>

        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title text-primary mb-0"><i class="bi bi-file-earmark-plus me-2"></i>Détails de la demande</h5>
                <p class="text-muted small mt-1">Saisissez les informations nécessaires pour soumettre votre demande.</p>
            </div>
            <div class="card-body p-4">
                <form action="<?= $this->url('/demandes/store') ?>" method="POST" id="formDemande" novalidate>
                    
                    <div class="mb-4">
                        <label for="service_id" class="form-label fw-semibold text-secondary small text-uppercase">Type de service</label>
                        <?php 
                        $preselected = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0; 
                        $preselectedName = '';
                        if ($preselected) {
                            foreach ($services as $service) {
                                if ($service->getId() === $preselected) {
                                    $preselectedName = $service->getNom();
                                    break;
                                }
                            }
                        }
                        ?>
                        
                        <?php if ($preselected): ?>
                            <input type="hidden" name="service_id" id="service_id" value="<?= $preselected ?>">
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-light border-secondary bg-secondary bg-opacity-10 text-secondary">
                                    <i class="bi bi-diagram-2"></i>
                                </span>
                                <div class="form-control border-start-0 border-secondary bg-secondary bg-opacity-10 text-muted d-flex align-items-center" style="pointer-events:none;">
                                    <?= htmlspecialchars($preselectedName) ?>
                                    <span class="badge bg-secondary ms-auto small">Verrouillé</span>
                                </div>
                            </div>

                        <?php else: ?>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-diagram-2 text-muted"></i>
                                </span>
                                <select class="form-select border-start-0 ps-0" id="service_id" name="service_id" required>
                                    <option value="" disabled selected>Sélectionnez un service approprié...</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= $service->getId() ?>">
                                            <?= htmlspecialchars($service->getNom() ?? '') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner un service.</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="email" class="form-label fw-semibold text-secondary small text-uppercase">Adresse Email</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" placeholder="votre.email@exemple.com" required>
                                <div class="invalid-feedback">Veuillez entrer une adresse email valide.</div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="telephone" class="form-label fw-semibold text-secondary small text-uppercase">Téléphone</label>
                            <div class="input-group has-validation">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                <input type="tel" class="form-control border-start-0 ps-0" id="telephone" name="telephone" value="<?= htmlspecialchars(isset($demande) ? $demande->getTelephone() : '') ?>" placeholder="Ex: 12345678" maxlength="8" pattern="[0-9]{8}" required>
                                <div class="invalid-feedback">Le numéro doit contenir exactement 8 chiffres.</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="titre" class="form-label fw-semibold text-secondary small text-uppercase">Sujet de la demande</label>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-fonts text-muted"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" id="titre" name="titre" placeholder="Ex: Demande d'attestation d'inscription" required>
                            <div class="invalid-feedback">Le sujet est requis.</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="description" class="form-label fw-semibold text-secondary small text-uppercase mb-0">Description détaillée</label>
                            <button type="button" class="btn btn-sm btn-outline-primary shadow-sm rounded-pill px-3" id="btnMagicDraft" onclick="generateMagicDraft()" title="Tapez quelques mots et laissez l'IA rédiger un texte formel à votre place">
                                <i class="bi bi-stars me-1 text-warning"></i> Rédiger avec l'IA
                            </button>
                        </div>
                        <div class="input-group has-validation">
                            <span class="input-group-text bg-light border-end-0 align-items-start pt-2"><i class="bi bi-text-paragraph text-muted"></i></span>
                            <textarea class="form-control border-start-0 ps-0" id="description" name="description" rows="6" placeholder="Expliquez clairement votre besoin en fournissant tous les détails utiles..." required></textarea>
                            <div class="invalid-feedback">La description est requise et doit contenir au moins 15 caractères.</div>
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-25 mb-4">
                    
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?= $this->url('/demandes/frontoffice') ?>" class="btn btn-light px-4">Annuler</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-send-fill me-1"></i> Soumettre la demande
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Alerte IA -->
<div class="modal fade" id="aiAlertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning bg-opacity-10 border-0 py-3">
        <h5 class="modal-title fs-6 fw-bold text-warning-emphasis"><i class="bi bi-exclamation-triangle-fill me-2"></i>Attention</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <div class="bg-warning bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-magic text-warning display-5"></i>
        </div>
        <p class="mb-0 fs-6 text-muted" id="aiAlertMessage"></p>
      </div>
      <div class="modal-footer border-0 justify-content-center pb-4">
        <button type="button" class="btn btn-warning text-dark px-4 shadow-sm" data-bs-dismiss="modal">D'accord, j'ai compris</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formDemande');
    const service = document.getElementById('service_id');
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

    const validateService = () => service ? validateField(service, service.value !== '') : true;
    const validateTitre = () => validateField(titre, titre.value.trim() !== '');
    const validateDesc = () => validateField(description, description.value.trim().length >= 15);
    const validateEmail = () => validateField(email, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim()));
    const validatePhone = () => validateField(telephone, /^[0-9]{8}$/.test(telephone.value.trim()));

    // Contrôle de saisie constant (live validation)
    if (service) service.addEventListener('change', validateService);
    titre.addEventListener('input', validateTitre);
    description.addEventListener('input', validateDesc);
    email.addEventListener('input', validateEmail);
    telephone.addEventListener('input', validatePhone);

    // Validation finale à la soumission
    form.addEventListener('submit', function(event) {
        const vS = validateService();
        const vT = validateTitre();
        const vD = validateDesc();
        const vE = validateEmail();
        const vP = validatePhone();
        
        if (!(vS && vT && vD && vE && vP)) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
});

function showAiAlert(message) {
    document.getElementById('aiAlertMessage').innerHTML = message.replace(/\n/g, '<br>');
    new bootstrap.Modal(document.getElementById('aiAlertModal')).show();
}

async function generateMagicDraft() {
    const descField = document.getElementById('description');
    const serviceSelect = document.getElementById('service_id');
    const btn = document.getElementById('btnMagicDraft');
    
    let serviceId = 0;
    if (serviceSelect) {
        serviceId = parseInt(serviceSelect.value) || 0;
    } else {
        const hiddenService = document.querySelector('input[name="service_id"]');
        if (hiddenService) serviceId = parseInt(hiddenService.value) || 0;
    }

    const keywords = descField.value.trim();

    if (!keywords || serviceId === 0) {
        showAiAlert("Pour utiliser le Rédacteur Magique : \n<br><b>1.</b> Sélectionnez un type de service.<br><b>2.</b> Tapez quelques mots-clés dans la zone de description (ex: <i>'besoin papier visa'</i>).");
        return;
    }

    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Rédaction en cours...';
    btn.disabled = true;
    btn.classList.replace('btn-outline-primary', 'btn-primary');

    try {
        const response = await fetch('<?= $this->url('/chatbot/draft') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ keywords: keywords, service_id: serviceId })
        });
        const data = await response.json();
        
        if (data.text) {
            descField.value = data.text;
            // Force re-validation
            descField.dispatchEvent(new Event('input'));
        } else {
            showAiAlert(data.error || "Une erreur est survenue lors de la génération.");
        }
    } catch (err) {
        showAiAlert("Erreur de connexion à l'assistant IA.");
    }

    btn.innerHTML = originalHtml;
    btn.disabled = false;
    btn.classList.replace('btn-primary', 'btn-outline-primary');
}
</script>
