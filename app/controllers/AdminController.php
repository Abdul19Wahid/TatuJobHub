<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/core/Validator.php';
require_once ROOT_PATH . '/app/helpers/NotificationService.php';

class AdminController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->requireRole('admin');
        $this->db = Database::getInstance();
    }

    // =========================================================================
    // DASHBOARD
    // =========================================================================
    public function dashboard(): void
    {
        $stats = [
            'total_users'     => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users"),
            'total_seekers'   => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role='seeker'"),
            'total_employers' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE role='employer'"),
            'active_jobs'     => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM job_listings WHERE status='active'"),
            'total_apps'      => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications"),
            'pending_verify'  => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM company_verifications WHERE status='pending'"),
            'new_users_today' => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()"),
            'apps_today'      => (int)$this->db->fetchColumn("SELECT COUNT(*) FROM applications WHERE DATE(applied_at)=CURDATE()"),
        ];

        $recentUsers = $this->db->fetchAll(
            "SELECT * FROM users ORDER BY created_at DESC LIMIT 6"
        );

        $pendingVerifications = $this->db->fetchAll(
            "SELECT cv.*, ep.company_name, ep.logo_path, u.email
               FROM company_verifications cv
               JOIN employer_profiles ep ON ep.id = cv.employer_id
               JOIN users u ON u.id = ep.user_id
              WHERE cv.status = 'pending'
              ORDER BY cv.created_at ASC LIMIT 5"
        );

        $this->view('admin/dashboard', [
            'title'                => 'Admin Dashboard',
            'stats'                => $stats,
            'recentUsers'          => $recentUsers,
            'pendingVerifications' => $pendingVerifications,
        ], 'admin');
    }

    // =========================================================================
    // USERS
    // =========================================================================
    public function users(): void
    {
        $role   = $_GET['role'] ?? '';
        $search = $_GET['q']    ?? '';
        $where  = ['1=1'];
        $params = [];

        if ($role)   { $where[] = 'role = ?';                     $params[] = $role; }
        if ($search) { $where[] = '(full_name LIKE ? OR email LIKE ?)';
                       $params[] = '%' . $search . '%'; $params[] = '%' . $search . '%'; }

        $sql   = "SELECT * FROM users WHERE " . implode(' AND ', $where) . " ORDER BY created_at DESC";
        $paged = $this->paginate_raw($sql, $params, $this->currentPage(), 20);

        $this->view('admin/users', [
            'title'  => 'User Management',
            'users'  => $paged['data'],
            'paging' => $paged,
            'role'   => $role,
            'search' => $search,
        ], 'admin');
    }

    public function viewUser(array $params): void
    {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [(int)$params['id']]);
        if (!$user) $this->abort(404);

        $profile = $user['role'] === 'seeker'
            ? $this->db->fetchOne("SELECT * FROM job_seeker_profiles WHERE user_id = ?", [$user['id']])
            : $this->db->fetchOne("SELECT * FROM employer_profiles WHERE user_id = ?",   [$user['id']]);

        $this->view('admin/user-detail', [
            'title'   => 'User — ' . $user['full_name'],
            'user'    => $user,
            'profile' => $profile,
        ], 'admin');
    }

    public function toggleUser(array $params): void
    {
        $this->verifyCsrf();
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [(int)$params['id']]);
        if (!$user) $this->abort(404);
        if ($user['role'] === 'admin') { $this->flash('error', 'Cannot deactivate admin accounts.'); $this->back(); return; }

        $newStatus = $user['is_active'] ? 0 : 1;
        $this->db->update('users', ['is_active' => $newStatus], 'id = ?', [$user['id']]);
        $this->flash('success', 'User ' . ($newStatus ? 'activated' : 'deactivated') . '.');

        // Log action
        $this->log('toggle_user', 'users', $user['id'],
            ($newStatus ? 'Activated' : 'Deactivated') . ' user: ' . $user['email']);

        $this->redirect(BASE_URL . '/admin/users');
    }

    public function deleteUser(array $params): void
    {
        $this->verifyCsrf();
        $user = $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [(int)$params['id']]);
        if (!$user || $user['role'] === 'admin') {
            $this->flash('error', 'Cannot delete this user.'); $this->back(); return;
        }

        $this->log('delete_user', 'users', $user['id'], 'Deleted user: ' . $user['email']);
        $this->db->delete('users', 'id = ?', [$user['id']]);
        $this->flash('success', 'User deleted successfully.');
        $this->redirect(BASE_URL . '/admin/users');
    }

    // =========================================================================
    // COMPANY VERIFICATIONS
    // =========================================================================
    public function verifications(): void
    {
        $status = $_GET['status'] ?? 'pending';
        $rows   = $this->db->fetchAll(
            "SELECT cv.*, ep.company_name, ep.logo_path, ep.industry,
                    u.full_name AS contact_name, u.email
               FROM company_verifications cv
               JOIN employer_profiles ep ON ep.id = cv.employer_id
               JOIN users u ON u.id = ep.user_id
              WHERE cv.status = ?
              ORDER BY cv.created_at ASC",
            [$status]
        );

        $this->view('admin/verifications', [
            'title'  => 'Company Verifications',
            'rows'   => $rows,
            'status' => $status,
        ], 'admin');
    }

    public function approveVerification(array $params): void
    {
        $this->verifyCsrf();
        $id = (int)$params['id'];

        $cv = $this->db->fetchOne("SELECT * FROM company_verifications WHERE id = ?", [$id]);
        if (!$cv) $this->abort(404);

        $this->db->transaction(function ($db) use ($cv, $id) {
            $db->update('company_verifications',
                ['status' => 'approved', 'reviewed_by' => Session::id(),
                 'admin_remarks' => $this->input('admin_remarks') ?: 'Approved.',
                 'reviewed_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?', [$id]);

            $db->update('employer_profiles',
                ['verification_status' => 'approved'],
                'id = ?', [$cv['employer_id']]);

            // Get employer user_id
            $ep = $db->fetchOne("SELECT user_id, company_name FROM employer_profiles WHERE id = ?", [$cv['employer_id']]);
            if ($ep) {
                NotificationService::companyApproved($ep['user_id'], $ep['company_name']);
            }
        });

        $this->log('approve_verification', 'company_verifications', $id, 'Approved verification for employer_id=' . $cv['employer_id']);
        $this->flash('success', 'Company verification approved!');
        $this->redirect(BASE_URL . '/admin/verifications');
    }

    public function rejectVerification(array $params): void
    {
        $this->verifyCsrf();
        $id = (int)$params['id'];
        $cv = $this->db->fetchOne("SELECT * FROM company_verifications WHERE id = ?", [$id]);
        if (!$cv) $this->abort(404);

        $remarks = $this->input('admin_remarks') ?: 'Verification rejected.';

        $this->db->transaction(function ($db) use ($cv, $id, $remarks) {
            $db->update('company_verifications',
                ['status' => 'rejected', 'reviewed_by' => Session::id(),
                 'admin_remarks' => $remarks, 'reviewed_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')],
                'id = ?', [$id]);

            $db->update('employer_profiles',
                ['verification_status' => 'rejected'],
                'id = ?', [$cv['employer_id']]);

            $ep = $db->fetchOne("SELECT user_id, company_name FROM employer_profiles WHERE id = ?", [$cv['employer_id']]);
            if ($ep) {
                NotificationService::companyRejected($ep['user_id'], $ep['company_name'], $remarks);
            }
        });

        $this->log('reject_verification', 'company_verifications', $id, 'Rejected: ' . $remarks);
        $this->flash('success', 'Verification rejected and employer notified.');
        $this->redirect(BASE_URL . '/admin/verifications');
    }

    // =========================================================================
    // JOB MODERATION
    // =========================================================================
    public function jobs(): void
    {
        $status = $_GET['status'] ?? 'active';
        $sql    = "SELECT jl.*, ep.company_name FROM job_listings jl
                   JOIN employer_profiles ep ON ep.id = jl.employer_id
                   WHERE jl.status = ? ORDER BY jl.created_at DESC";
        $paged  = $this->paginate_raw($sql, [$status], $this->currentPage(), 20);

        $this->view('admin/jobs', [
            'title'  => 'Job Moderation',
            'jobs'   => $paged['data'],
            'paging' => $paged,
            'status' => $status,
        ], 'admin');
    }

    public function removeJob(array $params): void
    {
        $this->verifyCsrf();
        $jobId = (int)$params['id'];
        $this->db->update('job_listings', ['status' => 'removed', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$jobId]);
        $this->log('remove_job', 'job_listings', $jobId, 'Admin removed job listing.');
        $this->flash('success', 'Job listing removed.');
        $this->redirect(BASE_URL . '/admin/jobs');
    }

    // =========================================================================
    // EMPLOYER REVIEWS — MODERATION
    // =========================================================================
    public function reviews(): void
    {
        require_once ROOT_PATH . '/app/models/EmployerReview.php';
        $status = $_GET['status'] ?? '';
        $model  = new EmployerReview();
        [$sql, $params] = $model->moderationQuery($status);
        $paged  = $this->paginate_raw($sql, $params, $this->currentPage(), 20);

        $this->view('admin/reviews', [
            'title'  => 'Review Moderation',
            'items'  => $paged['data'],
            'paging' => $paged,
            'status' => $status,
        ], 'admin');
    }

    public function hideReview(array $params): void
    {
        require_once ROOT_PATH . '/app/models/EmployerReview.php';
        $this->verifyCsrf();
        $id = (int)$params['id'];
        (new EmployerReview())->setStatus($id, 'hidden');
        $this->log('hide_review', 'employer_reviews', $id, 'Admin hid an employer review.');
        $this->flash('success', 'Review hidden from public view.');
        $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function unhideReview(array $params): void
    {
        require_once ROOT_PATH . '/app/models/EmployerReview.php';
        $this->verifyCsrf();
        $id = (int)$params['id'];
        (new EmployerReview())->setStatus($id, 'visible');
        $this->log('unhide_review', 'employer_reviews', $id, 'Admin restored an employer review.');
        $this->flash('success', 'Review restored.');
        $this->redirect(BASE_URL . '/admin/reviews');
    }

    public function deleteReview(array $params): void
    {
        require_once ROOT_PATH . '/app/models/EmployerReview.php';
        $this->verifyCsrf();
        $id = (int)$params['id'];
        (new EmployerReview())->destroy($id);
        $this->log('delete_review', 'employer_reviews', $id, 'Admin permanently deleted an employer review.');
        $this->flash('success', 'Review permanently deleted.');
        $this->redirect(BASE_URL . '/admin/reviews');
    }

    // =========================================================================
    // REPORTS
    // =========================================================================
    public function reports(): void
    {
        $usersByRole = $this->db->fetchAll(
            "SELECT role, COUNT(*) AS cnt FROM users GROUP BY role"
        );
        $appsByStatus = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS cnt FROM applications GROUP BY status ORDER BY cnt DESC"
        );
        $jobsByType = $this->db->fetchAll(
            "SELECT job_type, COUNT(*) AS cnt FROM job_listings WHERE status='active' GROUP BY job_type ORDER BY cnt DESC"
        );
        $topIndustries = $this->db->fetchAll(
            "SELECT industry, COUNT(*) AS cnt FROM job_listings
              WHERE status='active' AND industry IS NOT NULL AND industry != ''
              GROUP BY industry ORDER BY cnt DESC LIMIT 8"
        );
        // Last 30 days registrations
        $dailyRegistrations = $this->db->fetchAll(
            "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
               FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               GROUP BY DATE(created_at) ORDER BY day ASC"
        );
        // Last 30 days applications
        $dailyApplications = $this->db->fetchAll(
            "SELECT DATE(applied_at) AS day, COUNT(*) AS cnt
               FROM applications WHERE applied_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               GROUP BY DATE(applied_at) ORDER BY day ASC"
        );
        // Monthly job postings (last 6 months)
        $monthlyJobs = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                    COUNT(*) AS cnt
               FROM job_listings
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
              GROUP BY DATE_FORMAT(created_at, '%Y-%m')
              ORDER BY month ASC"
        );
        // Application conversion funnel
        $funnel = $this->db->fetchAll(
            "SELECT
                (SELECT COUNT(*) FROM applications) AS total_applied,
                (SELECT COUNT(*) FROM applications WHERE status IN ('under_review','shortlisted','interview_scheduled','offered','hired')) AS reviewed,
                (SELECT COUNT(*) FROM applications WHERE status IN ('shortlisted','interview_scheduled','offered','hired')) AS shortlisted,
                (SELECT COUNT(*) FROM applications WHERE status IN ('interview_scheduled','offered','hired')) AS interviewed,
                (SELECT COUNT(*) FROM applications WHERE status IN ('offered','hired')) AS offered,
                (SELECT COUNT(*) FROM applications WHERE status = 'hired') AS hired"
        );

        $this->view('admin/reports', [
            'title'              => 'Reports & Analytics',
            'usersByRole'        => $usersByRole,
            'appsByStatus'       => $appsByStatus,
            'jobsByType'         => $jobsByType,
            'topIndustries'      => $topIndustries,
            'dailyRegistrations' => $dailyRegistrations,
            'dailyApplications'  => $dailyApplications,
            'monthlyJobs'        => $monthlyJobs,
            'funnel'             => $funnel[0] ?? [],
        ], 'admin');
    }

    // =========================================================================
    // AUDIT LOG
    // =========================================================================
    public function logs(): void
    {
        $sql   = "SELECT al.*, u.full_name AS admin_name
                    FROM admin_logs al
                    LEFT JOIN users u ON u.id = al.admin_id
                   ORDER BY al.created_at DESC";
        $paged = $this->paginate_raw($sql, [], $this->currentPage(), 30);

        $this->view('admin/logs', [
            'title' => 'Audit Log',
            'logs'  => $paged['data'],
            'paging'=> $paged,
        ], 'admin');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================
    private function log(string $action, string $entity, int $entityId, string $desc): void
    {
        $this->db->insert('admin_logs', [
            'admin_id'    => Session::id(),
            'action'      => $action,
            'entity_type' => $entity,
            'entity_id'   => $entityId,
            'description' => $desc,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    private function paginate_raw(string $sql, array $params, int $page, int $perPage): array
    {
        $page   = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $total  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM ($sql) AS _c", $params
        );
        return [
            'data'         => $this->db->fetchAll("$sql LIMIT $perPage OFFSET $offset", $params),
            'total'        => $total,
            'pages'        => (int)ceil($total / $perPage),
            'current_page' => $page,
            'per_page'     => $perPage,
        ];
    }

    // =========================================================================
    // CSV EXPORTS
    // =========================================================================

    public function exportUsers(): void
    {
        $where  = '1=1';
        $params = [];
        $role   = $_GET['role'] ?? '';
        if ($role) { $where .= ' AND role = ?'; $params[] = $role; }
        
        $rows   = $this->db->fetchAll(
            "SELECT id, full_name, email, role,
                    CASE WHEN is_active=1 THEN 'Active' ELSE 'Inactive' END AS status,
                    CASE WHEN email_verified=1 THEN 'Yes' ELSE 'No' END AS email_verified,
                    DATE_FORMAT(created_at,'%Y-%m-%d') AS joined,
                    DATE_FORMAT(last_login_at,'%Y-%m-%d %H:%i') AS last_login
               FROM users WHERE $where
               ORDER BY created_at DESC",
            $params
        );

        $headers = ['ID','Full Name','Email','Role','Status','Email Verified','Joined','Last Login'];
        $this->sendCsv('users-export-' . date('Y-m-d') . '.csv', $headers, $rows);
    }

    public function exportJobs(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT jl.id, jl.title, ep.company_name,
                    jl.job_type, jl.experience_level, jl.industry,
                    jl.location_city, jl.location_country,
                    CASE WHEN jl.is_remote=1 THEN 'Yes' ELSE 'No' END AS remote,
                    jl.salary_min, jl.salary_max, jl.salary_currency,
                    jl.status, jl.view_count, jl.application_count,
                    DATE_FORMAT(jl.created_at,'%Y-%m-%d') AS posted_date,
                    DATE_FORMAT(jl.expires_at,'%Y-%m-%d')  AS expires_date
               FROM job_listings jl
               JOIN employer_profiles ep ON ep.id = jl.employer_id
               ORDER BY jl.created_at DESC"
        );

        $headers = ['ID','Title','Company','Type','Level','Industry','City','Country',
                    'Remote','Salary Min','Salary Max','Currency','Status',
                    'Views','Applications','Posted','Expires'];
        $this->sendCsv('jobs-export-' . date('Y-m-d') . '.csv', $headers, $rows);
    }

    public function exportApplications(): void
    {
        $rows = $this->db->fetchAll(
            "SELECT a.id,
                    u.full_name AS seeker_name, u.email AS seeker_email,
                    jl.title AS job_title, ep.company_name,
                    a.status,
                    DATE_FORMAT(a.applied_at,'%Y-%m-%d %H:%i') AS applied_at,
                    DATE_FORMAT(a.updated_at,'%Y-%m-%d %H:%i') AS updated_at
               FROM applications a
               JOIN job_seeker_profiles jsp ON jsp.id  = a.seeker_id
               JOIN users u                ON u.id    = jsp.user_id
               JOIN job_listings jl        ON jl.id   = a.job_id
               JOIN employer_profiles ep   ON ep.id   = jl.employer_id
               ORDER BY a.applied_at DESC"
        );

        $headers = ['ID','Seeker Name','Seeker Email','Job Title',
                    'Company','Status','Applied At','Last Updated'];
        $this->sendCsv('applications-export-' . date('Y-m-d') . '.csv', $headers, $rows);
    }

    // ── CSV helper ────────────────────────────────────────────────────────────
    private function sendCsv(string $filename, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');

        // UTF-8 BOM so Excel opens it correctly
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($out, $headers);
        foreach ($rows as $row) {
            fputcsv($out, array_values($row));
        }

        fclose($out);
        exit;
    }

}