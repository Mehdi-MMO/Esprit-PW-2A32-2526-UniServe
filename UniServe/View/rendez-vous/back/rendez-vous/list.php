<style>
/* ── Back RDV ── */
.back-page-header {
    background: #fff;
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    border: 1px solid var(--border);
    box-shadow: 0 1px 8px rgba(11,42,90,0.06);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.back-page-header-left { display: flex; align-items: center; gap: 16px; }
.back-page-icon {
    width: 50px; height: 50px; border-radius: 14px;
    background: linear-gradient(135deg,#0b2a5a,#1565c0);
    display: grid; place-items: center;
    color: #fff; font-size: 1.3rem;
    box-shadow: 0 4px 14px rgba(11,42,90,0.22);
    flex-shrink: 0;
}
.back-page-title { font-size: 1.15rem; font-weight: 800; color: var(--brand); margin-bottom: 2px; }
.back-page-sub   { font-size: .8rem; color: var(--text-muted); }
.back-stats-row  { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 24px; }
.back-stat-pill  {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 7px 14px; border-radius: 999px; font-size: .78rem; font-weight: 700;
    border: 1.5px solid;
}
.back-table-wrap {
    background: #fff;
    border-radius: 16px;
    border: 1px solid var(--border);
    box-shadow: 0 1px 8px rgba(11,42,90,0.06);
    overflow: hidden;
}
.back-table { width: 100%; border-collapse: collapse; }
.back-table thead tr { background: linear-gradient(90deg,#0b2a5a 0%,#1565c0 100%); }
.back-table thead th {
    color: rgba(255,255,255,0.92);
    font-size: .72rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .6px;
    padding: 14px 18px; white-space: nowrap; border: none;
}
.back-table thead th:first-child { padding-left: 24px; }
.back-table tbody tr { border-bottom: 1px solid rgba(11,42,90,0.06); transition: background .15s; }
.back-table tbody tr:last-child { border-bottom: none; }
.back-table tbody tr:hover { background: rgba(11,42,90,0.025); }
.back-table td { padding: 14px 18px; font-size: .875rem; vertical-align: middle; border: none; }
.back-table td:first-child { padding-left: 24px; }
.back-student-cell { display: flex; align-items: center; gap: 11px; }
.back-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    background: linear-gradient(135deg,#e8eef8,#c7d8f5);
    color: #0b2a5a; font-size: .8rem; font-weight: 800;
    display: grid; place-items: center; flex-shrink: 0;
}
.back-student-name { font-weight: 700; color: #111827; font-size: .875rem; }
.back-bureau-badge {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(11,42,90,0.06); color: #0b2a5a;
    border-radius: 8px; padding: 4px 10px; font-size: .76rem; font-weight: 600;
}
.back-date-cell { display: flex; align-items: center; gap: 7px; }
.back-date-icon {
    width: 30px; height: 30px; border-radius: 8px;
    background: rgba(11,42,90,0.06); color: #0b2a5a;
    display: grid; place-items: center; font-size: .8rem; flex-shrink: 0;
}
.back-date-text { font-size: .82rem; font-weight: 600; color: #374151; }
.back-date-time { font-size: .72rem; color: var(--text-muted); }
.status-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 13px; border-radius: 999px;
    font-size: .72rem; font-weight: 800; letter-spacing: .2px; border: 1.5px solid;
}
.status-pill-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
.back-actions { display: flex; align-items: center; gap: 6px; }
.back-btn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 13px; border-radius: 8px; font-size: .78rem; font-weight: 700;
    border: 1.5px solid; cursor: pointer; text-decoration: none;
    transition: all .16s;
}
.back-btn:hover { transform: translateY(-1px); filter: brightness(.93); }
.back-btn-approve { background:#dcfce7; color:#166534; border-color:#86efac; }
.back-btn-reject  { background:#fee2e2; color:#991b1b; border-color:#fca5a5; }
.back-empty-row td { text-align:center; padding:56px 20px; color:var(--text-muted); }
.back-empty-icon {
    width:56px; height:56px; border-radius:16px;
    background:rgba(11,42,90,0.06); color:#0b2a5a;
    display:grid; place-items:center; font-size:1.5rem; margin:0 auto 14px;
}
</style>

<?php
$badgeStyles = [
    'pending'   => ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047','dot'=>'#eab308','label'=>'En attente'],
    'confirmed' => ['bg'=>'#dcfce7','color'=>'#166534','border'=>'#86efac','dot'=>'#22c55e','label'=>'Confirmé'],
    'cancelled' => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','dot'=>'#ef4444','label'=>'Annulé'],
];
$counts = ['pending'=>0,'confirmed'=>0,'cancelled'=>0,'total'=>0];
$rows   = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $s = strtolower($row['statut']);
    $counts['total']++;
    if (isset($counts[$s])) $counts[$s]++;
    $rows[] = $row;
}
?>

<div class="back-page-header">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-calendar-check"></i></div>
        <div>
            <div class="back-page-title">Gestion des Rendez-vous</div>
            <div class="back-page-sub">Approuvez ou rejetez les demandes de rendez-vous.</div>
        </div>
    </div>
    <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:6px 16px;font-size:.78rem;font-weight:700;color:var(--brand);">
        <i class="bi bi-list-ul me-1"></i><?= $counts['total'] ?> rendez-vous
    </span>
</div>

<div class="back-stats-row">
    <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
        <i class="bi bi-hourglass-split"></i><?= $counts['pending'] ?> En attente
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= $counts['confirmed'] ?> Confirmés
    </span>
    <span class="back-stat-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
        <i class="bi bi-x-circle-fill"></i><?= $counts['cancelled'] ?> Annulés
    </span>
</div>

<div class="back-table-wrap">
    <div class="table-responsive">
        <table class="back-table">
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Bureau</th>
                    <th>Sujet</th>
                    <th>Date &amp; Heure</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($rows)): ?>
                <tr class="back-empty-row">
                    <td colspan="6">
                        <div class="back-empty-icon"><i class="bi bi-calendar-x"></i></div>
                        <div style="font-weight:600;font-size:.9rem;margin-bottom:4px;">Aucun rendez-vous</div>
                        <div style="font-size:.8rem;">Aucun rendez-vous n'a encore été enregistré.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $row):
                    $statut = strtolower($row['statut']);
                    $bs     = $badgeStyles[$statut] ?? $badgeStyles['pending'];
                    $initials = strtoupper(mb_substr($row['nom_etudiant'], 0, 1));
                ?>
                <tr>
                    <td>
                        <div class="back-student-cell">
                            <div class="back-avatar"><?= $initials ?></div>
                            <div class="back-student-name"><?= htmlspecialchars($row['nom_etudiant']) ?></div>
                        </div>
                    </td>
                    <td>
                        <span class="back-bureau-badge">
                            <i class="bi bi-building-fill" style="font-size:.7rem;"></i>
                            <?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td style="color:#374151;font-size:.85rem;max-width:180px;">
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($row['objet']) ?>">
                            <?= htmlspecialchars($row['objet']) ?>
                        </div>
                    </td>
                    <td>
                        <div class="back-date-cell">
                            <div class="back-date-icon"><i class="bi bi-calendar-event-fill"></i></div>
                            <div>
                                <div class="back-date-text"><?= date('d/m/Y', strtotime($row['date_rdv'])) ?></div>
                                <div class="back-date-time"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($row['heure_rdv']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-pill" style="background:<?= $bs['bg'] ?>;color:<?= $bs['color'] ?>;border-color:<?= $bs['border'] ?>;">
                            <span class="status-pill-dot" style="background:<?= $bs['dot'] ?>;"></span>
                            <?= $bs['label'] ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($statut === 'pending'): ?>
                        <div class="back-actions">
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=confirmed"
                               class="back-btn back-btn-approve">
                                <i class="bi bi-check-lg"></i>Approuver
                            </a>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=cancelled"
                               class="back-btn back-btn-reject">
                                <i class="bi bi-x-lg"></i>Rejeter
                            </a>
                        </div>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:.8rem;font-style:italic;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>