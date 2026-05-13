<?php
declare(strict_types=1);

$rdvs = $rdvs ?? [];
$statut_labels = $statut_labels ?? [];
$generated_at = (string) ($generated_at ?? '');

$fmt = static function (string $d): string {
    $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $d);
    if ($dt === false) {
        $ts = strtotime($d);

        return $ts !== false ? date('d/m/Y H:i:s', $ts) : htmlspecialchars($d, ENT_QUOTES, 'UTF-8');
    }

    return $dt->format('d/m/Y H:i:s');
};
?>
<div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h4 mb-1">Rendez-vous — export</h1>
            <p class="text-muted small mb-0">Généré le <?= htmlspecialchars($generated_at, ENT_QUOTES, 'UTF-8') ?> · <?= count($rdvs) ?> ligne(s)</p>
        </div>
        <button type="button" class="btn btn-primary d-print-none" onclick="window.print()">
            <i class="fa-solid fa-print me-2" aria-hidden="true"></i>Imprimer ou enregistrer en PDF
        </button>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Étudiant</th>
                    <th>Bureau</th>
                    <th>Sujet</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($rdvs === []): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">Aucun rendez-vous.</td></tr>
                <?php else: ?>
                    <?php foreach ($rdvs as $row): ?>
                        <?php
                        $st = (string) ($row['statut'] ?? '');
                        $lab = $statut_labels[$st] ?? $st;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars((string) ($row['etudiant_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($row['bureau_nom'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($row['motif'] ?? '—'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fmt((string) ($row['date_debut'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fmt((string) ($row['date_fin'] ?? '')), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($lab, ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p class="small text-muted d-print-none mb-0">Astuce : dans la boîte d’impression du navigateur, choisissez « Enregistrer au format PDF » comme destination.</p>
</div>
