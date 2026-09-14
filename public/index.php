<?php
// public/index.php — Local/XAMPP front controller.
// This file's own SCRIPT_NAME is reliable, so we let config/app.php
// auto-detect BASE_URL from it (no APP_BASE_PATH override needed).

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));   // Points to job-portal/ folder
}

require ROOT_PATH . '/core/bootstrap.php';
