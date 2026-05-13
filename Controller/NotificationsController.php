<?php

declare(strict_types=1);

require_once __DIR__ . '/../Model/NotificationModel.php';

class NotificationsController extends Controller
{
    private function isPost(): bool
    {
        return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
    }

    public function landing(): void
    {
        $this->index();
    }

    /**
     * JSON unread list (THEMODULES DEMANDE parity for future bell UI).
     */
    public function getUnread(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!$this->isLoggedIn()) {
            echo json_encode([]);
            return;
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            echo json_encode([]);
            return;
        }

        $rows = (new NotificationModel())->getUnreadByUser($userId);
        echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    }

    public function index(): void
    {
        $this->requireLogin();

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        $notifModel = new NotificationModel();
        $unread = $notifModel->getUnreadByUser($userId);

        $statement = (new Model())->query(
            'SELECT id, message, lien, lu, cree_le FROM notifications WHERE utilisateur_id = ? ORDER BY cree_le DESC LIMIT 100',
            [$userId]
        );
        /** @var list<array<string, mixed>> $all */
        $all = $statement->fetchAll();

        $this->render('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $all,
            'unread' => $unread,
            'unread_count' => $notifModel->countUnread($userId),
        ]);
    }

    public function markRead(int|string $id): void
    {
        $this->requireLogin();
        if (!$this->isPost()) {
            $this->redirect('/notifications');
            return;
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        (new NotificationModel())->markAsRead((int) $id, $userId);
        $this->redirect('/notifications');
    }

    /**
     * AJAX version of markRead. Returns JSON with the refreshed unread count
     * so the navbar bell badge can be synced without a full page reload.
     */
    public function markReadJson(int|string $id): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');

        if (!$this->isLoggedIn() || !$this->isPost()) {
            http_response_code($this->isLoggedIn() ? 405 : 401);
            echo json_encode(['ok' => false]);
            return;
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        if ($userId <= 0) {
            http_response_code(401);
            echo json_encode(['ok' => false]);
            return;
        }

        $notif = new NotificationModel();
        $notif->markAsRead((int) $id, $userId);

        echo json_encode([
            'ok' => true,
            'unread_count' => $notif->countUnread($userId),
        ]);
    }

    public function markAllRead(): void
    {
        $this->requireLogin();
        if (!$this->isPost()) {
            $this->redirect('/notifications');
            return;
        }

        $userId = (int) ($_SESSION['user']['id'] ?? 0);
        (new NotificationModel())->markAllRead($userId);
        $this->redirect('/notifications');
    }
}
