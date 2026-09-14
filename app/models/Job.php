<?php
require_once ROOT_PATH . '/core/Model.php';

class Job extends Model
{
    protected string $table = 'job_listings';

    // ── Public job search with filters ───────────────────────────────────────
    public function search(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $where  = ["jl.status = 'active'", "ep.verification_status = 'approved'",
                   "(jl.expires_at IS NULL OR jl.expires_at > NOW())"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[]  = "(jl.title LIKE ? OR jl.description LIKE ? OR ep.company_name LIKE ?)";
            $like     = '%' . $filters['q'] . '%';
            $params   = array_merge($params, [$like, $like, $like]);
        }
        if (!empty($filters['location'])) {
            $where[]  = "(jl.location_city LIKE ? OR jl.location_country LIKE ?)";
            $like     = '%' . $filters['location'] . '%';
            $params   = array_merge($params, [$like, $like]);
        }
        if (!empty($filters['job_type']) && $filters['job_type'] !== 'any') {
            $where[] = "jl.job_type = ?";
            $params[] = $filters['job_type'];
        }
        if (!empty($filters['experience_level']) && $filters['experience_level'] !== 'any') {
            $where[] = "jl.experience_level = ?";
            $params[] = $filters['experience_level'];
        }
        if (!empty($filters['industry'])) {
            $where[] = "jl.industry LIKE ?";
            $params[] = '%' . $filters['industry'] . '%';
        }
        if (isset($filters['is_remote']) && $filters['is_remote'] !== '') {
            $where[] = "jl.is_remote = ?";
            $params[] = (int)$filters['is_remote'];
        }
        if (!empty($filters['salary_min'])) {
            $where[] = "jl.salary_max >= ?";
            $params[] = (float)$filters['salary_min'];
        }

        $whereStr = implode(' AND ', $where);

        $sort = match($filters['sort'] ?? 'newest') {
            'salary_high' => 'jl.salary_max DESC',
            'salary_low'  => 'jl.salary_min ASC',
            default       => 'jl.is_featured DESC, jl.created_at DESC',
        };

        $sql = "SELECT jl.*, ep.company_name, ep.logo_path AS company_logo,
                       ep.slug AS company_slug, ep.industry AS company_industry
                  FROM job_listings jl
                  JOIN employer_profiles ep ON ep.id = jl.employer_id
                 WHERE {$whereStr}
                 ORDER BY {$sort}";

        return $this->paginate($sql, $params, $page, $perPage);
    }

    // ── Recommended jobs based on saved jobs ──────────────────────────────────
    public function recommendForSaved(array $savedIds, int $limit = 6): array
    {
        if (empty($savedIds)) {
            return [];
        }

        $industries = [];
        $jobTypes   = [];
        $savedJobs  = $this->db->fetchAll(
            "SELECT jl.industry, jl.job_type
               FROM job_listings jl
              WHERE jl.id IN (" . implode(',', array_fill(0, count($savedIds), '?')) . ")",
            $savedIds
        );

        foreach ($savedJobs as $job) {
            if (!empty($job['industry'])) {
                $industries[] = $job['industry'];
            }
            if (!empty($job['job_type'])) {
                $jobTypes[] = $job['job_type'];
            }
        }

        $industries = array_values(array_unique($industries));
        $jobTypes   = array_values(array_unique($jobTypes));
        if (empty($industries) && empty($jobTypes)) {
            return [];
        }

        $notInSql = implode(',', array_fill(0, count($savedIds), '?'));
        $whereClauses = [];
        $params = array_merge($savedIds, []);
        $industrySql = '';
        $typeSql = '';

        if (!empty($industries)) {
            $industrySql = implode(',', array_fill(0, count($industries), '?'));
            $whereClauses[] = "jl.industry IN ($industrySql)";
            $params = array_merge($params, $industries);
        }
        if (!empty($jobTypes)) {
            $typeSql = implode(',', array_fill(0, count($jobTypes), '?'));
            $whereClauses[] = "jl.job_type IN ($typeSql)";
            $params = array_merge($params, $jobTypes);
        }

        $whereSql = implode(' OR ', $whereClauses);
        $orderCases = [];
        $orderParams = [];

        if (!empty($industries)) {
            $orderCases[] = "WHEN jl.industry IN ($industrySql) THEN 0";
            $orderParams = array_merge($orderParams, $industries);
        }
        if (!empty($jobTypes)) {
            $orderCases[] = "WHEN jl.job_type IN ($typeSql) THEN 1";
            $orderParams = array_merge($orderParams, $jobTypes);
        }

        $orderSql = implode(' ', $orderCases);
        $orderClause = "CASE $orderSql ELSE 2 END";

        $sql = "SELECT jl.*, ep.company_name, ep.logo_path AS company_logo,
                       ep.slug AS company_slug
                  FROM job_listings jl
                  JOIN employer_profiles ep ON ep.id = jl.employer_id
                 WHERE jl.status = 'active'
                   AND ep.verification_status = 'approved'
                   AND (jl.expires_at IS NULL OR jl.expires_at > NOW())
                   AND jl.id NOT IN ($notInSql)
                   AND ($whereSql)
                 ORDER BY $orderClause, jl.created_at DESC
                 LIMIT $limit";

        return $this->db->fetchAll($sql, array_merge($params, $orderParams));
    }

