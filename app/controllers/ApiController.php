<?php
require_once ROOT_PATH . '/core/Controller.php';

class ApiController extends Controller
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    // ── Unread notification count ─────────────────────────────────────────
    public function unreadCount(): void
    {
        if (!$this->isLoggedIn()) { $this->json(['count' => 0]); }
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [Session::id()]
        );
        $this->json(['count' => $count]);
    }

    // ── Mark notifications read ───────────────────────────────────────────
    public function markNotificationsRead(): void
    {
        $this->requireLogin();
        $this->verifyCsrf();
        $this->db->update('notifications', ['is_read' => 1],
            'user_id = ? AND is_read = 0', [Session::id()]);
        $this->json(['success' => true]);
    }

    // ── Job search (AJAX) ─────────────────────────────────────────────────
    public function jobSearch(): void
    {
        require_once ROOT_PATH . '/app/models/Job.php';
        $filters = [
            'q'                => $this->query('q'),
            'location'         => $this->query('location'),
            'job_type'         => $this->query('job_type'),
            'experience_level' => $this->query('experience_level'),
            'industry'         => $this->query('industry'),
            'is_remote'        => $this->query('is_remote'),
            'salary_min'       => $this->query('salary_min'),
            'sort'             => $this->query('sort', 'newest'),
        ];
        $result = (new Job())->search($filters, 1, 12);
        $jobs   = array_map(fn($j) => [
            'id'               => $j['id'],
            'title'            => $j['title'],
            'slug'             => $j['slug'],
            'company_name'     => $j['company_name'],
            'company_logo'     => $j['company_logo'] ?? null,
            'location_city'    => $j['location_city'] ?? '',
            'is_remote'        => (bool)$j['is_remote'],
            'job_type'         => $j['job_type'],
            'job_type_label'   => status_label($j['job_type']),
            'experience_level' => $j['experience_level'],
            'experience_label' => status_label($j['experience_level']),
            'salary_display'   => (!$j['salary_is_hidden'] && $j['salary_min'])
                ? salary_range($j['salary_min'], $j['salary_max'], $j['salary_currency']) : '',
            'is_featured'      => (bool)$j['is_featured'],
            'time_ago'         => time_ago($j['created_at']),
        ], $result['data']);
        $this->json(['jobs' => $jobs, 'total' => $result['total'], 'pages' => $result['pages']]);
    }

    // ── Job detail ────────────────────────────────────────────────────────
    public function jobDetail(array $params): void
    {
        require_once ROOT_PATH . '/app/models/Job.php';
        $job = (new Job())->find((int)($params['id'] ?? 0));
        $job ? $this->json($job) : $this->json(['error' => 'Not found'], 404);
    }

    // ── Save job (AJAX) ───────────────────────────────────────────────────
    public function saveJob(): void
    {
        $this->requireLogin();
        if (!Session::isSeeker()) { $this->json(['error' => 'Seekers only'], 403); }
        $this->verifyCsrf();

        $jobId   = (int)($this->isAjax() ? json_decode(file_get_contents('php://input'),true)['job_id'] ?? 0 : $this->input('job_id'));
        $profile = $this->db->fetchOne("SELECT id FROM job_seeker_profiles WHERE user_id = ?", [Session::id()]);
        if (!$profile) { $this->json(['error' => 'Profile not found'], 404); }

        $exists = $this->db->fetchColumn(
            "SELECT id FROM saved_jobs WHERE seeker_id = ? AND job_id = ?",
            [$profile['id'], $jobId]
        );
        if (!$exists) {
            $this->db->insert('saved_jobs', [
                'seeker_id' => $profile['id'],
                'job_id'    => $jobId,
                'saved_at'  => date('Y-m-d H:i:s'),
            ]);
        }
        $this->json(['success' => true, 'saved' => true]);
    }

    // ── Unsave job (AJAX) ─────────────────────────────────────────────────
    public function unsaveJob(): void
    {
        $this->requireLogin();
        if (!Session::isSeeker()) { $this->json(['error' => 'Seekers only'], 403); }
        $this->verifyCsrf();

        $jobId   = (int)($this->isAjax() ? json_decode(file_get_contents('php://input'),true)['job_id'] ?? 0 : $this->input('job_id'));
        $profile = $this->db->fetchOne("SELECT id FROM job_seeker_profiles WHERE user_id = ?", [Session::id()]);
        if ($profile) {
            $this->db->delete('saved_jobs', 'seeker_id = ? AND job_id = ?', [$profile['id'], $jobId]);
        }
        $this->json(['success' => true, 'saved' => false]);
    }
}
