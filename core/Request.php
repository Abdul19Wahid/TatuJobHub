<?php
/**
 * Request.php — HTTP Request Helper
 *
 * Wraps $_GET, $_POST, $_FILES, $_SERVER with a clean, safe API.
 * Inject or instantiate directly inside controllers.
 *
 * Usage:
 *   $req = new Request();
 *   $email = $req->post('email');           // trimmed POST value
 *   $page  = $req->get('page', 1);          // GET with default
 *   $file  = $req->file('resume');          // uploaded file array
 *   $ip    = $req->ip();
 */

class Request
{
    // ── GET parameters ────────────────────────────────────────────────────────

    public function get(string $key = '', mixed $default = ''): mixed
    {
        if ($key === '') return $_GET;
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    // ── POST parameters ───────────────────────────────────────────────────────

    public function post(string $key = '', mixed $default = ''): mixed
    {
        if ($key === '') return $_POST;
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? trim($value) : $value;
    }

    // ── Any input (POST first, then GET) ─────────────────────────────────────

    public function input(string $key, mixed $default = ''): mixed
    {
        return $this->post($key) !== '' ? $this->post($key) : $this->get($key, $default);
    }

    // ── Uploaded files ────────────────────────────────────────────────────────

    public function file(string $key): array|null
    {
        $file = $_FILES[$key] ?? null;
        return ($file && $file['error'] !== UPLOAD_ERR_NO_FILE) ? $file : null;
    }

    // ── Request method ────────────────────────────────────────────────────────

    public function method(): string   { return strtoupper($_SERVER['REQUEST_METHOD']); }
    public function isGet(): bool      { return $this->method() === 'GET'; }
    public function isPost(): bool     { return $this->method() === 'POST'; }
    public function isAjax(): bool     { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }
    public function isSecure(): bool   { return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); }

    // ── URL / path info ───────────────────────────────────────────────────────

    public function uri(): string      { return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); }
    public function fullUrl(): string  { return $this->isSecure() ? 'https' : 'http' . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; }

    // ── Client information ────────────────────────────────────────────────────

    public function ip(): string
    {
        foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                // X_FORWARDED_FOR can be comma-separated list; take the first
                return trim(explode(',', $_SERVER[$key])[0]);
            }
        }
        return '0.0.0.0';
    }

    public function userAgent(): string { return $_SERVER['HTTP_USER_AGENT'] ?? ''; }
    public function referer(): string   { return $_SERVER['HTTP_REFERER'] ?? ''; }

    // ── Sanitisation helpers ──────────────────────────────────────────────────

    /** Strip HTML tags and trim. */
    public function clean(string $key, string $source = 'post'): string
    {
        $value = $source === 'get' ? $this->get($key) : $this->post($key);
        return strip_tags(trim((string) $value));
    }

    /** Cast POST value to int. */
    public function int(string $key, int $default = 0): int
    {
        return (int) ($this->post($key) ?: $this->get($key, $default));
    }

    /** Cast POST/GET to float. */
    public function float(string $key, float $default = 0.0): float
    {
        return (float) ($this->post($key) ?: $this->get($key, $default));
    }

    /** Boolean checkbox: present & not 'false'/'0' → true. */
    public function bool(string $key): bool
    {
        $val = $this->post($key) ?: $this->get($key, '');
        return !in_array(strtolower((string) $val), ['', '0', 'false', 'no', 'off'], true);
    }

    // ── Has checks ────────────────────────────────────────────────────────────

    public function has(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    public function hasFile(string $key): bool
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK;
    }

    // ── Raw body (for JSON API requests) ─────────────────────────────────────

    public function rawBody(): string { return file_get_contents('php://input') ?: ''; }

    public function json(): array
    {
        $data = json_decode($this->rawBody(), true);
        return is_array($data) ? $data : [];
    }
}
