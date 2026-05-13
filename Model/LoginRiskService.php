<?php

declare(strict_types=1);

class LoginRiskService
{
    private const FAILURE_LOOKBACK_MINUTES = 30;
    private const CHALLENGE_TTL_MINUTES = 10;
    private const CHALLENGE_MAX_ATTEMPTS = 5;

    private Model $model;
    private GroqLoginRiskService $groqRiskService;

    public function __construct()
    {
        $this->model = new Model();
        $this->groqRiskService = new GroqLoginRiskService();
        $this->ensureSchema();
    }

    public static function isStepUpEnabled(): bool
    {
        $flag = strtolower(trim((string) (getenv('LOGIN_RISK_STEP_UP_ENABLED') ?: '0')));

        return !in_array($flag, ['0', 'false', 'no', 'off'], true);
    }

    public static function challengeMaxAttempts(): int
    {
        return self::CHALLENGE_MAX_ATTEMPTS;
    }

    public function fingerprintFromRequest(): string
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        $ua = (string) ($_SERVER['HTTP_USER_AGENT'] ?? '');

        return hash('sha256', $ip . '|' . $ua);
    }

    public function recordFailedAttempt(string $email, string $fingerprintHash): void
    {
        $normalizedEmail = strtolower(trim($email));
        if ($normalizedEmail === '' || $fingerprintHash === '') {
            return;
        }

        $this->model->query(
            'INSERT INTO login_failure_events (email, fingerprint_hash) VALUES (?, ?)',
            [$normalizedEmail, $fingerprintHash]
        );
    }

    public function assessLogin(array $user, string $email, string $fingerprintHash): array
    {
        $userId = (int) ($user['id'] ?? 0);
        $signals = $this->collectSignals($userId, $email, $fingerprintHash);

        [$baseScore, $baseReasons] = $this->computeBaseScore($signals);
        $baseLevel = $this->levelFromScore($baseScore);

        $finalScore = $baseScore;
        $finalLevel = $baseLevel;
        $finalReasons = $baseReasons;

        $aiResult = $this->groqRiskService->assess($signals);
        if ($aiResult !== null) {
            $aiScore = max(0, min(100, (int) ($aiResult['risk_score'] ?? 0)));
            $aiLevel = (string) ($aiResult['risk_level'] ?? 'low');
            $aiReasons = is_array($aiResult['reason_codes'] ?? null) ? $aiResult['reason_codes'] : [];

            $finalScore = max($baseScore, (int) round(($baseScore + $aiScore) / 2));
            if ($this->levelRank($aiLevel) > $this->levelRank($finalLevel)) {
                $finalLevel = $aiLevel;
            } else {
                $finalLevel = $this->levelFromScore($finalScore);
            }

            $finalReasons = array_values(array_unique(array_merge($baseReasons, $aiReasons)));
        }

        return [
            'risk_level' => $finalLevel,
            'risk_score' => $finalScore,
            'reason_codes' => $finalReasons,
            'signals' => $signals,
        ];
    }

    public function createChallenge(int $userId, string $email): array
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $token = bin2hex(random_bytes(32));
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        $this->model->query(
            'INSERT INTO login_risk_challenges
                (user_id, email, otp_hash, request_token, expires_at)
             VALUES
                (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))',
            [$userId, strtolower(trim($email)), $otpHash, $token, self::CHALLENGE_TTL_MINUTES]
        );

        return [
            'otp' => $otp,
            'request_token' => $token,
        ];
    }

    public function findActiveChallenge(string $requestToken): ?array
    {
        $statement = $this->model->query(
            'SELECT id, user_id, email, otp_hash, request_token, expires_at, attempts, used_at
             FROM login_risk_challenges
             WHERE request_token = ?
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1',
            [$requestToken]
        );

        $row = $statement->fetch();

        return $row ?: null;
    }

    public function incrementChallengeAttempts(int $id): int
    {
        $this->model->query(
            'UPDATE login_risk_challenges
             SET attempts = attempts + 1
             WHERE id = ?',
            [$id]
        );

        $statement = $this->model->query(
            'SELECT attempts FROM login_risk_challenges WHERE id = ? LIMIT 1',
            [$id]
        );
        $row = $statement->fetch();

        return (int) ($row['attempts'] ?? 0);
    }

    public function markChallengeUsed(int $id): void
    {
        $this->model->query(
            'UPDATE login_risk_challenges
             SET used_at = NOW()
             WHERE id = ?',
            [$id]
        );
    }

    public function trustDevice(int $userId, string $fingerprintHash): void
    {
        if ($userId <= 0 || $fingerprintHash === '') {
            return;
        }

        $this->model->query(
            'INSERT INTO trusted_devices (user_id, fingerprint_hash, first_seen, last_seen)
             VALUES (?, ?, NOW(), NOW())
             ON DUPLICATE KEY UPDATE last_seen = NOW()',
            [$userId, $fingerprintHash]
        );
    }

    private function collectSignals(int $userId, string $email, string $fingerprintHash): array
    {
        $normalizedEmail = strtolower(trim($email));
        $knownDevice = false;
        if ($userId > 0 && $fingerprintHash !== '') {
            $statement = $this->model->query(
                'SELECT id FROM trusted_devices
                 WHERE user_id = ? AND fingerprint_hash = ?
                 LIMIT 1',
                [$userId, $fingerprintHash]
            );
            $knownDevice = (bool) $statement->fetch();
        }

        $failuresByEmail = 0;
        if ($normalizedEmail !== '') {
            $statement = $this->model->query(
                'SELECT COUNT(*) AS total
                 FROM login_failure_events
                 WHERE email = ?
                   AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)',
                [$normalizedEmail, self::FAILURE_LOOKBACK_MINUTES]
            );
            $row = $statement->fetch();
            $failuresByEmail = (int) ($row['total'] ?? 0);
        }

        $failuresByFingerprint = 0;
        if ($fingerprintHash !== '') {
            $statement = $this->model->query(
                'SELECT COUNT(*) AS total
                 FROM login_failure_events
                 WHERE fingerprint_hash = ?
                   AND attempted_at >= DATE_SUB(NOW(), INTERVAL ? MINUTE)',
                [$fingerprintHash, self::FAILURE_LOOKBACK_MINUTES]
            );
            $row = $statement->fetch();
            $failuresByFingerprint = (int) ($row['total'] ?? 0);
        }

        $hour = (int) date('G');
        $offHours = $hour < 6 || $hour >= 22;

        return [
            'known_device' => $knownDevice,
            'failures_email_30m' => $failuresByEmail,
            'failures_fingerprint_30m' => $failuresByFingerprint,
            'off_hours' => $offHours,
            'login_hour' => $hour,
        ];
    }

    /**
     * @return array{0: int, 1: list<string>}
     */
    private function computeBaseScore(array $signals): array
    {
        $score = 0;
        $reasons = [];

        if (!(bool) ($signals['known_device'] ?? false)) {
            $score += 35;
            $reasons[] = 'NEW_DEVICE';
        }

        $failuresByEmail = (int) ($signals['failures_email_30m'] ?? 0);
        if ($failuresByEmail >= 3) {
            $score += 20;
            $reasons[] = 'FAILED_ATTEMPTS_EMAIL';
        }

        $failuresByFingerprint = (int) ($signals['failures_fingerprint_30m'] ?? 0);
        if ($failuresByFingerprint >= 3) {
            $score += 15;
            $reasons[] = 'FAILED_ATTEMPTS_DEVICE';
        }

        if ((bool) ($signals['off_hours'] ?? false)) {
            $score += 10;
            $reasons[] = 'OFF_HOURS_LOGIN';
        }

        return [max(0, min(100, $score)), $reasons];
    }

    private function levelFromScore(int $score): string
    {
        $highThreshold = max(0, (int) (getenv('LOGIN_RISK_HIGH_THRESHOLD') ?: 70));
        $mediumThreshold = max(0, (int) (getenv('LOGIN_RISK_MEDIUM_THRESHOLD') ?: 35));

        if ($highThreshold < $mediumThreshold) {
            $highThreshold = $mediumThreshold;
        }

        if ($score >= $highThreshold) {
            return 'high';
        }

        if ($score >= $mediumThreshold) {
            return 'medium';
        }

        return 'low';
    }

    private function levelRank(string $level): int
    {
        return match ($level) {
            'high' => 3,
            'medium' => 2,
            default => 1,
        };
    }

    private function ensureSchema(): void
    {
        $this->model->query(
            'CREATE TABLE IF NOT EXISTS login_failure_events (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(255) NOT NULL,
                fingerprint_hash CHAR(64) NOT NULL,
                attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_lfe_email_time (email, attempted_at),
                INDEX idx_lfe_fingerprint_time (fingerprint_hash, attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $this->model->query(
            'CREATE TABLE IF NOT EXISTS trusted_devices (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                fingerprint_hash CHAR(64) NOT NULL,
                first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uq_trusted_device (user_id, fingerprint_hash),
                INDEX idx_td_user (user_id),
                FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );

        $this->model->query(
            'CREATE TABLE IF NOT EXISTS login_risk_challenges (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT NOT NULL,
                email VARCHAR(255) NOT NULL,
                otp_hash VARCHAR(255) NOT NULL,
                request_token CHAR(64) NOT NULL UNIQUE,
                expires_at DATETIME NOT NULL,
                attempts INT NOT NULL DEFAULT 0,
                used_at DATETIME NULL DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_lrc_user (user_id),
                INDEX idx_lrc_request (request_token),
                FOREIGN KEY (user_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
