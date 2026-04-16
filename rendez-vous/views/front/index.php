<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>UniServe - Rendez-vous Universitaires</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,sans-serif;background:#f5f7fa;color:#333}
        nav{background:#fff;padding:15px 40px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,.08)}
        .logo{font-size:22px;font-weight:bold;color:#1a237e}
        .logo span{color:#26a69a}
        .nav-links a{margin-left:25px;text-decoration:none;color:#333;font-size:14px;font-weight:500}
        .nav-links a:hover{color:#26a69a}
        .hero{text-align:center;padding:70px 40px;background:#fff;margin:20px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .hero h1{font-size:38px;color:#1a237e;margin-bottom:15px}
        .hero p{color:#666;margin-bottom:30px}
        .btn-primary{background:#1a237e;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;display:inline-block}
        .alert-success{background:#e8f5e9;color:#2e7d32;padding:15px;border-radius:8px;margin:20px;border-left:4px solid #26a69a}
        .section{padding:30px 40px}
        .section h2{font-size:22px;color:#1a237e;margin-bottom:20px}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}
        .card{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.07)}
        .badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:bold;text-transform:uppercase;margin-bottom:10px}
        .badge-pending{background:#fff8e1;color:#f57f17}
        .badge-confirmed{background:#e8f5e9;color:#2e7d32}
        .badge-cancelled{background:#ffebee;color:#c62828}
        footer{text-align:center;padding:40px;color:#999;font-size:13px}
    </style>
</head>
<body>

<nav>
    <div class="logo"><span>Uni</span>Serve</div>
    <div class="nav-links">
        <a href="index.php">🏠 Accueil</a>
        <a href="index.php?action=book">📅 Prendre rendez-vous</a>
    </div>
</nav>

<?php if (!empty($_GET['success'])): ?>
<div class="alert-success">✅ Votre demande de rendez-vous a été soumise avec succès !</div>
<?php endif; ?>

<div class="hero">
    <h1>Réservez votre prochain rendez-vous<br>en quelques secondes.</h1>
    <p>Choisissez un bureau, sélectionnez une date et évitez les files d'attente.</p>
    <a href="index.php?action=book" class="btn-primary">📅 Réserver maintenant</a>
</div>

<div class="section">
    <h2>🏢 Bureaux disponibles</h2>
    <div class="grid">
        <?php while ($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="card" style="border-left:4px solid #26a69a">
            <h3><?= htmlspecialchars($b['nom']) ?></h3>
            <p style="font-size:13px;color:#666;margin-top:8px">📍 <?= htmlspecialchars($b['localisation']) ?></p>
            <?php if (!empty($b['responsable'])): ?>
            <p style="font-size:13px;color:#888;margin-top:5px">👤 <?= htmlspecialchars($b['responsable']) ?></p>
            <?php endif; ?>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="section">
    <h2>📋 Rendez-vous récents</h2>
    <div class="grid">
        <?php 
        $count = 0; 
        $traductions = [
            'pending' => 'En attente',
            'confirmed' => 'Confirmé',
            'cancelled' => 'Annulé'
        ];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
            $count++; 
            $statut_brut = strtolower($row['statut']);
            $statut_fr = $traductions[$statut_brut] ?? $row['statut'];
        ?>
        <div class="card">
            <span class="badge badge-<?= $statut_brut ?>"><?= $statut_fr ?></span>
            <h3><?= htmlspecialchars($row['nom_etudiant']) ?></h3>
            <p style="margin:10px 0;font-size:14px"><strong>Sujet :</strong> <?= htmlspecialchars($row['objet']) ?></p>
            <div style="font-size:12px;color:#888;border-top:1px solid #eee;padding-top:10px">
                📅 Le <?= date('d/m/Y', strtotime($row['date_rdv'])) ?> à <?= $row['heure_rdv'] ?><br>
                🏢 <?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?>
            </div>
        </div>
        <?php endwhile; ?>
        
        <?php if ($count === 0): ?>
        <p style="color:#999">Aucun rendez-vous pour le moment.</p>
        <?php endif; ?>
    </div>
</div>

<footer>© 2026 UniServe — Tous droits réservés.</footer>
</body>
</html>