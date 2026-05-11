<?php

declare(strict_types=1);

class CalendarBriefService
{
    private const DEFAULT_MODEL = 'llama-3.3-70b-versatile';

    /**
     * @param array<int, array<string, mixed>> $events
     * @return array<string, mixed>
     */
    public function generateBrief(array $events, int $weekOffset = 0, string $activeFilter = 'all'): array
    {
        $window = $this->weekWindow($weekOffset);
        $normalizedEvents = $this->normalizeEvents($events, $window, $activeFilter);
        $fallback = $this->buildFallbackBrief($normalizedEvents, $window, $activeFilter, $weekOffset);

        if (!$this->isAiEnabled()) {
            return $fallback;
        }

        $aiResult = $this->requestAiBrief($normalizedEvents, $fallback, $window, $activeFilter);
        if ($aiResult === null) {
            return $fallback;
        }

        return $this->mergeWithFallback($aiResult, $fallback);
    }

    /**
     * @return array{start: DateTimeImmutable, end: DateTimeImmutable}
     */
    private function weekWindow(int $weekOffset): array
    {
        $today = new DateTimeImmutable('today');
        $dayOfWeek = (int) $today->format('N');
        $monday = $today->modify('-' . ($dayOfWeek - 1) . ' days');
        if ($weekOffset !== 0) {
            $monday = $monday->modify(($weekOffset > 0 ? '+' : '') . $weekOffset . ' week');
        }

        return [
            'start' => $monday->setTime(0, 0, 0),
            'end' => $monday->modify('+6 days')->setTime(23, 59, 59),
        ];
    }

    private function isAiEnabled(): bool
    {
        $flag = strtolower(trim((string) (getenv('CALENDAR_BRIEF_AI_ENABLED') ?: '1')));
        return !in_array($flag, ['0', 'false', 'no', 'off'], true);
    }

    /**
     * @param array<int, array<string, mixed>> $events
     * @param array{start: DateTimeImmutable, end: DateTimeImmutable} $window
     * @return array<int, array<string, mixed>>
     */
    private function normalizeEvents(array $events, array $window, string $activeFilter): array
    {
        $normalized = [];
        $windowStartTs = $window['start']->getTimestamp();
        $windowEndTs = $window['end']->getTimestamp();

        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }

            $sourceType = (string) ($event['source_type'] ?? 'events_public');
            if ($activeFilter !== 'all' && $sourceType !== $activeFilter) {
                continue;
            }

            $startRaw = (string) ($event['start'] ?? '');
            $startTs = $startRaw !== '' ? strtotime($startRaw) : false;
            if ($startTs === false || $startTs < $windowStartTs || $startTs > $windowEndTs) {
                continue;
            }

            $endRaw = (string) ($event['end'] ?? $startRaw);
            $endTs = $endRaw !== '' ? strtotime($endRaw) : false;
            if ($endTs === false || $endTs < $startTs) {
                $endTs = $startTs + (30 * 60);
                $endRaw = date('Y-m-d H:i:s', $endTs);
            }

            $durationMinutes = max(30, (int) floor(($endTs - $startTs) / 60));
            [$score, $reasons] = $this->scoreEvent([
                'title' => (string) ($event['title'] ?? 'Élément'),
                'source_type' => $sourceType,
                'status' => (string) ($event['status'] ?? ''),
                'start_ts' => $startTs,
                'duration_minutes' => $durationMinutes,
            ]);

