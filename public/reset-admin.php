<?php
/**
 * ONE-TIME admin password reset tool
 * IMPORTANT: Delete this file immediately after use!
 * Visit: tatujobhub.xo.je/reset-admin.php
 */

define('ROOT_PATH', dirname(__DIR__));

// Only allow if a secret key is passed (basic protection)
$secret = $_GET['key'] ?? '';
if ($secret !== 'tatujobhub2024reset') {
    die('<h2>Access Denied</h2><p>Missing or wrong key. Add <code>?key=tatujobhub2024reset</code> to the URL.</p>');
}

require_once ROOT_PATH . '/config/database.php';
$cfg = require ROOT_PATH . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset=utf8mb4",
        $cfg['username'],
        $cfg['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $newPassword = 'Admin@1234';
    $hash        = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    // Update admin + verify email
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ?, email_verified = 1, is_active = 1 WHERE role = 'admin'");
    $stmt->execute([$hash]);
    $affected = $stmt->rowCount();

    // Fetch the admin email
    $admin = $pdo->query("SELECT id, full_name, email FROM users WHERE role = 'admin' LIMIT 1")->fetch(PDO::FETCH_ASSOC);

    echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
    <title>Admin Reset</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    </head><body class="bg-light"><div class="container py-5" style="max-width:500px;">';

    if ($affected > 0 && $admin) {
        echo '<div class="card border-0 shadow p-4 text-center">
            <div style="font-size:3rem;">✅</div>
            <h4 class="fw-800 mt-3">Password Reset!</h4>
            <p class="text-muted mb-3">Admin account updated successfully.</p>
            <table class="table table-bordered text-start">
              <tr><td class="fw-600">Email</td><td>' . htmlspecialchars($admin['email']) . '</td></tr>
              <tr><td class="fw-600">Password</td><td><code>Admin@1234</code></td></tr>
              <tr><td class="fw-600">Name</td><td>' . htmlspecialchars($admin['full_name']) . '</td></tr>
            </table>
            <a href="/login" class="btn btn-primary fw-600 w-100 mt-2">Go to Login</a>
            <div class="alert alert-warning mt-3 text-start small">
              ⚠️ <strong>Delete this file now!</strong><br>
              Remove <code>public/reset-admin.php</code> from your server via FileZilla immediately after logging in.
            </div>
        </div>';
    } else {
        echo '<div class="alert alert-danger">No admin user found. <a href="/register">Create one first</a>.</div>';
    }

    echo '</div></body></html>';

} catch (Exception $e) {
    echo '<div class="alert alert-danger">DB Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
