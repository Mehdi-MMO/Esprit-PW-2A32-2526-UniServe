<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>BackOffice - Rendez-vous</title>
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

        /* HEADER */
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .header h1 { font-size: 26px; color: #1a237e; }
        .header p { color: #999; font-size: 13px; }
        .btn-add { background: #1a237e; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; }

        /* STATS */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .stat-card .label { font-size: 13px; color: #999; margin-bottom: 8px; }
        .stat-card .number { font-size: 32px; font-weight: bold; color: #1a237e; }
        .stat-card .sub { font-size: 12px; color: #26a69a; margin-top: 5px; }

        /* TABLE */
        .table-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .table-card h2 { font-size: 18px; color: #1a237e; margin-bottom: 5px; }
        .table-card p { color: #999; font-size: 13px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f5f7fa; color: #666; font-size: 12px; text-transform: uppercase; padding: 12px 15px; text-align: left; }
        td { padding: 14px 15px; border-bottom: 1px solid #f0f0f0; font-size: 14px; color: #333; }
        tr:last-child td { border-bottom: none; }
        .btn { padding: 6px 14px; border-radius: 6px; text-decoration: none; font-size: 13px; margin-right: 5px; }
        .btn-edit { background: #e3f2fd; color: #1a237e; }
        .btn-delete { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="logo"><span>Uni</span>Serve</div>
        <div class="menu-title">Administration</div>
        <a href="index.php?page=back" class="active">📊 Dashboard</a>
        <a href="index.php?page=front">🌐 FrontOffice</a>
        <a href="index.php?page=back&action=create">➕ Ajouter RDV</a>
    </div>

    <!-- MAIN -->
    <div class="main">
        <div class="header">
            <div>
                <h1>Back Office Dashboard</h1>
                <p>Gérez tous les rendez-vous du système.</p>
            </div>
            <a href="index.php?page=back&action=create" class="btn-add">+ Créer un RDV</a>
        </div>

        <!-- STATS -->
        <?php
            $stmt2 = $stmt;
            $allRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            $total = count($allRows);
        ?>
        <div class="stats">
            <div class="stat-card">
                <div class="label">Total Rendez-vous</div>
                <div class="number"><?= $total ?></div>
                <div class="sub">Dans le système</div>
            </div>
            <div class="stat-card">
                <div class="label">Aujourd'hui</div>
                <div class="number">📅</div>
                <div class="sub"><?= date('d/m/Y') ?></div>
            </div>
            <div class="stat-card">
                <div class="label">Statut</div>
                <div class="number">✅</div>
                <div class="sub">Système actif</div>
            </div>
            <div class="stat-card">
                <div class="label">Gestion</div>
                <div class="number">⚡</div>
                <div class="sub">CRUD complet</div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="table-card">
            <h2>Liste des Rendez-vous</h2>
            <p>Tableau de gestion principal du back office.</p>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Heure</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($allRows as $row): ?>
                <tr>
                    <td>#<?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['titre']) ?></td>
                    <td><?= $row['date_rdv'] ?></td>
                    <td><?= $row['heure_rdv'] ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td>
                        <a href="index.php?page=back&action=edit&id=<?= $row['id'] ?>" class="btn btn-edit">✏️ Modifier</a>
                        <a href="index.php?page=back&action=delete&id=<?= $row['id'] ?>" class="btn btn-delete"
                           onclick="return confirm('Supprimer ce rendez-vous ?')">🗑️ Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>

</body>
</html>