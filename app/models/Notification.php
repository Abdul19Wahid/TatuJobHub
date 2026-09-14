<?php
require_once ROOT_PATH . '/core/Model.php';

class Notification extends Model
{
    protected string $table = 'notifications';

    // ── Create ────────────────────────────────────────────────────────────────

    /**
     * Insert a new notification for a user.
     *
     * @param int    $userId
     * @param string $type    One of the TYPE_* constants below
     * @param string $title   Short heading shown in the dropdown
     * @param string $message Longer description
     * @param string|null $link URL to open when the notification is clicked
     */
    public function createNotification(
        int    $userId,
        string $type,
        string $title,
        string $message,
        ?string $link = null
    ): int {
        return $this->db->insert($this->table, [
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Read ──────────────────────────────────────────────────────────────────

    /** Count unread notifications for the bell badge. */
    public function countUnread(int $userId): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    /** Count unread notifications by type. */
    public function countUnreadByType(int $userId, string $type): int
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE user_id = ? AND type = ? AND is_read = 0",
            [$userId, $type]
        );
        return (int) ($row['cnt'] ?? 0);
    }

    /** Latest N notifications for the dropdown panel. */
    public function getRecent(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->table}
              WHERE user_id = ?
              ORDER BY created_at DESC
              LIMIT ?",
            [$userId, $limit]
        );
    }

    /** Full paginated list for the /notifications page. */
    public function getPaginated(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;

        $rows = $this->db->fetchAll(
            "SELECT * FROM {$this->table}
              WHERE user_id = ?
              ORDER BY created_at DESC
              LIMIT ? OFFSET ?",
            [$userId, $perPage, $offset]
        );

        $total = (int) ($this->db->fetchOne(
            "SELECT COUNT(*) AS cnt FROM {$this->table} WHERE user_id = ?",
            [$userId]
        )['cnt'] ?? 0);

        return [
            'items'       => $rows,
            'total'       => $total,
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => (int) ceil($total / $perPage),
        ];
    }

    // ── Mark read ─────────────────────────────────────────────────────────────

    public function markRead(int $notificationId, int $userId): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }

    public function markAllRead(int $userId): void
    {
        $this->db->query(
            "UPDATE {$this->table} SET is_read = 1 WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function deleteOne(int $notificationId, int $userId): void
    {
        $this->db->query(
            "DELETE FROM {$this->table} WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }

    public function deleteAllRead(int $userId): void
    {
        $this->db->query(
            "DELETE FROM {$this->table} WHERE user_id = ? AND is_read = 1",
            [$userId]
        );
    }

    // ── Notification type constants ───────────────────────────────────────────

    const TYPE_APPLICATION_RECEIVED  = 'application_received';   // employer
    const TYPE_APPLICATION_STATUS    = 'application_status';     // seeker
    const TYPE_INTERVIEW_SCHEDULED   = 'interview_scheduled';    // seeker
    const TYPE_INTERVIEW_REMINDER    = 'interview_reminder';     // seeker
    const TYPE_MESSAGE_RECEIVED      = 'message_received';       // any
    const TYPE_PROFILE_INCOMPLETE    = 'profile_incomplete';     // seeker
    const TYPE_JOB_EXPIRED           = 'job_expired';            // employer
    const TYPE_COMPANY_VERIFIED      = 'company_verified';       // employer
    const TYPE_GENERAL               = 'general';                // admin broadcast

    // ── Convenience factory methods ───────────────────────────────────────────

    /** Notify an employer that someone applied to their job. */
    public function notifyApplicationReceived(
        int $employerId, string $seekerName, string $jobTitle, string $applicationLink
    ): void {
        $this->createNotification(
            $employerId,
            self::TYPE_APPLICATION_RECEIVED,
            'New Application Received',
            "{$seekerName} applied for your \"{$jobTitle}\" position.",
            $applicationLink
        );
    }

    /** Notify a seeker that their application status changed. */
    public function notifyStatusChanged(
        int $seekerId, string $jobTitle, string $newStatus, string $applicationLink
    ): void {
        $label = ucfirst(str_replace('_', ' ', $newStatus));
        $this->createNotification(
            $seekerId,
            self::TYPE_APPLICATION_STATUS,
            'Application Status Updated',
            "Your application for \"{$jobTitle}\" has been marked as {$label}.",
            $applicationLink
        );
    }

    /** Notify a seeker that an interview has been scheduled. */
    public function notifyInterviewScheduled(
        int $seekerId, string $jobTitle, string $date, string $applicationLink
    ): void {
        $this->createNotification(
            $seekerId,
            self::TYPE_INTERVIEW_SCHEDULED,
            'Interview Scheduled',
            "An interview for \"{$jobTitle}\" has been scheduled on {$date}.",
            $applicationLink
        );
    }

    /** Notify an employer that their company has been verified by admin. */
    public function notifyCompanyVerified(int $employerId, string $companyName): void
    {
        $this->createNotification(
            $employerId,
            self::TYPE_COMPANY_VERIFIED,
            'Company Verified',
            "Congratulations! Your company \"{$companyName}\" has been verified.",
            url('/employer/company')
        );
    }
}
