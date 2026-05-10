<?php
$calendarEvents = $calendarEvents ?? [];
$calendarMeta = $calendarMeta ?? [];
$calendarPayload = [
    'events' => $calendarEvents,
    'meta' => $calendarMeta,
];
$aiBrief = is_array($aiBrief ?? null) ? $aiBrief : [];
$aiSummary = trim((string) ($aiBrief['summary'] ?? ''));
$aiPriorities = array_slice(is_array($aiBrief['ranked_priorities'] ?? null) ? $aiBrief['ranked_priorities'] : [], 0, 3);
$aiRisks = array_slice(is_array($aiBrief['risks'] ?? null) ? $aiBrief['risks'] : [], 0, 3);
$aiActions = array_slice(is_array($aiBrief['next_actions'] ?? null) ? $aiBrief['next_actions'] : [], 0, 3);
$aiDailyBriefs = array_slice(is_array($aiBrief['daily_briefs'] ?? null) ? $aiBrief['daily_briefs'] : [], 0, 7);
$aiSource = strtolower((string) ($aiBrief['source'] ?? 'fallback'));
$aiGeneratedAt = (string) ($aiBrief['generated_at'] ?? '');

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

<div class="us-calendar-hero mb-3">
    <div class="us-calendar-hero-grid">
        <div class="us-calendar-hero-copy">
            <div class="us-kicker mb-1">Accueil</div>
            <h1 class="h2 mb-2">Agenda de la semaine</h1>
            <p class="us-page-subtitle mb-3">Créneaux, rendez-vous et événements sur une même vue.</p>

            <div class="us-calendar-hero-meta" aria-label="Résumé des éléments calendrier">
                <span><strong><?= (int) ($calendarCounts['all'] ?? 0) ?></strong> éléments</span>
                <span><strong><?= (int) ($calendarCounts['rendezvous'] ?? 0) ?></strong> rendez-vous</span>
                <span><strong><?= $eventsTotal ?></strong> événements</span>
            </div>
        </div>

        <div class="us-calendar-hero-aside">
            <div class="us-calendar-focus-card">
                <div class="us-focus-label">Prochain créneau</div>
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
                        <h2 class="h5 mb-1">Semaine</h2>
                        <p class="text-muted mb-0 small">Navigation par semaine · filtres par type d’activité</p>
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
        <div class="card us-calendar-side-card us-ai-brief-card mb-3" id="us-ai-brief-card">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <div class="us-kicker mb-1">Synthèse</div>
                        <h3 class="h6 mb-0 fw-semibold">Points à retenir</h3>
                    </div>
                    <span class="badge <?= $aiSource === 'ai' ? 'text-bg-primary' : 'text-bg-secondary' ?>" id="us-ai-brief-source">
                        <?= $aiSource === 'ai' ? 'Analyse enrichie' : 'Vue locale' ?>
                    </span>
                </div>

                <p class="us-ai-brief-lede text-muted small mb-3" id="us-ai-brief-summary">
                    <?= htmlspecialchars($aiSummary !== '' ? $aiSummary : 'Résumé dérivé de votre agenda et des filtres sélectionnés.', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="us-ai-brief-section">
                    <div class="us-ai-brief-label">Priorités</div>
                    <ul class="us-ai-brief-list" id="us-ai-priorities">
                        <?php if ($aiPriorities !== []): ?>
                            <?php foreach ($aiPriorities as $priority): ?>
                                <li>
                                    <span class="fw-semibold"><?= htmlspecialchars((string) ($priority['label'] ?? 'Élément'), ENT_QUOTES, 'UTF-8') ?></span>
                                    <small><?= htmlspecialchars((string) ($priority['reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="us-ai-brief-muted">Rien à signaler.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="us-ai-brief-section">
                    <div class="us-ai-brief-label">Vigilance</div>
                    <ul class="us-ai-brief-list" id="us-ai-risks">
                        <?php if ($aiRisks !== []): ?>
                            <?php foreach ($aiRisks as $risk): ?>
                                <li><?= htmlspecialchars((string) $risk, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="us-ai-brief-muted">Rien à signaler.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="us-ai-brief-section">
                    <div class="us-ai-brief-label">À prévoir</div>
                    <ul class="us-ai-brief-list" id="us-ai-actions">
                        <?php if ($aiActions !== []): ?>
                            <?php foreach ($aiActions as $action): ?>
                                <li>
                                    <span><?= htmlspecialchars((string) ($action['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if (!empty($action['suggested_time'])): ?>
                                        <small><?= htmlspecialchars((string) $action['suggested_time'], ENT_QUOTES, 'UTF-8') ?></small>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="us-ai-brief-muted">Rien à signaler.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <p class="text-muted small mb-3 us-ai-freshness-line" id="us-ai-freshness">
                    <?= $aiGeneratedAt !== '' ? 'Mis à jour : ' . htmlspecialchars(date('d/m H:i', strtotime($aiGeneratedAt)), ENT_QUOTES, 'UTF-8') : 'Mis à jour : —' ?>
                </p>
                <p class="text-danger small mb-2 d-none" id="us-ai-brief-error"></p>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-primary btn-sm" id="us-ai-brief-refresh">Actualiser</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#usAiBriefModal">
                        Détail
                    </button>
                </div>
            </div>
        </div>

        <div class="card us-calendar-side-card mb-3">
            <div class="card-body p-3 p-md-4">
                <h3 class="h6 mb-3 text-secondary">Légende</h3>
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
                    <h3 class="h6 mb-0 text-secondary">À venir</h3>
                    <span id="us-calendar-upcoming-count" class="badge rounded-pill text-bg-light border"><?= count($upcomingItems) ?></span>
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
                <div class="us-kicker mb-1">Liens</div>
                <h3 class="h6 mb-3 text-secondary">Services</h3>
                <div class="d-grid gap-2">
                    <a href="<?= $this->url('/rendezvous') ?>" class="btn btn-outline-primary btn-sm">Voir mes rendez-vous</a>
                    <a href="<?= $this->url('/evenements') ?>" class="btn btn-outline-primary btn-sm">Parcourir les événements</a>
                    <a href="<?= $this->url('/documents') ?>" class="btn btn-outline-primary btn-sm">Mes documents</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="usAiBriefModal" tabindex="-1" aria-labelledby="usAiBriefModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="usAiBriefModalLabel">Synthèse — détail</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3" id="us-ai-modal-summary">
                    <?= htmlspecialchars($aiSummary !== '' ? $aiSummary : 'Résumé dérivé de votre agenda et des filtres sélectionnés.', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="row g-3">
                    <div class="col-md-6">
                        <h3 class="h6 text-secondary">Priorités</h3>
                        <ul class="us-ai-brief-list" id="us-ai-modal-priorities">
                            <?php if ($aiPriorities !== []): ?>
                                <?php foreach ($aiPriorities as $priority): ?>
                                    <li>
                                        <span class="fw-semibold"><?= htmlspecialchars((string) ($priority['label'] ?? 'Élément'), ENT_QUOTES, 'UTF-8') ?></span>
                                        <small><?= htmlspecialchars((string) ($priority['reason'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="us-ai-brief-muted">Rien à signaler.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h3 class="h6 text-secondary">À prévoir</h3>
                        <ul class="us-ai-brief-list" id="us-ai-modal-actions">
                            <?php if ($aiActions !== []): ?>
                                <?php foreach ($aiActions as $action): ?>
                                    <li>
                                        <span><?= htmlspecialchars((string) ($action['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if (!empty($action['suggested_time'])): ?>
                                            <small><?= htmlspecialchars((string) $action['suggested_time'], ENT_QUOTES, 'UTF-8') ?></small>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="us-ai-brief-muted">Rien à signaler.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="mt-3">
                    <h3 class="h6 text-secondary">Jour par jour</h3>
                    <ul class="us-ai-brief-list" id="us-ai-modal-daily">
                        <?php if ($aiDailyBriefs !== []): ?>
                            <?php foreach ($aiDailyBriefs as $daily): ?>
                                <li>
                                    <span class="fw-semibold"><?= htmlspecialchars((string) ($daily['day'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <small><?= htmlspecialchars((string) ($daily['brief'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
                                </li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="us-ai-brief-muted">Aucun indicateur pour ces jours.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary btn-sm" id="us-ai-brief-refresh-modal">Actualiser</button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
window.uniServeBasePath = <?= json_encode($this->url(''), JSON_UNESCAPED_SLASHES) ?>;
window.uniServeCalendarData = <?= json_encode($calendarPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.uniServeAiBriefData = <?= json_encode($aiBrief, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
window.uniServeAiBriefEndpoint = <?= json_encode($this->url('/frontoffice/weeklyBrief'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= $this->url('/View/shared/js/frontoffice-calendar.js') ?>"></script>
