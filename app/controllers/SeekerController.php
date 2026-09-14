<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Validator.php';
require_once ROOT_PATH . '/app/models/SeekerProfile.php';
require_once ROOT_PATH . '/app/models/Application.php';
require_once ROOT_PATH . '/app/models/EmployerReview.php';
require_once ROOT_PATH . '/app/models/Notification.php';
require_once ROOT_PATH . '/app/helpers/FileUpload.php';

class SeekerController extends Controller
{
    private SeekerProfile  $profiles;
    private Application    $apps;
    private EmployerReview $reviews;
    private Notification   $notifications;

    public function __construct()
    {
        // Note: requireRole('seeker') was removed from here.
        // All /seeker/* routes already enforce role via middleware in routes.php.
        // Keeping it here would block unsubscribeAlert() which is a public route.
        $this->profiles      = new SeekerProfile();
        $this->apps          = new Application();
        $this->reviews       = new EmployerReview();
        $this->notifications = new Notification();
    }

    // ── Helpers ───────────────────────────────────────────────────────────────
    private function seekerProfile(): array
    {
        $p = $this->profiles->getByUserId(Session::id());
        if (!$p) $this->abort(404);
        return $p;
    }

    // =========================================================================
    // DASHBOARD
    // =========================================================================
    public function dashboard(): void
    {
        $profile  = $this->seekerProfile();
        $seekerId = $profile['id'];

        $statusCounts   = $this->apps->countByStatus($seekerId);
        $recentApps     = $this->apps->getRecent($seekerId, 5);
        $nextInterview  = $this->apps->getNextInterview($seekerId);
        $reminderCount  = $this->notifications->countUnreadByType(Session::id(), Notification::TYPE_INTERVIEW_REMINDER);

        $skills  = $this->profiles->getSkills($seekerId);
        $exp     = $this->profiles->getExperience($seekerId);
        $edu     = $this->profiles->getEducation($seekerId);
        $score   = $this->profiles->completionScore($profile, $skills, $exp, $edu);

        $this->view('seeker/dashboard', [
            'title'          => 'My Dashboard',
            'profile'        => $profile,
            'statusCounts'   => $statusCounts,
            'recentApps'     => $recentApps,
            'nextInterview'  => $nextInterview,
            'reminderCount'  => $reminderCount,
            'score'          => $score,
            'skills'         => $skills,
        ], 'seeker');
    }

    // =========================================================================
    // PROFILE
    // =========================================================================
    public function profile(): void
    {
        $profile  = $this->seekerProfile();
        $seekerId = $profile['id'];

        $skills = $this->profiles->getSkills($seekerId);
        $exp    = $this->profiles->getExperience($seekerId);
        $edu    = $this->profiles->getEducation($seekerId);
        $score  = $this->profiles->completionScore($profile, $skills, $exp, $edu);

        $this->view('seeker/profile', [
            'title'   => 'My Profile',
            'profile' => $profile,
            'skills'  => $skills,
            'exp'     => $exp,
            'edu'     => $edu,
            'score'   => $score,
        ], 'seeker');
    }

