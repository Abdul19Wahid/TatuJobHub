<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/app/models/Application.php';
require_once ROOT_PATH . '/app/models/SeekerProfile.php';
require_once ROOT_PATH . '/app/helpers/FileUpload.php';
require_once ROOT_PATH . '/app/helpers/NotificationService.php';

class ApplicationController extends Controller
{
    private Application   $apps;
    private SeekerProfile $profiles;

    public function __construct()
    {
        $this->apps     = new Application();
        $this->profiles = new SeekerProfile();
    }

    // ── Seeker: list all applications ─────────────────────────────────────────
    public function seekerIndex(): void
    {
        $this->requireRole('seeker');
        $profile = $this->profiles->getByUserId(Session::id());
        if (!$profile) $this->abort(404);

        $result = $this->apps->getForSeeker($profile['id'], $this->currentPage(), 10);

        $this->view('seeker/applications', [
            'title'        => 'My Applications',
            'profile'      => $profile,
            'applications' => $result['data'],
            'paging'       => $result,
        ], 'seeker');
    }

    // ── Seeker: apply for a job ───────────────────────────────────────────────
    public function apply(array $params): void
    {
        $this->requireRole('seeker');
        $this->verifyCsrf();

        $jobId   = (int)($params['jobId'] ?? 0);
        $profile = $this->profiles->getByUserId(Session::id());

        if (!$profile) { $this->flash('error', 'Profile not found.'); $this->back(); return; }

        // Check job exists and is active
        $db  = Database::getInstance();
        $job = $db->fetchOne("SELECT * FROM job_listings WHERE id = ? AND status = 'active'", [$jobId]);
        if (!$job) {
            $this->flash('error', 'This job is no longer available.');
            $this->back();
            return;
        }

        // Prevent duplicate application
        if ($this->apps->hasApplied($profile['id'], $jobId)) {
            $this->flash('error', 'You have already applied for this job.');
            $this->back();
            return;
        }

        /*
         * FIX (2026-08-14): Resume is required on every application, even if
         * the seeker already has one on their profile — they must attach a
         * fresh copy for this specific job. A profile resume is no longer
         * sufficient by itself; $_FILES['resume_file'] must be present.
         */
        if (empty($_FILES['resume_file']) || $_FILES['resume_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->flash('error', 'Please upload your resume to apply for this job.');
            $this->back();
            return;
        }
        $resumeResult = FileUpload::upload($_FILES['resume_file'], 'resumes');
        if (!$resumeResult['ok']) {
            $this->flash('error', 'Resume upload failed: ' . $resumeResult['error']);
            $this->back();
            return;
        }
        $resumeSnapshotPath         = $resumeResult['path'];
        $resumeSnapshotOriginalName = $resumeResult['original_name'];

        // Handle optional cover letter file upload
        $coverLetterPath = null;
        if (!empty($_FILES['cover_letter']) && $_FILES['cover_letter']['error'] === UPLOAD_ERR_OK) {
            $result = FileUpload::upload($_FILES['cover_letter'], 'cover-letters');
            if (!$result['ok']) {
                $this->flash('error', 'Cover letter upload failed: ' . $result['error']);
                $this->back();
                return;
            }
            $coverLetterPath = $result['path'];
        }

        $coverLetterText = trim($this->input('cover_letter_text'));

        $appId = $this->apps->apply(
            $profile['id'],
            $jobId,
            $coverLetterPath,
            $coverLetterText ?: null,
            $resumeSnapshotPath,
            $resumeSnapshotOriginalName
        );

        // Notify employer via NotificationService (in-app + email)
        $ep = $db->fetchOne("SELECT user_id FROM employer_profiles WHERE id = ?", [$job['employer_id']]);
        if ($ep) {
            NotificationService::applicationReceived(
                $job['title'],
                $profile['full_name'],
                $ep['user_id'],
                $jobId
            );
        }

        $this->flash('success', '✓ Application submitted successfully!');
        $this->redirect(BASE_URL . '/seeker/applications');
    }

    // ── Seeker: withdraw application ─────────────────────────────────────────
    public function withdraw(array $params): void
    {
        $this->requireRole('seeker');
        $this->verifyCsrf();

        $appId   = (int)($params['appId'] ?? 0);
        $profile = $this->profiles->getByUserId(Session::id());

        if (!$profile) { $this->flash('error', 'Profile not found.'); $this->back(); return; }

        if ($this->apps->withdraw($appId, $profile['id'])) {
            $this->flash('success', 'Application withdrawn.');
        } else {
            $this->flash('error', 'Could not withdraw this application.');
        }

        $this->redirect(BASE_URL . '/seeker/applications');
    }

    // ── Employer: list applicants for a job ───────────────────────────────────
    public function employerIndex(array $params): void
    {
        $this->requireRole('employer');
        $jobId = (int)($params['id'] ?? 0);

        $db = Database::getInstance();

        /*
         * FIX (2026-07-16): Ownership check added.
         * Previously this loaded ANY job by ID with no check that the
         * logged-in employer actually owns it, so any employer could view
         * another company's applicant list by editing the job ID in the URL.
         */
        $employerId = $db->fetchColumn(
            "SELECT id FROM employer_profiles WHERE user_id = ?",
            [Session::id()]
        );
        if (!$employerId) $this->abort(404);

        $job = $db->fetchOne(
            "SELECT * FROM job_listings WHERE id = ? AND employer_id = ?",
            [$jobId, $employerId]
        );
        if (!$job) $this->abort(404);

        $applicants = $db->fetchAll(
            "SELECT a.*, u.full_name AS seeker_name, u.email AS seeker_email,
                    jsp.headline, jsp.avatar_path, jsp.years_experience,
                    jsp.location_city, jsp.resume_path
               FROM applications a
               JOIN job_seeker_profiles jsp ON jsp.id  = a.seeker_id
               JOIN users u                ON u.id    = jsp.user_id
              WHERE a.job_id = ?
              ORDER BY a.applied_at DESC",
            [$jobId]
        );

        // Mark all as read by employer
        $db->update('applications', ['is_read_by_employer' => 1], 'job_id = ?', [$jobId]);

        $this->view('employer/applicants', [
            'title'      => 'Applicants — ' . $job['title'],
            'job'        => $job,
            'applicants' => $applicants,
        ], 'employer');
    }

    // ── Employer: view single application ─────────────────────────────────────
    public function show(array $params): void
    {
        $this->requireRole('employer');
        $appId = (int)($params['id'] ?? 0);

        $db  = Database::getInstance();

        /*
         * FIX (2026-07-16): Ownership check added here too — same class of
         * bug as employerIndex(). Without this, any employer could view a
         * candidate's full application detail (resume, contact info, cover
         * letter) for a job that isn't theirs, just by guessing/incrementing
         * an application ID in the URL.
         */
        $employerId = $db->fetchColumn(
            "SELECT id FROM employer_profiles WHERE user_id = ?",
            [Session::id()]
        );
        if (!$employerId) $this->abort(404);

        $app = $db->fetchOne(
            "SELECT a.*, u.full_name AS seeker_name, u.email AS seeker_email,
                    jsp.headline, jsp.bio, jsp.avatar_path, jsp.years_experience,
                    jsp.location_city, jsp.resume_path, jsp.resume_original_name,
                    jsp.linkedin_url, jsp.github_url, jsp.portfolio_url,
                    jl.title AS job_title
               FROM applications a
               JOIN job_seeker_profiles jsp ON jsp.id  = a.seeker_id
               JOIN users u                ON u.id    = jsp.user_id
               JOIN job_listings jl        ON jl.id   = a.job_id
              WHERE a.id = ? AND jl.employer_id = ? LIMIT 1",
            [$appId, $employerId]
        );
        if (!$app) $this->abort(404);

        $skills = $db->fetchAll(
            "SELECT * FROM seeker_skills WHERE seeker_id = ? ORDER BY skill_name",
            [$app['seeker_id']]
        );

        $this->view('employer/application-detail', [
            'title'  => 'Application — ' . $app['seeker_name'],
            'app'    => $app,
            'skills' => $skills,
        ], 'employer');
    }

    // ── Employer: update application status ───────────────────────────────────
    public function updateStatus(array $params): void
    {
        $this->requireRole('employer');
        $this->verifyCsrf();

        $appId  = (int)($params['id'] ?? 0);
        $status = $this->input('status');
        $note   = trim($this->input('status_note') ?? '');
        if ($note === '') $note = null;

        $allowed = ['under_review','shortlisted','interview_scheduled','offered','hired','rejected'];
        if (!in_array($status, $allowed)) {
            $this->flash('error', 'Invalid status.'); $this->back(); return;
        }

        $db = Database::getInstance();

        /*
         * FIX (2026-07-16): Ownership check added — without this an employer
         * could change the status of an application belonging to a job that
         * isn't theirs, just by posting a different application ID.
         */
        $employerId = $db->fetchColumn(
            "SELECT id FROM employer_profiles WHERE user_id = ?",
            [Session::id()]
        );
        if (!$employerId) { $this->abort(404); return; }

        $app = $db->fetchOne(
            "SELECT a.* FROM applications a
             JOIN job_listings jl ON jl.id = a.job_id
             WHERE a.id = ? AND jl.employer_id = ?",
            [$appId, $employerId]
        );
        if (!$app) $this->abort(404);

        $oldStatus = $app['status'];
        $now       = date('Y-m-d H:i:s');

        // Update application with status, note, and timestamp
        $db->update('applications', [
            'status'            => $status,
            'status_note'       => $note,
            'status_updated_at' => $now,
            'updated_at'        => $now,
        ], 'id = ?', [$appId]);

        // Write to audit log
        $db->insert('application_status_logs', [
            'application_id' => $appId,
            'old_status'     => $oldStatus,
            'new_status'     => $status,
            'note'           => $note,
            'changed_by'     => Session::id(),
            'changed_at'     => $now,
        ]);

        // Notify seeker via NotificationService (in-app + SMS/email)
        $jsp = $db->fetchOne("SELECT user_id FROM job_seeker_profiles WHERE id = ?", [$app['seeker_id']]);
        $jl  = $db->fetchOne(
            "SELECT jl.title, ep.company_name FROM job_listings jl
             JOIN employer_profiles ep ON ep.id = jl.employer_id
             WHERE jl.id = ?",
            [$app['job_id']]
        );
        if ($jsp && $jl) {
            NotificationService::statusChanged(
                $status,
                $jl['title'],
                $jl['company_name'],
                $jsp['user_id'],
                $note
            );
        }

        $this->flash('success', 'Status updated to ' . status_label($status) . '.');
        $this->back();
    }

    // ── Employer: save internal notes ─────────────────────────────────────────
    public function updateNotes(array $params): void
    {
        $this->requireRole('employer');
        $this->verifyCsrf();

        $appId = (int)($params['id'] ?? 0);
        $db    = Database::getInstance();

        /*
         * FIX (2026-07-16): Ownership check added — same reasoning as
         * updateStatus() above.
         */
        $employerId = $db->fetchColumn(
            "SELECT id FROM employer_profiles WHERE user_id = ?",
            [Session::id()]
        );
        if (!$employerId) { $this->abort(404); return; }

        $owns = $db->fetchColumn(
            "SELECT a.id FROM applications a
             JOIN job_listings jl ON jl.id = a.job_id
             WHERE a.id = ? AND jl.employer_id = ?",
            [$appId, $employerId]
        );
        if (!$owns) { $this->abort(404); return; }

        $db->update('applications',
            ['employer_notes' => $this->input('employer_notes'), 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$appId]
        );

        $this->flash('success', 'Notes saved.');
        $this->back();
    }

    // ── Apply by job slug ─────────────────────────────────────────────────────

    public function applyBySlug(array $params): void
    {
        $this->requireRole('seeker');
        $this->verifyCsrf();

        $slug = $params['slug'] ?? '';
        $db   = Database::getInstance();
        $job  = $db->fetchOne(
            "SELECT * FROM job_listings WHERE slug = ? AND status = 'active'",
            [$slug]
        );

        if (!$job) {
            $this->flash('error', 'This job listing is no longer available.');
            $this->redirect(BASE_URL . '/jobs');
            return;
        }

        // Use the existing apply() method via jobId param
        $params['jobId'] = $job['id'];
        $this->apply($params);
    }

}
