<?php
// config/public/index.php — InfinityFree front controller.
// Reached via the root .htaccess ErrorDocument 404 trick (see /index.php
// and /.htaccess). SCRIPT_NAME here is NOT reliable for base-path
// detection when proxied that way, so we set APP_BASE_PATH explicitly.

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(dirname(__DIR__)));
}

if (!defined('APP_BASE_PATH')) {
    define('APP_BASE_PATH', '/config/public');
}

require ROOT_PATH . '/core/bootstrap.php';
