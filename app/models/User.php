<?php
require_once ROOT_PATH . '/core/Model.php';

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ? LIMIT 1",
            [strtolower(trim($email))]
        );
    }

    public function findByVerifyToken(string $token): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE verify_token = ? LIMIT 1", [$token]
        );
    }

    public function emailExists(string $email): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM users WHERE email = ?", [strtolower(trim($email))]
        );
    }

    public function findByGoogleId(string $googleId): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM users WHERE google_id = ? LIMIT 1",
            [$googleId]
        );
    }

    /**
     * Create a brand-new account from Google profile data (no password).
     */
    public function registerFromGoogle(array $data): int
    {
        return $this->db->transaction(function (Database $db) use ($data) {
            $userId = $db->insert('users', [
                'full_name'      => $data['full_name'] ?: 'Google User',
                'email'          => strtolower(trim($data['email'])),
                'google_id'      => $data['google_id'],
                'password_hash'  => null,
                'role'           => $data['role'],
                'is_active'      => 1,
                'email_verified' => 1, // Google already verified this email
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            if ($data['role'] === 'seeker') {
                $db->insert('job_seeker_profiles', [
                    'user_id'         => $userId,
                    'is_open_to_work' => 1,
                    'created_at'      => date('Y-m-d H:i:s'),
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
            } elseif ($data['role'] === 'employer') {
                $db->insert('employer_profiles', [
                    'user_id'             => $userId,
                    'company_name'        => 'My Company',
                    'verification_status' => 'pending',
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
            }

            return $userId;
        });
    }

    /**
     * Link a Google ID to an existing email/password account
     * (so someone who registered manually can also use "Sign in with Google").
     */
    public function linkGoogleId(int $userId, string $googleId): void
    {
        $this->update($userId, ['google_id' => $googleId]);
    }

    public function register(array $data): int
    {
        return $this->db->transaction(function (Database $db) use ($data) {
            $token  = bin2hex(random_bytes(32));
            $userId = $db->insert('users', [
                'full_name'      => $data['full_name'],
                'email'          => strtolower(trim($data['email'])),
                'phone_number'   => !empty($data['phone_number']) ? $data['phone_number'] : null,
                'password_hash'  => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]),
                'role'           => $data['role'],
                'is_active'      => 1,
                'email_verified' => 0,
                'verify_token'   => $token,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);

            if ($data['role'] === 'seeker') {
                $db->insert('job_seeker_profiles', [
                    'user_id'        => $userId,
                    'is_open_to_work' => 1,
                    'created_at'     => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
            } elseif ($data['role'] === 'employer') {
                $db->insert('employer_profiles', [
                    'user_id'             => $userId,
                    'company_name'        => $data['company_name'] ?? 'My Company',
                    'verification_status' => 'pending',
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
            }

            return $userId;
        });
    }

    public function authenticate(string $email, string $password): array|false
    {
        $user = $this->findByEmail($email);
        if (!$user)                    return false;
        if (!$user['is_active'])       return false;
        if (!password_verify($password, $user['password_hash'])) return false;

        // Rehash if needed
        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) {
            $this->update($user['id'], [
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])
            ]);
        }

        $this->update($user['id'], ['last_login_at' => date('Y-m-d H:i:s')]);
        return $user;
    }

    public function verifyEmail(string $token): bool
    {
        $user = $this->findByVerifyToken($token);
        if (!$user) return false;
        $this->update($user['id'], ['email_verified' => 1, 'verify_token' => null]);
        return true;
    }

    public function getVerifyToken(int $userId): ?string
    {
        $val = $this->db->fetchColumn("SELECT verify_token FROM users WHERE id = ?", [$userId]);
        return $val ?: null;
    }

    public function createResetToken(int $userId): string
    {
        $this->db->delete('password_resets', 'user_id = ?', [$userId]);
        $token = bin2hex(random_bytes(32));
        $this->db->insert('password_resets', [
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
            'used'       => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return $token;
    }

    public function findValidResetToken(string $token): array|false
    {
        return $this->db->fetchOne(
            "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() LIMIT 1",
            [$token]
        );
    }

    public function resetPassword(string $token, string $newPassword): bool
    {
        $reset = $this->findValidResetToken($token);
        if (!$reset) return false;

        $this->db->transaction(function (Database $db) use ($reset, $newPassword) {
            $db->update('users',
                ['password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12])],
                'id = ?', [$reset['user_id']]
            );
            $db->update('password_resets', ['used' => 1], 'id = ?', [$reset['id']]);
        });

        return true;
    }
}
