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
    'certifications' => 0,
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

$latestItem = $upcomingItems[0] ?? null;
$latestLabel = '';
$latestTime = '';
$latestSource = '';
if ($latestItem !== null) {
    $latestLabel = (string) ($latestItem['title'] ?? 'Élément à venir');
    $latestTime = !empty($latestItem['start']) ? date('d/m H:i', strtotime((string) $latestItem['start'])) : '';
    $latestSource = (string) ($latestItem['owner_label'] ?? '');
}

$sessionUser = $_SESSION['user'] ?? [];
$firstName = trim((string) ($sessionUser['prenom'] ?? ''));
if ($firstName === '') {
    $firstName = trim((string) ($sessionUser['nom'] ?? ''));
}
$greetingHour = (int) date('G');
$greetingPrefix = $greetingHour < 18 ? 'Bonjour' : 'Bonsoir';
$greeting = $firstName !== '' ? $greetingPrefix . ', ' . $firstName : $greetingPrefix;

$todayLabel = '';
if (class_exists('IntlDateFormatter')) {
    $fmt = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
    if ($fmt !== false) {
        $todayLabel = (string) $fmt->format(new DateTimeImmutable('today'));
    }
}
if ($todayLabel === '') {
    $frenchDays = ['Sunday' => 'dimanche', 'Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi', 'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi'];
    $frenchMonths = ['January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin', 'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre'];
    $todayLabel = ($frenchDays[date('l')] ?? date('l')) . ' ' . date('j') . ' ' . ($frenchMonths[date('F')] ?? date('F')) . ' ' . date('Y');
}
?>

<div class="us-fo-header mb-3">
    <div class="us-fo-header-text">
        <div class="us-kicker mb-1">Accueil</div>
        <h1 class="us-fo-title"><?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?></h1>
        <p class="us-fo-subtitle mb-0">
            <?= htmlspecialchars(ucfirst($todayLabel), ENT_QUOTES, 'UTF-8') ?> · Votre semaine en un coup d'œil.
        </p>
    </div>
    <div class="us-fo-header-actions">
        <a class="btn btn-outline-primary btn-sm" href="<?= $this->url('/rendezvous/createForm') ?>">
            <i class="fa-solid fa-calendar-plus me-1" aria-hidden="true"></i> Rendez-vous
        </a>
        <a class="btn btn-primary btn-sm" href="<?= $this->url('/demandes/createForm') ?>">
            <i class="fa-solid fa-paper-plane me-1" aria-hidden="true"></i> Nouvelle demande
        </a>
    </div>
</div>

