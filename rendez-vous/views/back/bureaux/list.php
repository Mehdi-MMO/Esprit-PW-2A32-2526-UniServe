<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Bureaux – UniServe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial; }
        body { background: #f0f2f5; }
        nav { background: #fff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        .logo { font-size: 22px; font-weight: bold; color: #1a237e; text-decoration: none; }
        .logo span { color: #26a69a; }
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .btn-add { background: #26a69a; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        thead { background: #1a237e; color: white; }
        .btn-edit { color: #1a237e; text-decoration: none; font-weight: bold; margin-right: 15px; }
        .btn-delete { color: #d32f2f; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php?page=back" class="logo"><span>Uni</span>Serve</a>
        <div style="display:flex; gap:25px;">
            <a href="index.php?page=back&module=appointments" style="text-decoration:none; color:#333;">📋 Rendez-vous</a>
            <a href="index.php?page=back&module=offices" style="text-decoration:none; color:#26a69a; font-weight:bold;">🏢 Bureaux</a>
        </div>
    </nav>
    <div class="container">
        <div class="header-actions">
            <h1>🏢 Liste des Bureaux</h1>
            <a href="index.php?page=back&module=offices&action=create" class="btn-add">+ Ajouter un bureau</a>
        </div>

        <?php if(isset($_GET['error']) && $_GET['error'] === 'has_rdv'): ?>
            <p style="color: #d32f2f; background: #ffebee; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                Impossible de supprimer : ce bureau possède encore <?= $_GET['count'] ?> rendez-vous actifs.
            </p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Nom du Bureau</th>
                    <th>Localisation</th>
                    <th>Responsable</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['nom']) ?></strong></td>
                    <td><?= htmlspecialchars($row['localisation']) ?></td>
                    <td><?= htmlspecialchars($row['responsable'] ?? 'Non assigné') ?></td>
                    <td>
                        <a href="index.php?page=back&module=offices&action=edit&id=<?= $row['id'] ?>" class="btn-edit">Modifier</a>
                        <a href="index.php?page=back&module=offices&action=delete&id=<?= $row['id'] ?>" class="btn-delete" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce bureau ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>