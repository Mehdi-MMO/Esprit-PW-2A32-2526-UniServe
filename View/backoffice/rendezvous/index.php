

<?php
$badgeStyles = [
    'reserve'   => ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047','dot'=>'#eab308','label'=>'En attente'],
    'confirme'  => ['bg'=>'#dcfce7','color'=>'#166534','border'=>'#86efac','dot'=>'#22c55e','label'=>'Confirmé'],
    'annule'    => ['bg'=>'#fee2e2','color'=>'#991b1b','border'=>'#fca5a5','dot'=>'#ef4444','label'=>'Annulé'],
    'termine'   => ['bg'=>'#f3f4f6','color'=>'#4b5563','border'=>'#e5e7eb','dot'=>'#9ca3af','label'=>'Terminé'],
];
?>

<div class="back-page-header">
    <div class="back-page-header-left">
        <div class="back-page-icon"><i class="bi bi-calendar-check"></i></div>
        <div>
            <div class="back-page-title">Gestion des Rendez-vous</div>
            <div class="back-page-sub">Approuvez ou rejetez les demandes de rendez-vous.</div>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $this->url('/rendezvous?tab=bureaux') ?>" class="btn btn-outline-primary" style="font-weight:700;border-radius:12px;padding:8px 16px;">
            <i class="bi bi-building me-1"></i> Gérer les bureaux
        </a>
        <span style="background:rgba(11,42,90,0.06);border:1px solid rgba(11,42,90,0.14);border-radius:999px;padding:8px 16px;font-size:.85rem;font-weight:700;color:var(--brand);display:inline-flex;align-items:center;">
            <i class="bi bi-list-ul me-2"></i><?= $stats['total'] ?> rendez-vous
        </span>
    </div>
</div>

<!-- Filtres simples -->
<form method="get" action="<?= $this->url('/rendezvous') ?>" class="mb-4">
    <div class="d-flex gap-2 align-items-center">
        <select name="statut" class="form-select" style="max-width:200px; border-radius:10px; font-size:.9rem; font-weight:600;" onchange="this.form.submit()">
            <option value="">Tous les statuts</option>
            <option value="reserve"  <?= ($filters['statut'] ?? '') === 'reserve' ? 'selected' : '' ?>>En attente</option>
            <option value="confirme" <?= ($filters['statut'] ?? '') === 'confirme' ? 'selected' : '' ?>>Confirmé</option>
            <option value="annule"   <?= ($filters['statut'] ?? '') === 'annule' ? 'selected' : '' ?>>Annulé</option>
            <option value="termine"  <?= ($filters['statut'] ?? '') === 'termine' ? 'selected' : '' ?>>Terminé</option>
        </select>
        <?php if (!empty($filters['statut'])): ?>
            <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">Effacer le filtre</a>
        <?php endif; ?>
    </div>
</form>

<div class="back-stats-row">
    <span class="back-stat-pill" style="background:#fef9c3;color:#854d0e;border-color:#fde047;">
        <i class="bi bi-hourglass-split"></i><?= $stats['reserve'] ?? 0 ?> En attente
    </span>
    <span class="back-stat-pill" style="background:#dcfce7;color:#166534;border-color:#86efac;">
        <i class="bi bi-check-circle-fill"></i><?= $stats['confirme'] ?? 0 ?> Confirmés
    </span>
    <span class="back-stat-pill" style="background:#fee2e2;color:#991b1b;border-color:#fca5a5;">
        <i class="bi bi-x-circle-fill"></i><?= $stats['annule'] ?? 0 ?> Annulés
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
            <?php if (empty($rdvs)): ?>
                <tr class="back-empty-row">
                    <td colspan="6">
                        <div class="back-empty-icon"><i class="bi bi-calendar-x"></i></div>
                        <div style="font-weight:600;font-size:.9rem;margin-bottom:4px;">Aucun rendez-vous</div>
                        <div style="font-size:.8rem;">Aucun rendez-vous ne correspond à vos critères.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rdvs as $row):
                    $statut = strtolower($row['statut']);
                    $bs     = $badgeStyles[$statut] ?? $badgeStyles['reserve'];
                    $prenom = $row['prenom_etudiant'] ?? '';
                    $nom = $row['nom_etudiant'] ?? '';
                    $fullname = trim($prenom . ' ' . $nom);
                    if ($fullname === '') $fullname = 'Étudiant Inconnu';
                    $initials = strtoupper(mb_substr($prenom, 0, 1) . mb_substr($nom, 0, 1));
                ?>
                <tr>
                    <td>
                        <div class="back-student-cell">
                            <div class="back-avatar"><?= $initials ?></div>
                            <div class="back-student-name"><?= htmlspecialchars($fullname) ?></div>
                        </div>
                    </td>
                    <td>
                        <span class="back-bureau-badge">
                            <i class="bi bi-building-fill" style="font-size:.7rem;"></i>
                            <?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?>
                        </span>
                    </td>
                    <td style="color:#374151;font-size:.85rem;max-width:180px;">
                        <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($row['motif'] ?? '') ?>">
                            <?= htmlspecialchars($row['motif'] ?? 'Aucun motif') ?>
                        </div>
                    </td>
                    <td>
                        <div class="back-date-cell">
                            <div class="back-date-icon"><i class="bi bi-calendar-event-fill"></i></div>
                            <div>
                                <div class="back-date-text"><?= date('d/m/Y', strtotime($row['date_debut'])) ?></div>
                                <div class="back-date-time"><i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($row['date_debut'])) ?> - <?= date('H:i', strtotime($row['date_fin'])) ?></div>
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
                        <div class="back-actions">
                        <?php if ($statut === 'reserve'): ?>
                            <form action="<?= $this->url('/rendezvous/updateStatut/' . $row['id']) ?>" method="post" style="display:inline;">
                                <input type="hidden" name="statut" value="confirme">
                                <button type="submit" class="back-btn back-btn-approve">
                                    <i class="bi bi-check-lg"></i> Approuver
                                </button>
                            </form>
                            <form action="<?= $this->url('/rendezvous/updateStatut/' . $row['id']) ?>" method="post" style="display:inline;">
                                <input type="hidden" name="statut" value="annule">
                                <button type="submit" class="back-btn back-btn-reject">
                                    <i class="bi bi-x-lg"></i> Rejeter
                                </button>
                            </form>
                        <?php else: ?>
                            <span style="color:var(--text-muted);font-size:.8rem;font-style:italic;margin-right:10px;">—</span>
                        <?php endif; ?>
                            <form action="<?= $this->url('/rendezvous/delete/' . $row['id']) ?>" method="post" style="display:inline;" onsubmit="return confirm('Confirmer la suppression définitive de ce RDV ?');">
                                <button type="submit" class="back-btn back-btn-delete" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
