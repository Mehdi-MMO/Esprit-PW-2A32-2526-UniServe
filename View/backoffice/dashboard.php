<?php
$stats = $stats ?? [];
$userStats = $stats['users'] ?? [];
$demandesStats = $stats['demandes'] ?? [];
$rdvStats = $stats['rendezvous'] ?? [];
$docsStats = $stats['documents'] ?? [];
$eventsStats = $stats['evenements'] ?? [];
$activityStats = $stats['activity'] ?? [];
$demoCalendar = $demoCalendar ?? [];
$demoNotice = trim((string) ($demoNotice ?? ''));
$demoError = trim((string) ($demoError ?? ''));

$usersRoleData = [
    (int) ($userStats['etudiant'] ?? 0),
    (int) ($userStats['enseignant'] ?? 0),
    (int) ($userStats['staff'] ?? 0),
];

$demandesChartData = [
    (int) ($demandesStats['en_attente'] ?? 0),
    (int) ($demandesStats['en_cours'] ?? 0),
    (int) ($demandesStats['traite'] ?? 0),
    (int) ($demandesStats['rejete'] ?? 0),
];

$loginTrend = $activityStats['login_trend'] ?? [];
$loginLabels = array_map(static fn(array $row): string => (string) ($row['label'] ?? ''), $loginTrend);
$loginCounts = array_map(static fn(array $row): int => (int) ($row['count'] ?? 0), $loginTrend);

$hasDemandesData = array_sum($demandesChartData) > 0;
$hasUsersData = array_sum($usersRoleData) > 0;
$hasLoginData = array_sum($loginCounts) > 0;
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-3 us-page-header">
    <div>
        <div class="us-kicker mb-1">Pilotage</div>
        <h1 class="h3 mb-1">Tableau de bord administratif</h1>
        <p class="text-muted mb-0">Vue consolidée des comptes, demandes et activité.</p>
    </div>
</div>

