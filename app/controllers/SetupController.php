<?php
/**
 * ONE-TIME password setup — DELETE after use!
 * Visit: tatujobhub.xo.je/config/public/setup?key=tatu2024
 */
class SetupController
{
    public function run(): void
    {
        if (($_GET['key'] ?? '') !== 'tatu2024') {
            die('Access denied.');
        }

        $cfg = require ROOT_PATH . '/config/database.php';
        $pdo = new PDO(
            "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset=utf8mb4",
            $cfg['username'], $cfg['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $email    = 'hardiabdulwahid19@gmail.com';
        $password = 'Aw0543568910';
        $hash     = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);

        // Check if exists
        $stmt = $pdo->prepare("SELECT id, email, role, is_active, email_verified FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update existing
            $pdo->prepare("UPDATE users SET password_hash=?, email_verified=1, is_active=1 WHERE email=?")
                ->execute([$hash, $email]);
            $action = 'Updated';
        } else {
            // Create new seeker account
            $pdo->prepare("INSERT INTO users (full_name,email,password_hash,role,is_active,email_verified,created_at)
                           VALUES (?,?,?,'seeker',1,1,NOW())")
                ->execute(['Hardi Abdul-Wahid', $email, $hash]);
            $action = 'Created';

            // Create seeker profile
            $newId = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO job_seeker_profiles (user_id,is_open_to_work,created_at,updated_at)
                           VALUES (?,1,NOW(),NOW())")
                ->execute([$newId]);
        }

        // Also verify all existing accounts
        $pdo->exec("UPDATE users SET email_verified=1, is_active=1");

        // Show all users
        $users = $pdo->query("SELECT id, full_name, email, role, is_active, email_verified FROM users")->fetchAll(PDO::FETCH_ASSOC);

        echo '<!DOCTYPE html><html><head><meta charset="UTF-8">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        </head><body class="bg-light"><div class="container py-4" style="max-width:700px">';
        echo "<div class='alert alert-success'><strong>✅ {$action}:</strong> {$email}</div>";
        echo "<p><strong>Generated hash:</strong> <code>" . htmlspecialchars($hash) . "</code></p>";
        echo "<hr><h5>All Users in DB:</h5><table class='table table-bordered table-sm'>";
        echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Verified</th></tr>";
        foreach ($users as $u) {
            echo "<tr><td>{$u['id']}</td><td>" . htmlspecialchars($u['full_name']) . "</td><td>" . htmlspecialchars($u['email']) . "</td><td>{$u['role']}</td><td>{$u['is_active']}</td><td>{$u['email_verified']}</td></tr>";
        }
        echo "</table>";
        echo "<div class='alert alert-warning mt-3'>⚠️ <strong>Delete this route from routes.php after use!</strong></div>";
        echo "<a href='/config/public/login' class='btn btn-primary'>Go to Login →</a>";
        echo '</div></body></html>';
        exit;
    }
}
