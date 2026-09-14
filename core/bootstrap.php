<?php
/**
 * core/bootstrap.php — Single shared application bootstrap.
 *
 * Both entry points (public/index.php for local/XAMPP, and
 * config/public/index.php for InfinityFree's ErrorDocument trick)
 * require this file instead of duplicating boot logic. Before requiring
 * it, the entry point MUST define:
 *
 *   ROOT_PATH      - absolute path to the project root (the folder
 *                     containing /core, /app, /config, /routes.php)
 *
 * and MAY define:
 *
 *   APP_BASE_PATH  - the URL path prefix this entry point is served
 *                     under (e.g. '/config/public'). If not defined,
 *                     config/app.php will auto-detect it from
 *                     SCRIPT_NAME, which is reliable ONLY when the
 *                     currently-executing file is the real front
 *                     controller (true for public/index.php on
 *                     Apache/XAMPP and for `php -S` with the bundled
 *                     public/router.php). Entry points that proxy
 *                     through another script (like the InfinityFree
 *                     ErrorDocument path) must set this explicitly
 *                     because SCRIPT_NAME is unreliable there.
 */

if (!defined('ROOT_PATH')) {
    die('bootstrap.php requires ROOT_PATH to be defined first.');
}

$_isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true)
           || str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost');

ini_set('display_errors', $_isLocal ? '1' : '0');
ini_set('display_startup_errors', $_isLocal ? '1' : '0');
error_reporting($_isLocal ? E_ALL : 0);

if (!file_exists(ROOT_PATH . '/core/Router.php')) {
    die('<div style="font-family:sans-serif;max-width:640px;margin:60px auto;padding:30px;
         background:#fff3cd;border:2px solid #ffc107;border-radius:12px;">
    <h2 style="color:#856404;">Setup Error — Core files not found</h2>
    <p>Current ROOT_PATH: <code>' . htmlspecialchars(ROOT_PATH) . '</code></p>
    <p>Expected to find: <code>' . htmlspecialchars(ROOT_PATH) . '/core/Router.php</code></p>
    </div>');
}

$appConfig = require ROOT_PATH . '/config/app.php';
define('BASE_URL', rtrim($appConfig['base_url'] ?? '', '/'));
define('APP_NAME', $appConfig['name'] ?? 'Tatu Job Hub');
define('APP_ENV', $appConfig['env'] ?? 'development');
define('APP_DEBUG', $appConfig['debug'] ?? $_isLocal);

date_default_timezone_set($appConfig['timezone'] ?? 'Africa/Accra');

spl_autoload_register(function (string $class): void {
    $paths = [
        ROOT_PATH . '/core/' . $class . '.php',
        ROOT_PATH . '/app/middleware/' . $class . '.php',
    ];
    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

require_once ROOT_PATH . '/app/helpers/functions.php';

if ($_isLocal) {
    set_exception_handler(function (Throwable $e): void {
        http_response_code(500);
        echo '<!DOCTYPE html><html><head><title>Error</title>
        <style>body{font-family:monospace;background:#1e1e1e;color:#d4d4d4;padding:2rem;}
        h2{color:#f48771;}pre{background:#252526;padding:1rem;border-radius:6px;
        overflow:auto;font-size:13px;}.tip{background:#1a2a3a;border:1px solid #2563eb;
        border-radius:8px;padding:1rem;margin-top:1rem;font-family:sans-serif;
        font-size:13px;color:#93c5fd;}</style></head><body>';
        echo '<h2>' . htmlspecialchars(get_class($e)) . '</h2>';
        echo '<p style="color:#fbbf24;margin-bottom:.5rem;">' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<p style="color:#9cdcfe;">File: ' . htmlspecialchars($e->getFile()) . '</p>';
        echo '<p style="color:#86efac;">Line: ' . $e->getLine() . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '<div class="tip"><strong>Common fixes:</strong><br>';
        echo 'DB error &rarr; MySQL running? Imported schema.sql?<br>';
        echo 'Class not found &rarr; File in wrong folder?<br>';
        echo 'View not found &rarr; Check view path in controller</div>';
        echo '</body></html>';
        exit;
    });
}

Session::start();

$router = new Router();
require ROOT_PATH . '/routes.php';
$router->dispatch();
