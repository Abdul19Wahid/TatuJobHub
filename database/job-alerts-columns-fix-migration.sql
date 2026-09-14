-- ============================================================
-- TatuJobHub — Job Alerts Missing Columns Fix
-- Run in phpMyAdmin on BOTH local XAMPP and InfinityFree
--
-- FIX: some local/live job_alerts tables were created before
-- salary_min, is_remote, and unsubscribe_token were folded into
-- schema.sql, so JobController::fireJobAlerts() fails with
-- "Unknown column 'ja.unsubscribe_token'" (and would fail the
-- same way for salary_min / is_remote if those are also missing).
-- This adds each column only if it isn't already there, then adds
-- the unique index from job-alerts-migration.sql if it isn't
-- already there either. Safe to run repeatedly on any environment.
-- ============================================================

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'job_alerts'
       AND COLUMN_NAME  = 'salary_min'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE job_alerts ADD COLUMN salary_min DECIMAL(12,2) NULL AFTER experience_level',
    'SELECT "salary_min already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'job_alerts'
       AND COLUMN_NAME  = 'is_remote'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE job_alerts ADD COLUMN is_remote TINYINT(1) NOT NULL DEFAULT 0 AFTER salary_min',
    'SELECT "is_remote already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'job_alerts'
       AND COLUMN_NAME  = 'unsubscribe_token'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE job_alerts ADD COLUMN unsubscribe_token VARCHAR(64) NULL DEFAULT NULL AFTER is_remote',
    'SELECT "unsubscribe_token already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'job_alerts'
       AND INDEX_NAME    = 'idx_ja_token'
);
SET @sql := IF(@idx_exists = 0,
    'ALTER TABLE job_alerts ADD UNIQUE INDEX idx_ja_token (unsubscribe_token)',
    'SELECT "idx_ja_token already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Existing rows have unsubscribe_token = NULL, which is fine (NULLs
-- don't collide under a UNIQUE index) — but each alert needs a real
-- token to build unsubscribe links. Backfill any that are missing one.
UPDATE job_alerts
   SET unsubscribe_token = SUBSTRING(MD5(RAND()), 1, 32)
 WHERE unsubscribe_token IS NULL;
