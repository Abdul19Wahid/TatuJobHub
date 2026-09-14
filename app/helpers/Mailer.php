<?php
class Mailer
{
    /**
     * Send email.
     *
     * On InfinityFree shared hosting, set driver = 'mail' in config/mail.php.
     * InfinityFree blocks outbound SMTP to external servers (ports 25/465/587),
     * so PHPMailer + any external SMTP will always fail silently.
     * PHP mail() works because InfinityFree routes it through their internal relay.
     * Just make sure the From address is a real mailbox on your domain.
     *
     * Set driver = 'smtp' only if you move to a VPS with open SMTP ports.
     */
    public static function send(string $to, string $name, string $subject, string $body): bool
    {
        $cfg = require ROOT_PATH . '/config/mail.php';

        if (($cfg['driver'] ?? 'mail') === 'smtp'
            && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return self::sendViaSmtp($to, $name, $subject, $body);
        }

        // Default: PHP mail() via InfinityFree's internal sendmail relay
        return self::sendViaMail($to, $name, $subject, $body);
    }
    private static function sendViaSmtp(string $to, string $name, string $subject, string $body): bool
    {
        $cfg  = require ROOT_PATH . '/config/mail.php';
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $cfg['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $cfg['smtp_user'];
            $mail->Password   = $cfg['smtp_pass'];
            $mail->SMTPSecure = $cfg['smtp_encryption'] === 'ssl'
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMIME
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$cfg['smtp_port'];
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($to, $name);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = self::wrap($subject, $body);
            $mail->AltBody = strip_tags($body);
            $mail->send();
            return true;
        } catch (\Exception $e) {
            error_log('[Mailer] SMTP failed: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaMail(string $to, string $name, string $subject, string $body): bool
    {
        $cfg      = require ROOT_PATH . '/config/mail.php';
        $from     = $cfg['from_email'] ?? 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $fromName = $cfg['from_name']  ?? APP_NAME;
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$from}>\r\n";
        $headers .= "Reply-To: {$from}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        return mail($to, $subject, self::wrap($subject, $body), $headers);
    }

    // ── Pre-built templates ───────────────────────────────────────────────────

    public static function sendVerification(string $to, string $name, string $token): bool
    {
        $link = BASE_URL . '/verify-email?token=' . urlencode($token);
        $body = "
            <p>Hi <strong>" . e($name) . "</strong>,</p>
            <p>Thank you for registering on <strong>" . APP_NAME . "</strong>.
               Please verify your email address by clicking the button below.</p>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$link}' style='background:#1A56DB;color:#fff;padding:14px 32px;
                   border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;
                   display:inline-block;'>✉ Verify Email Address</a>
            </p>
            <p style='color:#6b7280;font-size:13px;'>Or copy this link: <br>
               <a href='{$link}' style='color:#1A56DB;word-break:break-all;'>{$link}</a></p>
            <p style='color:#6b7280;font-size:13px;'>Link expires in <strong>24 hours</strong>.
               If you did not register, ignore this email.</p>
        ";
        return self::send($to, $name, 'Verify Your Email — ' . APP_NAME, $body);
    }

    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $link = BASE_URL . '/reset-password?token=' . urlencode($token);
        $body = "
            <p>Hi <strong>" . e($name) . "</strong>,</p>
            <p>We received a request to reset your <strong>" . APP_NAME . "</strong> password.</p>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$link}' style='background:#1A56DB;color:#fff;padding:14px 32px;
                   border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;
                   display:inline-block;'>🔑 Reset My Password</a>
            </p>
            <p style='color:#6b7280;font-size:13px;'>Or copy this link:<br>
               <a href='{$link}' style='color:#1A56DB;word-break:break-all;'>{$link}</a></p>
            <p style='color:#6b7280;font-size:13px;'>Link expires in <strong>1 hour</strong>.
               If you did not request this, ignore this email.</p>
        ";
        return self::send($to, $name, 'Reset Your Password — ' . APP_NAME, $body);
    }

    public static function sendWelcome(string $to, string $name, string $role): bool
    {
        $dash  = BASE_URL . '/' . $role . '/dashboard';
        $label = $role === 'employer' ? 'Employer' : 'Job Seeker';
        $tips  = $role === 'seeker'
            ? '<li>Complete your profile &amp; upload your resume</li>
               <li>Search thousands of job listings</li>
               <li>Set up job alerts so you never miss an opportunity</li>'
            : '<li>Complete your company profile</li>
               <li>Post your first job listing</li>
               <li>Search and shortlist candidates</li>';
        $body  = "
            <p>Hi <strong>" . e($name) . "</strong>,</p>
            <p>Welcome to <strong>" . APP_NAME . "</strong>! Your {$label} account is now active.</p>
            <ul>{$tips}</ul>
            <p style='text-align:center;margin:32px 0;'>
                <a href='{$dash}' style='background:#1A56DB;color:#fff;padding:14px 32px;
                   border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;
                   display:inline-block;'>🚀 Go to My Dashboard</a>
            </p>
        ";
        return self::send($to, $name, 'Welcome to ' . APP_NAME . '!', $body);
    }


    // ── Job Alert email ───────────────────────────────────────────────────────

    /**
     * Email a seeker when a new job matches one of their alerts.
     *
     * @param string $to             Seeker email
     * @param string $name           Seeker full name
     * @param array  $alert          Row from job_alerts table
     * @param array  $jobs           Matching job rows (title, slug, company_name, location_city, job_type, salary_min, salary_max, salary_currency, salary_is_hidden)
     */
    public static function sendJobAlert(
        string $to,
        string $name,
        array  $alert,
        array  $jobs
    ): bool {
        if (empty($jobs)) return false;

        $alertName      = e($alert['alert_name'] ?: 'Job Alert');
        $unsubLink      = BASE_URL . '/alerts/unsubscribe?token=' . urlencode($alert['unsubscribe_token'] ?? '');
        $browseLink     = BASE_URL . '/jobs';

        // Build job rows
        $jobRows = '';
        foreach ($jobs as $job) {
            $jobLink   = BASE_URL . '/jobs/' . $job['slug'];
            $title     = e($job['title']);
            $company   = e($job['company_name']);
            $location  = $job['is_remote'] ? 'Remote' : e($job['location_city'] ?? 'N/A');
            $type      = ucwords(str_replace('_', ' ', $job['job_type']));
            $salaryTxt = (!$job['salary_is_hidden'] && $job['salary_min'])
                ? e($job['salary_currency'] ?? 'GHS') . ' ' . number_format($job['salary_min'])
                  . ($job['salary_max'] ? ' – ' . number_format($job['salary_max']) : '+')
                : 'Competitive';

            $jobRows .= "
            <tr>
              <td style='padding:16px 0;border-bottom:1px solid #e2e8f0;'>
                <a href='{$jobLink}' style='color:#1A56DB;font-weight:700;font-size:15px;
                   text-decoration:none;display:block;margin-bottom:4px;'>{$title}</a>
                <span style='color:#374151;font-size:13px;'>{$company}</span>
                &nbsp;·&nbsp;
                <span style='color:#6b7280;font-size:13px;'><span>&#128205;</span> {$location}</span>
                &nbsp;·&nbsp;
                <span style='color:#6b7280;font-size:13px;'>{$type}</span>
                <br>
                <span style='color:#057A55;font-size:13px;font-weight:600;'>{$salaryTxt}</span>
                &nbsp;&nbsp;
                <a href='{$jobLink}' style='background:#1A56DB;color:#fff;padding:5px 14px;
                   border-radius:6px;text-decoration:none;font-size:12px;font-weight:600;'>
                   View &amp; Apply
                </a>
              </td>
            </tr>";
        }

        $count = count($jobs);
        $label = $count === 1 ? '1 new job matches' : "{$count} new jobs match";

        $body = "
            <p>Hi <strong>" . e($name) . "</strong>,</p>
            <p>{$label} your <strong>{$alertName}</strong> alert on <strong>" . APP_NAME . "</strong>.</p>

            <table width='100%' cellpadding='0' cellspacing='0' style='border-collapse:collapse;'>
              {$jobRows}
            </table>

            <p style='text-align:center;margin:32px 0;'>
                <a href='{$browseLink}' style='background:#1A56DB;color:#fff;padding:14px 32px;
                   border-radius:8px;text-decoration:none;font-weight:700;font-size:15px;
                   display:inline-block;'>Browse All Jobs</a>
            </p>

            <p style='color:#9ca3af;font-size:12px;margin-top:24px;text-align:center;'>
                You are receiving this because you set up a job alert on " . APP_NAME . ".<br>
                <a href='{$unsubLink}' style='color:#9ca3af;'>Unsubscribe from this alert</a>
            </p>
        ";

        $subject = $count === 1
            ? "New job match: {$jobs[0]['title']} — {$alertName}"
            : "{$count} new job matches for your alert: {$alertName}";

        return self::send($to, $name, $subject, $body);
    }

    // ── HTML email wrapper ────────────────────────────────────────────────────

    private static function wrap(string $title, string $content): string
    {
        $app  = APP_NAME;
        $year = date('Y');
        $url  = BASE_URL;
        return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>{$title}</title></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;">
  <tr><td style="background:#1A56DB;border-radius:12px 12px 0 0;padding:24px 40px;text-align:center;">
    <span style="color:#fff;font-size:20px;font-weight:800;">💼 {$app}</span>
  </td></tr>
  <tr><td style="background:#fff;padding:40px;border:1px solid #e2e8f0;border-top:none;">
    <div style="color:#374151;font-size:15px;line-height:1.7;">{$content}</div>
    <hr style="border:none;border-top:1px solid #e2e8f0;margin:32px 0;">
    <p style="color:#94a3b8;font-size:12px;margin:0;">
      This email was sent by {$app}. Visit <a href="{$url}" style="color:#1A56DB;">{$url}</a>
    </p>
  </td></tr>
  <tr><td style="background:#f8fafc;border:1px solid #e2e8f0;border-top:none;
                 border-radius:0 0 12px 12px;padding:16px 40px;text-align:center;">
    <p style="color:#94a3b8;font-size:12px;margin:0;">&copy; {$year} {$app}. All rights reserved.</p>
  </td></tr>
</table></td></tr></table></body></html>
HTML;
    }
}
