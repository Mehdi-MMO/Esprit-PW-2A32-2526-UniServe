<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>Votre demande de rendez-vous a été soumise avec succès !
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Hero -->
<div class="us-hero rounded-3 p-5 mb-4 text-center">
    <h1 class="fw-bold mb-2" style="color:var(--brand)">Réservez votre rendez-vous<br>en quelques secondes</h1>
    <p class="text-muted mb-4">Choisissez un bureau, sélectionnez une date et évitez les files d'attente.</p>
    <a href="index.php?action=book" class="btn btn-primary px-4">
        <i class="bi bi-calendar-plus me-2"></i>Réserver maintenant
    </a>
</div>

<!-- Bureaux disponibles -->
<div class="mb-5">
    <h2 class="h5 fw-bold mb-3" style="color:var(--brand)"><i class="bi bi-building me-2"></i>Bureaux disponibles</h2>
    <div class="row g-3">
        <?php while ($b = $stmtBureaux->fetch(PDO::FETCH_ASSOC)): ?>
        <div class="col-sm-6 col-lg-4">
            <div class="us-card p-3 h-100">
                <div class="fw-bold us-card-title mb-1"><?= htmlspecialchars($b['nom']) ?></div>
                <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($b['localisation']) ?></div>
                <?php if (!empty($b['responsable'])): ?>
                <div class="text-muted small mt-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($b['responsable']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>

<!-- Rendez-vous récents -->
<div>
    <h2 class="h5 fw-bold mb-3" style="color:var(--brand)"><i class="bi bi-calendar-check me-2"></i>Rendez-vous récents</h2>
    <div class="row g-3">
        <?php
        $count = 0;
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
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
            $count++;
            $statut = strtolower($row['statut']);
        ?>
        <div class="col-sm-6 col-lg-4">
            <div class="us-card p-3 h-100">
                <span class="badge <?= $badgeMap[$statut] ?? 'bg-secondary' ?> mb-2">
                    <?= $labelMap[$statut] ?? htmlspecialchars($row['statut']) ?>
                </span>
                <div class="fw-bold us-card-title"><?= htmlspecialchars($row['nom_etudiant']) ?></div>
                <div class="text-muted small mt-1">
                    <i class="bi bi-chat-text me-1"></i><?= htmlspecialchars($row['objet']) ?>
                </div>
                <div class="us-divider my-2"></div>
                <div class="text-muted small">
                    <i class="bi bi-calendar me-1"></i><?= date('d/m/Y', strtotime($row['date_rdv'])) ?> à <?= htmlspecialchars($row['heure_rdv']) ?><br>
                    <i class="bi bi-building me-1"></i><?= htmlspecialchars($row['bureau_nom'] ?? 'N/A') ?>
                </div>
            </div>
        </div>
        <?php endwhile; ?>

        <?php if ($count === 0): ?>
        <div class="col-12">
            <p class="text-muted">Aucun rendez-vous pour le moment.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
