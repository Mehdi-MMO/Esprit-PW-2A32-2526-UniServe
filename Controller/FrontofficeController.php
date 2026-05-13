<?php

declare(strict_types=1);

class FrontofficeController extends Controller
{
    public function landing(): void
    {
        $this->dashboard();
    }

    public function dashboard(): void
    {
        $this->requireLogin();
        $this->requireRole(['etudiant', 'enseignant']);

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        $role = (string) ($currentUser['role'] ?? 'etudiant');

        $fromDate = (new DateTime('first day of this month'))->modify('-7 days')->format('Y-m-d 00:00:00');
        $toDate = (new DateTime('last day of this month'))->modify('+14 days')->format('Y-m-d 23:59:59');

        $calendarService = new CalendarService();
        $calendarFeed = $calendarService->getCalendarFeedForUser(
            $userId,
            $role,
            $fromDate,
            $toDate
        );
        $calendarFeed['events'] = $this->normalizeCalendarEventUrls($calendarFeed['events'] ?? []);

        $events = is_array($calendarFeed['events'] ?? null) ? $calendarFeed['events'] : [];
        $userSnapshot = UserAiSnapshot::build($userId, $role);
        $aiBrief = $this->resolveCachedBrief($userId, 0, 'all', $events, false, $userSnapshot);

        $this->render('frontoffice/dashboard', [
            'title' => 'Accueil',
            'calendarEvents' => $calendarFeed['events'] ?? [],
            'calendarMeta' => $calendarFeed['meta'] ?? [],
            'aiBrief' => $aiBrief,
        ]);
    }

    public function weeklyBrief(): void
    {
        $this->requireLogin();
        $this->requireRole(['etudiant', 'enseignant']);

        $currentUser = $this->currentUser();
        $userId = (int) ($currentUser['id'] ?? 0);
        $role = (string) ($currentUser['role'] ?? 'etudiant');
        $weekOffset = (int) ($_GET['week_offset'] ?? 0);
        if ($weekOffset > 12) {
            $weekOffset = 12;
        } elseif ($weekOffset < -12) {
            $weekOffset = -12;
        }

        $activeFilter = strtolower(trim((string) ($_GET['filter'] ?? 'all')));
        if (!in_array($activeFilter, ['all', 'rendezvous', 'events_registered', 'events_public', 'certifications'], true)) {
            $activeFilter = 'all';
        }

        [$fromDate, $toDate] = $this->weekDateRange($weekOffset);
        $filters = $activeFilter === 'all' ? [] : [$activeFilter];

        $calendarService = new CalendarService();
        $calendarFeed = $calendarService->getCalendarFeedForUser(
            $userId,
            $role,
            $fromDate,
            $toDate,
            $filters
        );
        $calendarFeed['events'] = $this->normalizeCalendarEventUrls($calendarFeed['events'] ?? []);

        $forceRefresh = isset($_GET['refresh']) && in_array((string) $_GET['refresh'], ['1', 'true', 'yes'], true);
        $events = is_array($calendarFeed['events'] ?? null) ? $calendarFeed['events'] : [];
        $userSnapshot = UserAiSnapshot::build($userId, $role);
        $brief = $this->resolveCachedBrief($userId, $weekOffset, $activeFilter, $events, $forceRefresh, $userSnapshot);

        $this->jsonResponse($brief);
    }

    /**
     * Returns a brief either from the per-user cache (if the underlying agenda hasn't
     * changed and the user didn't ask for a refresh) or by calling the brief service
     * and storing the result. Keeps Groq API calls down to once per (user, week, filter,
     * agenda fingerprint) combination.
     *
     * @param list<array<string, mixed>> $events
     * @param array<string, mixed> $userSnapshot
     * @return array<string, mixed>
     */
    private function resolveCachedBrief(
        int $userId,
        int $weekOffset,
        string $activeFilter,
        array $events,
        bool $forceRefresh,
        array $userSnapshot = []
    ): array {
        $cache = new CalendarBriefCache();
        $snapDigest = UserAiSnapshot::digest($userSnapshot);
        $fingerprint = $cache->fingerprint($events, $weekOffset, $activeFilter, $snapDigest);

        if (!$forceRefresh && $userId > 0) {
            $cached = $cache->get($userId, $weekOffset, $activeFilter, $fingerprint);
            if ($cached !== null) {
                return $cached;
            }
        }

        $brief = (new CalendarBriefService())->generateBrief($events, $weekOffset, $activeFilter, $userSnapshot);

        if ($userId > 0) {
            $cache->put($userId, $weekOffset, $activeFilter, $fingerprint, $brief);
        }

        return $brief;
    }

    /**
     * Prefix calendar event URLs with the app base path so links work when the app is served from a subdirectory (e.g. /INTEG/...).
     *
     * @param list<array<string, mixed>> $events
     * @return list<array<string, mixed>>
     */
    private function normalizeCalendarEventUrls(array $events): array
    {
        foreach ($events as &$event) {
            if (!is_array($event)) {
                continue;
            }
            $raw = isset($event['url']) ? trim((string) $event['url']) : '';
            if ($raw === '' || preg_match('#^https?://#i', $raw)) {
                continue;
            }
            $event['url'] = $this->url($raw);
        }
        unset($event);

        return $events;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function weekDateRange(int $weekOffset): array
    {
        $today = new DateTimeImmutable('today');
        $dayOfWeek = (int) $today->format('N');
        $monday = $today->modify('-' . ($dayOfWeek - 1) . ' days');
        if ($weekOffset !== 0) {
            $monday = $monday->modify(($weekOffset > 0 ? '+' : '') . $weekOffset . ' week');
        }

        $start = $monday->setTime(0, 0, 0);
        $end = $monday->modify('+6 days')->setTime(23, 59, 59);
        return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function jsonResponse(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
