<?php
// Google OAuth — put real Client ID/Secret in .env only (never commit them)

define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');

$_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_isProduction = (strpos($_host, 'localhost') === false && strpos($_host, '127.0.0.1') === false);

define('GOOGLE_REDIRECT_URI', $_isProduction
    ? 'http://tatujobhub.xo.je/auth/google/callback'
    : 'http://localhost/job-portal/public/auth/google/callback'
);

define('GOOGLE_AUTH_URL',  'https://accounts.google.com/o/oauth2/v2/auth');
define('GOOGLE_TOKEN_URL', 'https://oauth2.googleapis.com/token');
define('GOOGLE_USER_URL',  'https://www.googleapis.com/oauth2/v3/userinfo');