<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Prendre un Rendez-vous – UniServe</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Segoe UI', sans-serif; background:#f5f7fa; }
        nav { background:#fff; padding:15px 40px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 5px rgba(0,0,0,.08); }
        .logo { font-size:22px; font-weight:bold; color:#1a237e; text-decoration:none; }
        .logo span { color:#26a69a; }
        .container { background:#fff; padding:35px; max-width:500px; margin:40px auto; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.07); }
        h2 { color:#1a237e; margin-bottom:10px; }
        label { display:block; margin-top:15px; font-weight:bold; color:#555; font-size:14px; }
        input, select, textarea { width:100%; padding:10px; margin-top:5px; border:1px solid #ddd; border-radius:6px; }
        .btn-submit { background:#26a69a; color:white; border:none; padding:12px; width:100%; border-radius:6px; cursor:pointer; font-weight:bold; margin-top:20px; }
        .btn-cancel { display:block; text-align:center; margin-top:15px; color:#999; text-decoration:none; font-size:14px; }
    </style>
</head>
<body>
    <nav><a href="index.php" class="logo"><span>Uni</span>Serve</a></nav>
    <div class="container">
        <h2>Prendre un rendez-vous</h2>
        <p style="color:#666; font-size:13px;">Remplissez le formulaire pour réserver votre créneau.</p>
        
        <form action="index.php?action=store_booking" method="POST">
            <label>Votre Nom Complet</label>
            <input type="text" name="nom_etudiant" placeholder="Ex: Jean Dupont" required>
            
            <label>Bureau / Service</label>
            <select name="id_bureau" required>
                <option value="">Sélectionnez un bureau</option>
                <?php while($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nom']) ?></option>
                <?php endwhile; ?>
            </select>
            
            <label>Sujet du rendez-vous</label>
            <input type="text" name="objet" placeholder="Ex: Inscription, Tutorat..." required>
            
            <label>Date souhaitée</label>
            <input type="date" name="date_rdv" required>
            
            <label>Heure</label>
            <input type="time" name="heure_rdv" required>
            
            <button type="submit" class="btn-submit">Confirmer la demande</button>
            <a href="index.php" class="btn-cancel">Annuler</a>
        </form>
    </div>
</body>
</html>