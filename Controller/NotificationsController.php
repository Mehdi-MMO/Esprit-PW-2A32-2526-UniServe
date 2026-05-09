<?php
declare(strict_types=1);

require_once __DIR__ . '/../Model/Notification.php';

class NotificationsController extends Controller
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function getUnread(): void
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        if ($userId === 0) {
            echo json_encode([]);
            return;
        }

        $notifications = $this->notificationModel->getUnreadByUser((int)$userId);
        echo json_encode($notifications);
    }

    public function markAsRead(string $id): void
    {
        $notif = $this->notificationModel->query("SELECT * FROM notifications WHERE id = ?", [$id])->fetch(PDO::FETCH_ASSOC);
        $this->notificationModel->markAsRead((int)$id);
        
        if ($notif && !empty($notif['lien'])) {
            $this->redirect($notif['lien']);
        } else {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }

    public function markAllAsRead(): void
    {
        $userId = $_SESSION['user']['id'] ?? 0;
        if ($userId !== 0) {
            $this->notificationModel->markAllAsRead((int)$userId);
        }
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }
}
