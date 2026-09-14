-- ============================================================
-- Migration: interview response deadline + candidate confirmation
-- ============================================================
-- Adds a response_deadline the employer can set alongside the
-- interview date/time (by when the candidate must confirm or
-- decline), and confirmed_at / declined_at to track the response.
-- Interviews left unanswered past their deadline are lazily
-- flipped to 'expired' by InterviewController rather than a cron job.

-- Safe to re-run: each column/index is added only if missing.

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'interview_schedules'
       AND COLUMN_NAME  = 'response_deadline'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE interview_schedules ADD COLUMN response_deadline DATETIME NULL COMMENT "Deadline for candidate to confirm/decline; NULL = no deadline" AFTER scheduled_at',
    'SELECT "response_deadline already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'interview_schedules'
       AND COLUMN_NAME  = 'confirmed_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE interview_schedules ADD COLUMN confirmed_at DATETIME NULL AFTER status',
    'SELECT "confirmed_at already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'interview_schedules'
       AND COLUMN_NAME  = 'declined_at'
);
SET @sql := IF(@col_exists = 0,
    'ALTER TABLE interview_schedules ADD COLUMN declined_at DATETIME NULL AFTER confirmed_at',
    'SELECT "declined_at already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE interview_schedules
    MODIFY COLUMN status ENUM(
        'scheduled','confirmed','declined','completed',
        'cancelled','rescheduled','no_show','expired'
    ) NOT NULL DEFAULT 'scheduled';

SET @idx_exists := (
    SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = DATABASE()
       AND TABLE_NAME   = 'interview_schedules'
       AND INDEX_NAME    = 'idx_is_response_deadline'
);
SET @sql := IF(@idx_exists = 0,
    'CREATE INDEX idx_is_response_deadline ON interview_schedules (response_deadline)',
    'SELECT "idx_is_response_deadline already exists — skipped"'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
