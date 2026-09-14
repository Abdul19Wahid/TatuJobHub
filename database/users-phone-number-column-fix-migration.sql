-- ============================================================
-- TatuJobHub — Users Phone Number Column Fix
-- Run in phpMyAdmin on BOTH local XAMPP and InfinityFree
--
-- FIX: registration collects a phone number and User::register() /
-- AuthController's forgot-password flow both read/write
-- users.phone_number for Twilio SMS (verification, welcome message,
-- password reset), but schema.sql's users table never defined the
-- column. Every signup was failing with a caught, hidden DB error
-- ("Something went wrong. Please try again.") because
-- AuthController::register() swallows the real exception.
--
-- Safe to re-run: the column is added only if missing.
-- ============================================================

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'users'
       AND COLUMN_NAME  = 'phone_number'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE users ADD COLUMN phone_number VARCHAR(20) NULL AFTER email',
    'SELECT "phone_number already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
