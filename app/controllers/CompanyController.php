<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/app/models/EmployerReview.php';
require_once ROOT_PATH . '/app/models/SeekerProfile.php';

class CompanyController extends Controller
{
    private Database       $db;
    private EmployerReview $reviews;

    public function __construct()
    {
        $this->db      = Database::getInstance();
        $this->reviews = new EmployerReview();
    }

    // ── Public company listing ─────────────────────────────────────────────────
    public function index(): void
    {
        $search   = $this->query('q');
        $industry = $this->query('industry');
        $size     = $this->query('size');

        $where  = ["ep.verification_status = 'approved'"];
        $params = [];

        if ($search) {
            $where[]  = "(ep.company_name LIKE ? OR ep.description LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($industry) {
            $where[]  = "ep.industry = ?";
            $params[] = $industry;
        }
        if ($size) {
            $where[]  = "ep.company_size = ?";
            $params[] = $size;
        }

        $whereStr = implode(' AND ', $where);

        $sql = "SELECT ep.*,
                       (SELECT COUNT(*) FROM job_listings jl
                         WHERE jl.employer_id = ep.id
                           AND jl.status = 'active'
                           AND (jl.expires_at IS NULL OR jl.expires_at > NOW())
                       ) AS active_job_count
                  FROM employer_profiles ep
                 WHERE {$whereStr}
                 ORDER BY ep.is_featured DESC, active_job_count DESC, ep.company_name ASC";

        $page    = $this->currentPage();
        $perPage = 12;
        $total   = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM ({$sql}) AS _c", $params
        );
        $offset  = ($page - 1) * $perPage;
        $companies = $this->db->fetchAll("{$sql} LIMIT {$perPage} OFFSET {$offset}", $params);

        // Get distinct industries for filter
        $industries = $this->db->fetchAll(
            "SELECT DISTINCT industry FROM employer_profiles
              WHERE verification_status = 'approved' AND industry IS NOT NULL AND industry != ''
              ORDER BY industry ASC"
        );

        $this->view('companies/index', [
            'title'      => 'Companies',
            'companies'  => $companies,
            'industries' => $industries,
            'paging'     => [
                'total'        => $total,
                'pages'        => (int)ceil($total / $perPage),
                'current_page' => $page,
                'per_page'     => $perPage,
            ],
            'filters'    => compact('search', 'industry', 'size'),
        ]);
    }

    // ── Public company detail page ─────────────────────────────────────────────
    public function show(array $params): void
    {
        $slug = $params['slug'] ?? '';

        $company = $this->db->fetchOne(
            "SELECT ep.*, u.email AS contact_email
               FROM employer_profiles ep
               JOIN users u ON u.id = ep.user_id
              WHERE ep.slug = ? AND ep.verification_status = 'approved' LIMIT 1",
            [$slug]
        );

        if (!$company) $this->abort(404);

        // Active job listings for this company
        $jobs = $this->db->fetchAll(
            "SELECT * FROM job_listings
              WHERE employer_id = ?
                AND status = 'active'
                AND (expires_at IS NULL OR expires_at > NOW())
              ORDER BY is_featured DESC, created_at DESC
              LIMIT 20",
            [$company['id']]
        );

        // Stats
        $stats = [
            'active_jobs'  => count($jobs),
            'total_hired'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications a
                   JOIN job_listings jl ON jl.id = a.job_id
                  WHERE jl.employer_id = ? AND a.status = 'hired'",
                [$company['id']]
            ),
        ];

        // Increment profile views
        $this->db->update('employer_profiles',
            ['profile_views' => $company['profile_views'] + 1],
            'id = ?', [$company['id']]
        );

        // ── Ratings & Reviews ────────────────────────────────────────────────
        $reviewStats = $this->reviews->getStats((int)$company['id']);
        $reviewList  = $this->reviews->getForEmployerPublic((int)$company['id']);

        $canReview      = false;
        $alreadyReviewed = false;
        if (is_logged_in() && Session::isSeeker()) {
            $seekerProfiles = new SeekerProfile();
            $sp = $seekerProfiles->getByUserId(Session::id());
            if ($sp) {
                $alreadyReviewed = $this->reviews->hasReviewed($sp['id'], (int)$company['id']);
                $canReview = !$alreadyReviewed
                    && (bool)$this->reviews->eligibleApplication($sp['id'], (int)$company['id']);
            }
        }

        $this->view('companies/show', [
            'title'           => $company['company_name'] . ' — Company Profile',
            'company'         => $company,
            'jobs'            => $jobs,
            'stats'           => $stats,
            'reviewStats'     => $reviewStats,
            'reviewList'      => $reviewList,
            'canReview'       => $canReview,
            'alreadyReviewed' => $alreadyReviewed,
        ]);
    }
}
