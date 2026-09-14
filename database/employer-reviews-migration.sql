-- ============================================================
-- TatuJobHub — Employer Ratings & Reviews Migration
-- Run in phpMyAdmin on BOTH local XAMPP and InfinityFree
--
-- app/models/EmployerReview.php, CompanyController::show(), and
-- AdminController's moderation actions already read/write
-- employer_reviews, but schema.sql never defined the table.
-- ============================================================

CREATE TABLE IF NOT EXISTS employer_reviews (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    employer_id      INT UNSIGNED    NOT NULL COMMENT 'References employer_profiles.id',
    seeker_id        INT UNSIGNED    NOT NULL COMMENT 'References job_seeker_profiles.id',
    application_id   INT UNSIGNED    NOT NULL COMMENT 'References applications.id — proves eligibility',
    rating           TINYINT UNSIGNED NOT NULL COMMENT '1-5',
    review_text      TEXT            NULL,
    status           ENUM('visible','hidden') NOT NULL DEFAULT 'visible',
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_review_employer_seeker (employer_id, seeker_id)
      COMMENT 'One review per seeker per employer — matches EmployerReview::hasReviewed()',
    INDEX idx_er_employer_id (employer_id),
    INDEX idx_er_seeker_id (seeker_id),
    INDEX idx_er_status (status),
    CONSTRAINT fk_er_employer
        FOREIGN KEY (employer_id) REFERENCES employer_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_er_seeker
        FOREIGN KEY (seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_er_application
        FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