<div class="us-fo-kpis row row-cols-2 row-cols-md-3 row-cols-xl-5 g-3 mb-3">
    <div class="col">
        <div class="us-kpi">
            <div class="us-kpi-icon us-kpi-icon--brand">
                <i class="fa-solid fa-calendar-week" aria-hidden="true"></i>
            </div>
            <div class="us-kpi-body">
                <div class="us-kpi-label">Éléments</div>
                <div class="us-kpi-value"><?= (int) $calendarCounts['all'] ?></div>
                <div class="us-kpi-meta">Sur la fenêtre courante</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="us-kpi">
            <div class="us-kpi-icon us-kpi-icon--rdv">
                <i class="fa-solid fa-user-check" aria-hidden="true"></i>
            </div>
            <div class="us-kpi-body">
                <div class="us-kpi-label">Rendez-vous</div>
                <div class="us-kpi-value"><?= (int) $calendarCounts['rendezvous'] ?></div>
                <div class="us-kpi-meta">
                    <a class="us-kpi-link" href="<?= $this->url('/rendezvous') ?>">Voir mes rendez-vous <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="us-kpi">
            <div class="us-kpi-icon us-kpi-icon--mine">
                <i class="fa-solid fa-ticket" aria-hidden="true"></i>
            </div>
            <div class="us-kpi-body">
                <div class="us-kpi-label">Mes événements</div>
                <div class="us-kpi-value"><?= (int) $calendarCounts['events_registered'] ?></div>
                <div class="us-kpi-meta">Inscriptions en cours</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="us-kpi">
            <div class="us-kpi-icon us-kpi-icon--public">
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
            </div>
            <div class="us-kpi-body">
                <div class="us-kpi-label">Événements publics</div>
                <div class="us-kpi-value"><?= (int) $calendarCounts['events_public'] ?></div>
                <div class="us-kpi-meta">
                    <a class="us-kpi-link" href="<?= $this->url('/evenements') ?>">Parcourir <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="us-kpi">
            <div class="us-kpi-icon us-kpi-icon--cert">
                <i class="fa-solid fa-graduation-cap" aria-hidden="true"></i>
            </div>
            <div class="us-kpi-body">
                <div class="us-kpi-label">Certifications</div>
                <div class="us-kpi-value"><?= (int) $calendarCounts['certifications'] ?></div>
                <div class="us-kpi-meta">
                    <a class="us-kpi-link" href="<?= $this->url('/certifications') ?>">Mes demandes <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($latestItem !== null): ?>
    <a href="<?= htmlspecialchars((string) ($latestItem['url'] ?? $this->url('/rendezvous')), ENT_QUOTES, 'UTF-8') ?>" class="us-next-callout mb-3" aria-label="Prochain créneau à venir">
        <div class="us-next-callout-badge">
            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
            <span>Prochain</span>
        </div>
        <div class="us-next-callout-body">
            <div class="us-next-callout-title"><?= htmlspecialchars($latestLabel, ENT_QUOTES, 'UTF-8') ?></div>
            <div class="us-next-callout-meta">
                <?php if ($latestTime !== ''): ?><span><i class="fa-regular fa-clock me-1" aria-hidden="true"></i><?= htmlspecialchars($latestTime, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
                <?php if ($latestSource !== ''): ?><span><i class="fa-solid fa-location-dot me-1" aria-hidden="true"></i><?= htmlspecialchars($latestSource, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
            </div>
        </div>
        <div class="us-next-callout-cta">
            Détails <i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i>
        </div>
    </a>
<?php endif; ?>

<div class="row g-3">
    <div class="col-xl-9">
        <div class="card us-calendar-card h-100">
            <div class="card-body p-3 p-md-4">
                <div class="us-calendar-toolbar">
                    <div>
                        <h2 class="h5 mb-1">Semaine</h2>
                        <p class="text-muted mb-0 small">Navigation par semaine · filtres par type d'activité</p>
                    </div>
                    <div class="us-calendar-filters" role="group" aria-label="Filtres calendrier">
                        <button type="button" class="btn btn-sm btn-outline-primary active" data-calendar-filter="all" aria-pressed="true">Tout</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="rendezvous" aria-pressed="false">Rendez-vous</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="events_registered" aria-pressed="false">Mes événements</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="events_public" aria-pressed="false">Événements publics</button>
                        <button type="button" class="btn btn-sm btn-outline-primary" data-calendar-filter="certifications" aria-pressed="false">Certifications</button>
                    </div>
                </div>

                <div id="us-front-calendar"></div>

                <div class="us-calendar-legend-footer" aria-label="Légende des couleurs">
                    <div class="us-calendar-legend-item"><span class="us-dot" style="background:#2f7df4;"></span>Rendez-vous</div>
                    <div class="us-calendar-legend-item"><span class="us-dot" style="background:#1fa971;"></span>Événements publics</div>
                    <div class="us-calendar-legend-item"><span class="us-dot" style="background:#7056d8;"></span>Événements inscrits</div>
                    <div class="us-calendar-legend-item"><span class="us-dot" style="background:#f1a535;"></span>Planifiés</div>
                    <div class="us-calendar-legend-item"><span class="us-dot" style="background:#8e44ad;"></span>Certifications</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3">
        <div class="card us-side-card us-ai-card-compact mb-3" id="us-ai-brief-card">
            <div class="card-body p-3 p-md-4">
                <div class="us-ai-card-head">
                    <div>
                        <div class="us-kicker mb-1">Synthèse</div>
                        <h3 class="h6 mb-0 fw-semibold">Cette semaine</h3>
                    </div>
                    <span class="us-ai-source-badge <?= $aiSource === 'ai' ? 'is-ai' : 'is-local' ?>" id="us-ai-brief-source">
                        <i class="fa-solid <?= $aiSource === 'ai' ? 'fa-wand-magic-sparkles' : 'fa-list-check' ?>" aria-hidden="true"></i>
                        <?= $aiSource === 'ai' ? 'Analyse IA' : 'Vue locale' ?>
                    </span>
                </div>

                <p class="us-ai-summary" id="us-ai-brief-summary">
                    <?= htmlspecialchars($aiSummary !== '' ? $aiSummary : 'Résumé dérivé de votre agenda et des filtres sélectionnés.', ENT_QUOTES, 'UTF-8') ?>
                </p>

                <div class="us-ai-priorities" id="us-ai-priorities">
                    <?php if ($aiPriorities !== []): ?>
                        <?php foreach ($aiPriorities as $priorityIndex => $priority): ?>
                            <div class="us-ai-priority">
                                <div class="us-ai-priority-rank"><?= (int) $priorityIndex + 1 ?></div>
                                <div class="us-ai-priority-body">
                                    <div class="us-ai-priority-label"><?= htmlspecialchars((string) ($priority['label'] ?? 'Élément'), ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php if (!empty($priority['reason'])): ?>
                                        <div class="us-ai-priority-reason"><?= htmlspecialchars((string) $priority['reason'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="us-ai-empty">Aucune priorité détectée pour cette semaine.</div>
                    <?php endif; ?>
                </div>

                <?php if ($aiRisks !== []): ?>
                    <div class="us-ai-risk" id="us-ai-risks-inline">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <span><?= htmlspecialchars((string) $aiRisks[0], ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php else: ?>
                    <div class="us-ai-risk d-none" id="us-ai-risks-inline">
                        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
                        <span></span>
                    </div>
                <?php endif; ?>

                <ul class="us-ai-brief-list d-none" id="us-ai-risks" aria-hidden="true">
                    <?php foreach ($aiRisks as $risk): ?>
                        <li><?= htmlspecialchars((string) $risk, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
                <ul class="us-ai-brief-list d-none" id="us-ai-actions" aria-hidden="true">
                    <?php foreach ($aiActions as $action): ?>
                        <li><?= htmlspecialchars((string) ($action['action'] ?? ''), ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>

                <div class="us-ai-foot">
                    <span class="us-ai-freshness" id="us-ai-freshness">
                        <i class="fa-regular fa-clock me-1" aria-hidden="true"></i>
                        <?= $aiGeneratedAt !== '' ? 'Mis à jour ' . htmlspecialchars(date('d/m H:i', strtotime($aiGeneratedAt)), ENT_QUOTES, 'UTF-8') : 'Pas encore généré' ?>
                    </span>
                    <div class="us-ai-foot-actions">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="us-ai-brief-refresh" title="Actualiser la synthèse">
                            <i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#usAiBriefModal">
                            Détail
                        </button>
                    </div>
                </div>
                <p class="text-danger small mb-0 mt-2 d-none" id="us-ai-brief-error"></p>
            </div>
        </div>

        <div class="card us-side-card">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="us-kicker mb-1">Agenda</div>
                        <h3 class="h6 mb-0 fw-semibold">À venir</h3>
                    </div>
                    <span id="us-calendar-upcoming-count" class="us-pill"><?= count($upcomingItems) ?></span>
                </div>

                <div id="us-calendar-upcoming" class="us-upcoming-list">
                    <?php if (!empty($upcomingItems)): ?>
                        <?php foreach ($upcomingItems as $item): ?>
                            <?php
                            $itemStart = (string) ($item['start'] ?? '');
                            $itemLabel = !empty($itemStart) ? date('d/m H:i', strtotime($itemStart)) : '';
                            $sourceLabel = (string) ($item['owner_label'] ?? '');
                            $itemUrl = (string) ($item['url'] ?? '');
                            ?>
                            <a class="us-upcoming-item" href="<?= htmlspecialchars($itemUrl !== '' ? $itemUrl : $this->url('/rendezvous'), ENT_QUOTES, 'UTF-8') ?>">
                                <span class="us-upcoming-dot" style="background:<?= htmlspecialchars((string) ($item['color'] ?? '#2f7df4'), ENT_QUOTES, 'UTF-8') ?>;"></span>
                                <span class="us-upcoming-body">
                                    <span class="us-upcoming-title"><?= htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></span>
                                    <span class="us-upcoming-meta"><?= htmlspecialchars(trim($itemLabel . ($sourceLabel !== '' ? ' · ' . $sourceLabel : '')), ENT_QUOTES, 'UTF-8') ?></span>
                                </span>
                                <i class="fa-solid fa-chevron-right us-upcoming-chevron" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted small mb-0">Rien à afficher pour le moment.</p>
                    <?php endif; ?>
                </div>

                <div class="us-quick-actions mt-3">
                    <a href="<?= $this->url('/rendezvous') ?>" class="us-quick-action">
                        <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                        <span>Rendez-vous</span>
                    </a>
                    <a href="<?= $this->url('/evenements') ?>" class="us-quick-action">
                        <i class="fa-solid fa-calendar-day" aria-hidden="true"></i>
                        <span>Événements</span>
                    </a>
                    <a href="<?= $this->url('/documents') ?>" class="us-quick-action">
                        <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                        <span>Documents</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="usAiBriefModal" tabindex="-1" aria-labelledby="usAiBriefModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="usAiBriefModalLabel">Synthèse · détail</h2>
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
                    <div class="col-md-12">
                        <h3 class="h6 text-secondary">Points de vigilance</h3>
                        <ul class="us-ai-brief-list" id="us-ai-modal-risks">
                            <?php if ($aiRisks !== []): ?>
                                <?php foreach ($aiRisks as $risk): ?>
                                    <li><?= htmlspecialchars((string) $risk, ENT_QUOTES, 'UTF-8') ?></li>
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
<script src="<?= $this->asset('/View/shared/js/frontoffice-calendar.js') ?>"></script>
