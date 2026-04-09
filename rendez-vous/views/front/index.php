<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>FrontOffice - Rendez-vous</title>
    <style>
        body { font-family: Arial; margin: 0; background: #eaf4fb; }
        header { background: #2196F3; color: white; padding: 20px; text-align: center; }
        .container { max-width: 900px; margin: 30px auto; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .card h3 { color: #2196F3; margin: 0 0 10px; }
        .info { color: #666; font-size: 14px; }
        footer { text-align: center; padding: 20px; color: #999; }
    </style>
</head>
<body>
    <header>
        <h1>📅 Nos Rendez-vous</h1>
        <p>Consultez tous les rendez-vous disponibles</p>
    </header>
    <div class="container">
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="card">
            <h3><?= htmlspecialchars($row['titre']) ?></h3>
            <p class="info">📆 <?= $row['date_rdv'] ?> &nbsp;|&nbsp; 🕐 <?= $row['heure_rdv'] ?></p>
            <p><?= htmlspecialchars($row['description']) ?></p>
        </div>
        <?php endwhile; ?>
    </div>
    <footer>
        <a href="index.php?page=back">Accès BackOffice</a>
    </footer>
</body>
</html>