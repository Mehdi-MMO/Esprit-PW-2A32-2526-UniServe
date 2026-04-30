<?php
$calendarEvents = $calendarEvents ?? [];
$calendarMeta = $calendarMeta ?? [];
$calendarPayload = [
    'events' => $calendarEvents,
    'meta' => $calendarMeta,
];

$calendarCounts = [
    'all' => count($calendarEvents),
    'rendezvous' => 0,
    'events_registered' => 0,
    'events_public' => 0,
];

$upcomingItems = [];
$now = time();
foreach ($calendarEvents as $item) {
    $sourceType = (string) ($item['source_type'] ?? '');
    if (isset($calendarCounts[$sourceType])) {
        $calendarCounts[$sourceType]++;
    }

    $startValue = (string) ($item['start'] ?? '');
    $startTimestamp = $startValue !== '' ? strtotime($startValue) : false;
    if ($startTimestamp !== false && $startTimestamp >= $now) {
        $upcomingItems[] = $item;
    }
}

usort($upcomingItems, static function (array $a, array $b): int {
    return strcmp((string) ($a['start'] ?? ''), (string) ($b['start'] ?? ''));
});
$upcomingItems = array_slice($upcomingItems, 0, 5);
$eventsTotal = (int) ($calendarCounts['events_registered'] ?? 0) + (int) ($calendarCounts['events_public'] ?? 0);

$latestItem = $upcomingItems[0] ?? null;
$latestLabel = 'Aucun élément à venir';
$latestTime = '';
$latestSource = 'Calendrier';
if ($latestItem !== null) {
    $latestLabel = (string) ($latestItem['title'] ?? 'Élément à venir');
    $latestTime = !empty($latestItem['start']) ? date('d/m H:i', strtotime((string) $latestItem['start'])) : '';
    $latestSource = (string) ($latestItem['owner_label'] ?? 'Calendrier');
}
?>

<div class="us-calendar-hero mb-4">
    <div class="us-calendar-hero-grid">
        <div class="us-calendar-hero-copy">
            <div class="us-kicker mb-1">Front office</div>
            <h1 class="h2 mb-2">Votre agenda hebdomadaire UniServe</h1>
            <p class="us-page-subtitle mb-3">Votre planning en un coup d'oeil, avec vos prochains créneaux et événements à suivre.</p>

            <div class="us-calendar-hero-meta" aria-label="Résumé des éléments calendrier">
                <span><strong><?= (int) ($calendarCounts['all'] ?? 0) ?></strong> éléments</span>
                <span><strong><?= (int) ($calendarCounts['rendezvous'] ?? 0) ?></strong> rendez-vous</span>
                <span><strong><?= $eventsTotal ?></strong> événements</span>
            </div>
        </div>

        <div class="us-calendar-hero-aside">
            <div class="us-calendar-focus-card">
                <div class="us-focus-label">Prochaine échéance</div>
                <div class="us-focus-title"><?= htmlspecialchars($latestLabel, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="us-focus-meta"><?= htmlspecialchars(($latestTime !== '' ? $latestTime . ' · ' : '') . $latestSource, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="us-focus-bar"></div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-light btn-sm">Rendez-vous</a>
                    <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-light btn-sm">Événements</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-xl-8">
        <div class="card us-calendar-card h-100">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h2 class="h5 mb-1">Calendrier</h2>
                        <p class="text-muted mb-0">Une vue agenda hebdomadaire avec créneaux, événements et navigation par semaine.</p>
                    </div>
                    <div class="us-calendar-filters" role="group" aria-label="Filtres calendrier">
                        <button type="button" class="btn btn-sm btn-outline-primary active" data-calendar-filter="all" aria-pressed="true">Tout</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="rendezvous" aria-pressed="false">Rendez-vous</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="events_registered" aria-pressed="false">Mes événements</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="events_public" aria-pressed="false">Événements publics</button>
                    </div>
                </div>

                <div id="us-front-calendar"></div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card us-calendar-side-card mb-3">
            <div class="card-body p-3 p-md-4">
                <h3 class="h6 mb-3">Légende</h3>
                <div class="us-calendar-legend">
                    <div><span class="us-dot" style="background:#2f7df4;"></span> Rendez-vous</div>
                    <div><span class="us-dot" style="background:#1fa971;"></span> Événements publics</div>
                    <div><span class="us-dot" style="background:#7056d8;"></span> Événements complets</div>
                    <div><span class="us-dot" style="background:#f1a535;"></span> Événements planifiés</div>
                </div>
            </div>
        </div>

        <div class="card us-calendar-side-card mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h3 class="h6 mb-0">Prochaines échéances</h3>
                    <span class="badge text-bg-light"><?= count($upcomingItems) ?></span>
                </div>

                <div id="us-calendar-upcoming">
                    <?php if (!empty($upcomingItems)): ?>
                        <?php foreach ($upcomingItems as $item): ?>
                            <?php
                            $itemStart = (string) ($item['start'] ?? '');
                            $itemLabel = !empty($itemStart) ? date('d/m H:i', strtotime($itemStart)) : '';
                            $sourceLabel = (string) ($item['owner_label'] ?? '');
                            ?>
                            <div class="us-upcoming-item">
                                <div class="us-upcoming-dot" style="background:<?= htmlspecialchars((string) ($item['color'] ?? '#2f7df4'), ENT_QUOTES, 'UTF-8') ?>;"></div>
                                <div>
                                    <div class="us-upcoming-title"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="us-upcoming-meta"><?= htmlspecialchars(trim($itemLabel . ($sourceLabel !== '' ? ' · ' . $sourceLabel : '')), ENT_QUOTES, 'UTF-8') ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Rien à afficher pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card us-calendar-side-card">
            <div class="card-body p-3 p-md-4">
                <div class="us-kicker mb-1">Raccourcis</div>
                <h3 class="h6 mb-3">Accès rapides</h3>
                <div class="d-grid gap-2">
                    <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm">Voir mes rendez-vous</a>
                    <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm">Parcourir les événements</a>
                    <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm">Mes documents</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.uniServeBasePath = <?= json_encode($this->url(''), JSON_UNESCAPED_SLASHES) ?>;
window.uniServeCalendarData = <?= json_encode($calendarPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= $this->url('/View/shared/js/frontoffice-calendar.js') ?>"></script>
