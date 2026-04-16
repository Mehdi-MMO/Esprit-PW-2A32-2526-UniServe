<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier le Bureau – UniServe</title>
    <style>
        /* Mêmes styles que create.php */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Arial; }
        body { background:#f0f2f5; }
        .container { background:#fff; padding:35px; max-width:600px; margin:50px auto; border-radius:12px; box-shadow:0 5px 20px rgba(0,0,0,0.1); }
        h2 { color:#1a237e; margin-bottom:25px; }
        label { display:block; margin-top:15px; font-weight:bold; color:#555; }
        input { width:100%; padding:12px; margin-top:5px; border:1px solid #ddd; border-radius:6px; }
        .btn-group { margin-top:30px; display:flex; gap:15px; }
        .btn-save { background:#1a237e; color:white; border:none; padding:12px 25px; border-radius:6px; cursor:pointer; font-weight:bold; }
        .btn-back { background:#eee; color:#333; text-decoration:none; padding:12px 25px; border-radius:6px; font-weight:bold; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Modifier le bureau</h2>
        <form action="index.php?page=back&module=offices&action=update" method="POST">
            <input type="hidden" name="id" value="<?= $data['id'] ?>">
            
            <label>Nom du Bureau</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($data['nom']) ?>" required>
            
            <label>Localisation</label>
            <input type="text" name="localisation" value="<?= htmlspecialchars($data['localisation']) ?>" required>
            
            <label>Responsable</label>
            <input type="text" name="responsable" value="<?= htmlspecialchars($data['responsable']) ?>">
            
            <div class="btn-group">
                <button type="submit" class="btn-save">Mettre à jour</button>
                <a href="index.php?page=back&module=offices" class="btn-back">Retour</a>
            </div>
        </form>
    </div>
</body>
</html>