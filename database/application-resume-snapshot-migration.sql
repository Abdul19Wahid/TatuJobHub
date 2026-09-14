-- ============================================================
-- TatuJobHub — Per-Application Resume Snapshot Fix
-- Run in phpMyAdmin on BOTH local XAMPP and InfinityFree
--
-- schema.sql already has applications.resume_snapshot_path but it
-- was never wired up (applying reused the seeker's profile resume
-- instead). This adds the matching original-filename column so the
-- employer sees a real filename rather than the stored random hash.
--
-- Safe to re-run: the column is added only if missing.
-- ============================================================

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'applications'
       AND COLUMN_NAME  = 'resume_snapshot_original_name'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE applications ADD COLUMN resume_snapshot_original_name VARCHAR(255) NULL AFTER resume_snapshot_path',
    'SELECT "resume_snapshot_original_name already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
