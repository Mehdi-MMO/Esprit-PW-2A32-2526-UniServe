<?php

declare(strict_types=1);

/**
 * Per-user file cache for the AI weekly calendar brief.
 *
 * Goals:
 *  - Avoid hammering the Groq API: serve the same brief from disk while the
 *    underlying agenda hasn't changed.
 *  - Per-user isolation: each user has their own cache entries.
 *  - Self-invalidating: each entry stores a fingerprint of the events it was
 *    generated from. A mismatch on lookup discards the entry.
 *  - Safety net: an absolute TTL (default 24h) discards stale entries even when
 *    the fingerprint is unchanged.
 *
 * Cache files live under `Model/storage/cache/calendar-brief/` (gitignored), next to
 * other PHP app code — not under View/ and not in a root `var/` tree. Override with
 * env `CALENDAR_BRIEF_CACHE_DIR`.
 */
class CalendarBriefCache
{
    private const DEFAULT_TTL_SECONDS = 86400;

    private string $cacheDir;
    private int $ttlSeconds;

    public function __construct(?string $cacheDir = null, ?int $ttlSeconds = null)
    {
        $this->cacheDir = $cacheDir ?? self::defaultCacheDirFromEnv();
        $this->ttlSeconds = $ttlSeconds ?? self::resolveTtlFromEnv();
        $this->ensureCacheDir();
    }

    private static function defaultCacheDirFromEnv(): string
    {
        $fromEnv = trim((string) (getenv('CALENDAR_BRIEF_CACHE_DIR') ?: ''));
        if ($fromEnv !== '') {
            return rtrim($fromEnv, '/\\');
        }

        return __DIR__ . '/storage/cache/calendar-brief';
    }

    /**
     * Build a deterministic fingerprint over the inputs that influence the brief.
     * Any change in events for the window (or in the requested filter / offset)
     * produces a different fingerprint and therefore a cache miss.
     *
     * @param array<int, array<string, mixed>> $events
     */
    public function fingerprint(array $events, int $weekOffset, string $filter, string $snapshotDigest = ''): string
    {
        $signature = [];
        foreach ($events as $event) {
            if (!is_array($event)) {
                continue;
            }
            $signature[] = [
                'i' => (string) ($event['id'] ?? ''),
                's' => (string) ($event['source_type'] ?? ''),
                't' => (string) ($event['title'] ?? ''),
                'b' => (string) ($event['start'] ?? ''),
                'e' => (string) ($event['end'] ?? ''),
                'st' => (string) ($event['status'] ?? ''),
                'o' => (string) ($event['owner_label'] ?? ''),
                'l' => (string) ($event['location'] ?? ''),
            ];
        }

        usort($signature, static function (array $a, array $b): int {
            return strcmp(
                $a['i'] . '|' . $a['b'] . '|' . $a['s'],
                $b['i'] . '|' . $b['b'] . '|' . $b['s']
            );
        });

        $payload = json_encode(
            ['wo' => $weekOffset, 'f' => $filter, 'sig' => $signature, 'snap' => $snapshotDigest],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($payload === false) {
            $payload = $weekOffset . '|' . $filter . '|' . count($signature);
        }

        return substr(hash('sha256', $payload), 0, 32);
    }

    /**
     * @return array<string, mixed>|null cached brief, or null on miss / mismatch / expiry.
     */
    public function get(int $userId, int $weekOffset, string $filter, string $fingerprint): ?array
    {
        if ($userId <= 0) {
            return null;
        }

        $path = $this->entryPath($userId, $weekOffset, $filter);
        if (!is_file($path)) {
            return null;
        }

        $contents = @file_get_contents($path);
        if ($contents === false || $contents === '') {
            return null;
        }

        $data = json_decode($contents, true);
        if (!is_array($data)) {
            return null;
        }

        $storedFingerprint = (string) ($data['fingerprint'] ?? '');
        if ($storedFingerprint !== $fingerprint) {
            return null;
        }

        $cachedAt = (int) ($data['cached_at'] ?? 0);
        if ($cachedAt > 0 && (time() - $cachedAt) > $this->ttlSeconds) {
            return null;
        }

        $brief = $data['brief'] ?? null;
        return is_array($brief) ? $brief : null;
    }

    /**
     * @param array<string, mixed> $brief
     */
    public function put(int $userId, int $weekOffset, string $filter, string $fingerprint, array $brief): void
    {
        if ($userId <= 0) {
            return;
        }
        if (!$this->ensureCacheDir()) {
            return;
        }

        $payload = [
            'fingerprint' => $fingerprint,
            'cached_at' => time(),
            'week_offset' => $weekOffset,
            'filter' => $filter,
            'brief' => $brief,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return;
        }

        @file_put_contents($this->entryPath($userId, $weekOffset, $filter), $json, LOCK_EX);
    }

    /**
     * Invalidate cache entries. Pass only $userId to drop everything for a user;
     * pass week offset and filter to remove a single entry.
     */
    public function forget(int $userId, ?int $weekOffset = null, ?string $filter = null): void
    {
        if ($userId <= 0) {
            return;
        }

        if ($weekOffset !== null && $filter !== null) {
            @unlink($this->entryPath($userId, $weekOffset, $filter));
            return;
        }

        $pattern = $this->cacheDir . '/user-' . $userId . '-*.json';
        $matches = glob($pattern);
        if (!is_array($matches)) {
            return;
        }
        foreach ($matches as $path) {
            @unlink($path);
        }
    }

    private function entryPath(int $userId, int $weekOffset, string $filter): string
    {
        $safeFilter = preg_replace('/[^a-z0-9_]/i', '', $filter);
        if ($safeFilter === '' || $safeFilter === null) {
            $safeFilter = 'all';
        }
        $offsetSlug = ($weekOffset >= 0 ? 'p' : 'n') . abs($weekOffset);
        return $this->cacheDir . '/user-' . $userId . '-wo' . $offsetSlug . '-' . $safeFilter . '.json';
    }

    private function ensureCacheDir(): bool
    {
        if (is_dir($this->cacheDir)) {
            return true;
        }
        return @mkdir($this->cacheDir, 0775, true) || is_dir($this->cacheDir);
    }

    private static function resolveTtlFromEnv(): int
    {
        $raw = getenv('CALENDAR_BRIEF_CACHE_TTL');
        if ($raw === false || $raw === '') {
            return self::DEFAULT_TTL_SECONDS;
        }
        $value = (int) trim((string) $raw);
        if ($value <= 0) {
            return self::DEFAULT_TTL_SECONDS;
        }
        return $value;
    }
}
