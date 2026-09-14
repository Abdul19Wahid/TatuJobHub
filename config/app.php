<?php
// ── BASE URL ────────────────────────────────────────────────────────────────
// Prefer an explicit APP_BASE_PATH set by the entry point (public/index.php or
// config/public/index.php) -- that's the one place that actually knows which
// front controller is running. We only fall back to guessing from SCRIPT_NAME,
// and that guess is trusted as-is, including when it's empty (app served from
// the domain root) -- we never silently force it to /config/public, because
// that broke local testing with `php -S` (SCRIPT_NAME is empty there too, but
// the correct base path in that case IS the root, not /config/public).
$_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$_host     = $_SERVER['HTTP_HOST'] ?? 'localhost';

if (defined('APP_BASE_PATH')) {
    $_scriptDir = rtrim(APP_BASE_PATH, '/');
} else {
    $_scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'), '/\\');
    if ($_scriptDir === '/' || $_scriptDir === '\\') {
        $_scriptDir = '';
    }
}

$_baseUrl = $_protocol . '://' . $_host . $_scriptDir;
$_isLocal = in_array($_host, ['localhost', '127.0.0.1'], true);

return [
    'name'      => 'Tatu Job Hub',
    'tagline'   => 'Find Your Dream Job or Hire Top Talent',
    'base_url'  => $_baseUrl,
    'env'       => $_isLocal ? 'development' : 'production',
    'debug'     => $_isLocal,
    'timezone'  => 'Africa/Accra',
    'locale'    => 'en',
    'per_page'  => 10,
    'max_resume_size_mb'   => 5,
    'max_image_size_mb'    => 2,
    'allowed_resume_types' => ['application/pdf','application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'allowed_image_types'  => ['image/jpeg','image/png','image/webp'],
    'storage' => [
        'resumes'       => '/storage/resumes/',
        'avatars'       => '/storage/avatars/',
        'logos'         => '/storage/company-logos/',
        'cover_letters' => '/storage/cover-letters/',
        'documents'     => '/storage/documents/',
    ],
    'job_expiry_days' => 30,
    'support_email'   => 'support@tatujobhub.xo.je',
    'noreply_email'   => 'noreply@tatujobhub.xo.je',
];
