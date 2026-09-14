-- ============================================================
-- Google OAuth Migration
-- Run this in phpMyAdmin on BOTH your local XAMPP and InfinityFree
-- ============================================================

-- 1. Allow NULL passwords (OAuth users have no password)
ALTER TABLE users
    MODIFY COLUMN password_hash VARCHAR(255) NULL DEFAULT NULL;

-- 2. Add google_id column (stores Google's unique "sub" identifier)
ALTER TABLE users
    ADD COLUMN google_id VARCHAR(255) NULL DEFAULT NULL AFTER password_hash;

-- 3. Unique index so two accounts can't share a Google ID
ALTER TABLE users
    ADD UNIQUE INDEX idx_google_id (google_id);
