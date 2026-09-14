<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Validator.php';

class AccountController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->requireLogin();
        $this->db = Database::getInstance();
    }

    // ── Settings page ─────────────────────────────────────────────────────
    public function settings(): void
    {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", [Session::id()]
        );
        $this->view('account/settings', [
            'title' => 'Account Settings',
            'user'  => $user,
        ], $this->layoutFor(Session::role()));
    }

    // ── Change password ───────────────────────────────────────────────────
    public function changePassword(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'current_password'  => 'required',
            'new_password'      => 'required|min:8|max:72',
            'confirm_password'  => 'required|confirmed:new_password',
        ], [
            'confirm_password.confirmed' => 'New passwords do not match.',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", [Session::id()]
        );

        if (!password_verify($this->input('current_password'), $user['password_hash'])) {
            $this->flash('error', 'Current password is incorrect.');
            $this->back();
            return;
        }

        $this->db->update('users', [
            'password_hash' => password_hash(
                $_POST['new_password'], PASSWORD_BCRYPT, ['cost' => 12]
            ),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ?', [Session::id()]);

        $this->flash('success', '✓ Password changed successfully! Please log in again.');
        Session::logout();
        $this->redirect(BASE_URL . '/login');
    }

    // ── Change email ──────────────────────────────────────────────────────
    public function changeEmail(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'new_email'       => 'required|email|max:180',
            'confirm_password'=> 'required',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", [Session::id()]
        );

        if (!password_verify($this->input('confirm_password'), $user['password_hash'])) {
            $this->flash('error', 'Password is incorrect.');
            $this->back();
            return;
        }

        $newEmail = strtolower(trim($this->input('new_email')));

        // Check not taken
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE email = ? AND id != ?",
            [$newEmail, Session::id()]
        );
        if ($exists) {
            $this->flash('error', 'This email address is already in use by another account.');
            $this->back();
            return;
        }

        $this->db->update('users', [
            'email'          => $newEmail,
            'email_verified' => 0,
            'updated_at'     => date('Y-m-d H:i:s'),
        ], 'id = ?', [Session::id()]);

        // Update session email
        $_SESSION['user']['email'] = $newEmail;

        $this->flash('success', '✓ Email updated to ' . $newEmail . '.');
        $this->redirect(BASE_URL . '/' . Session::role() . '/account/settings');
    }

    // ── Delete account ────────────────────────────────────────────────────
    public function deleteAccount(): void
    {
        $this->verifyCsrf();

        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE id = ?", [Session::id()]
        );

        if (!password_verify($this->input('confirm_password'), $user['password_hash'])) {
            $this->flash('error', 'Password is incorrect. Account not deleted.');
            $this->back();
            return;
        }

        if ($user['role'] === 'admin') {
            $this->flash('error', 'Admin accounts cannot be deleted this way.');
            $this->back();
            return;
        }

        $userId = Session::id();
        Session::logout();

        // Cascade delete handled by FK constraints in DB
        $this->db->delete('users', 'id = ?', [$userId]);

        $this->flash('success', 'Your account has been permanently deleted.');
        $this->redirect(BASE_URL . '/');
    }

    // ── Notification preferences ──────────────────────────────────────────
    public function updateNotifications(): void
    {
        $this->verifyCsrf();
        // Store preferences in session for now (can be moved to a DB table)
        Session::set('notif_prefs', [
            'email_applications' => isset($_POST['email_applications']),
            'email_alerts'       => isset($_POST['email_alerts']),
            'email_interviews'   => isset($_POST['email_interviews']),
        ]);
        $this->flash('success', '✓ Notification preferences saved.');
        $this->back();
    }

    // ── Helper ────────────────────────────────────────────────────────────
    private function layoutFor(string $role): string
    {
        return match($role) {
            'seeker'   => 'seeker',
            'employer' => 'employer',
            'admin'    => 'admin',
            default    => 'main',
        };
    }
}
