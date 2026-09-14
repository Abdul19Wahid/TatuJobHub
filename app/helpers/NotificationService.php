<?php
/**
 * NotificationService.php
 * Central service for creating in-app notifications AND sending emails.
 * Use this everywhere instead of calling $db->insert('notifications') directly.
 *
 * Usage:
 *   NotificationService::applicationReceived($jobTitle, $seekerName, $employerUserId, $jobId);
 *   NotificationService::statusChanged($newStatus, $jobTitle, $seekerUserId);
 *   NotificationService::interviewScheduled($appId, $seekerUserId, $scheduledAt, $type, $link);
 *   NotificationService::companyApproved($employerUserId, $companyName);
 *   NotificationService::companyRejected($employerUserId, $companyName, $reason);
 */

require_once ROOT_PATH . '/app/helpers/Mailer.php';
require_once ROOT_PATH . '/app/helpers/Sms.php';

class NotificationService
{
    private static Database $db;

    private static function db(): Database
    {
        if (!isset(self::$db)) self::$db = Database::getInstance();
        return self::$db;
    }

    // ── Store in-app notification ─────────────────────────────────────────
    private static function create(
        int    $userId,
        string $type,
        string $title,
        string $message,
        string $link = ''
    ): void {
        self::db()->insert('notifications', [
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'link'       => $link,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Get user email ─────────────────────────────────────────────────────
    private static function getUserEmail(int $userId): ?array
    {
        return self::db()->fetchOne(
            "SELECT full_name, email FROM users WHERE id = ? AND is_active = 1",
            [$userId]
        ) ?: null;
    }

    // ── Send notification (SMS first, email fallback) ───────────────────────
    private static function notify(array $user, string $subject, string $smsMsg): void
    {
        if (!empty($user['phone_number'])) {
            Sms::send($user['phone_number'], $smsMsg);
        } else {
            Mailer::send($user['email'], $user['full_name'], $subject,
                '<p>' . nl2br(htmlspecialchars($smsMsg)) . '</p>');
        }
    }

    // ── Check if user wants email notifications ────────────────────────────
    private static function wantsEmail(int $userId, string $type): bool
    {
        // Default: all notifications on. Can be extended with a preferences table.
        return true;
    }

    // ==========================================================================
    // APPLICATION EVENTS
    // ==========================================================================

    /** Notify employer when a new application arrives */
    public static function applicationReceived(
        string $jobTitle,
        string $seekerName,
        int    $employerUserId,
        int    $jobId
    ): void {
        $title   = 'New application received';
        $message = "{$seekerName} applied for \"{$jobTitle}\".";
        $link    = "/employer/jobs/{$jobId}/applicants";

        self::create($employerUserId, 'application_received', $title, $message, $link);

        if (self::wantsEmail($employerUserId, 'application_received')) {
            $user = self::getUserEmail($employerUserId);
            if ($user) {
                $body = "
                    <p>Hi <strong>" . e($user['full_name']) . "</strong>,</p>
                    <p><strong>" . e($seekerName) . "</strong> has applied for your job listing
                       <strong>\"" . e($jobTitle) . "\"</strong>.</p>
                    <p style='text-align:center;margin:28px 0;'>
                        <a href='" . BASE_URL . $link . "'
                           style='background:#1A56DB;color:#fff;padding:12px 28px;border-radius:8px;
                                  text-decoration:none;font-weight:700;'>
                            View Application
                        </a>
                    </p>
                ";
                $smsMsg = APP_NAME . ": Hi {$user['full_name']}, {$seekerName} applied for \"{$jobTitle}\". View: " . BASE_URL . $link;
                self::notify($user, "New Application — {$jobTitle}", $smsMsg);
            }
        }
    }

    /** Notify seeker when their application status changes */
    public static function statusChanged(
        string  $newStatus,
        string  $jobTitle,
        string  $companyName,
        int     $seekerUserId,
        ?string $note = null
    ): void {
        $labels = [
            'under_review'         => 'is now under review',
            'shortlisted'          => 'has been shortlisted! 🎉',
            'interview_scheduled'  => 'has an interview scheduled',
            'offered'              => 'has received an offer! 🎊',
            'hired'                => 'resulted in a hire! 🏆',
            'rejected'             => 'was not selected this time',
            'withdrawn'            => 'has been withdrawn',
        ];

        $phrase  = $labels[$newStatus] ?? "status changed to {$newStatus}";
        $title   = 'Application update — ' . $jobTitle;
        $message = "Your application for \"{$jobTitle}\" at {$companyName} {$phrase}."
                 . ($note ? " Message: {$note}" : '');
        $link    = '/seeker/applications';

        self::create($seekerUserId, 'application_status_changed', $title, $message, $link);

        if (self::wantsEmail($seekerUserId, 'status_changed')) {
            $user = self::getUserEmail($seekerUserId);
            if ($user) {
                $statusColors = [
                    'shortlisted'         => '#1A56DB',
                    'offered'             => '#057A55',
                    'hired'               => '#057A55',
                    'interview_scheduled' => '#D97706',
                    'rejected'            => '#DC2626',
                ];
                $color = $statusColors[$newStatus] ?? '#374151';

                $body = "
                    <p>Hi <strong>" . e($user['full_name']) . "</strong>,</p>
                    <p>There is an update on your application for
                       <strong>\"" . e($jobTitle) . "\"</strong> at
                       <strong>" . e($companyName) . "</strong>.</p>
                    <div style='background:#f8fafc;border-left:4px solid {$color};
                                border-radius:8px;padding:16px;margin:20px 0;'>
                        <p style='margin:0;font-weight:700;color:{$color};font-size:15px;'>
                            " . ucfirst(str_replace('_', ' ', $newStatus)) . "
                        </p>
                        <p style='margin:6px 0 0;color:#374151;'>" . e($message) . "</p>
                    </div>
                    <p style='text-align:center;margin:28px 0;'>
                        <a href='" . BASE_URL . $link . "'
                           style='background:#1A56DB;color:#fff;padding:12px 28px;
                                  border-radius:8px;text-decoration:none;font-weight:700;'>
                            View My Applications
                        </a>
                    </p>
                ";
                $smsMsg = APP_NAME . ": Hi {$user['full_name']}, " . $message . " View: " . BASE_URL . $link;
                self::notify($user, "Application Update — {$jobTitle}", $smsMsg);
            }
        }
    }

    // ==========================================================================
    // INTERVIEW EVENTS
    // ==========================================================================

    /** Notify seeker when an interview is scheduled */
    public static function interviewScheduled(
        int    $seekerUserId,
        string $jobTitle,
        string $companyName,
        string $scheduledAt,
        string $interviewType,
        string $meetingLink = ''
    ): void {
        $dateStr = date('l, F j Y \a\t H:i', strtotime($scheduledAt));
        $title   = 'Interview Scheduled — ' . $jobTitle;
        $message = "Your interview for \"{$jobTitle}\" at {$companyName} is scheduled for {$dateStr}.";
        $link    = '/seeker/applications';

        self::create($seekerUserId, 'interview_scheduled', $title, $message, $link);

        $user = self::getUserEmail($seekerUserId);
        if ($user) {
            $typeLabel = ucfirst(str_replace('_', ' ', $interviewType));
            $meetingHtml = $meetingLink
                ? "<p><strong>Meeting Link:</strong>
                   <a href='" . e($meetingLink) . "' style='color:#1A56DB;'>
                   Click to join</a></p>"
                : '';

            $body = "
                <p>Hi <strong>" . e($user['full_name']) . "</strong>,</p>
                <p>Great news! An interview has been scheduled for your application at
                   <strong>" . e($companyName) . "</strong>.</p>
                <div style='background:#f0fdf4;border:1px solid #86efac;border-radius:10px;
                            padding:20px;margin:20px 0;'>
                    <p style='margin:0 0 8px;'><strong>Job:</strong> " . e($jobTitle) . "</p>
                    <p style='margin:0 0 8px;'><strong>Company:</strong> " . e($companyName) . "</p>
                    <p style='margin:0 0 8px;'><strong>Date & Time:</strong> {$dateStr}</p>
                    <p style='margin:0 0 8px;'><strong>Type:</strong> {$typeLabel}</p>
                    {$meetingHtml}
                </div>
                <p style='text-align:center;margin:28px 0;'>
                    <a href='" . BASE_URL . $link . "'
                       style='background:#1A56DB;color:#fff;padding:12px 28px;
                              border-radius:8px;text-decoration:none;font-weight:700;'>
                        View Application
                    </a>
                </p>
                <p style='color:#6b7280;font-size:13px;'>
                    Make sure to prepare well. Good luck! 🍀
                </p>
            ";
            $smsMsg = APP_NAME . ": Hi {$user['full_name']}, " . $message . ($meetingLink ? " Join: {$meetingLink}" : "");
            self::notify($user, "Interview Scheduled — {$jobTitle}", $smsMsg);
        }
    }

    // ==========================================================================
    // COMPANY VERIFICATION EVENTS
    // ==========================================================================

    /** Notify employer when company is approved */
    public static function companyApproved(int $employerUserId, string $companyName): void
    {
        $title   = '✓ Company Verified!';
        $message = "Your company \"{$companyName}\" has been verified. You can now post jobs.";
        $link    = '/employer/dashboard';

        self::create($employerUserId, 'company_approved', $title, $message, $link);

        $user = self::getUserEmail($employerUserId);
        if ($user) {
            $body = "
                <p>Hi <strong>" . e($user['full_name']) . "</strong>,</p>
                <p>🎉 Great news! Your company <strong>\"" . e($companyName) . "\"</strong>
                   has been verified on " . APP_NAME . ".</p>
                <p>You can now start posting job listings and reach thousands of candidates.</p>
                <p style='text-align:center;margin:28px 0;'>
                    <a href='" . BASE_URL . "/employer/jobs/create'
                       style='background:#1A56DB;color:#fff;padding:12px 28px;
                              border-radius:8px;text-decoration:none;font-weight:700;'>
                        Post Your First Job
                    </a>
                </p>
            ";
            $smsMsg = APP_NAME . ": Hi {$user['full_name']}, your company \"{$companyName}\" is now verified! Post jobs: " . BASE_URL . "/employer/jobs/create";
            self::notify($user, "Company Verified — " . APP_NAME, $smsMsg);
        }
    }

    /** Notify employer when company is rejected */
    public static function companyRejected(
        int    $employerUserId,
        string $companyName,
        string $reason = ''
    ): void {
        $title   = 'Verification Not Approved';
        $message = "Your verification for \"{$companyName}\" was not approved."
                 . ($reason ? " Reason: {$reason}" : '');
        $link    = '/employer/profile';

        self::create($employerUserId, 'company_rejected', $title, $message, $link);

        $user = self::getUserEmail($employerUserId);
        if ($user) {
            $body = "
                <p>Hi <strong>" . e($user['full_name']) . "</strong>,</p>
                <p>Unfortunately, the verification request for
                   <strong>\"" . e($companyName) . "\"</strong>
                   was not approved at this time.</p>
                " . ($reason ? "<div style='background:#fef2f2;border-left:4px solid #ef4444;
                    border-radius:6px;padding:14px;margin:16px 0;'>
                    <strong>Reason:</strong> " . e($reason) . "</div>" : "") . "
                <p>Please update your documents and resubmit for verification.</p>
                <p style='text-align:center;margin:28px 0;'>
                    <a href='" . BASE_URL . "/employer/profile'
                       style='background:#1A56DB;color:#fff;padding:12px 28px;
                              border-radius:8px;text-decoration:none;font-weight:700;'>
                        Update & Resubmit
                    </a>
                </p>
            ";
            $smsMsg = APP_NAME . ": Hi {$user['full_name']}, your verification for \"{$companyName}\" was not approved." . ($reason ? " Reason: {$reason}." : '') . " Update & resubmit: " . BASE_URL . "/employer/profile";
            self::notify($user, "Verification Update — " . APP_NAME, $smsMsg);
        }
    }

    // ==========================================================================
    // JOB ALERT NOTIFICATIONS
    // ==========================================================================

    /** Send job alert email when new matching jobs are posted */
    public static function sendJobAlert(
        int    $userId,
        string $alertName,
        array  $matchingJobs
    ): void {
        if (empty($matchingJobs)) return;

        $user = self::getUserEmail($userId);
        if (!$user) return;

        $count    = count($matchingJobs);
        $jobsHtml = '';
        foreach (array_slice($matchingJobs, 0, 5) as $job) {
            $jobsHtml .= "
                <div style='border:1px solid #e5e7eb;border-radius:8px;padding:16px;margin-bottom:12px;'>
                    <p style='margin:0 0 4px;font-weight:700;font-size:15px;'>
                        <a href='" . BASE_URL . "/jobs/" . e($job['slug']) . "'
                           style='color:#1A56DB;text-decoration:none;'>" . e($job['title']) . "</a>
                    </p>
                    <p style='margin:0;color:#6b7280;font-size:13px;'>
                        " . e($job['company_name']) . " •
                        " . ($job['is_remote'] ? 'Remote' : e($job['location_city'] ?? '')) . " •
                        " . ucfirst(str_replace('_', ' ', $job['job_type'])) . "
                    </p>
                </div>
            ";
        }

        $body = "
            <p>Hi <strong>" . e($user['full_name']) . "</strong>,</p>
            <p>We found <strong>{$count} new job" . ($count > 1 ? 's' : '') . "</strong>
               matching your alert <strong>\"" . e($alertName) . "\"</strong>:</p>
            {$jobsHtml}
            " . ($count > 5 ? "<p><a href='" . BASE_URL . "/jobs' style='color:#1A56DB;'>
                View all {$count} matching jobs →</a></p>" : "") . "
            <p style='text-align:center;margin:28px 0;'>
                <a href='" . BASE_URL . "/jobs'
                   style='background:#1A56DB;color:#fff;padding:12px 28px;
                          border-radius:8px;text-decoration:none;font-weight:700;'>
                    Browse All Jobs
                </a>
            </p>
            <p style='color:#94a3b8;font-size:12px;'>
                To manage your job alerts, visit your
                <a href='" . BASE_URL . "/seeker/alerts' style='color:#1A56DB;'>alerts page</a>.
            </p>
        ";

        $smsMsg = APP_NAME . ": Hi {$user['full_name']}, {$count} new job" . ($count > 1 ? 's' : '') . " match your \"{$alertName}\" alert. View: " . BASE_URL . "/seeker/alerts";
        self::notify($user, "{$count} New Job" . ($count > 1 ? 's' : '') . " — {$alertName}", $smsMsg);

        self::create($userId, 'job_alert',
            "{$count} new job" . ($count > 1 ? 's' : '') . " matching \"{$alertName}\"",
            "We found {$count} new matching jobs for your alert.",
            '/seeker/alerts'
        );
    }
}
