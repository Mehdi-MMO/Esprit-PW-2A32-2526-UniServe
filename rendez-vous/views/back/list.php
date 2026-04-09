<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BackOffice - Rendez-vous</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f4f4f4; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #4CAF50; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .btn { padding: 6px 12px; border: none; cursor: pointer; border-radius: 4px; text-decoration: none; }
        .btn-add { background: #4CAF50; color: white; }
        .btn-edit { background: #2196F3; color: white; }
        .btn-delete { background: #f44336; color: white; }
    </style>
</head>
<body>
    <h1>🗓️ BackOffice - Gestion des Rendez-vous</h1>
    <a href="index.php?page=back&action=create" class="btn btn-add">+ Ajouter</a>
    <br><br>
    <table>
        <tr>
            <th>ID</th><th>Titre</th><th>Date</th><th>Heure</th><th>Description</th><th>Actions</th>
        </tr>
        <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['titre']) ?></td>
            <td><?= $row['date_rdv'] ?></td>
            <td><?= $row['heure_rdv'] ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td>
                <a href="index.php?page=back&action=edit&id=<?= $row['id'] ?>" class="btn btn-edit">Modifier</a>
                <a href="index.php?page=back&action=delete&id=<?= $row['id'] ?>" class="btn btn-delete"
                   onclick="return confirm('Supprimer ce rendez-vous ?')">Supprimer</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <br>
    <a href="index.php?page=front">Voir FrontOffice</a>
</body>
</html>