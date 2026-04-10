<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>UniServe - Rendez-vous</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f7fa; }

        /* NAVBAR */
        nav { background: white; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        .logo { font-size: 22px; font-weight: bold; color: #1a237e; }
        .logo span { color: #26a69a; }
        .nav-links a { margin-left: 25px; text-decoration: none; color: #333; font-size: 14px; }
        .nav-links a:hover { color: #1a237e; }
        .nav-buttons a { margin-left: 10px; padding: 8px 18px; border-radius: 6px; text-decoration: none; font-size: 14px; }
        .btn-login { border: 2px solid #1a237e; color: #1a237e; }
        .btn-start { background: #1a237e; color: white; }

        /* HERO */
        .hero { display: flex; justify-content: space-between; align-items: center; padding: 50px 40px; background: white; margin: 20px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .hero-left { max-width: 550px; }
        .hero-badge { background: #e8f5e9; color: #2e7d32; padding: 6px 14px; border-radius: 20px; font-size: 12px; display: inline-block; margin-bottom: 15px; }
        .hero-left h1 { font-size: 36px; color: #1a237e; line-height: 1.3; margin-bottom: 15px; }
        .hero-left p { color: #666; font-size: 15px; margin-bottom: 25px; }
        .hero-btns a { padding: 12px 22px; border-radius: 8px; text-decoration: none; font-size: 14px; margin-right: 10px; }
        .btn-primary { background: #1a237e; color: white; }
        .btn-secondary { border: 2px solid #1a237e; color: #1a237e; }
        .hero-stats { display: flex; gap: 20px; margin-top: 25px; }
        .stat { text-align: center; }
        .stat strong { font-size: 22px; color: #1a237e; display: block; }
        .stat span { font-size: 12px; color: #999; }

        /* LOGIN CARD */
        .login-card { background: white; border-radius: 12px; padding: 30px; width: 320px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .login-card h2 { color: #1a237e; margin-bottom: 5px; }
        .login-card p { color: #999; font-size: 13px; margin-bottom: 20px; }
        .login-card label { font-size: 13px; font-weight: bold; color: #333; display: block; margin-bottom: 5px; }
        .login-card input, .login-card select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-bottom: 15px; }
        .btn-signin { width: 100%; background: #1a237e; color: white; padding: 12px; border: none; border-radius: 6px; font-size: 15px; cursor: pointer; }

        /* QUICK ACTIONS */
        .section { padding: 30px 40px; }
        .section h2 { font-size: 22px; color: #1a237e; margin-bottom: 5px; }
        .section p { color: #999; font-size: 13px; margin-bottom: 20px; }
        .cards-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .card h3 { color: #1a237e; margin-bottom: 8px; font-size: 16px; }
        .card p { color: #666; font-size: 13px; margin-bottom: 10px; }
        .card .date { color: #26a69a; font-size: 13px; font-weight: bold; }
        .card .heure { color: #999; font-size: 12px; }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav>
        <div class="logo"><span>Uni</span>Serve</div>
        <div class="nav-links">
            <a href="#">Home</a>
            <a href="#">Services</a>
            <a href="index.php?page=front">Rendez-vous</a>
            <a href="#">Documents</a>
        </div>
        <div class="nav-buttons">
            <a href="index.php?page=back" class="nav-buttons">
                <a href="index.php?page=back" class="btn-login">Log in</a>
                <a href="index.php?page=back&action=create" class="btn-start">Get Started</a>
            </a>
        </div>
    </nav>

    <!-- HERO -->
    <div class="hero">
        <div class="hero-left">
            <span class="hero-badge">🎓 Plateforme de Rendez-vous</span>
            <h1>Un espace pour chaque rendez-vous.</h1>
            <p>Consultez, réservez et gérez vos rendez-vous en ligne — tout dans un portail simple et rapide.</p>
            <div class="hero-btns">
                <a href="index.php?page=back&action=create" class="btn-primary">📅 Prendre RDV</a>
                <a href="index.php?page=back" class="btn-secondary">Gérer les RDV</a>
            </div>
            <div class="hero-stats">
                <div class="stat"><strong>24/7</strong><span>Accès en ligne</span></div>
                <div class="stat"><strong>100%</strong><span>Suivi digital</span></div>
                <div class="stat"><strong>Simple</strong><span>Facile à utiliser</span></div>
            </div>
        </div>
        <div class="login-card">
            <h2>Accès Admin</h2>
            <p>Gérez tous les rendez-vous depuis le back office.</p>
            <label>Email</label>
            <input type="text" placeholder="admin@uniserve.tn">
            <label>Mot de passe</label>
            <input type="password" placeholder="••••••••">
            <label>Rôle</label>
            <select><option>Administrateur</option></select>
            <button class="btn-signin" onclick="window.location.href='index.php?page=back'">Sign In</button>
        </div>
    </div>

    <!-- LISTE DES RENDEZ-VOUS -->
    <div class="section">
        <h2>📋 Rendez-vous disponibles</h2>
        <p>Tous les rendez-vous enregistrés dans le système.</p>
        <div class="cards-grid">
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="card">
                <h3><?= htmlspecialchars($row['titre']) ?></h3>
                <p><?= htmlspecialchars($row['description']) ?></p>
                <div class="date">📆 <?= $row['date_rdv'] ?></div>
                <div class="heure">🕐 <?= $row['heure_rdv'] ?></div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>