    // ── Get single active job by slug ─────────────────────────────────────────
    public function getBySlug(string $slug): array|false
    {
        return $this->db->fetchOne(
            "SELECT jl.*, ep.company_name, ep.logo_path AS company_logo,
                    ep.slug AS company_slug, ep.description AS company_desc,
                    ep.website AS company_website, ep.company_size,
                    ep.location_city AS company_city, ep.location_country AS company_country,
                    ep.id AS employer_profile_id,
                    u.id AS employer_user_id
               FROM job_listings jl
               JOIN employer_profiles ep ON ep.id = jl.employer_id
               JOIN users u              ON u.id  = ep.user_id
              WHERE jl.slug = ? LIMIT 1",
            [$slug]
        );
    }

    // ── Get by id with employer info ──────────────────────────────────────────
    public function getWithEmployer(int $id): array|false
    {
        return $this->db->fetchOne(
            "SELECT jl.*, ep.company_name, ep.logo_path AS company_logo,
                    ep.id AS employer_profile_id, u.id AS employer_user_id
               FROM job_listings jl
               JOIN employer_profiles ep ON ep.id = jl.employer_id
               JOIN users u              ON u.id  = ep.user_id
              WHERE jl.id = ? LIMIT 1",
            [$id]
        );
    }

    // ── Get employer's own listings ───────────────────────────────────────────
    public function getForEmployer(int $employerProfileId, int $page = 1, int $perPage = 15): array
    {
        $sql = "SELECT jl.*,
                       (SELECT COUNT(*) FROM applications a WHERE a.job_id = jl.id)       AS app_count,
                       (SELECT COUNT(*) FROM applications a WHERE a.job_id = jl.id
                         AND a.status = 'shortlisted')                                     AS shortlist_count
                  FROM job_listings jl
                 WHERE jl.employer_id = ?
                 ORDER BY jl.created_at DESC";
        return $this->paginate($sql, [$employerProfileId], $page, $perPage);
    }

