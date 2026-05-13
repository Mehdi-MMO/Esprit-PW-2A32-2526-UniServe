<?php

declare(strict_types=1);

/**
 * In-app notifications (THEMODULES/DEMANDE_MODULE parity).
 */
class NotificationModel
{
    private Model $model;

    public function __construct()
    {
        $this->model = new Model();
        $this->ensureTable();
    }

    private function ensureTable(): void
    {
        $this->model->query(
            'CREATE TABLE IF NOT EXISTS notifications (
                id BIGINT AUTO_INCREMENT PRIMARY KEY,
                utilisateur_id BIGINT NOT NULL,
                message VARCHAR(512) NOT NULL,
                lien VARCHAR(512) NULL,
                lu TINYINT(1) NOT NULL DEFAULT 0,
                cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_notif_user_lu (utilisateur_id, lu)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public function create(int $userId, string $message, ?string $lien = null): void
    {
        if ($userId <= 0 || trim($message) === '') {
            return;
        }

        $this->model->query(
            'INSERT INTO notifications (utilisateur_id, message, lien, lu) VALUES (?, ?, ?, 0)',
            [$userId, $message, $lien]
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getUnreadByUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $statement = $this->model->query(
            'SELECT id, message, lien, cree_le FROM notifications WHERE utilisateur_id = ? AND lu = 0 ORDER BY cree_le DESC LIMIT 50',
            [$userId]
        );

        return $statement->fetchAll();
    }

    public function countUnread(int $userId): int
    {
        if ($userId <= 0) {
            return 0;
        }

        $statement = $this->model->query(
            'SELECT COUNT(*) AS c FROM notifications WHERE utilisateur_id = ? AND lu = 0',
            [$userId]
        );

        return (int) ($statement->fetch()['c'] ?? 0);
    }

    public function markAsRead(int $notificationId, int $userId): bool
    {
        $this->model->query(
            'UPDATE notifications SET lu = 1 WHERE id = ? AND utilisateur_id = ?',
            [$notificationId, $userId]
        );

        return true;
    }

    public function markAllRead(int $userId): void
    {
        if ($userId <= 0) {
            return;
        }

        $this->model->query(
            'UPDATE notifications SET lu = 1 WHERE utilisateur_id = ? AND lu = 0',
            [$userId]
        );
    }
}
