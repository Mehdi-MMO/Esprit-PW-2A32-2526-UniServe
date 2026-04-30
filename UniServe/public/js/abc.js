// public/js/abc.js

function openEditCours(data) {
    document.getElementById('edit_cours_old_titre').value = data.titre || '';
    document.getElementById('edit_cours_titre').value = data.titre || '';
    document.getElementById('edit_cours_formateur').value = data.formateur || '';
    document.getElementById('edit_cours_desc').value = data.description || '';
    new bootstrap.Modal(document.getElementById('editCoursModal')).show();
}

function openEditCertif(data) {
    document.getElementById('edit_certif_id').value = data.id || '';
    document.getElementById('edit_certif_nom').value = data.nom_certificat || '';
    document.getElementById('edit_certif_cours').value = data.titre_cours || '';
    document.getElementById('edit_certif_org').value = data.organisation || '';
    document.getElementById('edit_certif_date').value = data.date_obtention || '';
    new bootstrap.Modal(document.getElementById('editCertifModal')).show();
}

// ================= CONTROLE DE SAISIE =================

document.addEventListener("DOMContentLoaded", function () {

    // Désactiver la validation HTML native du navigateur
    document.querySelectorAll('form').forEach(f => f.setAttribute('novalidate', ''));

    const allForms = document.querySelectorAll('form');
    allForms.forEach(form => {
        form.addEventListener('submit', function (e) {

            // 1. Validation des champs texte obligatoires
            const inputsText = form.querySelectorAll('input[type="text"], textarea');
            for (let input of inputsText) {
                if (input.hasAttribute('required') && input.value.trim() === '') {
                    e.preventDefault();
                    alert("Erreur : Le champ '" + (input.placeholder || "Texte") + "' ne peut pas être vide.");
                    input.focus();
                    return;
                }
            }

            // 2. Validation des champs date obligatoires
            const inputsDate = form.querySelectorAll('input[type="date"]');
            for (let input of inputsDate) {
                if (input.hasAttribute('required') && input.value.trim() === '') {
                    e.preventDefault();
                    alert("Erreur : Veuillez saisir une date.");
                    input.focus();
                    return;
                }
            }

            // 3. Validation des selects obligatoires
            const selects = form.querySelectorAll('select');
            for (let select of selects) {
                if (select.hasAttribute('required') && select.value === '') {
                    e.preventDefault();
                    alert("Erreur : Veuillez sélectionner un cours.");
                    select.focus();
                    return;
                }
            }

            // 4. Validation du fichier (taille et format)
            const fileInput = form.querySelector('input[type="file"]');
            if (fileInput && fileInput.files.length > 0) {
                const file = fileInput.files[0];
                const maxSize = 5 * 1024 * 1024; // 5 MB
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

                if (file.size > maxSize) {
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