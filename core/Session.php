<?php
class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        session_set_cookie_params([
            'lifetime' => 0, 'path' => '/',
            'secure'   => !in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost','127.0.0.1'], true) && !str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name('JP_SESSION');
        session_start();
        if (!isset($_SESSION['_started'])) {
            session_regenerate_id(true);
            $_SESSION['_started'] = time();
        }
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'       => (int)$user['id'],
            'name'     => $user['full_name'],
            'email'    => $user['email'],
            'role'     => $user['role'],
            'verified' => (bool)$user['email_verified'],
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function user(): ?array  { return $_SESSION['user'] ?? null; }
    public static function id(): int       { return (int)($_SESSION['user']['id'] ?? 0); }
    public static function role(): string  { return $_SESSION['user']['role'] ?? ''; }
    public static function isLoggedIn(): bool { return isset($_SESSION['user']); }
    public static function isSeeker(): bool   { return self::role() === 'seeker'; }
    public static function isEmployer(): bool { return self::role() === 'employer'; }
    public static function isAdmin(): bool    { return self::role() === 'admin'; }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function getFlash(): array
    {
        $msgs = $_SESSION['_flash'] ?? [];
        unset($_SESSION['_flash']);
        return $msgs;
    }

    public static function hasFlash(): bool { return !empty($_SESSION['_flash']); }

    public static function csrf(): string
    {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(string $token): bool
    {
        return !empty($token) && hash_equals(self::csrf(), $token);
    }

    public static function set(string $key, mixed $val): void  { $_SESSION[$key] = $val; }
    public static function get(string $key, mixed $def = null): mixed { return $_SESSION[$key] ?? $def; }
    public static function has(string $key): bool   { return isset($_SESSION[$key]); }
    public static function forget(string $key): void { unset($_SESSION[$key]); }

    public static function setIntended(string $url): void { self::set('_intended', $url); }
    public static function intended(string $fallback = '/'): string
    {
        $url = self::get('_intended', $fallback);
        self::forget('_intended');
        return $url;
    }
}