            $normalized[] = [
                'id' => (string) ($event['id'] ?? ''),
                'title' => (string) ($event['title'] ?? 'Élément'),
                'source_type' => $sourceType,
                'source_label' => (string) ($event['owner_label'] ?? $this->defaultSourceLabel($sourceType)),
                'start' => date('c', $startTs),
                'end' => date('c', $endTs),
                'status' => (string) ($event['status'] ?? ''),
                'location' => (string) ($event['location'] ?? ''),
                'duration_minutes' => $durationMinutes,
                'priority_score' => $score,
                'priority_reasons' => $reasons,
            ];
        }

        usort($normalized, static function (array $a, array $b): int {
            return strcmp((string) ($a['start'] ?? ''), (string) ($b['start'] ?? ''));
        });

        return $normalized;
    }

    private function defaultSourceLabel(string $sourceType): string
    {
        return match ($sourceType) {
            'rendezvous' => 'Rendez-vous',
            'events_registered' => 'Événement inscrit',
            default => 'Événement public',
        };
    }

    /**
     * @param array<string, mixed> $event
     * @return array{0: int, 1: array<int, string>}
     */
    private function scoreEvent(array $event): array
    {
        $score = 0;
        $reasons = [];

        $startTs = (int) ($event['start_ts'] ?? time());
        $daysUntil = max(0, (int) floor(($startTs - time()) / 86400));

        if ($daysUntil <= 1) {
            $score += 35;
            $reasons[] = 'échéance proche';
        } elseif ($daysUntil <= 3) {
            $score += 25;
            $reasons[] = 'à traiter cette semaine';
        } else {
            $score += 12;
        }

        $sourceType = (string) ($event['source_type'] ?? 'events_public');
        if ($sourceType === 'rendezvous') {
            $score += 24;
            $reasons[] = 'créneau fixe';
        } elseif ($sourceType === 'events_registered') {
            $score += 18;
            $reasons[] = 'engagement confirmé';
        } else {
            $score += 10;
        }

        $status = strtolower((string) ($event['status'] ?? ''));
        if (in_array($status, ['complet', 'confirme'], true)) {
            $score += 8;
            $reasons[] = 'fenêtre de manœuvre réduite';
        }

        $title = strtolower((string) ($event['title'] ?? ''));
        if (preg_match('/examen|deadline|projet|rendu|presentation|présentation|soutenance|entretien/u', $title) === 1) {
            $score += 20;
            $reasons[] = 'fort impact académique';
        }

        $durationMinutes = (int) ($event['duration_minutes'] ?? 30);
        if ($durationMinutes >= 120) {
            $score += 7;
            $reasons[] = 'durée importante';
        }

        return [max(0, min(100, $score)), array_values(array_unique($reasons))];
    }

    /**
     * @param array<int, array<string, mixed>> $events
     * @param array{start: DateTimeImmutable, end: DateTimeImmutable} $window
     * @return array<string, mixed>
     */
    private function buildFallbackBrief(array $events, array $window, string $activeFilter, int $weekOffset): array
    {
        $countsByDay = [];
        $minutesByDay = [];
        $nowTs = time();

        foreach ($events as $event) {
            $startTs = strtotime((string) ($event['start'] ?? '')) ?: $nowTs;
            $dayKey = date('Y-m-d', $startTs);
            $countsByDay[$dayKey] = (int) ($countsByDay[$dayKey] ?? 0) + 1;
            $minutesByDay[$dayKey] = (int) ($minutesByDay[$dayKey] ?? 0) + (int) ($event['duration_minutes'] ?? 30);
        }

        arsort($countsByDay);
        $busiestDayKey = $countsByDay !== [] ? (string) array_key_first($countsByDay) : '';
        $busiestDayLabel = $busiestDayKey !== '' ? date('l', strtotime($busiestDayKey) ?: $nowTs) : 'Aucun jour chargé';
        $busiestDayCount = $busiestDayKey !== '' ? (int) ($countsByDay[$busiestDayKey] ?? 0) : 0;

        $summary = 'Cette semaine: ' . count($events) . ' élément(s) planifié(s)';
        if ($busiestDayKey !== '') {
            $summary .= ', pic de charge le ' . $busiestDayLabel . ' (' . $busiestDayCount . ').';
        } else {
            $summary .= ', agenda léger pour le moment.';
        }

        $prioritiesPool = $events;
        usort($prioritiesPool, static function (array $a, array $b): int {
            return ((int) ($b['priority_score'] ?? 0)) <=> ((int) ($a['priority_score'] ?? 0));
        });

        $rankedPriorities = [];
        foreach (array_slice($prioritiesPool, 0, 3) as $event) {
            $rankedPriorities[] = [
                'label' => (string) ($event['title'] ?? 'Élément'),
                'score' => (int) ($event['priority_score'] ?? 0),
                'reason' => implode(', ', array_slice((array) ($event['priority_reasons'] ?? []), 0, 2)),
                'start' => (string) ($event['start'] ?? ''),
                'source_type' => (string) ($event['source_type'] ?? ''),
            ];
        }

        $risks = [];
        foreach ($countsByDay as $day => $count) {
            $minutes = (int) ($minutesByDay[$day] ?? 0);
            if ($count >= 4 || $minutes >= 360) {
                $risks[] = sprintf(
                    '%s semble surchargé: %d élément(s), %dh%02d au total.',
                    date('l', strtotime($day) ?: $nowTs),
                    $count,
                    (int) floor($minutes / 60),
                    $minutes % 60
                );
            }
        }
        if ($risks === [] && $rankedPriorities !== []) {
            $risks[] = 'Aucun conflit critique détecté, mais gardez un créneau de préparation avant vos priorités.';
        }
        if ($rankedPriorities === []) {
            $risks[] = 'Aucun élément prioritaire trouvé. Profitez-en pour planifier vos travaux à venir.';
        }

        $nextActions = [];
        foreach (array_slice($rankedPriorities, 0, 3) as $priority) {
            $eventStartTs = strtotime((string) ($priority['start'] ?? '')) ?: ($nowTs + 3600);
            $prepTs = $eventStartTs - (24 * 3600);
            if ($prepTs < $nowTs) {
                $prepTs = $nowTs + 3600;
            }

            $nextActions[] = [
                'action' => 'Bloquer un créneau de préparation pour: ' . (string) ($priority['label'] ?? 'priorité'),
                'suggested_time' => date('Y-m-d H:i', $prepTs),
                'impact' => ((int) ($priority['score'] ?? 0)) >= 70 ? 'high' : 'medium',
            ];
        }
        if ($nextActions === []) {
            $nextActions[] = [
                'action' => 'Ajouter au moins un bloc Focus de 45 minutes dans votre semaine.',
                'suggested_time' => $window['start']->format('Y-m-d 18:00'),
                'impact' => 'medium',
            ];
        }

        $dailyBriefs = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $window['start']->modify('+' . $i . ' day');
            $key = $day->format('Y-m-d');
            $count = (int) ($countsByDay[$key] ?? 0);
            $dailyBriefs[] = [
                'day' => $key,
                'brief' => $count > 0
                    ? sprintf('%s: %d élément(s) planifié(s).', ucfirst($day->format('l')), $count)
                    : sprintf('%s: journée légère, utile pour avancer sur les priorités.', ucfirst($day->format('l'))),
            ];
        }

        return [
            'summary' => $summary,
            'ranked_priorities' => $rankedPriorities,
            'risks' => array_slice($risks, 0, 3),
            'next_actions' => array_slice($nextActions, 0, 3),
            'daily_briefs' => $dailyBriefs,
            'generated_at' => date('c'),
            'week_start' => $window['start']->format('Y-m-d'),
            'week_end' => $window['end']->format('Y-m-d'),
            'week_offset' => $weekOffset,
            'active_filter' => $activeFilter,
            'source' => 'fallback',
            'is_fallback' => true,
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $events
     * @param array<string, mixed> $fallback
     * @param array{start: DateTimeImmutable, end: DateTimeImmutable} $window
     */
    private function requestAiBrief(array $events, array $fallback, array $window, string $activeFilter): ?array
    {
        $apiKey = trim((string) (getenv('GROQ_API_KEY') ?: ''));
        if ($apiKey === '') {
            return null;
        }

        $model = trim((string) (getenv('CALENDAR_BRIEF_GROQ_MODEL') ?: getenv('GROQ_MODEL') ?: self::DEFAULT_MODEL));
        $userPrompt = $this->buildPrompt($events, $fallback, $window, $activeFilter);

        $systemInstruction = 'You reply with a single JSON object only (no markdown). '
            . 'Keys required: summary, ranked_priorities, risks, next_actions, daily_briefs. '
            . 'Shape must match the user instructions exactly.';

        $requestBody = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $systemInstruction],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            'temperature' => 0.2,
            'response_format' => ['type' => 'json_object'],
        ];

        [$httpCode, $raw, $transportError] = GroqClient::postChatCompletions($apiKey, $requestBody, 20);
        if ($transportError !== '' || !is_string($raw) || $raw === '' || $httpCode < 200 || $httpCode >= 300) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }

        $choice = $decoded['choices'][0] ?? null;
        if (!is_array($choice) || !isset($choice['message']['content'])) {
            return null;
        }

        $text = (string) $choice['message']['content'];
        if ($text === '') {
            return null;
        }

        $parsed = $this->decodeAssistantJson($text);
        if ($parsed === null) {
            return null;
        }

        return $this->normalizeAiResult($parsed);
    }

    /**
     * @param array<int, array<string, mixed>> $events
     * @param array{start: DateTimeImmutable, end: DateTimeImmutable} $window
     * @param array<string, mixed> $fallback
     */
    private function buildPrompt(array $events, array $fallback, array $window, string $activeFilter): string
    {
        $compactEvents = array_map(static function (array $event): array {
            return [
                'title' => (string) ($event['title'] ?? ''),
                'start' => (string) ($event['start'] ?? ''),
                'source_type' => (string) ($event['source_type'] ?? ''),
                'priority_score' => (int) ($event['priority_score'] ?? 0),
                'priority_reasons' => array_values(array_slice((array) ($event['priority_reasons'] ?? []), 0, 3)),
            ];
        }, array_slice($events, 0, 24));

        $eventsJson = json_encode($compactEvents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $fallbackJson = json_encode([
            'summary' => $fallback['summary'] ?? '',
            'ranked_priorities' => $fallback['ranked_priorities'] ?? [],
            'risks' => $fallback['risks'] ?? [],
            'next_actions' => $fallback['next_actions'] ?? [],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!is_string($eventsJson) || $eventsJson === '') {
            $eventsJson = '[]';
        }
        if (!is_string($fallbackJson) || $fallbackJson === '') {
            $fallbackJson = '{}';
        }

        return 'You are an AI planner for a university front-office dashboard. '
            . 'Return strict JSON only with keys: summary, ranked_priorities, risks, next_actions, daily_briefs. '
            . 'No markdown, no extra keys. '
            . 'summary: one short sentence in French. '
            . 'ranked_priorities: max 3 items, each with label (string), score (0-100 int), reason (string), start (ISO datetime), source_type (string). '
            . 'risks: max 3 short strings. '
            . 'next_actions: max 3 items, each with action (string), suggested_time (YYYY-MM-DD HH:MM), impact (low|medium|high). '
            . 'daily_briefs: exactly 7 items, each with day (YYYY-MM-DD) and brief (string). '
            . 'Use specific concrete wording. Avoid generic advice. '
            . 'Week window: ' . $window['start']->format('Y-m-d') . ' to ' . $window['end']->format('Y-m-d') . '. '
            . 'Active filter: ' . $activeFilter . '. '
            . 'Events: ' . $eventsJson . "\n"
            . 'Deterministic baseline (reference only): ' . $fallbackJson;
    }

    private function decodeAssistantJson(string $text): ?array
    {
        $trimmed = trim($text);
        if ($trimmed === '') {
            return null;
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $trimmed, $matches) === 1) {
            $candidate = json_decode((string) ($matches[0] ?? ''), true);
            if (is_array($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>|null
     */
    private function normalizeAiResult(array $result): ?array
    {
        $summary = trim((string) ($result['summary'] ?? ''));
        if ($summary === '') {
            return null;
        }

        $priorities = [];
        foreach ((array) ($result['ranked_priorities'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            if ($label === '') {
                continue;
            }
            $priorities[] = [
                'label' => $label,
                'score' => max(0, min(100, (int) ($item['score'] ?? 0))),
                'reason' => trim((string) ($item['reason'] ?? '')),
                'start' => trim((string) ($item['start'] ?? '')),
                'source_type' => trim((string) ($item['source_type'] ?? '')),
            ];
            if (count($priorities) >= 3) {
                break;
            }
        }

        $risks = [];
        foreach ((array) ($result['risks'] ?? []) as $risk) {
            $riskText = trim((string) $risk);
            if ($riskText !== '') {
                $risks[] = $riskText;
            }
            if (count($risks) >= 3) {
                break;
            }
        }

        $actions = [];
        foreach ((array) ($result['next_actions'] ?? []) as $action) {
            if (!is_array($action)) {
                continue;
            }
            $label = trim((string) ($action['action'] ?? ''));
            if ($label === '') {
                continue;
            }
            $impact = strtolower(trim((string) ($action['impact'] ?? 'medium')));
            if (!in_array($impact, ['low', 'medium', 'high'], true)) {
                $impact = 'medium';
            }

            $actions[] = [
                'action' => $label,
                'suggested_time' => trim((string) ($action['suggested_time'] ?? '')),
                'impact' => $impact,
            ];
            if (count($actions) >= 3) {
                break;
            }
        }

        $dailyBriefs = [];
        foreach ((array) ($result['daily_briefs'] ?? []) as $item) {
            if (!is_array($item)) {
                continue;
            }
            $day = trim((string) ($item['day'] ?? ''));
            $brief = trim((string) ($item['brief'] ?? ''));
            if ($day === '' || $brief === '') {
                continue;
            }
            $dailyBriefs[] = [
                'day' => $day,
                'brief' => $brief,
            ];
            if (count($dailyBriefs) >= 7) {
                break;
            }
        }

        if (count($dailyBriefs) === 0) {
            return null;
        }

        return [
            'summary' => $summary,
            'ranked_priorities' => $priorities,
            'risks' => $risks,
            'next_actions' => $actions,
            'daily_briefs' => $dailyBriefs,
        ];
    }

    /**
     * @param array<string, mixed> $ai
     * @param array<string, mixed> $fallback
     * @return array<string, mixed>
     */
    private function mergeWithFallback(array $ai, array $fallback): array
    {
        return [
            'summary' => (string) ($ai['summary'] ?? $fallback['summary'] ?? ''),
            'ranked_priorities' => (array) (($ai['ranked_priorities'] ?? []) !== [] ? $ai['ranked_priorities'] : ($fallback['ranked_priorities'] ?? [])),
            'risks' => (array) (($ai['risks'] ?? []) !== [] ? $ai['risks'] : ($fallback['risks'] ?? [])),
            'next_actions' => (array) (($ai['next_actions'] ?? []) !== [] ? $ai['next_actions'] : ($fallback['next_actions'] ?? [])),
            'daily_briefs' => (array) (($ai['daily_briefs'] ?? []) !== [] ? $ai['daily_briefs'] : ($fallback['daily_briefs'] ?? [])),
            'generated_at' => date('c'),
            'week_start' => (string) ($fallback['week_start'] ?? ''),
            'week_end' => (string) ($fallback['week_end'] ?? ''),
            'week_offset' => (int) ($fallback['week_offset'] ?? 0),
            'active_filter' => (string) ($fallback['active_filter'] ?? 'all'),
            'source' => 'ai',
            'is_fallback' => false,
        ];
    }
}