    public function updateProfile(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'headline'         => 'max:160',
            'bio'              => 'max:1000',
            'phone'            => 'max:20',
            'location_city'    => 'max:80',
            'location_country' => 'max:80',
            'years_experience' => 'numeric|min_val:0|max_val:50',
            'linkedin_url'     => 'max:300',
            'github_url'       => 'max:300',
            'portfolio_url'    => 'max:300',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $fields = [
            'headline', 'bio', 'phone', 'location_city', 'location_country',
            'years_experience', 'expected_salary_min', 'expected_salary_max',
            'salary_currency', 'linkedin_url', 'github_url', 'portfolio_url',
            'is_open_to_work',
        ];

        $data = [];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) {
                $data[$f] = trim($_POST[$f]) === '' ? null : trim($_POST[$f]);
            }
        }
        $data['is_open_to_work'] = isset($_POST['is_open_to_work']) ? 1 : 0;

        // Also update full_name in users table
        if (!empty($_POST['full_name'])) {
            $db = Database::getInstance();
            $db->update('users',
                ['full_name' => trim($_POST['full_name'])],
                'id = ?', [Session::id()]
            );
            // Refresh session name
            $_SESSION['user']['name'] = trim($_POST['full_name']);
        }

        $this->profiles->updateByUserId(Session::id(), $data);
        $this->flash('success', 'Profile updated successfully!');
        $this->redirect(BASE_URL . '/seeker/profile');
    }

    // =========================================================================
    // AVATAR UPLOAD
    // =========================================================================
    public function uploadAvatar(): void
    {
        $this->verifyCsrf();

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->flash('error', 'Please select an image to upload.');
            $this->back();
            return;
        }

        $result = FileUpload::upload($_FILES['avatar'], 'avatars');

        if (!$result['ok']) {
            $this->flash('error', $result['error']);
            $this->back();
            return;
        }

        // Delete old avatar
        $profile = $this->seekerProfile();
        if ($profile['avatar_path']) {
            FileUpload::delete($profile['avatar_path']);
        }

        $this->profiles->updateAvatar(Session::id(), $result['path']);
        $this->flash('success', 'Profile photo updated!');
        $this->redirect(BASE_URL . '/seeker/profile');
    }

    // =========================================================================
    // RESUME UPLOAD
    // =========================================================================
    public function uploadResume(): void
    {
        $this->verifyCsrf();

        if (empty($_FILES['resume']) || $_FILES['resume']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->flash('error', 'Please select a file to upload.');
            $this->back();
            return;
        }

        $result = FileUpload::upload($_FILES['resume'], 'resumes');

        if (!$result['ok']) {
            $this->flash('error', $result['error']);
            $this->back();
            return;
        }

        // Delete old resume
        $profile = $this->seekerProfile();
        if ($profile['resume_path']) {
            FileUpload::delete($profile['resume_path']);
        }

        $this->profiles->updateResume(
            Session::id(),
            $result['path'],
            $result['original_name']
        );

        $this->flash('success', 'Resume uploaded successfully!');
        $this->redirect(BASE_URL . '/seeker/profile');
    }

    // =========================================================================
    // SKILLS
    // =========================================================================
    public function addSkill(): void
    {
        $this->verifyCsrf();

        $skill = trim($this->input('skill_name'));
        $prof  = $this->input('proficiency', 'intermediate');

        if (empty($skill)) {
            $this->flash('error', 'Skill name cannot be empty.');
            $this->back();
            return;
        }

        if (strlen($skill) > 80) {
            $this->flash('error', 'Skill name is too long (max 80 characters).');
            $this->back();
            return;
        }

        $allowed = ['beginner', 'intermediate', 'advanced', 'expert'];
        if (!in_array($prof, $allowed)) $prof = 'intermediate';

        $profile = $this->seekerProfile();
        $this->profiles->addSkill($profile['id'], $skill, $prof);

        $this->flash('success', "Skill \"{$skill}\" added!");
        $this->redirect(BASE_URL . '/seeker/profile#skills');
    }

    public function deleteSkill(): void
    {
        $this->verifyCsrf();
        $profile = $this->seekerProfile();
        $skillId = (int)$this->input('skill_id');
        $this->profiles->deleteSkill($skillId, $profile['id']);
        $this->flash('success', 'Skill removed.');
        $this->redirect(BASE_URL . '/seeker/profile#skills');
    }

    // =========================================================================
    // WORK EXPERIENCE
    // =========================================================================
    public function addExperience(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'job_title'    => 'required|max:120',
            'company_name' => 'required|max:120',
            'start_date'   => 'required|date',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $profile = $this->seekerProfile();
        $this->profiles->addExperience($profile['id'], $_POST);
        $this->flash('success', 'Work experience added!');
        $this->redirect(BASE_URL . '/seeker/profile#experience');
    }

    public function deleteExperience(): void
    {
        $this->verifyCsrf();
        $profile = $this->seekerProfile();
        $expId   = (int)$this->input('exp_id');
        $this->profiles->deleteExperience($expId, $profile['id']);
        $this->flash('success', 'Experience entry removed.');
        $this->redirect(BASE_URL . '/seeker/profile#experience');
    }

    // =========================================================================
    // EDUCATION
    // =========================================================================
    public function addEducation(): void
    {
        $this->verifyCsrf();

        $v = Validator::make($_POST, [
            'institution' => 'required|max:160',
            'degree'      => 'required|max:120',
            'start_year'  => 'required|numeric|min_val:1950|max_val:2030',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $profile = $this->seekerProfile();
        $this->profiles->addEducation($profile['id'], $_POST);
        $this->flash('success', 'Education added!');
        $this->redirect(BASE_URL . '/seeker/profile#education');
    }

    public function deleteEducation(): void
    {
        $this->verifyCsrf();
        $profile = $this->seekerProfile();
        $eduId   = (int)$this->input('edu_id');
        $this->profiles->deleteEducation($eduId, $profile['id']);
        $this->flash('success', 'Education entry removed.');
        $this->redirect(BASE_URL . '/seeker/profile#education');
    }

    // =========================================================================
    // APPLICATIONS TRACKER
    // =========================================================================
    public function applications(): void
    {
        $profile = $this->seekerProfile();
        $result  = $this->apps->getForSeeker($profile['id'], $this->currentPage(), 10);

        $this->view('seeker/applications', [
            'title'   => 'My Applications',
            'profile' => $profile,
            'apps'    => $result['data'],
            'paging'  => $result,
        ], 'seeker');
    }

    // =========================================================================
    // SAVED JOBS
    // =========================================================================
    public function savedJobs(): void
    {
        require_once ROOT_PATH . '/app/models/Job.php';

        $profile  = $this->seekerProfile();
        $db       = Database::getInstance();
        $saved    = $db->fetchAll(
            "SELECT sj.*, jl.title, jl.job_type, jl.location_city, jl.is_remote,
                    jl.salary_min, jl.salary_max, jl.salary_currency, jl.salary_is_hidden,
                    jl.slug AS job_slug, jl.status AS job_status,
                    ep.company_name, ep.logo_path AS company_logo
               FROM saved_jobs sj
               JOIN job_listings jl      ON jl.id  = sj.job_id
               JOIN employer_profiles ep ON ep.id  = jl.employer_id
              WHERE sj.seeker_id = ?
              ORDER BY sj.saved_at DESC",
            [$profile['id']]
        );

        $savedIds = array_column($saved, 'job_id');
        $recommendations = [];
        if (!empty($savedIds)) {
            $recommendations = (new Job())->recommendForSaved($savedIds, 6);
        }

        $this->view('seeker/saved-jobs', [
            'title'           => 'Saved Jobs',
            'profile'         => $profile,
            'saved'           => $saved,
            'recommendations' => $recommendations,
        ], 'seeker');
    }

    public function saveJob(array $params): void
    {
        $this->verifyCsrf();
        $profile = $this->seekerProfile();
        $jobId   = (int)($params['jobId'] ?? 0);

        $db      = Database::getInstance();
        $exists  = $db->fetchColumn(
            "SELECT id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?",
            [$profile['id'], $jobId]
        );

        if (!$exists) {
            $db->insert('saved_jobs', [
                'seeker_id' => $profile['id'],
                'job_id'    => $jobId,
                'saved_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->isAjax()) {
            $this->json(['success' => true, 'saved' => true]);
        }
        $this->flash('success', 'Job saved!');
        $this->back();
    }

    public function unsaveJob(array $params): void
    {
        $this->verifyCsrf();
        $profile = $this->seekerProfile();
        $jobId   = (int)($params['jobId'] ?? 0);

        $db = Database::getInstance();
        $db->delete('saved_jobs', 'seeker_id = ? AND job_id = ?', [$profile['id'], $jobId]);

        if ($this->isAjax()) {
            $this->json(['success' => true, 'saved' => false]);
        }
        $this->flash('success', 'Job removed from saved list.');
        $this->back();
    }

    // =========================================================================
    // NOTIFICATIONS
    // =========================================================================
    public function notifications(): void
    {
        $db    = Database::getInstance();
        $notifs = $db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ?
              ORDER BY created_at DESC LIMIT 50",
            [Session::id()]
        );

        // Mark all as read
        $db->update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [Session::id()]);

        $this->view('seeker/notifications', [
            'title'   => 'Notifications',
            'notifs'  => $notifs,
        ], 'seeker');
    }

    // =========================================================================
    // JOB ALERTS
    // =========================================================================
    public function alerts(): void
    {
        require_once ROOT_PATH . '/app/models/Job.php';

        $db     = Database::getInstance();
        $alerts = $db->fetchAll(
            "SELECT * FROM job_alerts WHERE user_id = ? ORDER BY created_at DESC",
            [Session::id()]
        );

        $jobModel = new Job();
        foreach ($alerts as &$alert) {
            $filters = [
                'q'                => $alert['keywords'] ?? '',
                'location'         => $alert['location'] ?? '',
                'job_type'         => $alert['job_type'] ?? 'any',
                'experience_level' => $alert['experience_level'] ?? 'any',
                'industry'         => $alert['industry'] ?? '',
                'salary_min'       => $alert['salary_min'] ? (string)$alert['salary_min'] : '',
                'is_remote'        => isset($alert['is_remote']) && $alert['is_remote'] ? '1' : '',
                'sort'             => 'newest',
            ];

            $result = $jobModel->search($filters, 1, 3);
            $alert['matches']      = $result['total'];
            $alert['preview_jobs'] = $result['data'];
        }
        unset($alert);

        $this->view('seeker/alerts', [
            'title'  => 'Job Alerts',
            'alerts' => $alerts,
        ], 'seeker');
    }

    public function createAlert(): void
    {
        $this->verifyCsrf();

        $salaryMin = $this->input('salary_min');
        $db = Database::getInstance();
        $db->insert('job_alerts', [
            'user_id'           => Session::id(),
            'unsubscribe_token' => bin2hex(random_bytes(24)),
            'alert_name'        => trim($this->input('alert_name')) ?: 'My Alert',
            'keywords'          => $this->input('keywords') ?: null,
            'location'          => $this->input('location') ?: null,
            'job_type'          => $this->input('job_type', 'any'),
            'experience_level'  => $this->input('experience_level', 'any'),
            'industry'          => $this->input('industry') ?: null,
            'salary_min'        => $salaryMin !== '' ? (float)$salaryMin : null,
            'is_remote'         => $this->input('is_remote') ? 1 : 0,
            'frequency'         => $this->input('frequency', 'daily'),
            'is_active'         => 1,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->flash('success', 'Job alert created!');
        $this->redirect(BASE_URL . '/seeker/alerts');
    }

    public function deleteAlert(): void
    {
        $this->verifyCsrf();
        $db      = Database::getInstance();
        $alertId = (int)$this->input('alert_id');
        $db->delete('job_alerts', 'id = ? AND user_id = ?', [$alertId, Session::id()]);
        $this->flash('success', 'Alert deleted.');
        $this->redirect(BASE_URL . '/seeker/alerts');
    }

    // ── Unsubscribe via email link (no login required) ────────────────────────

    public function unsubscribeAlert(): void
    {
        $token = trim($_GET['token'] ?? '');
        $db    = Database::getInstance();

        if (empty($token)) {
            $this->redirect(BASE_URL . '/');
            return;
        }

        $alert = $db->fetchOne(
            "SELECT * FROM job_alerts WHERE unsubscribe_token = ? LIMIT 1",
            [$token]
        );

        if (!$alert) {
            $this->redirect(BASE_URL . '/');
            return;
        }

        $db->update('job_alerts', ['is_active' => 0], 'id = ?', [$alert['id']]);

        $name = htmlspecialchars($alert['alert_name'] ?: 'Job Alert', ENT_QUOTES);
        echo <<<HTML
        <!DOCTYPE html><html><head><meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Unsubscribed — TatuJobHub</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        </head><body class="bg-light">
        <div class="container py-5 text-center" style="max-width:480px;">
          <div class="card border-0 shadow-sm p-5">
            <div class="mb-3" style="font-size:3rem;">&#9989;</div>
            <h4 class="fw-800 mb-2">Unsubscribed</h4>
            <p class="text-muted">You have been removed from the <strong>{$name}</strong> alert and will no longer receive emails for it.</p>
            <a href="/" class="btn btn-primary mt-3 px-4">Back to TatuJobHub</a>
          </div>
        </div></body></html>
        HTML;
        exit;
    }

    // =========================================================================
    // EMPLOYER REVIEWS
    // =========================================================================
    // Gated: seeker must have at least one application to a job posted by
    // this employer, and must not have already reviewed them.
    public function submitReview(): void
    {
        $this->verifyCsrf();
        $profile    = $this->seekerProfile();
        $employerId = (int)$this->input('employer_id');
        $rating     = (int)$this->input('rating');
        $text       = trim($this->input('review_text'));

        $companySlug = $this->input('redirect_slug');
        $redirectUrl = BASE_URL . '/companies' . ($companySlug ? '/' . $companySlug : '');

        if ($rating < 1 || $rating > 5) {
            $this->flash('error', 'Please select a star rating between 1 and 5.');
            $this->redirect($redirectUrl);
        }

        if ($this->reviews->hasReviewed($profile['id'], $employerId)) {
            $this->flash('error', 'You have already reviewed this employer.');
            $this->redirect($redirectUrl);
        }

        $eligible = $this->reviews->eligibleApplication($profile['id'], $employerId);
        if (!$eligible) {
            $this->flash('error', 'You can only review employers you have applied to.');
            $this->redirect($redirectUrl);
        }

        $this->reviews->submit($employerId, $profile['id'], (int)$eligible['id'], $rating, $text);
        $this->flash('success', 'Thanks — your review has been posted!');
        $this->redirect($redirectUrl);
    }
}
