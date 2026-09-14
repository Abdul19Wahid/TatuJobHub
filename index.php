<?php
/**
 * Tatu Job Hub — Root entry point (htdocs/index.php)
 * Handles all requests via ErrorDocument 404 routing.
 * Apache preserves $_POST for ErrorDocument local redirects.
 */

// Reset Apache's 404 status
http_response_code(200);

// Set ROOT_PATH to htdocs/ before loading the front controller
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// Boot the full application — handles both GET and POST
require_once ROOT_PATH . '/config/public/index.php';
