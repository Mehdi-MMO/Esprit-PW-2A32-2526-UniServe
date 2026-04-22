<!-- En-tête -->
<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color:var(--brand)">
            <i class="bi bi-calendar-check me-2"></i>Gestion des Rendez-vous
        </h1>
        <p class="text-muted small mb-0">Approuvez ou rejetez les demandes de rendez-vous.</p>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:var(--brand); color:#fff;">
                <tr>
                    <th class="px-4 py-3">Étudiant</th>
                    <th class="py-3">Bureau</th>
                    <th class="py-3">Sujet</th>
                    <th class="py-3">Date &amp; Heure</th>
                    <th class="py-3">Statut</th>
                    <th class="py-3">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $badgeMap = [
                    'pending'   => 'bg-warning text-dark',
                    'confirmed' => 'bg-success',
                    'cancelled' => 'bg-danger',
                ];
                $labelMap = [
                    'pending'   => 'En attente',
                    'confirmed' => 'Confirmé',
                    'cancelled' => 'Annulé',
                ];
                $count = 0;
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    $count++;
                    $statut = strtolower($row['statut']);
                ?>
                <tr>
                    <td class="px-4 fw-semibold"><?= htmlspecialchars($row['nom_etudiant']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($row['objet']) ?></td>
                    <td class="text-muted small">
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('d/m/Y', strtotime($row['date_rdv'])) ?>
                        &nbsp;à&nbsp;<?= htmlspecialchars($row['heure_rdv']) ?>
                    </td>
                    <td>
                        <span class="badge <?= $badgeMap[$statut] ?? 'bg-secondary' ?>">
                            <?= $labelMap[$statut] ?? htmlspecialchars($row['statut']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($statut === 'pending'): ?>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=confirmed"
                               class="btn btn-sm btn-outline-success me-1">
                                <i class="bi bi-check-lg me-1"></i>Approuver
                            </a>
                            <a href="index.php?page=back&module=appointments&action=updateStatus&id=<?= $row['id'] ?>&status=cancelled"
                               class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-x-lg me-1"></i>Rejeter
                            </a>
                        <?php else: ?>
                            <span class="text-muted fst-italic small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if ($count === 0): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                        Aucun rendez-vous enregistré.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
