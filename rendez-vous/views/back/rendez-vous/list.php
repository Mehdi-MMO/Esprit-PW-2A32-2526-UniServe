<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Rendez-vous – UniServe</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Arial; }
        body { background: #f0f2f5; }
        nav { background: #fff; padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.08); }
        .logo { font-size: 22px; font-weight: bold; color: #1a237e; text-decoration: none; }
        .logo span { color: #26a69a; }
        .container { padding: 40px; max-width: 1200px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        thead { background: #1a237e; color: white; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .status-confirmed { background: #e8f5e9; color: #2e7d32; }
        .status-pending { background: #fff3e0; color: #ef6c00; }
        .status-cancelled { background: #ffebee; color: #c62828; }
        .btn-approve { color: #2e7d32; text-decoration: none; font-weight: bold; margin-right: 10px; }
        .btn-reject { color: #d32f2f; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php?page=back" class="logo"><span>Uni</span>Serve</a>
        <div style="display:flex; gap:25px;">
            <a href="index.php?page=back&module=appointments" style="text-decoration:none; color:#26a69a; font-weight:bold;">📋 Rendez-vous</a>
            <a href="index.php?page=back&module=offices" style="text-decoration:none; color:#333;">🏢 Bureaux</a>
        </div>
    </nav>
    <div class="container">
        <h1 style="margin-bottom: 30px;">🗓️ Gestion des Rendez-vous</h1>
        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Bureau</th>
                    <th>Sujet</th>
                    <th>Date & Heure</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $traductions = ['pending' => 'En attente', 'confirmed' => 'Confirmé', 'cancelled' => 'Annulé'];
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): 
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['student_name'] ?? 'Inconnu') ?></strong></td>
                    <td><?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['objet'] ?? 'Sans objet') ?></td>
                    <td><?= htmlspecialchars($row['date_rdv']) ?></td>
                    <td>
                        <span class="status-badge status-<?= strtolower($row['statut']) ?>">
                            <?= $traductions[strtolower($row['statut'])] ?? $row['statut'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($row['statut'] === 'pending'): ?>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=confirmed" class="btn-approve">✔️ Approuver</a>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=cancelled" class="btn-reject">❌ Rejeter</a>
                        <?php else: ?>
                            <span style="color:#999; font-style:italic;">Aucune action</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>