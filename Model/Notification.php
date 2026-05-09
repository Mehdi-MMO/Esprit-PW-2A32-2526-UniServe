<?php
declare(strict_types=1);

require_once __DIR__ . '/Model.php';

class Notification extends Model
{
    protected string $table = 'notifications';

    public function create(array $data): bool
    {
        $sql = "INSERT INTO {$this->table} (utilisateur_id, message, lien, lu) VALUES (?, ?, ?, 0)";
        $stmt = $this->query($sql, [
            $data['utilisateur_id'],
            $data['message'],
            $data['lien'] ?? null
        ]);
        return $stmt->rowCount() > 0;
    }

    public function getUnreadByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE utilisateur_id = ? AND lu = 0 ORDER BY cree_le DESC";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead(int $notificationId): bool
    {
        $sql = "UPDATE {$this->table} SET lu = 1 WHERE id = ?";
        $stmt = $this->query($sql, [$notificationId]);
        return $stmt->rowCount() > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET lu = 1 WHERE utilisateur_id = ?";
        $stmt = $this->query($sql, [$userId]);
        return $stmt->rowCount() > 0;
    }
}
