-- ============================================================
--  JOB RECRUITMENT PORTAL — Complete Database Schema
--  Compatible with: MySQL 8.0+
--  Encoding: UTF-8 (utf8mb4)
--  Author: Generated for Final Year Project
--  Usage: Run this file in phpMyAdmin or MySQL CLI
--         mysql -u root -p < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS job_portal
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE job_portal;

-- Disable FK checks during setup
SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- DROP TABLES (safe re-run)
-- ============================================================
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS company_verifications;
DROP TABLE IF EXISTS job_listing_skills;
DROP TABLE IF EXISTS seeker_skills;
DROP TABLE IF EXISTS seeker_education;
DROP TABLE IF EXISTS seeker_work_experience;
DROP TABLE IF EXISTS job_alerts;
DROP TABLE IF EXISTS interview_schedules;
DROP TABLE IF EXISTS saved_jobs;
DROP TABLE IF EXISTS applications;
DROP TABLE IF EXISTS job_listings;
DROP TABLE IF EXISTS employer_profiles;
DROP TABLE IF EXISTS job_seeker_profiles;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;


-- ============================================================
-- TABLE 1: users
-- Central account table for ALL roles (seeker, employer, admin)
-- ============================================================
CREATE TABLE users (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    full_name       VARCHAR(120)    NOT NULL,
    email           VARCHAR(180)    NOT NULL,
    password_hash   VARCHAR(255)    NOT NULL,
    role            ENUM('seeker','employer','admin') NOT NULL DEFAULT 'seeker',
    is_active       TINYINT(1)      NOT NULL DEFAULT 1,
    email_verified  TINYINT(1)      NOT NULL DEFAULT 0,
    verify_token    VARCHAR(64)     NULL,
    last_login_at   TIMESTAMP       NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    INDEX idx_users_role (role),
    INDEX idx_users_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Central user accounts for all roles';


-- ============================================================
-- TABLE 2: password_resets
-- Stores one-time tokens for forgot-password flow
-- ============================================================
CREATE TABLE password_resets (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    token       VARCHAR(64)     NOT NULL,
    expires_at  TIMESTAMP       NOT NULL,
    used        TINYINT(1)      NOT NULL DEFAULT 0,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_pr_token (token),
    INDEX idx_pr_user_id (user_id),
    CONSTRAINT fk_pr_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Password reset tokens';


-- ============================================================
-- TABLE 3: job_seeker_profiles
-- Extended profile data for job seekers (one row per seeker)
-- ============================================================
CREATE TABLE job_seeker_profiles (
    id                  INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED        NOT NULL,
    headline            VARCHAR(160)        NULL     COMMENT 'e.g. "Full-Stack Developer | 3 Years Experience"',
    bio                 TEXT                NULL,
    phone               VARCHAR(20)         NULL,
    location_city       VARCHAR(80)         NULL,
    location_country    VARCHAR(80)         NULL,
    date_of_birth       DATE                NULL,
    gender              ENUM('male','female','other','prefer_not_to_say') NULL,
    years_experience    TINYINT UNSIGNED    NOT NULL DEFAULT 0,
    expected_salary_min DECIMAL(12,2)       NULL,
    expected_salary_max DECIMAL(12,2)       NULL,
    salary_currency     VARCHAR(5)          NOT NULL DEFAULT 'USD',
    job_type_pref       SET('full_time','part_time','contract','remote','internship') NULL,
    avatar_path         VARCHAR(300)        NULL,
    resume_path         VARCHAR(300)        NULL,
    resume_original_name VARCHAR(200)       NULL,
    linkedin_url        VARCHAR(300)        NULL,
    github_url          VARCHAR(300)        NULL,
    portfolio_url       VARCHAR(300)        NULL,
    is_open_to_work     TINYINT(1)          NOT NULL DEFAULT 1,
    profile_views       INT UNSIGNED        NOT NULL DEFAULT 0,
    created_at          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_jsp_user_id (user_id),
    INDEX idx_jsp_location (location_city, location_country),
    INDEX idx_jsp_open_to_work (is_open_to_work),
    INDEX idx_jsp_experience (years_experience),
    CONSTRAINT fk_jsp_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Extended profiles for job seekers';


-- ============================================================
-- TABLE 4: seeker_work_experience
-- Multiple work history entries per seeker
-- ============================================================
CREATE TABLE seeker_work_experience (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    seeker_id       INT UNSIGNED    NOT NULL,
    job_title       VARCHAR(120)    NOT NULL,
    company_name    VARCHAR(120)    NOT NULL,
    location        VARCHAR(120)    NULL,
    start_date      DATE            NOT NULL,
    end_date        DATE            NULL     COMMENT 'NULL means currently working here',
    is_current      TINYINT(1)      NOT NULL DEFAULT 0,
    description     TEXT            NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_swe_seeker_id (seeker_id),
    CONSTRAINT fk_swe_seeker
        FOREIGN KEY (seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Work experience history for seekers';


-- ============================================================
-- TABLE 5: seeker_education
-- Multiple education entries per seeker
-- ============================================================
CREATE TABLE seeker_education (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    seeker_id       INT UNSIGNED    NOT NULL,
    institution     VARCHAR(160)    NOT NULL,
    degree          VARCHAR(120)    NOT NULL  COMMENT 'e.g. BSc Computer Science',
    field_of_study  VARCHAR(120)    NULL,
    start_year      YEAR            NOT NULL,
    end_year        YEAR            NULL     COMMENT 'NULL if still studying',
    is_current      TINYINT(1)      NOT NULL DEFAULT 0,
    grade           VARCHAR(40)     NULL,
    description     TEXT            NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_se_seeker_id (seeker_id),
    CONSTRAINT fk_se_seeker
        FOREIGN KEY (seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Education history for seekers';


-- ============================================================
-- TABLE 6: seeker_skills
-- Skills tagged on a seeker profile
-- ============================================================
CREATE TABLE seeker_skills (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    seeker_id   INT UNSIGNED    NOT NULL,
    skill_name  VARCHAR(80)     NOT NULL,
    proficiency ENUM('beginner','intermediate','advanced','expert') NOT NULL DEFAULT 'intermediate',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_ss_seeker_skill (seeker_id, skill_name),
    INDEX idx_ss_skill_name (skill_name),
    CONSTRAINT fk_ss_seeker
        FOREIGN KEY (seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Skills attached to seeker profiles';


-- ============================================================
-- TABLE 7: employer_profiles
-- Company profile for employers (one row per employer user)
-- ============================================================
CREATE TABLE employer_profiles (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED    NOT NULL,
    company_name        VARCHAR(160)    NOT NULL,
    slug                VARCHAR(180)    NULL     COMMENT 'URL-friendly company name',
    industry            VARCHAR(100)    NULL,
    company_size        ENUM('1-10','11-50','51-200','201-500','501-1000','1000+') NULL,
    founded_year        YEAR            NULL,
    website             VARCHAR(300)    NULL,
    phone               VARCHAR(30)     NULL,
    email               VARCHAR(180)    NULL     COMMENT 'Company contact email',
    location_city       VARCHAR(80)     NULL,
    location_country    VARCHAR(80)     NULL,
    full_address        VARCHAR(300)    NULL,
    description         TEXT            NULL,
    logo_path           VARCHAR(300)    NULL,
    cover_image_path    VARCHAR(300)    NULL,
    linkedin_url        VARCHAR(300)    NULL,
    twitter_url         VARCHAR(300)    NULL,
    verification_status ENUM('pending','approved','rejected','suspended') NOT NULL DEFAULT 'pending',
    is_featured         TINYINT(1)      NOT NULL DEFAULT 0,
    profile_views       INT UNSIGNED    NOT NULL DEFAULT 0,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_ep_user_id (user_id),
    UNIQUE KEY uq_ep_slug (slug),
    INDEX idx_ep_verification (verification_status),
    INDEX idx_ep_industry (industry),
    INDEX idx_ep_location (location_city, location_country),
    CONSTRAINT fk_ep_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Company profiles for employers';


-- ============================================================
-- TABLE 8: company_verifications
-- Admin verification workflow for employer profiles
-- ============================================================
CREATE TABLE company_verifications (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    employer_id     INT UNSIGNED    NOT NULL,
    document_path   VARCHAR(300)    NULL     COMMENT 'Uploaded verification document',
    document_type   VARCHAR(80)     NULL     COMMENT 'e.g. Business Registration Certificate',
    notes           TEXT            NULL     COMMENT 'Applicant notes to admin',
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_remarks   TEXT            NULL     COMMENT 'Admin feedback on decision',
    reviewed_by     INT UNSIGNED    NULL     COMMENT 'Admin user id who reviewed',
    reviewed_at     TIMESTAMP       NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_cv_employer_id (employer_id),
    INDEX idx_cv_status (status),
    CONSTRAINT fk_cv_employer
        FOREIGN KEY (employer_id) REFERENCES employer_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_cv_admin
        FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin verification requests for employer companies';


-- ============================================================
-- TABLE 9: job_listings
-- All job postings on the platform
-- ============================================================
CREATE TABLE job_listings (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    employer_id         INT UNSIGNED    NOT NULL,
    title               VARCHAR(160)    NOT NULL,
    slug                VARCHAR(200)    NULL,
    description         LONGTEXT        NOT NULL,
    requirements        TEXT            NULL,
    responsibilities    TEXT            NULL,
    benefits            TEXT            NULL,
    location_city       VARCHAR(80)     NULL,
    location_country    VARCHAR(80)     NULL,
    is_remote           TINYINT(1)      NOT NULL DEFAULT 0,
    job_type            ENUM('full_time','part_time','contract','internship','freelance','volunteer') NOT NULL DEFAULT 'full_time',
    experience_level    ENUM('entry','junior','mid','senior','lead','executive') NOT NULL DEFAULT 'entry',
    industry            VARCHAR(100)    NULL,
    salary_min          DECIMAL(12,2)   NULL,
    salary_max          DECIMAL(12,2)   NULL,
    salary_currency     VARCHAR(5)      NOT NULL DEFAULT 'USD',
    salary_period       ENUM('hourly','monthly','yearly') NOT NULL DEFAULT 'yearly',
    salary_is_hidden    TINYINT(1)      NOT NULL DEFAULT 0  COMMENT 'Show "Competitive" instead of range',
    vacancies           TINYINT UNSIGNED NOT NULL DEFAULT 1,
    application_deadline DATE           NULL,
    status              ENUM('draft','active','closed','expired','removed') NOT NULL DEFAULT 'draft',
    is_featured         TINYINT(1)      NOT NULL DEFAULT 0,
    view_count          INT UNSIGNED    NOT NULL DEFAULT 0,
    application_count   INT UNSIGNED    NOT NULL DEFAULT 0,
    expires_at          TIMESTAMP       NULL,
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_jl_slug (slug),
    INDEX idx_jl_employer_id (employer_id),
    INDEX idx_jl_status (status),
    INDEX idx_jl_job_type (job_type),
    INDEX idx_jl_experience_level (experience_level),
    INDEX idx_jl_location (location_city, location_country),
    INDEX idx_jl_industry (industry),
    INDEX idx_jl_salary (salary_min, salary_max),
    INDEX idx_jl_created_at (created_at),
    INDEX idx_jl_is_remote (is_remote),
    FULLTEXT INDEX ft_jl_search (title, description, requirements)
      COMMENT 'Enables FULLTEXT search on job listings',
    CONSTRAINT fk_jl_employer
        FOREIGN KEY (employer_id) REFERENCES employer_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='All job postings on the platform';


-- ============================================================
-- TABLE 10: job_listing_skills
-- Skills required or preferred for a job posting
-- ============================================================
CREATE TABLE job_listing_skills (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    job_id      INT UNSIGNED    NOT NULL,
    skill_name  VARCHAR(80)     NOT NULL,
    is_required TINYINT(1)      NOT NULL DEFAULT 1  COMMENT '1=required, 0=nice-to-have',
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_jls_job_skill (job_id, skill_name),
    INDEX idx_jls_skill_name (skill_name),
    CONSTRAINT fk_jls_job
        FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Skills required for each job listing';


-- ============================================================
-- TABLE 11: applications
-- Job applications submitted by seekers
-- ============================================================
CREATE TABLE applications (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    job_id              INT UNSIGNED    NOT NULL,
    seeker_id           INT UNSIGNED    NOT NULL  COMMENT 'References job_seeker_profiles.id',
    cover_letter_path   VARCHAR(300)    NULL,
    cover_letter_text   TEXT            NULL     COMMENT 'Optional typed cover letter',
    resume_snapshot_path VARCHAR(300)   NULL     COMMENT 'Copy of resume at time of application',
    status              ENUM(
                          'applied',
                          'under_review',
                          'shortlisted',
                          'interview_scheduled',
                          'offered',
                          'hired',
                          'rejected',
                          'withdrawn'
                        ) NOT NULL DEFAULT 'applied',
    employer_notes      TEXT            NULL     COMMENT 'Internal recruiter notes (not shown to seeker)',
    rejection_reason    VARCHAR(300)    NULL,
    is_read_by_employer TINYINT(1)      NOT NULL DEFAULT 0,
    applied_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_app_job_seeker (job_id, seeker_id)
      COMMENT 'Prevents duplicate applications to same job',
    INDEX idx_app_job_id (job_id),
    INDEX idx_app_seeker_id (seeker_id),
    INDEX idx_app_status (status),
    INDEX idx_app_applied_at (applied_at),
    CONSTRAINT fk_app_job
        FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE,
    CONSTRAINT fk_app_seeker
        FOREIGN KEY (seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Job applications from seekers to job listings';


-- ============================================================
-- TABLE 12: saved_jobs
-- Jobs bookmarked / saved by seekers
-- ============================================================
CREATE TABLE saved_jobs (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    seeker_id   INT UNSIGNED    NOT NULL,
    job_id      INT UNSIGNED    NOT NULL,
    saved_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_sj_seeker_job (seeker_id, job_id),
    INDEX idx_sj_seeker_id (seeker_id),
    INDEX idx_sj_job_id (job_id),
    CONSTRAINT fk_sj_seeker
        FOREIGN KEY (seeker_id) REFERENCES job_seeker_profiles(id) ON DELETE CASCADE,
    CONSTRAINT fk_sj_job
        FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Jobs bookmarked by seekers';


-- ============================================================
-- TABLE 13: interview_schedules
-- Interview bookings linked to an application
-- ============================================================
CREATE TABLE interview_schedules (
    id              INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    application_id  INT UNSIGNED    NOT NULL,
    scheduled_at    DATETIME        NOT NULL,
    duration_mins   SMALLINT        NOT NULL DEFAULT 60,
    interview_type  ENUM('phone','video','on_site','technical','panel') NOT NULL DEFAULT 'video',
    meeting_link    VARCHAR(500)    NULL  COMMENT 'Zoom/Meet/Teams URL',
    location_address VARCHAR(300)   NULL  COMMENT 'Physical address for on-site interviews',
    instructions    TEXT            NULL  COMMENT 'Notes sent to the candidate',
    status          ENUM('scheduled','completed','cancelled','rescheduled','no_show') NOT NULL DEFAULT 'scheduled',
    cancelled_reason VARCHAR(300)   NULL,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_is_application_id (application_id),
    INDEX idx_is_scheduled_at (scheduled_at),
    INDEX idx_is_status (status),
    CONSTRAINT fk_is_application
        FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Interview scheduling linked to applications';


-- ============================================================
-- TABLE 14: job_alerts
-- Seeker subscriptions to keyword/location-based job alerts
-- ============================================================
CREATE TABLE job_alerts (
    id                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id             INT UNSIGNED    NOT NULL,
    alert_name          VARCHAR(120)    NULL     COMMENT 'User-given name e.g. "PHP Jobs in Accra"',
    keywords            VARCHAR(300)    NULL,
    location            VARCHAR(120)    NULL,
    job_type            ENUM('full_time','part_time','contract','internship','freelance','any') NOT NULL DEFAULT 'any',
    experience_level    ENUM('entry','junior','mid','senior','lead','any') NOT NULL DEFAULT 'any',
    salary_min          DECIMAL(12,2)   NULL,
    is_remote           TINYINT(1)      NOT NULL DEFAULT 0,
    unsubscribe_token   VARCHAR(64)    NULL     DEFAULT NULL,
    industry            VARCHAR(100)    NULL,
    frequency           ENUM('instant','daily','weekly') NOT NULL DEFAULT 'daily',
    is_active           TINYINT(1)      NOT NULL DEFAULT 1,
    last_sent_at        TIMESTAMP      NULL,
    created_at          TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_ja_user_id (user_id),
    INDEX idx_ja_is_active (is_active),
    INDEX idx_ja_frequency (frequency),
    CONSTRAINT fk_ja_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Job alert subscriptions for seekers';


-- ============================================================
-- TABLE 15: notifications
-- In-app notification log for all users
-- ============================================================
CREATE TABLE notifications (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED    NOT NULL,
    type        ENUM(
                  'application_received',
                  'application_status_changed',
                  'shortlisted',
                  'interview_scheduled',
                  'interview_cancelled',
                  'job_alert',
                  'company_approved',
                  'company_rejected',
                  'job_expired',
                  'system'
                ) NOT NULL,
    title       VARCHAR(200)    NOT NULL,
    message     TEXT            NOT NULL,
    link        VARCHAR(500)    NULL     COMMENT 'URL to navigate to on click',
    is_read     TINYINT(1)      NOT NULL DEFAULT 0,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_notif_user_id (user_id),
    INDEX idx_notif_is_read (is_read),
    INDEX idx_notif_created_at (created_at),
    CONSTRAINT fk_notif_user
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='In-app notifications for all user roles';


-- ============================================================
-- TABLE 16: admin_logs
-- Audit trail for all admin actions
-- ============================================================
CREATE TABLE admin_logs (
    id          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    admin_id    INT UNSIGNED    NULL     COMMENT 'NULL if admin account deleted',
    action      VARCHAR(100)    NOT NULL COMMENT 'e.g. approve_company, delete_job, ban_user',
    entity_type VARCHAR(60)     NULL     COMMENT 'Table name: users, job_listings, etc.',
    entity_id   INT UNSIGNED    NULL     COMMENT 'ID of the affected record',
    description TEXT            NULL     COMMENT 'Human-readable summary of the action',
    ip_address  VARCHAR(45)     NULL,
    created_at  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    INDEX idx_al_admin_id (admin_id),
    INDEX idx_al_action (action),
    INDEX idx_al_entity (entity_type, entity_id),
    INDEX idx_al_created_at (created_at),
    CONSTRAINT fk_al_admin
        FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Audit log for all administrator actions';


-- ============================================================
-- SEED DATA — Demo records for testing
-- ============================================================

-- Admin user  (password: Admin@1234)
-- FIX: previous hash was stale and didn't match ANY of the three documented
-- demo passwords -- nobody could log into any seeded account. Regenerated
-- with password_hash(..., PASSWORD_BCRYPT, ['cost'=>12]), one hash per account.
INSERT INTO users (full_name, email, password_hash, role, is_active, email_verified)
VALUES (
  'Portal Admin',
  'admin@jobportal.com',
  '$2y$12$oqG0.ZJDshNK2NdYrLgedO/u0Ts37sLKXCYaVTa4AVO9et.16mo6y',
  'admin', 1, 1
);

-- Demo job seeker  (password: Seeker@1234)
INSERT INTO users (full_name, email, password_hash, role, is_active, email_verified)
VALUES (
  'Kwame Asante',
  'kwame@example.com',
  '$2y$12$UqNZF9S5PY1wAa7CdXOX5eFYQhZk6bWMIdSUVDkw4i4ay3e.oCCFi',
  'seeker', 1, 1
);

-- Demo employer  (password: Employer@1234)
INSERT INTO users (full_name, email, password_hash, role, is_active, email_verified)
VALUES (
  'Ama Owusu',
  'ama@techcorp.com',
  '$2y$12$bnhcolg62n64Kpc7RqaH1uKmytYmqx2WzFqO/QcYieATyztZxfGyS',
  'employer', 1, 1
);

-- Seeker profile
INSERT INTO job_seeker_profiles
  (user_id, headline, bio, phone, location_city, location_country,
   years_experience, expected_salary_min, expected_salary_max, salary_currency,
   job_type_pref, is_open_to_work)
VALUES
  (2, 'PHP Developer | 3 Years Experience | Open to Remote',
   'Passionate web developer with 3 years of experience building PHP applications.',
   '+233 20 000 0000', 'Accra', 'Ghana',
   3, 2000.00, 4000.00, 'USD', 'full_time,remote', 1);

-- Seeker skill tags
INSERT INTO seeker_skills (seeker_id, skill_name, proficiency) VALUES
  (1, 'PHP',        'advanced'),
  (1, 'MySQL',      'advanced'),
  (1, 'HTML/CSS',   'expert'),
  (1, 'JavaScript', 'intermediate'),
  (1, 'Bootstrap',  'advanced');

-- Seeker education
INSERT INTO seeker_education
  (seeker_id, institution, degree, field_of_study, start_year, end_year)
VALUES
  (1, 'University of Ghana', 'BSc Information Technology', 'Computer Science', 2018, 2022);

-- Employer profile
INSERT INTO employer_profiles
  (user_id, company_name, slug, industry, company_size, website,
   location_city, location_country, description, verification_status)
VALUES
  (3, 'TechCorp Ghana Ltd', 'techcorp-ghana',
   'Information Technology', '11-50',
   'https://techcorp.example.com',
   'Accra', 'Ghana',
   'TechCorp Ghana is a leading software development firm delivering innovative digital solutions across West Africa.',
   'approved');

-- Company verification record
INSERT INTO company_verifications
  (employer_id, document_type, status, admin_remarks, reviewed_by, reviewed_at)
VALUES
  (1, 'Business Registration Certificate', 'approved',
   'All documents verified successfully.', 1, NOW());

-- Demo job listing 1
INSERT INTO job_listings
  (employer_id, title, slug, description, requirements, responsibilities,
   location_city, location_country, is_remote, job_type, experience_level,
   industry, salary_min, salary_max, salary_currency, salary_period,
   vacancies, status, expires_at)
VALUES (
  1,
  'PHP Backend Developer',
  'php-backend-developer-techcorp-001',
  'We are looking for a skilled PHP Backend Developer to join our growing team in Accra. You will design and build scalable web applications using PHP and MySQL.',
  'Minimum 2 years of PHP experience. Proficiency in MySQL and RESTful API design. Familiarity with MVC frameworks.',
  'Develop and maintain PHP-based web applications. Write clean, tested, and documented code. Collaborate with frontend developers and the QA team.',
  'Accra', 'Ghana', 0,
  'full_time', 'mid',
  'Information Technology',
  2500.00, 4000.00, 'USD', 'monthly',
  2, 'active',
  DATE_ADD(NOW(), INTERVAL 30 DAY)
);

-- Demo job listing 2
INSERT INTO job_listings
  (employer_id, title, slug, description, requirements, responsibilities,
   location_city, location_country, is_remote, job_type, experience_level,
   industry, salary_min, salary_max, salary_currency, salary_period,
   vacancies, status, expires_at)
VALUES (
  1,
  'Frontend Developer (HTML/CSS/JS)',
  'frontend-developer-techcorp-002',
  'Join our product team to build beautiful, responsive user interfaces. You will work closely with our design and backend teams.',
  'Strong HTML5, CSS3, JavaScript skills. Experience with Bootstrap or Tailwind. Basic understanding of REST APIs.',
  'Build responsive web pages from design mockups. Optimise pages for speed and accessibility. Review and improve existing UI components.',
  'Accra', 'Ghana', 1,
  'full_time', 'junior',
  'Information Technology',
  1500.00, 2500.00, 'USD', 'monthly',
  1, 'active',
  DATE_ADD(NOW(), INTERVAL 45 DAY)
);

-- Skills for job listings
INSERT INTO job_listing_skills (job_id, skill_name, is_required) VALUES
  (1, 'PHP',        1),
  (1, 'MySQL',      1),
  (1, 'REST APIs',  1),
  (1, 'Git',        0),
  (2, 'HTML/CSS',   1),
  (2, 'JavaScript', 1),
  (2, 'Bootstrap',  0),
  (2, 'Figma',      0);

-- Demo application
INSERT INTO applications
  (job_id, seeker_id, cover_letter_text, status, is_read_by_employer)
VALUES (
  1, 1,
  'I am very excited to apply for the PHP Backend Developer role at TechCorp Ghana. With 3 years of PHP and MySQL experience and a BSc in Information Technology, I am confident I can contribute meaningfully to your team.',
  'under_review', 1
);

-- Saved job
INSERT INTO saved_jobs (seeker_id, job_id) VALUES (1, 2);

-- Job alert subscription
INSERT INTO job_alerts
  (user_id, alert_name, keywords, location, job_type, frequency)
VALUES
  (2, 'PHP Jobs in Accra', 'PHP developer backend', 'Accra', 'full_time', 'daily');

-- Demo notification
INSERT INTO notifications (user_id, type, title, message, link)
VALUES (
  2,
  'application_status_changed',
  'Your application is under review',
  'TechCorp Ghana is reviewing your application for PHP Backend Developer.',
  '/seeker/applications'
);

-- Admin log entry
INSERT INTO admin_logs (admin_id, action, entity_type, entity_id, description, ip_address)
VALUES (1, 'approve_company', 'employer_profiles', 1, 'Approved TechCorp Ghana Ltd after document verification.', '127.0.0.1');


-- ============================================================
-- USEFUL VIEWS
-- ============================================================

-- View: Active job listings with employer info (for job search page)
CREATE OR REPLACE VIEW vw_active_jobs AS
SELECT
    jl.id,
    jl.title,
    jl.slug,
    jl.location_city,
    jl.location_country,
    jl.is_remote,
    jl.job_type,
    jl.experience_level,
    jl.industry,
    jl.salary_min,
    jl.salary_max,
    jl.salary_currency,
    jl.salary_period,
    jl.salary_is_hidden,
    jl.vacancies,
    jl.application_deadline,
    jl.view_count,
    jl.application_count,
    jl.is_featured,
    jl.created_at,
    jl.expires_at,
    ep.id           AS employer_id,
    ep.company_name,
    ep.logo_path    AS company_logo,
    ep.location_city AS company_city,
    ep.industry     AS company_industry
FROM job_listings jl
INNER JOIN employer_profiles ep ON ep.id = jl.employer_id
WHERE jl.status = 'active'
  AND (jl.expires_at IS NULL OR jl.expires_at > NOW())
  AND ep.verification_status = 'approved';


-- View: Application summary for employer dashboard
CREATE OR REPLACE VIEW vw_application_summary AS
SELECT
    a.id            AS application_id,
    a.status,
    a.applied_at,
    a.is_read_by_employer,
    jl.id           AS job_id,
    jl.title        AS job_title,
    ep.id           AS employer_id,
    jsp.id          AS seeker_profile_id,
    u.full_name     AS seeker_name,
    u.email         AS seeker_email,
    jsp.headline    AS seeker_headline,
    jsp.location_city AS seeker_city,
    jsp.years_experience,
    jsp.avatar_path
FROM applications a
INNER JOIN job_listings jl    ON jl.id  = a.job_id
INNER JOIN employer_profiles ep ON ep.id = jl.employer_id
INNER JOIN job_seeker_profiles jsp ON jsp.id = a.seeker_id
INNER JOIN users u            ON u.id   = jsp.user_id;


-- View: Seeker application tracker
CREATE OR REPLACE VIEW vw_seeker_applications AS
SELECT
    a.id            AS application_id,
    a.status,
    a.applied_at,
    a.updated_at,
    jl.id           AS job_id,
    jl.title        AS job_title,
    jl.job_type,
    jl.location_city,
    jl.is_remote,
    ep.company_name,
    ep.logo_path    AS company_logo,
    isc.scheduled_at AS interview_date,
    isc.interview_type,
    isc.meeting_link
FROM applications a
INNER JOIN job_listings jl         ON jl.id  = a.job_id
INNER JOIN employer_profiles ep    ON ep.id  = jl.employer_id
LEFT  JOIN interview_schedules isc ON isc.application_id = a.id
                                   AND isc.status = 'scheduled';


-- ============================================================
-- STORED PROCEDURES
-- ============================================================

DROP PROCEDURE IF EXISTS sp_increment_job_view;
DROP PROCEDURE IF EXISTS sp_mark_notifications_read;
DROP PROCEDURE IF EXISTS sp_admin_stats;

DELIMITER $$

-- Procedure: Increment job view count (called on job detail page load)
CREATE PROCEDURE sp_increment_job_view(IN p_job_id INT UNSIGNED)
BEGIN
    UPDATE job_listings SET view_count = view_count + 1 WHERE id = p_job_id;
END$$

-- Procedure: Mark all notifications as read for a user
CREATE PROCEDURE sp_mark_notifications_read(IN p_user_id INT UNSIGNED)
BEGIN
    UPDATE notifications SET is_read = 1
    WHERE user_id = p_user_id AND is_read = 0;
END$$

-- Procedure: Get platform statistics for admin dashboard
CREATE PROCEDURE sp_admin_stats()
BEGIN
    SELECT
        (SELECT COUNT(*) FROM users WHERE role = 'seeker')                          AS total_seekers,
        (SELECT COUNT(*) FROM users WHERE role = 'employer')                         AS total_employers,
        (SELECT COUNT(*) FROM job_listings WHERE status = 'active')                  AS active_jobs,
        (SELECT COUNT(*) FROM job_listings)                                          AS total_jobs,
        (SELECT COUNT(*) FROM applications)                                          AS total_applications,
        (SELECT COUNT(*) FROM employer_profiles WHERE verification_status = 'pending') AS pending_verifications,
        (SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE())              AS new_users_today,
        (SELECT COUNT(*) FROM applications WHERE DATE(applied_at) = CURDATE())       AS applications_today;
END$$

DELIMITER ;

-- ============================================================
-- End of schema.sql
-- Total tables   : 16
-- Total views    : 3
-- Total procedures: 3
-- Seed records   : Demo admin, seeker, employer + sample data
-- ============================================================