    // ── Get job skills ────────────────────────────────────────────────────────
    public function getSkills(int $jobId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM job_listing_skills WHERE job_id = ? ORDER BY is_required DESC, skill_name ASC",
            [$jobId]
        );
    }

    // ── Create job listing ────────────────────────────────────────────────────
    public function createListing(int $employerProfileId, array $data): int
    {
        return $this->db->transaction(function (Database $db) use ($employerProfileId, $data) {

            $jobId = $db->insert('job_listings', [
                'employer_id'      => $employerProfileId,
                'title'            => $data['title'],
                'slug'             => $this->makeSlug($data['title'], $employerProfileId),
                'description'      => $data['description'],
                'requirements'     => $data['requirements']     ?? null,
                'responsibilities' => $data['responsibilities'] ?? null,
                'benefits'         => $data['benefits']         ?? null,
                'location_city'    => $data['location_city']    ?? null,
                'location_country' => $data['location_country'] ?? null,
                'is_remote'        => !empty($data['is_remote']) ? 1 : 0,
                'job_type'         => $data['job_type']         ?? 'full_time',
                'experience_level' => $data['experience_level'] ?? 'entry',
                'industry'         => $data['industry']         ?? null,
                'salary_min'       => !empty($data['salary_min']) ? (float)$data['salary_min'] : null,
                'salary_max'       => !empty($data['salary_max']) ? (float)$data['salary_max'] : null,
                'salary_currency'  => $data['salary_currency']  ?? 'USD',
                'salary_period'    => $data['salary_period']    ?? 'monthly',
                'salary_is_hidden' => !empty($data['salary_is_hidden']) ? 1 : 0,
                'vacancies'        => (int)($data['vacancies']  ?? 1),
                'application_deadline' => !empty($data['application_deadline']) ? $data['application_deadline'] : null,
                'status'           => 'active',
                'expires_at'       => date('Y-m-d H:i:s', strtotime('+30 days')),
                'created_at'       => date('Y-m-d H:i:s'),
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            // Insert skills
            if (!empty($data['skills'])) {
                foreach (array_filter(array_map('trim', explode(',', $data['skills']))) as $skill) {
                    $db->insert('job_listing_skills', [
                        'job_id'      => $jobId,
                        'skill_name'  => $skill,
                        'is_required' => 1,
                        'created_at'  => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            return $jobId;
        });
    }

    // ── Update job listing ────────────────────────────────────────────────────
    public function updateListing(int $jobId, array $data): void
    {
        $this->db->transaction(function (Database $db) use ($jobId, $data) {
            $db->update('job_listings', [
                'title'            => $data['title'],
                'description'      => $data['description'],
                'requirements'     => $data['requirements']     ?? null,
                'responsibilities' => $data['responsibilities'] ?? null,
                'benefits'         => $data['benefits']         ?? null,
                'location_city'    => $data['location_city']    ?? null,
                'location_country' => $data['location_country'] ?? null,
                'is_remote'        => !empty($data['is_remote']) ? 1 : 0,
                'job_type'         => $data['job_type']         ?? 'full_time',
                'experience_level' => $data['experience_level'] ?? 'entry',
                'industry'         => $data['industry']         ?? null,
                'salary_min'       => !empty($data['salary_min']) ? (float)$data['salary_min'] : null,
                'salary_max'       => !empty($data['salary_max']) ? (float)$data['salary_max'] : null,
                'salary_currency'  => $data['salary_currency']  ?? 'USD',
                'salary_period'    => $data['salary_period']    ?? 'monthly',
                'salary_is_hidden' => !empty($data['salary_is_hidden']) ? 1 : 0,
                'vacancies'        => (int)($data['vacancies']  ?? 1),
                'application_deadline' => !empty($data['application_deadline']) ? $data['application_deadline'] : null,
                'updated_at'       => date('Y-m-d H:i:s'),
            ], 'id = ?', [$jobId]);

            // Replace skills
            $db->delete('job_listing_skills', 'job_id = ?', [$jobId]);
            if (!empty($data['skills'])) {
                foreach (array_filter(array_map('trim', explode(',', $data['skills']))) as $skill) {
                    $db->insert('job_listing_skills', [
                        'job_id'     => $jobId,
                        'skill_name' => $skill,
                        'is_required'=> 1,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        });
    }

    // ── Quick status changes ──────────────────────────────────────────────────
    public function close(int $jobId, int $employerProfileId): void
    {
        $this->db->update('job_listings', ['status' => 'closed', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ? AND employer_id = ?', [$jobId, $employerProfileId]);
    }

    public function repost(int $jobId, int $employerProfileId): void
    {
        $this->db->update('job_listings', [
            'status'     => 'active',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'id = ? AND employer_id = ?', [$jobId, $employerProfileId]);
    }

    public function destroy(int $jobId, int $employerProfileId): void
    {
        $this->db->update('job_listings', ['status' => 'removed', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ? AND employer_id = ?', [$jobId, $employerProfileId]);
    }

    // ── Unique slug ───────────────────────────────────────────────────────────
    private function makeSlug(string $title, int $employerId): string
    {
        $base  = slug($title) . '-' . $employerId . '-' . substr(uniqid(), -4);
        $count = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM job_listings WHERE slug = ?", [$base]
        );
        return $count ? $base . '-' . rand(100, 999) : $base;
    }

    // ── Related jobs ──────────────────────────────────────────────────────────
    public function getRelated(int $jobId, string $industry, int $limit = 4): array
    {
        return $this->db->fetchAll(
            "SELECT jl.id, jl.title, jl.slug, jl.job_type, jl.location_city, jl.is_remote,
                    ep.company_name, ep.logo_path AS company_logo
               FROM job_listings jl
               JOIN employer_profiles ep ON ep.id = jl.employer_id
              WHERE jl.status = 'active' AND jl.id != ?
                AND (jl.industry = ? OR ep.industry = ?)
                AND (jl.expires_at IS NULL OR jl.expires_at > NOW())
              ORDER BY jl.created_at DESC
              LIMIT ?",
            [$jobId, $industry, $industry, $limit]
        );
    }

    // ── Employer analytics ────────────────────────────────────────────────────
    public function getAnalytics(int $jobId): array
    {
        $db = $this->db;
        return [
            'views'       => (int)$db->fetchColumn("SELECT view_count FROM job_listings WHERE id = ?", [$jobId]),
            'applications'=> (int)$db->fetchColumn("SELECT COUNT(*) FROM applications WHERE job_id = ?", [$jobId]),
            'shortlisted' => (int)$db->fetchColumn("SELECT COUNT(*) FROM applications WHERE job_id = ? AND status = 'shortlisted'", [$jobId]),
            'interviewed' => (int)$db->fetchColumn("SELECT COUNT(*) FROM applications WHERE job_id = ? AND status = 'interview_scheduled'", [$jobId]),
            'by_status'   => $db->fetchAll("SELECT status, COUNT(*) as cnt FROM applications WHERE job_id = ? GROUP BY status", [$jobId]),
        ];
    }
}
