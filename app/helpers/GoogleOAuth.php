<?php
require_once ROOT_PATH . '/config/google_oauth.php';

class GoogleOAuth
{
    /**
     * Build the Google consent-screen URL and store a CSRF state token.
     */
    public static function getAuthUrl(): string
    {
        $state = bin2hex(random_bytes(16));

        // Store in both session (works fine locally / on single-server hosts)
        // AND a short-lived cookie (survives InfinityFree's load-balanced
        // servers, which don't share session files between requests).
        Session::set('oauth_state', $state);
        setcookie('oauth_state', $state, [
            'expires'  => time() + 600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => false,
        ]);

        $params = http_build_query([
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'access_type'   => 'online',
            'state'         => $state,
            'prompt'        => 'select_account',
        ]);

        return GOOGLE_AUTH_URL . '?' . $params;
    }

    /**
     * Exchange the authorization code for Google user info.
     *
     * @return array{google_id: string, email: string, full_name: string, avatar: string}
     * @throws RuntimeException on any failure
     */
    public static function getUserFromCode(string $code): array
    {
        // Step 1 — Exchange code for access token
        $tokenData = self::post(GOOGLE_TOKEN_URL, [
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]);

        if (empty($tokenData['access_token'])) {
            $err = $tokenData['error_description'] ?? $tokenData['error'] ?? 'unknown error';
            throw new RuntimeException('Google token exchange failed: ' . $err);
        }

        // Step 2 — Fetch user profile with the access token
        $userInfo = self::get(GOOGLE_USER_URL, $tokenData['access_token']);

        if (empty($userInfo['sub'])) {
            throw new RuntimeException('Failed to retrieve user profile from Google.');
        }

        return [
            'google_id' => $userInfo['sub'],
            'email'     => strtolower(trim($userInfo['email']   ?? '')),
            'full_name' => trim($userInfo['name']               ?? ''),
            'avatar'    => $userInfo['picture']                 ?? '',
        ];
    }

    // ── Private HTTP helpers ──────────────────────────────────────────────────

    private static function post(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($data),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $body  = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            throw new RuntimeException('cURL error during token exchange: ' . $errno);
        }

        return json_decode($body ?: '{}', true) ?? [];
    }

    private static function get(string $url, string $accessToken): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $accessToken],
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $body  = curl_exec($ch);
        $errno = curl_errno($ch);
        curl_close($ch);

        if ($errno) {
            throw new RuntimeException('cURL error fetching user info: ' . $errno);
        }

        return json_decode($body ?: '{}', true) ?? [];
    }
}