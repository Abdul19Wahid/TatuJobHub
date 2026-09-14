-- ============================================================
-- TatuJobHub — Application Status Tracking Migration
-- Run in phpMyAdmin on BOTH local XAMPP and InfinityFree
--
-- ApplicationController::updateStatus() and the employer/seeker
-- applicant views already read/write status_note, status_updated_at,
-- and the application_status_logs audit table, but schema.sql never
-- defined them. This adds what's missing.
--
-- Safe to re-run: the ALTER TABLE columns are added conditionally
-- (plain ADD COLUMN errors with "Duplicate column name" on a second
-- run), and CREATE TABLE already uses IF NOT EXISTS.
-- ============================================================

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'applications'
       AND COLUMN_NAME  = 'status_note'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE applications ADD COLUMN status_note TEXT NULL DEFAULT NULL AFTER rejection_reason',
    'SELECT "status_note already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'applications'
       AND COLUMN_NAME  = 'status_updated_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE applications ADD COLUMN status_updated_at TIMESTAMP NULL DEFAULT NULL AFTER status_note',
    'SELECT "status_updated_at already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS application_status_logs (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    application_id   INT UNSIGNED NOT NULL,
    old_status        VARCHAR(30) NULL,
    new_status        VARCHAR(30) NOT NULL,
    note              TEXT        NULL,
    changed_by        INT UNSIGNED NOT NULL COMMENT 'References users.id',
    changed_at        TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_asl_application_id (application_id),
    INDEX idx_asl_changed_by (changed_by),
    CONSTRAINT fk_asl_application
        FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    CONSTRAINT fk_asl_changed_by
        FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- RECOVERY ONLY — run the two lines below by themselves if you hit
-- "Tablespace is missing for a table" on application_status_logs
-- (InnoDB corruption, e.g. after an unclean MySQL shutdown on XAMPP).
-- This wipes that table's existing rows, so don't run it routinely.
-- ------------------------------------------------------------
-- DROP TABLE IF EXISTS application_status_logs;
-- -- then re-run this whole file to recreate it.

