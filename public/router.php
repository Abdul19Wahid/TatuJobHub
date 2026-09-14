<?php
/**
 * public/router.php — Router script for PHP's built-in dev server.
 *
 * PHP's built-in server (`php -S`) does NOT read .htaccess files, so the
 * clean-URL rewriting that public/.htaccess provides under Apache/XAMPP
 * does nothing here. This script replicates that behaviour manually.
 *
 * Usage (run from the job-portal/public folder):
 *   php -S localhost:8000 router.php
 *
 * Then browse to http://localhost:8000/
 *
 * This is an ALTERNATIVE to XAMPP/Apache for quick local testing. If you
 * already have XAMPP running, you don't need this file — use
 * http://localhost/job-portal/public/ instead.
 */

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$filePath = __DIR__ . $requestPath;

// Serve real static files (css, js, images, uploaded resumes, etc.) directly.
if ($requestPath !== '/' && file_exists($filePath) && !is_dir($filePath)) {
    return false;
}

// This entry point is served from the domain root under `php -S`,
// so the base path is empty — not /config/public.
if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', '');
}

require __DIR__ . '/index.php';
