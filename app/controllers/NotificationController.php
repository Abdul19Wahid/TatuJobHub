<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/app/models/Notification.php';

class NotificationController extends Controller
{
    private Notification $notifications;

    public function __construct()
    {
        $this->notifications = new Notification();
    }

    // ── AJAX — called every 15 seconds by the navbar poller ──────────────────

    /**
     * GET /notifications/poll
     * Returns unread count + recent items as JSON.
     */
    public function poll(): void
    {
        $this->requireLogin();
        $userId = Session::id();

        $unread  = $this->notifications->countUnread($userId);
        $recent  = $this->notifications->getRecent($userId, 10);

        // Format timestamps for display
        foreach ($recent as &$n) {
            $n['time_ago'] = $this->timeAgo($n['created_at']);
        }

        $this->json([
            'unread' => $unread,
            'items'  => $recent,
        ]);
    }

    // ── Full notifications page ───────────────────────────────────────────────

    /**
     * GET /notifications
     */
    public function index(): void
    {
        $this->requireLogin();
        $userId = Session::id();
        $page   = max(1, (int) ($_GET['page'] ?? 1));

        $data = $this->notifications->getPaginated($userId, $page);

        // Mark all as read when the page is opened
        $this->notifications->markAllRead($userId);

        $this->view('notifications/index', [
            'title'        => 'Notifications',
            'notifications' => $data['items'],
            'pagination'   => $data,
        ]);
    }

    // ── AJAX — mark a single notification read ────────────────────────────────

    /**
     * POST /notifications/mark-read
     * Body: { id: int }
     */
    public function markRead(): void
    {
        $this->requireLogin();
        $userId = Session::id();
        $id     = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->notifications->markRead($id, $userId);
        }

        $this->json(['success' => true]);
    }

    /**
     * POST /notifications/mark-all-read
     */
    public function notifyMarkAllRead(): void
    {
        $this->requireLogin();
        $this->notifications->markAllRead(Session::id());
        $this->json(['success' => true]);
    }

    // ── AJAX — delete ─────────────────────────────────────────────────────────

    /**
     * POST /notifications/delete
     * Body: { id: int }
     */
    public function notifyDelete(): void
    {
        $this->requireLogin();
        $userId = Session::id();
        $id     = (int) ($_POST['id'] ?? 0);

        if ($id > 0) {
            $this->notifications->deleteOne($id, $userId);
        }

        $this->json(['success' => true]);
    }

    /**
     * POST /notifications/clear-read
     */
    public function clearReadNotifications(): void
    {
        $this->requireLogin();
        $this->notifications->deleteAllRead(Session::id());
        $this->json(['success' => true]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);

        if ($diff < 60)     return 'just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';

        return date('d M Y', strtotime($datetime));
    }


}
