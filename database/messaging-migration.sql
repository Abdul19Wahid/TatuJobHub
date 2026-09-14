-- ============================================================
--  Messaging System — Database Migration
--  Run this in phpMyAdmin after importing the main schema
--  SQL tab → paste → Go
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ── Conversations (one per employer-seeker pair, optionally linked to a job)
CREATE TABLE IF NOT EXISTS conversations (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    employer_id     INT UNSIGNED    NOT NULL,   -- users.id (role=employer)
    seeker_id       INT UNSIGNED    NOT NULL,   -- users.id (role=seeker)
    job_id          INT UNSIGNED    NULL,        -- optional job context
    last_message_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_conv (employer_id, seeker_id, job_id),
    KEY idx_employer (employer_id),
    KEY idx_seeker   (seeker_id),
    FOREIGN KEY (employer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seeker_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id)      REFERENCES job_listings(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Messages (individual messages within a conversation)
CREATE TABLE IF NOT EXISTS messages (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    conversation_id INT UNSIGNED    NOT NULL,
    sender_id       INT UNSIGNED    NOT NULL,   -- users.id
    body            TEXT            NOT NULL,
    is_read         TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_conv   (conversation_id),
    KEY idx_sender (sender_id),
    KEY idx_unread (conversation_id, is_read),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id)       REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify
-- FIX: 'rows' is a reserved word in MariaDB 10.11+ (used in window-frame
-- syntax), so using it as an unquoted alias threw a syntax error here even
-- though the CREATE TABLE statements above it succeeded fine.
SELECT 'conversations' AS tbl, COUNT(*) AS row_count FROM conversations
UNION ALL
SELECT 'messages',            COUNT(*)               FROM messages;
