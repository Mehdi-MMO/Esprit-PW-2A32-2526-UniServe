<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Rendez-vous</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f7fa; display: flex; }

        /* SIDEBAR */
        .sidebar { width: 240px; min-height: 100vh; background: #1a237e; color: white; padding: 25px 0; position: fixed; }
        .sidebar .logo { font-size: 20px; font-weight: bold; padding: 0 25px 25px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar .logo span { color: #26a69a; }
        .sidebar .menu-title { font-size: 11px; color: rgba(255,255,255,0.5); padding: 20px 25px 10px; text-transform: uppercase; }
        .sidebar a { display: block; padding: 12px 25px; color: rgba(255,255,255,0.8); text-decoration: none; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255,255,255,0.15); color: white; border-left: 3px solid #26a69a; }

        /* MAIN */
        .main { margin-left: 240px; padding: 30px; width: 100%; }
        .header { margin-bottom: 25px; }
        .header h1 { font-size: 26px; color: #1a237e; }
        .header p { color: #999; font-size: 13px; }

        /* FORM CARD */
        .form-card { background: white; border-radius: 12px; padding: 35px; max-width: 600px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .form-card h2 { color: #1a237e; margin-bottom: 5px; font-size: 20px; }
        .form-card p { color: #999; font-size: 13px; margin-bottom: 25px; }
        label { font-size: 13px; font-weight: bold; color: #333; display: block; margin-bottom: 6px; }
        input, textarea { width: 100%; padding: 11px 14px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; margin-bottom: 5px; }
        input:focus, textarea:focus { outline: none; border-color: #1a237e; }
        .error { color: #c62828; font-size: 12px; margin-bottom: 12px; display: block; }
        .form-group { margin-bottom: 18px; }
        .btn-submit { background: #1a237e; color: white; padding: 12px 30px; border: none; border-radius: 8px; font-size: 15px; cursor: pointer; }
        .btn-submit:hover { background: #283593; }
        .btn-cancel { color: #999; text-decoration: none; margin-left: 15px; font-size: 14px; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo"><span>Uni</span>Serve</div>
        <div class="menu-title">Administration</div>
        <a href="index.php?page=back">📊 Dashboard</a>
        <a href="index.php?page=front">🌐 FrontOffice</a>
        <a href="index.php?page=back&action=create" class="active">➕ Ajouter RDV</a>
    </div>

    <!-- MAIN -->
    <div class="main">
        <div class="header">
            <h1>➕ Ajouter un Rendez-vous</h1>
            <p>Remplissez le formulaire pour créer un nouveau rendez-vous.</p>
        </div>

        <div class="form-card">
            <h2>Nouveau Rendez-vous</h2>
            <p>Tous les champs marqués sont obligatoires.</p>

            <form method="POST" action="index.php?page=back&action=store" onsubmit="return validerFormulaire()">

                <div class="form-group">
                    <label>Titre</label>
                    <input type="text" name="titre" id="titre" placeholder="Ex: Consultation médecin" value="<?= $_POST['titre'] ?? '' ?>">
                    <span class="error" id="err_titre"><?= $errors['titre'] ?? '' ?></span>
                </div>

                <div class="form-group">
                    <label>Date</label>
                    <input type="text" name="date_rdv" id="date_rdv" placeholder="YYYY-MM-DD" value="<?= $_POST['date_rdv'] ?? '' ?>">
                    <span class="error" id="err_date"><?= $errors['date_rdv'] ?? '' ?></span>
                </div>

                <div class="form-group">
                    <label>Heure</label>
                    <input type="text" name="heure_rdv" id="heure_rdv" placeholder="HH:MM" value="<?= $_POST['heure_rdv'] ?? '' ?>">
                    <span class="error" id="err_heure"><?= $errors['heure_rdv'] ?? '' ?></span>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="description" rows="4" placeholder="Décrivez le rendez-vous..."><?= $_POST['description'] ?? '' ?></textarea>
                </div>

                <button type="submit" class="btn-submit">✅ Enregistrer</button>
                <a href="index.php?page=back" class="btn-cancel">Annuler</a>
            </form>
        </div>
    </div>

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
            docum