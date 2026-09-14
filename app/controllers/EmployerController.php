<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Validator.php';
require_once ROOT_PATH . '/app/helpers/FileUpload.php';

class EmployerController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->requireRole('employer');
        $this->db = Database::getInstance();
    }

    // ── Helper: get employer profile (auto-creates row if missing) ────────────
    private function ep(): array
    {
        $ep = $this->db->fetchOne(
            "SELECT ep.*, u.full_name, u.email
               FROM employer_profiles ep
               JOIN users u ON u.id = ep.user_id
              WHERE ep.user_id = ? LIMIT 1",
            [Session::id()]
        );

        if (!$ep) {
            // Profile row missing (e.g. registration DB hiccup) — auto-create it
            $user = $this->db->fetchOne(
                "SELECT full_name, email FROM users WHERE id = ? LIMIT 1",
                [Session::id()]
            );
            if (!$user) $this->abort(403);

            $this->db->insert('employer_profiles', [
                'user_id'             => Session::id(),
                'company_name'        => $user['full_name'] . "'s Company",
                'verification_status' => 'pending',
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ]);

            // Re-fetch with JOIN so the shape matches everywhere
            $ep = $this->db->fetchOne(
                "SELECT ep.*, u.full_name, u.email
                   FROM employer_profiles ep
                   JOIN users u ON u.id = ep.user_id
                  WHERE ep.user_id = ? LIMIT 1",
                [Session::id()]
            );
            if (!$ep) $this->abort(500);
        }

        return $ep;
    }

    // =========================================================================
    // DASHBOARD
    // =========================================================================
    public function dashboard(): void
    {
        $ep = $this->ep();

        $stats = [
            'active_jobs'   => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM job_listings WHERE employer_id = ? AND status = 'active'", [$ep['id']]),
            'total_apps'    => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications a JOIN job_listings jl ON jl.id = a.job_id WHERE jl.employer_id = ?", [$ep['id']]),
            'shortlisted'   => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications a JOIN job_listings jl ON jl.id = a.job_id WHERE jl.employer_id = ? AND a.status = 'shortlisted'", [$ep['id']]),
            'new_apps'      => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications a JOIN job_listings jl ON jl.id = a.job_id WHERE jl.employer_id = ? AND a.is_read_by_employer = 0", [$ep['id']]),
        ];

        $recentApps = $this->db->fetchAll(
            "SELECT a.*, u.full_name AS seeker_name, jsp.headline, jsp.avatar_path,
                    jl.title AS job_title
               FROM applications a
               JOIN job_seeker_profiles jsp ON jsp.id = a.seeker_id
               JOIN users u                ON u.id   = jsp.user_id
               JOIN job_listings jl        ON jl.id  = a.job_id
              WHERE jl.employer_id = ?
              ORDER BY a.applied_at DESC LIMIT 5",
            [$ep['id']]
        );

        $activeJobs = $this->db->fetchAll(
            "SELECT jl.*,
                    (SELECT COUNT(*) FROM applications a WHERE a.job_id = jl.id) AS app_count
               FROM job_listings jl
              WHERE jl.employer_id = ? AND jl.status = 'active'
              ORDER BY jl.created_at DESC LIMIT 5",
            [$ep['id']]
        );

        $this->view('employer/dashboard', [
            'title'      => 'Employer Dashboard',
            'ep'         => $ep,
            'stats'      => $stats,
            'recentApps' => $recentApps,
            'activeJobs' => $activeJobs,
        ], 'employer');
    }

    // =========================================================================
    // COMPANY PROFILE
    // =========================================================================
    public function profile(): void
    {
        $ep = $this->ep();
        $this->view('employer/profile', [
            'title' => 'Company Profile',
            'ep'    => $ep,
        ], 'employer');
    }

    public function updateProfile(): void
    {
        $this->verifyCsrf();
        $ep = $this->ep();

        $v = Validator::make($_POST, [
            'company_name' => 'required|min:2|max:160',
            'website'      => 'max:300',
            'phone'        => 'max:30',
        ]);

        if ($v->fails()) {
            $this->flash('error', $v->first());
            $this->back();
            return;
        }

        $fields = ['company_name','industry','company_size','founded_year','website',
                   'phone','location_city','location_country','full_address',
                   'description','linkedin_url','twitter_url'];

        $data = [];
        foreach ($fields as $f) {
            $data[$f] = isset($_POST[$f]) && trim($_POST[$f]) !== '' ? trim($_POST[$f]) : null;
        }
        $data['slug']       = slug($data['company_name']) . '-' . $ep['id'];
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->update('employer_profiles', $data, 'id = ?', [$ep['id']]);

        // Update user full_name
        if (!empty($_POST['contact_name'])) {
            $this->db->update('users', ['full_name' => trim($_POST['contact_name'])], 'id = ?', [Session::id()]);
            $_SESSION['user']['name'] = trim($_POST['contact_name']);
        }

        $this->flash('success', 'Company profile updated!');
        $this->redirect(BASE_URL . '/employer/profile');
    }

    public function uploadLogo(): void
    {
        $this->verifyCsrf();
        $ep = $this->ep();

        if (empty($_FILES['logo']) || $_FILES['logo']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->flash('error', 'Please select a logo image.');
            $this->back();
            return;
        }

        $result = FileUpload::upload($_FILES['logo'], 'company-logos');
        if (!$result['ok']) {
            $this->flash('error', $result['error']);
            $this->back();
            return;
        }

        if ($ep['logo_path']) FileUpload::delete($ep['logo_path']);

        $this->db->update('employer_profiles',
            ['logo_path' => $result['path'], 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$ep['id']]
        );

        $this->flash('success', 'Company logo updated!');
        $this->redirect(BASE_URL . '/employer/profile');
    }

    public function submitVerification(): void
    {
        $this->verifyCsrf();
        $ep = $this->ep();

        $docPath = null;
        if (!empty($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
            $result = FileUpload::upload($_FILES['document'], 'documents');
            if (!$result['ok']) {
                $this->flash('error', $result['error']);
                $this->back();
                return;
            }
            $docPath = $result['path'];
        }

        // Check existing verification
        $existing = $this->db->fetchOne(
            "SELECT id FROM company_verifications WHERE employer_id = ? AND status = 'pending'",
            [$ep['id']]
        );

        if (!$existing) {
            $this->db->insert('company_verifications', [
                'employer_id'   => $ep['id'],
                'document_path' => $docPath,
                'document_type' => $this->input('document_type', 'Business Registration'),
                'notes'         => $this->input('notes'),
                'status'        => 'pending',
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
            $this->db->update('employer_profiles',
                ['verification_status' => 'pending'], 'id = ?', [$ep['id']]);
        }

        $this->flash('success', 'Verification request submitted! Admin will review within 24–48 hours.');
        $this->redirect(BASE_URL . '/employer/profile');
    }

    // =========================================================================
    // NOTIFICATIONS
    // =========================================================================
    public function notifications(): void
    {
        $notifs = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 50",
            [Session::id()]
        );
        $this->db->update('notifications', ['is_read' => 1],
            'user_id = ? AND is_read = 0', [Session::id()]);

        $this->view('employer/notifications', [
            'title'  => 'Notifications',
            'notifs' => $notifs,
        ], 'employer');
    }

    // =========================================================================
    // RATINGS & REVIEWS
    // =========================================================================
    public function reviews(): void
    {
        require_once ROOT_PATH . '/app/models/EmployerReview.php';
        $ep      = $this->ep();
        $model   = new EmployerReview();
        $reviews = $model->getForEmployer((int)$ep['id']);
        $stats   = $model->getStats((int)$ep['id']);

        $this->view('employer/reviews', [
            'title'   => 'Ratings & Reviews',
            'reviews' => $reviews,
            'stats'   => $stats,
        ], 'employer');
    }
}