<?php if ($demoNotice !== ''): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($demoNotice, ENT_QUOTES, 'UTF-8') ?>
        <?php if (!empty($_GET['demo_created']) || !empty($_GET['demo_cleared'])): ?>
            <div class="small mt-1 text-success-emphasis">
                <?php if (!empty($_GET['demo_created'])): ?>
                    Créés: <?= (int) $_GET['demo_created'] ?>
                <?php endif; ?>
                <?php if (!empty($_GET['demo_cleared'])): ?>
                    <?= !empty($_GET['demo_created']) ? ' · ' : '' ?>Vidés: <?= (int) $_GET['demo_cleared'] ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<?php if ($demoError !== ''): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($demoError, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
    </div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card h-100 us-stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Utilisateurs</p>
                        <h3 class="mb-0"><?= (int) ($userStats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-primary"><i class="bi bi-people"></i></div>
                </div>
                <small class="d-block mt-2"><span class="badge bg-success bg-opacity-25 text-success"><?= (int) ($userStats['actif'] ?? 0) ?> actifs</span></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card h-100 us-stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Demandes</p>
                        <h3 class="mb-0"><?= (int) ($demandesStats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-warning"><i class="bi bi-clipboard-check"></i></div>
                </div>
                <small class="d-block mt-2"><span class="badge bg-warning bg-opacity-25 text-warning"><?= (int) ($demandesStats['en_attente'] ?? 0) ?> en attente</span></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card h-100 us-stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Rendez-vous</p>
                        <h3 class="mb-0"><?= (int) ($rdvStats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-info"><i class="bi bi-calendar-event"></i></div>
                </div>
                <small class="d-block mt-2"><span class="badge bg-info bg-opacity-25 text-info"><?= (int) ($rdvStats['upcoming'] ?? 0) ?> à venir</span></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card h-100 us-stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Documents</p>
                        <h3 class="mb-0"><?= (int) ($docsStats['total'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-secondary"><i class="bi bi-file-earmark"></i></div>
                </div>
                <small class="d-block mt-2"><span class="badge bg-secondary bg-opacity-25 text-secondary"><?= (int) ($docsStats['livre'] ?? 0) ?> livrés</span></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card h-100 us-stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Événements</p>
                        <h3 class="mb-0"><?= (int) ($eventsStats['total_events'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-danger"><i class="bi bi-stars"></i></div>
                </div>
                <small class="d-block mt-2"><span class="badge bg-danger bg-opacity-25 text-danger"><?= (int) ($eventsStats['total_inscriptions'] ?? 0) ?> inscrits</span></small>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-4 col-xl-2">
        <div class="card h-100 us-stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <p class="text-muted mb-1 small">Connexions</p>
                        <h3 class="mb-0"><?= (int) ($activityStats['active_today'] ?? 0) ?></h3>
                    </div>
                    <div class="stat-icon stat-icon-success"><i class="bi bi-activity"></i></div>
                </div>
                <small class="d-block mt-2"><span class="badge bg-success bg-opacity-25 text-success">Aujourd'hui</span></small>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4 us-quick-actions">
    <div class="card-body d-flex flex-wrap gap-2">
        <a href="<?= $this->url('/utilisateurs') ?>" class="btn btn-outline-primary btn-sm">Utilisateurs</a>
        <a href="<?= $this->url('/demandes') ?>" class="btn btn-outline-primary btn-sm">Demandes</a>
        <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm">Rendez-vous</a>
        <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm">Documents</a>
        <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm">Événements</a>
    </div>
</div>

<div class="card mb-4 us-demo-card">
    <div class="card-body d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <div class="us-kicker mb-1">Agenda de démonstration</div>
            <h2 class="h5 mb-1">Remplir le calendrier frontoffice avec des faux rendez-vous et événements</h2>
            <p class="text-muted mb-0">
                Compte cible: <strong><?= htmlspecialchars((string) ($demoCalendar['target_user'] ?? 'Étudiant démo'), ENT_QUOTES, 'UTF-8') ?></strong>
                <?php if ((int) ($demoCalendar['item_count'] ?? 0) > 0): ?>
                    · <?= (int) ($demoCalendar['item_count'] ?? 0) ?> éléments actifs
                <?php else: ?>
                    · aucun jeu de démonstration chargé
                <?php endif; ?>
            </p>
            <?php if (!empty($demoCalendar['first_item']) && !empty($demoCalendar['last_item'])): ?>
                <div class="text-muted small mt-1">
                    Période: <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $demoCalendar['first_item'])), ENT_QUOTES, 'UTF-8') ?>
                    → <?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $demoCalendar['last_item'])), ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2 us-demo-actions">
            <form method="post" action="<?= $this->url('/backoffice/agendaDemo') ?>" class="m-0">
                <input type="hidden" name="task" value="seed">
                <button type="submit" class="btn btn-primary btn-sm">Générer la démo</button>
            </form>
            <form method="post" action="<?= $this->url('/backoffice/agendaDemo') ?>" class="m-0">
                <input type="hidden" name="task" value="clear">
                <button type="submit" class="btn btn-outline-danger btn-sm">Vider la démo</button>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Demandes par statut</h5></div>
            <div class="card-body">
                <?php if ($hasDemandesData): ?>
                    <div class="us-chart-wrap"><canvas id="demandesChart"></canvas></div>
                <?php else: ?>
                    <div class="us-empty-state">Aucune demande enregistrée pour le moment.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Distribution des utilisateurs</h5></div>
            <div class="card-body">
                <?php if ($hasUsersData): ?>
                    <div class="us-chart-wrap"><canvas id="usersChart"></canvas></div>
                <?php else: ?>
                    <div class="us-empty-state">Aucun utilisateur disponible pour la répartition.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Tendance des connexions (7 derniers jours)</h5></div>
            <div class="card-body">
                <?php if ($hasLoginData): ?>
                    <div class="us-chart-wrap us-chart-wrap--wide"><canvas id="loginTrendChart"></canvas></div>
                <?php else: ?>
                    <div class="us-empty-state">Pas encore de données de connexion.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Demandes de service</h5></div>
            <div class="card-body">
                <div class="list-group us-list-compact">
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>En attente</span><span class="badge bg-warning"><?= (int) ($demandesStats['en_attente'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>En cours</span><span class="badge bg-info"><?= (int) ($demandesStats['en_cours'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Traité</span><span class="badge bg-success"><?= (int) ($demandesStats['traite'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Rejeté</span><span class="badge bg-danger"><?= (int) ($demandesStats['rejete'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0"><strong>Cette semaine</strong><strong><?= (int) ($demandesStats['this_week'] ?? 0) ?></strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Pipeline des documents</h5></div>
            <div class="card-body">
                <div class="list-group us-list-compact">
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>En attente</span><span class="badge bg-warning"><?= (int) ($docsStats['en_attente'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>En validation</span><span class="badge bg-info"><?= (int) ($docsStats['en_validation'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Validé</span><span class="badge bg-success"><?= (int) ($docsStats['valide'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Livré</span><span class="badge bg-success"><?= (int) ($docsStats['livre'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0"><span>Rejeté</span><span class="badge bg-danger"><?= (int) ($docsStats['rejete'] ?? 0) ?></span></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Événements</h5></div>
            <div class="card-body">
                <div class="list-group us-list-compact">
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Total d'événements</span><strong><?= (int) ($eventsStats['total_events'] ?? 0) ?></strong></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Total d'inscriptions</span><strong><?= (int) ($eventsStats['total_inscriptions'] ?? 0) ?></strong></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Présents</span><span class="badge bg-success"><?= (int) ($eventsStats['present'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Absents</span><span class="badge bg-danger"><?= (int) ($eventsStats['absent'] ?? 0) ?></span></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-0"><strong>Taux de présence</strong><strong class="text-success"><?= (float) ($eventsStats['attendance_rate'] ?? 0) ?>%</strong></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header border-0"><h5 class="card-title mb-0">Activité récente</h5></div>
            <div class="card-body">
                <div class="list-group us-list-compact mb-3">
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Connexions aujourd'hui</span><strong><?= (int) ($activityStats['active_today'] ?? 0) ?></strong></div>
                    <div class="list-group-item d-flex justify-content-between align-items-center"><span>Connexions cette semaine</span><strong><?= (int) ($activityStats['active_this_week'] ?? 0) ?></strong></div>
                </div>

                <p class="text-muted small mb-2">Dernières connexions</p>
                <div class="recent-logins">
                    <?php if (!empty($activityStats['recent_logins'])): ?>
                        <?php foreach (array_slice($activityStats['recent_logins'], 0, 5) as $login): ?>
                            <?php $rawDate = (string) ($login['derniere_connexion'] ?? ''); ?>
                            <?php $formattedDate = $rawDate !== '' ? date('d/m/Y H:i', strtotime($rawDate)) : 'N/A'; ?>
                            <div class="d-flex align-items-center gap-2 py-2 border-bottom us-login-row">
                                <i class="bi bi-person-circle"></i>
                                <div class="flex-grow-1">
                                    <strong><?= htmlspecialchars((string) ($login['prenom'] ?? '')) ?> <?= htmlspecialchars((string) ($login['nom'] ?? '')) ?></strong>
                                    <div class="text-muted small"><?= htmlspecialchars($formattedDate) ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Aucune connexion récente.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header border-0"><h5 class="card-title mb-0">Rendez-vous</h5></div>
    <div class="card-body">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3"><p class="text-muted small mb-1">Réservés</p><h4 class="mb-0"><?= (int) ($rdvStats['reserve'] ?? 0) ?></h4></div>
            <div class="col-6 col-md-3"><p class="text-muted small mb-1">Confirmés</p><h4 class="mb-0"><?= (int) ($rdvStats['confirme'] ?? 0) ?></h4></div>
            <div class="col-6 col-md-3"><p class="text-muted small mb-1">À venir</p><h4 class="mb-0 text-success"><?= (int) ($rdvStats['upcoming'] ?? 0) ?></h4></div>
            <div class="col-6 col-md-3"><p class="text-muted small mb-1">Annulés</p><h4 class="mb-0 text-danger"><?= (int) ($rdvStats['annule'] ?? 0) ?></h4></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(() => {
    const commonLegend = {
        labels: {
            boxWidth: 10,
            boxHeight: 10,
            usePointStyle: true,
            pointStyle: 'circle',
        }
    };

    const demandesCanvas = document.getElementById('demandesChart');
    if (demandesCanvas) {
        new Chart(demandesCanvas, {
            type: 'bar',
            data: {
                labels: ['En attente', 'En cours', 'Traité', 'Rejeté'],
                datasets: [{
                    label: 'Demandes',
                    data: <?= json_encode($demandesChartData, JSON_UNESCAPED_UNICODE) ?>,
                    backgroundColor: ['#f4b400', '#2bb0ed', '#25a971', '#d64550'],
                    borderRadius: 8,
                    maxBarThickness: 48,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(0,0,0,.06)' },
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const usersCanvas = document.getElementById('usersChart');
    if (usersCanvas) {
        new Chart(usersCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Étudiants', 'Enseignants', 'Staff'],
                datasets: [{
                    data: <?= json_encode($usersRoleData, JSON_UNESCAPED_UNICODE) ?>,
                    backgroundColor: ['#22b8d3', '#20905a', '#2a6edf'],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 6,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '58%',
                plugins: {
                    legend: {
                        ...commonLegend,
                        position: 'bottom',
                    }
                }
            }
        });
    }

    const loginTrendCanvas = document.getElementById('loginTrendChart');
    if (loginTrendCanvas) {
        new Chart(loginTrendCanvas, {
            type: 'line',
            data: {
                labels: <?= json_encode($loginLabels, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [{
                    label: 'Connexions',
                    data: <?= json_encode($loginCounts, JSON_UNESCAPED_UNICODE) ?>,
                    borderColor: '#2a6edf',
                    backgroundColor: 'rgba(42, 110, 223, .14)',
                    fill: true,
                    tension: .35,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#2a6edf',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 },
                        grid: { color: 'rgba(0,0,0,.06)' },
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }
})();
</script>
