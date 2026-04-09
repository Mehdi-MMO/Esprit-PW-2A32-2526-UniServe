<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Rendez-vous</title>
    <style>
        body { font-family: Arial; margin: 30px; background: #f4f4f4; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 500px; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0 5px 0; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .error { color: red; font-size: 13px; }
        .btn { background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer; border-radius: 4px; }
        label { font-weight: bold; }
    </style>
</head>
<body>
    <h2>➕ Ajouter un Rendez-vous</h2>
    <form method="POST" action="index.php?page=back&action=store" onsubmit="return validerFormulaire()">
        <label>Titre :</label>
        <input type="text" name="titre" id="titre" value="<?= $_POST['titre'] ?? '' ?>">
        <span class="error" id="err_titre"><?= $errors['titre'] ?? '' ?></span>

        <label>Date :</label>
        <input type="text" name="date_rdv" id="date_rdv" placeholder="YYYY-MM-DD" value="<?= $_POST['date_rdv'] ?? '' ?>">
        <span class="error" id="err_date"><?= $errors['date_rdv'] ?? '' ?></span>

        <label>Heure :</label>
        <input type="text" name="heure_rdv" id="heure_rdv" placeholder="HH:MM" value="<?= $_POST['heure_rdv'] ?? '' ?>">
        <span class="error" id="err_heure"><?= $errors['heure_rdv'] ?? '' ?></span>

        <label>Description :</label>
        <textarea name="description" id="description" rows="4"><?= $_POST['description'] ?? '' ?></textarea>

        <button type="submit" class="btn">Enregistrer</button>
        <a href="index.php?page=back">Annuler</a>
    </form>

    <script>
    function validerFormulaire() {
        let valide = true;

        let titre = document.getElementById("titre").value.trim();
        if (titre === "") {
            document.getElementById("err_titre").innerText = "Le titre est obligatoire.";
            valide = false;
        } else if (titre.length < 3) {
            document.getElementById("err_titre").innerText = "Minimum 3 caractères.";
            valide = false;
        } else {
            document.getElementById("err_titre").innerText = "";
        }

        let date = document.getElementById("date_rdv").value.trim();
        let dateRegex = /^\d{4}-\d{2}-\d{2}$/;
        if (date === "") {
            document.getElementById("err_date").innerText = "La date est obligatoire.";
            valide = false;
        } else if (!dateRegex.test(date)) {
            document.getElementById("err_date").innerText = "Format requis : YYYY-MM-DD";
            valide = false;
        } else {
            document.getElementById("err_date").innerText = "";
        }

        let heure = document.getElementById("heure_rdv").value.trim();
        let heureRegex = /^\d{2}:\d{2}$/;
        if (heure === "") {
            document.getElementById("err_heure").innerText = "L'heure est obligatoire.";
            valide = false;
        } else if (!heureRegex.test(heure)) {
            document.getElementById("err_heure").innerText = "Format requis : HH:MM";
            valide = false;
        } else {
            document.getElementById("err_heure").innerText = "";
        }

        return valide;
    }
    </script>
</body>
</html>