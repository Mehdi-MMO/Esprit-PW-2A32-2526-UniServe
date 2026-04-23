<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3"><?= htmlspecialchars($title) ?></h1>
    <a href="<?= $this->url('/demandes/backoffice') ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Retour</a>
</div>

<div class="card shadow-sm max-w-md mx-auto" style="max-width: 600px;">
    <div class="card-body">
        <form action="<?= $this->url('/services/update/' . $service['id']) ?>" method="POST" id="formService" novalidate>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom du service</label>
                <input type="text" class="form-control" id="nom" name="nom" value="<?= htmlspecialchars($service['nom']) ?>" required>
                <div class="invalid-feedback">Le nom du service est requis.</div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required><?= htmlspecialchars($service['description']) ?></textarea>
                <div class="invalid-feedback">La description est requise et doit contenir au moins 10 caractères.</div>
            </div>
            
            <div class="mb-4 form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="actif" name="actif" <?= $service['actif'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="actif">Service actif</label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formService');
    form.addEventListener('submit', function(event) {
        let valid = true;
        const nom = document.getElementById('nom');
        const description = document.getElementById('description');
        
        if (nom.value.trim() === '') {
            nom.classList.add('is-invalid');
            valid = false;
        } else {
            nom.classList.remove('is-invalid');
            nom.classList.add('is-valid');
        }
        
        if (description.value.trim().length < 10) {
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
