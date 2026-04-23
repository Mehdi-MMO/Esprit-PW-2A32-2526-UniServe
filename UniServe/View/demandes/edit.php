<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= htmlspecialchars($title) ?></h1>
    <a href="<?= $this->url('/demandes/frontoffice') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour</a>
</div>

<div class="card shadow-sm max-w-lg mx-auto" style="max-width: 700px;">
    <div class="card-body">
        <form action="<?= $this->url('/demandes/update/' . $demande['id']) ?>" method="POST" id="formDemande" novalidate>
            <div class="mb-3">
                <label for="service_id" class="form-label">Type de service</label>
                <select class="form-select" id="service_id" name="service_id" required>
                    <option value="" disabled>Sélectionnez un service</option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?= $service['id'] ?>" <?= $service['id'] == $demande['service_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($service['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="invalid-feedback">Veuillez sélectionner un service.</div>
            </div>

            <div class="mb-3">
                <label for="titre" class="form-label">Titre de la demande</label>
                <input type="text" class="form-control" id="titre" name="titre" value="<?= htmlspecialchars($demande['titre']) ?>" required>
                <div class="invalid-feedback">Le titre est requis.</div>
            </div>
            
            <div class="mb-4">
                <label for="description" class="form-label">Description détaillée</label>
                <textarea class="form-control" id="description" name="description" rows="5" required><?= htmlspecialchars($demande['description']) ?></textarea>
                <div class="invalid-feedback">La description est requise et doit contenir au moins 15 caractères.</div>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Mettre à jour la demande</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formDemande');
    form.addEventListener('submit', function(event) {
        let valid = true;
        const service = document.getElementById('service_id');
        const titre = document.getElementById('titre');
        const description = document.getElementById('description');
        
        if (service.value === '') {
            service.classList.add('is-invalid');
            valid = false;
        } else {
            service.classList.remove('is-invalid');
            service.classList.add('is-valid');
        }

        if (titre.value.trim() === '') {
            titre.classList.add('is-invalid');
            valid = false;
        } else {
            titre.classList.remove('is-invalid');
            titre.classList.add('is-valid');
        }
        
        if (description.value.trim().length < 15) {
            description.classList.add('is-invalid');
            valid = false;
        } else {
            description.classList.remove('is-invalid');
            description.classList.add('is-valid');
        }
        
        if (!valid) {
            event.preventDefault();
            event.stopPropagation();
        }
    });
});
</script>
