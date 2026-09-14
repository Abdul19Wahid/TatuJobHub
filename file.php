<?php
/**
 * public/file.php — Secure file serving for uploaded files
 *
 * Serves files stored outside the web root (in /storage/).
 * Usage:  /file?path=storage/avatars/abc123.jpg
 *
 * Add this to your routes.php:
 *   $router->get('/file', 'FileController@serve');
 *
 * OR drop this file directly in public/ as a fallback.
 */

// Bootstrap the app
define('ROOT_PATH', dirname(__DIR__));
$appConfig = require ROOT_PATH . '/config/app.php';
define('BASE_URL', rtrim($appConfig['base_url'], '/'));
define('APP_NAME', $appConfig['name']);
define('APP_ENV',  $appConfig['env']);
define('APP_DEBUG',$appConfig['debug']);

$path = $_GET['path'] ?? '';

// Security: block path traversal attacks
$path = ltrim($path, '/');
$path = str_replace(['..', "\0"], '', $path);

// Only allow files from our storage directories
$allowed = ['storage/resumes/', 'storage/avatars/', 'storage/company-logos/',
            'storage/cover-letters/', 'storage/documents/'];

$ok = false;
foreach ($allowed as $dir) {
    if (str_starts_with($path, $dir)) { $ok = true; break; }
}

if (!$ok || !$path) {
    http_response_code(404);
    exit('File not found.');
}

$fullPath = ROOT_PATH . '/' . $path;

if (!file_exists($fullPath) || !is_file($fullPath)) {
    http_response_code(404);
    exit('File not found.');
}

// Determine MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $fullPath);
finfo_close($finfo);

// Allow only safe MIME types
$safeMimes = [
    'image/jpeg', 'image/png', 'image/webp', 'image/gif',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];

if (!in_array($mime, $safeMimes)) {
    http_response_code(403);
    exit('File type not allowed.');
}

// Cache headers for images (1 week), no-cache for documents
if (str_starts_with($mime, 'image/')) {
    header('Cache-Control: public, max-age=604800');
} else {
    header('Cache-Control: private, no-cache');
    header('Content-Disposition: inline; filename="' . basename($fullPath) . '"');
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($fullPath));
header('X-Content-Type-Options: nosniff');

readfile($fullPath);
exit;
