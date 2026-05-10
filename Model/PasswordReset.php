<?php

declare(strict_types=1);

class PasswordReset
{
    private const OTP_TTL_MINUTES = 10;

    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
    }

    public function createForUser(int $userId, string $email): array
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $requestToken = bin2hex(random_bytes(32));
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);

        $this->model->query(
            'INSERT INTO password_reset_otps
                (user_id, email, otp_hash, request_token, expires_at)
             VALUES
                (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE))',
            [$userId, strtolower(trim($email)), $otpHash, $requestToken, self::OTP_TTL_MINUTES]
        );

        return [
            'otp' => $otp,
            'request_token' => $requestToken,
        ];
    }

    public function findActiveByRequestToken(string $token): ?array
    {
        $statement = $this->model->query(
            'SELECT id, user_id, email, otp_hash, request_token, reset_token, expires_at, attempts, verified_at, used_at
             FROM password_reset_otps
             WHERE request_token = ?
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1',
            [$token]
        );

        $row = $statement->fetch();
        return $row ?: null;
    }

    public function findActiveByResetToken(string $token): ?array
    {
        $statement = $this->model->query(
            'SELECT id, user_id, email, otp_hash, request_token, reset_token, expires_at, attempts, verified_at, used_at
             FROM password_reset_otps
             WHERE reset_token = ?
               AND used_at IS NULL
               AND expires_at > NOW()
             LIMIT 1',
            [$token]
        );

        $row = $statement->fetch();
        return $row ?: null;
    }

    public function incrementAttempts(int $id): int
    {
        $this->model->query(
            'UPDATE password_reset_otps
             SET attempts = attempts + 1
             WHERE id = ?',
            [$id]
        );

        $statement = $this->model->query(
            'SELECT attempts FROM password_reset_otps WHERE id = ? LIMIT 1',
            [$id]
        );
        $row = $statement->fetch();
        return (int) ($row['attempts'] ?? 0);
    }

    public function markVerified(int $id): string
    {
        $resetToken = bin2hex(random_bytes(32));
        $this->model->query(
            'UPDATE password_reset_otps
             SET verified_at = NOW(), reset_token = ?
             WHERE id = ?',
            [$resetToken, $id]
        );

        return $resetToken;
    }

    public function markUsed(int $id): void
    {
        $this->model->query(
            'UPDATE password_reset_otps
             SET used_at = NOW()
             WHERE id = ?',
            [$id]
        );
    }

    public function lastRequestSecondsAgo(string $email): ?int
    {
        $statement = $this->model->query(
            'SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS seconds_ago
             FROM password_reset_otps
             WHERE email = ?
             ORDER BY id DESC
             LIMIT 1',
            [strtolower(trim($email))]
        );

        $row = $statement->fetch();
        if (!$row || !isset($row['seconds_ago'])) {
            return null;
        }

        return max(0, (int) $row['seconds_ago']);
    }

    public function deleteExpiredFor(int $userId): void
    {
        $this->model->query(
            'DELETE FROM password_reset_otps
             WHERE user_id = ?
               AND (used_at IS NOT NULL OR expires_at <= NOW())',
            [$userId]
        );
    }
}
