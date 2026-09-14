<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Validator.php';
require_once ROOT_PATH . '/app/models/User.php';
require_once ROOT_PATH . '/app/helpers/Mailer.php';
require_once ROOT_PATH . '/app/helpers/Sms.php';

class AuthController extends Controller
{
    private User $users;

    public function __construct()
    {
        $this->users = new User();
    }

    // ── Show register page ────────────────────────────────────────────────────
    public function showRegister(): void
    {
        $this->view('auth/register', ['title' => 'Create Account']);
    }

    // ── Handle registration ───────────────────────────────────────────────────
    public function register(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'full_name'    => 'required|min:2|max:120',
            'email'        => 'required|email|max:180',
            'phone_number' => 'required|min:9|max:20',
            'password'     => 'required|min:8|max:72',
            'password_confirmation' => 'required|confirmed:password',
            'role'         => 'required|in:seeker,employer',
        ], [
            'password_confirmation.confirmed' => 'Passwords do not match.',
            'phone_number.required'      => 'Phone number is required for SMS notifications.',
        ]);

        // Employer needs a company name
        if (($this->input('role')) === 'employer' && trim($this->input('company_name')) === '') {
            $this->flash('error', 'Company name is required for employer accounts.');
            $this->saveOldInput();
            $this->back();
            return;
        }

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->saveOldInput();
            $this->back();
            return;
        }

        if ($this->users->emailExists($this->input('email'))) {
            $this->flash('error', 'An account with this email address already exists.');
            $this->saveOldInput();
            $this->back();
            return;
        }

        try {
            $userId = $this->users->register([
                'full_name'    => $this->input('full_name'),
                'email'        => $this->input('email'),
                'phone_number' => Sms::normalise($this->input('phone_number')),
                'password'     => $_POST['password'],
                'role'         => $this->input('role'),
                'company_name' => $this->input('company_name'),
            ]);

            // Send SMS verification (and welcome message)
            $token = $this->users->getVerifyToken($userId);
            $phone = Sms::normalise($this->input('phone_number'));
            if ($token && $phone) {
                Sms::sendVerification($phone, $this->input('full_name'), $token);
            }
            if ($phone) {
                Sms::sendWelcome($phone, $this->input('full_name'), $this->input('role'));
            }

            $this->flash('success',
                'Account created! Check your phone for a verification SMS.'
            );
            $this->redirect(BASE_URL . '/login');

        } catch (\Throwable $e) {
            error_log('[register] ' . $e->getMessage());
            $this->flash('error', APP_DEBUG
                ? 'Registration failed: ' . $e->getMessage()
                : 'Something went wrong. Please try again.'
            );
            $this->saveOldInput();
            $this->back();
        }
    }

    // ── Show login page ───────────────────────────────────────────────────────
    public function showLogin(): void
    {
        $this->view('auth/login', ['title' => 'Sign In']);
    }

    // ── Handle login ──────────────────────────────────────────────────────────
    public function login(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->saveOldInput();
            $this->back();
            return;
        }

        $user = $this->users->authenticate(
            $this->input('email'),
            $_POST['password']
        );

        if (!$user) {
            $this->flash('error', 'Invalid email or password. Please try again.');
            $this->saveOldInput();
            $this->back();
            return;
        }

        Session::login($user);

        $intended = Session::intended($this->dashboardUrl($user['role']));
        $this->redirect($intended);
    }

    // ── Logout ────────────────────────────────────────────────────────────────
    public function logout(): void
    {
        Session::logout();
        $this->flash('success', 'You have been logged out successfully.');
        $this->redirect(BASE_URL . '/login');
    }

    // ── Email verification ────────────────────────────────────────────────────
    public function verifyEmail(): void
    {
        $token = $this->query('token');

        if (!$token) {
            $this->flash('error', 'Invalid verification link.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if ($this->users->verifyEmail($token)) {
            $this->flash('success', '✓ Email verified! You can now log in.');
        } else {
            $this->flash('error', 'This verification link is invalid or already used.');
        }

        $this->redirect(BASE_URL . '/login');
    }

    // ── Forgot password ───────────────────────────────────────────────────────
    public function showForgot(): void
    {
        $this->view('auth/forgot-password', ['title' => 'Forgot Password']);
    }

    public function sendReset(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, ['email' => 'required|email']);
        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $user = $this->users->findByEmail(strtolower(trim($this->input('email'))));

        // Same message whether account exists or not (prevents user enumeration)
        if ($user && $user['is_active']) {
            $token = $this->users->createResetToken($user['id']);
            // Try SMS first, fall back to email if no phone
            if (!empty($user['phone_number'])) {
                Sms::sendPasswordReset($user['phone_number'], $user['full_name'], $token);
            } else {
                Mailer::sendPasswordReset($user['email'], $user['full_name'], $token);
            }
        }

        $this->flash('success', 'If an account exists for that email, a reset link has been sent to your phone/email.');
        $this->redirect(BASE_URL . '/forgot-password');
    }

    // ── Reset password ────────────────────────────────────────────────────────
    public function showReset(): void
    {
        $token = $this->query('token');

        if (!$token || !$this->users->findValidResetToken($token)) {
            $this->flash('error', 'This reset link is invalid or has expired.');
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $this->view('auth/reset-password', ['title' => 'Set New Password', 'token' => $token]);
    }

    public function resetPassword(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'token'                 => 'required',
            'password'              => 'required|min:8|max:72',
            'password_confirmation' => 'required|confirmed:password',
        ], [
            'password_confirmation.confirmed' => 'Passwords do not match.',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($this->input('token')));
            return;
        }

        if (!$this->users->resetPassword($this->input('token'), $_POST['password'])) {
            $this->flash('error', 'This reset link is invalid or expired. Please request a new one.');
            $this->redirect(BASE_URL . '/forgot-password');
            return;
        }

        $this->flash('success', 'Password updated! Please log in with your new password.');
        $this->redirect(BASE_URL . '/login');
    }

    // ── Google OAuth: kick off ────────────────────────────────────────────────
    public function redirectToGoogle(): void
    {
        require_once ROOT_PATH . '/app/helpers/GoogleOAuth.php';

        $role = $this->query('role') === 'employer' ? 'employer' : 'seeker';
        Session::set('oauth_intended_role', $role);
        setcookie('oauth_role', $role, [
            'expires'  => time() + 600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => false,
        ]);

        $this->redirect(GoogleOAuth::getAuthUrl());
    }

    // ── Google OAuth: handle the redirect back from Google ───────────────────
    public function handleGoogleCallback(): void
    {
        require_once ROOT_PATH . '/app/helpers/GoogleOAuth.php';

        $state       = $this->query('state');
        $storedState = $_COOKIE['oauth_state'] ?? Session::get('oauth_state');
        Session::forget('oauth_state');
        setcookie('oauth_state', '', ['expires' => time() - 3600, 'path' => '/']);

        if (!empty($this->query('error'))) {
            $this->flash('error', 'Google sign-in was cancelled.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if (!$state || !$storedState || !hash_equals($storedState, $state)) {
            $this->flash('error', 'Your Google sign-in session expired. Please try again.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        $code = $this->query('code');
        if (!$code) {
            $this->flash('error', 'Google did not return an authorization code. Please try again.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        try {
            $profile = GoogleOAuth::getUserFromCode($code);
        } catch (\Throwable $e) {
            error_log('[google-oauth] ' . $e->getMessage());
            $this->flash('error', 'Google sign-in failed. Please try again or use your email and password.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        if (empty($profile['email'])) {
            $this->flash('error', 'Google did not share an email address. Please try again.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        // 1. Already linked to this Google account?
        $user = $this->users->findByGoogleId($profile['google_id']);

        // 2. Not linked yet, but an account with this email already exists → link it
        if (!$user) {
            $existing = $this->users->findByEmail($profile['email']);
            if ($existing) {
                $this->users->linkGoogleId($existing['id'], $profile['google_id']);
                $user = $this->users->findByEmail($profile['email']);
            }
        }

        // 3. Brand new user → create an account
        if (!$user) {
            $role   = $_COOKIE['oauth_role'] ?? Session::get('oauth_intended_role', 'seeker');
            $role   = $role === 'employer' ? 'employer' : 'seeker';
            $userId = $this->users->registerFromGoogle([
                'full_name' => $profile['full_name'],
                'email'     => $profile['email'],
                'google_id' => $profile['google_id'],
                'role'      => $role,
            ]);
            $user = $this->users->findByEmail($profile['email']);
        }

        Session::forget('oauth_intended_role');
        setcookie('oauth_role', '', ['expires' => time() - 3600, 'path' => '/']);

        if (!$user['is_active']) {
            $this->flash('error', 'This account has been deactivated. Please contact support.');
            $this->redirect(BASE_URL . '/login');
            return;
        }

        Session::login($user);
        $intended = Session::intended($this->dashboardUrl($user['role']));
        $this->redirect($intended);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function dashboardUrl(string $role): string
    {
        return match($role) {
            'seeker'   => BASE_URL . '/seeker/dashboard',
            'employer' => BASE_URL . '/employer/dashboard',
            'admin'    => BASE_URL . '/admin/dashboard',
            default    => BASE_URL,
        };
    }

    private function saveOldInput(): void
    {
        $safe = $_POST;
        unset($safe['password'], $safe['password_confirmation'], $safe['_csrf']);
        Session::set('_old_input', $safe);
    }
}
