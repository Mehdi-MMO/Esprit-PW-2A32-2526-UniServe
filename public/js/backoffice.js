// js/backoffice.js

function openEditCours(data) {
    document.getElementById('edit_cours_old_titre').value = data.titre || '';
    document.getElementById('edit_cours_titre').value     = data.titre || '';
    document.getElementById('edit_cours_formateur').value = data.formateur || '';
    document.getElementById('edit_cours_desc').value      = data.description || '';
    new bootstrap.Modal(document.getElementById('editCoursModal')).show();
}

function openEditCertif(data) {
    document.getElementById('edit_certif_id').value    = data.id || '';
    document.getElementById('edit_certif_nom').value   = data.nom_certificat || '';
    document.getElementById('edit_certif_cours').value = data.titre_cours || '';
    document.getElementById('edit_certif_org').value   = data.organisation || '';
    document.getElementById('edit_certif_date').value  = data.date_obtention || '';
    new bootstrap.Modal(document.getElementById('editCertifModal')).show();
}

// ================= VALIDATION =================

document.addEventListener("DOMContentLoaded", function () {

    // Désactiver la validation HTML native
    document.querySelectorAll('form').forEach(f => f.setAttribute('novalidate', ''));

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {

            // 1. Champs texte obligatoires
            for (let input of form.querySelectorAll('input[type="text"], textarea')) {
                if (input.hasAttribute('required') && input.value.trim() === '') {
                    e.preventDefault();
                    alert("Erreur : Le champ '" + (input.placeholder || "Texte") + "' ne peut pas être vide.");
                    input.focus();
                    return;
                }
            }

            // 2. Champs date obligatoires
            for (let input of form.querySelectorAll('input[type="date"]')) {
                if (input.hasAttribute('required') && input.value.trim() === '') {
                    e.preventDefault();
                    alert("Erreur : Veuillez saisir une date.");
                    input.focus();
                    return;
                }
            }

            // 3. Select obligatoire
            for (let select of form.querySelectorAll('select')) {
                if (select.hasAttribute('required') && select.value === '') {
                    e.preventDefault();
                    alert("Erreur : Veuillez sélectionner un cours.");
                    select.focus();
                    return;
                }
            }

            // 4. Fichier : taille et format
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file         = fileInput.files[0];
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
                if (file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert("Erreur : Le fichier est trop lourd (5 MB maximum).");
                    return;
                }
                if (!allowedTypes.includes(file.type)) {
                    e.preventDefault();
                    alert("Erreur : Format non autorisé. Utilisez PDF, JPG ou PNG.");
                    return;
                }
            }
        });
    });
});