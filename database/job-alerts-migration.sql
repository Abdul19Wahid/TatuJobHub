-- ============================================================
-- TatuJobHub — Job Alerts Migration
-- Run in phpMyAdmin on BOTH local XAMPP and InfinityFree
--
-- FIX: schema.sql now already defines salary_min, is_remote, and
-- unsubscribe_token directly on job_alerts (they were folded in after
-- this migration was first written), so re-adding those columns here
-- threw "Duplicate column name 'is_remote'" on any fresh install.
-- Only the unique index below was ever actually missing -- the whole
-- ALTER TABLE previously failed atomically before reaching it, so no
-- one running this against schema.sql actually got the index either.
-- ============================================================

ALTER TABLE job_alerts
    ADD UNIQUE INDEX idx_ja_token (unsubscribe_token);
