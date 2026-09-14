<?php
/**
 * Sms — Twilio SMS helper for TatuJobHub
 * File location: app/helpers/Sms.php
 *
 * Uses Twilio REST API directly via cURL — no SDK or composer package needed.
 * InfinityFree allows outbound HTTPS (port 443), so this works fine.
 *
 * TRIAL ACCOUNT LIMITATION:
 * Twilio trial accounts can only send SMS to verified phone numbers.
 * To verify a number: Twilio Console → Phone Numbers → Verified Caller IDs → Add New
 * Once you upgrade your Twilio account this restriction is removed.
 */
class Sms
{
    // ── Send a raw SMS ────────────────────────────────────────────────────────

    public static function send(string $to, string $message): bool
    {
        $cfg = require ROOT_PATH . '/config/sms.php';

        if (empty($cfg['enabled'])) return false;

        // Normalise phone number to E.164 format
        // Accepts: 0241234567 → +233241234567 (Ghana)
        //          +233241234567 → unchanged
        $to = self::normalise($to);
        if (!$to) {
            error_log('[SMS] Invalid phone number provided');
            return false;
        }

        $url  = 'https://api.twilio.com/2010-04-01/Accounts/'
              . $cfg['account_sid'] . '/Messages.json';

        $data = http_build_query([
            'From' => $cfg['from_number'],
            'To'   => $to,
            'Body' => $message,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_USERPWD        => $cfg['account_sid'] . ':' . $cfg['auth_token'],
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 15,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 201) return true;

        $body = json_decode($response, true);
        error_log('[SMS] Twilio error ' . $httpCode . ': ' . ($body['message'] ?? $response));
        return false;
    }

    // ── Pre-built message templates ───────────────────────────────────────────

    public static function sendVerification(string $to, string $name, string $token): bool
    {
        $link = BASE_URL . '/verify-email?token=' . urlencode($token);
        $msg  = APP_NAME . ": Hi {$name}, verify your account here: {$link}";
        return self::send($to, $msg);
    }

    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $link = BASE_URL . '/reset-password?token=' . urlencode($token);
        $msg  = APP_NAME . ": Hi {$name}, reset your password here: {$link} (expires in 1 hour)";
        return self::send($to, $msg);
    }

    public static function sendWelcome(string $to, string $name, string $role): bool
    {
        $dash = BASE_URL . '/' . $role . '/dashboard';
        $label = $role === 'employer' ? 'Employer' : 'Job Seeker';
        $msg  = APP_NAME . ": Welcome {$name}! Your {$label} account is active. Go to your dashboard: {$dash}";
        return self::send($to, $msg);
    }

    public static function sendJobAlert(string $to, string $name, string $alertName, int $count): bool
    {
        $link = BASE_URL . '/seeker/alerts';
        $jobs = $count === 1 ? '1 new job matches' : "{$count} new jobs match";
        $msg  = APP_NAME . ": Hi {$name}, {$jobs} your \"{$alertName}\" alert. View them: {$link}";
        return self::send($to, $msg);
    }

    public static function sendApplicationUpdate(string $to, string $name, string $jobTitle, string $status): bool
    {
        $link = BASE_URL . '/seeker/applications';
        $msg  = APP_NAME . ": Hi {$name}, your application for \"{$jobTitle}\" has been updated to: "
              . ucwords(str_replace('_', ' ', $status)) . ". View details: {$link}";
        return self::send($to, $msg);
    }

    // ── Phone number normalisation ────────────────────────────────────────────

    /**
     * Normalise a phone number to E.164 format.
     * Defaults to Ghana (+233) for local numbers starting with 0.
     */
    public static function normalise(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone); // strip spaces
        $phone = preg_replace('/[^+0-9]/', '', $phone); // strip non-numeric except +

        if (empty($phone)) return '';

        // Already in E.164
        if (str_starts_with($phone, '+')) return $phone;

        // Ghanaian local number: 0XXXXXXXXX → +233XXXXXXXXX
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '+233' . substr($phone, 1);
        }

        // Ghanaian number without leading 0: 2XXXXXXXXX (10 digits)
        if (strlen($phone) === 9 && in_array($phone[0], ['2','5'])) {
            return '+233' . $phone;
        }

        // Return as-is with + prefix as fallback
        return '+' . ltrim($phone, '+');
    }
}
