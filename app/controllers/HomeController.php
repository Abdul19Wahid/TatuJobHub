<?php
require_once ROOT_PATH . '/core/Controller.php';

class HomeController extends Controller
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function index(): void
    {
        $stats = [
            'active_jobs' => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM job_listings WHERE status='active' AND (expires_at IS NULL OR expires_at>NOW())"),
            'companies'   => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM employer_profiles WHERE verification_status='approved'"),
            'seekers'     => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM users WHERE role='seeker' AND is_active=1"),
            'placements'  => (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM applications WHERE status IN ('hired','offered')"),
        ];

        $featuredJobs = $this->db->fetchAll(
            "SELECT jl.id,jl.title,jl.slug,jl.job_type,jl.experience_level,
                    jl.location_city,jl.is_remote,jl.salary_min,jl.salary_max,
                    jl.salary_currency,jl.salary_is_hidden,jl.is_featured,jl.created_at,
                    ep.company_name,ep.logo_path AS company_logo
               FROM job_listings jl
               JOIN employer_profiles ep ON ep.id=jl.employer_id
              WHERE jl.status='active' AND ep.verification_status='approved'
                AND (jl.expires_at IS NULL OR jl.expires_at>NOW())
              ORDER BY jl.is_featured DESC,jl.created_at DESC LIMIT 6"
        );

        $topCompanies = $this->db->fetchAll(
            "SELECT ep.id,ep.company_name,ep.slug,ep.logo_path,ep.industry,ep.location_city,
                    COUNT(jl.id) AS job_count
               FROM employer_profiles ep
               LEFT JOIN job_listings jl ON jl.employer_id=ep.id AND jl.status='active'
                     AND (jl.expires_at IS NULL OR jl.expires_at>NOW())
              WHERE ep.verification_status='approved'
              GROUP BY ep.id ORDER BY ep.is_featured DESC,job_count DESC LIMIT 6"
        );

        $categories = $this->db->fetchAll(
            "SELECT industry,COUNT(*) AS job_count FROM job_listings
              WHERE status='active' AND industry IS NOT NULL AND industry!=''
                AND (expires_at IS NULL OR expires_at>NOW())
              GROUP BY industry ORDER BY job_count DESC LIMIT 8"
        );

        $this->view('home/index',[
            'title'=>'Find Your Dream Job',
            'stats'=>$stats,'featuredJobs'=>$featuredJobs,
            'topCompanies'=>$topCompanies,'categories'=>$categories,
        ]);
    }

    public function about(): void   { $this->view('home/about',   ['title'=>'About Us']); }
    public function contact(): void { $this->view('home/contact', ['title'=>'Contact Us']); }
    public function sendContact(): void
    {
        $this->verifyCsrf();
        $this->flash('success','Thank you! We will get back to you shortly.');
        $this->redirect(BASE_URL.'/contact');
    }
}
