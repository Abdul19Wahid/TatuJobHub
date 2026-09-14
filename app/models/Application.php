<?php
require_once ROOT_PATH . '/core/Model.php';

class Application extends Model
{
    protected string $table = 'applications';

    // ── All applications for a seeker (with job + company info) ──────────────
    public function getForSeeker(int $seekerId, int $page = 1, int $perPage = 10): array
    {
        /*
         * FIX (2026-07-16): Removed duplicate 'a.status_note' and
         * 'a.status_updated_at' — both are already included by 'a.*'.
         * Having them twice caused MySQL error 1060 "Duplicate column name"
         * whenever Model::paginate() wrapped this query as a derived table
         * to run its COUNT(*) (derived tables require unique column names,
         * unlike a normal top-level SELECT which tolerates duplicates).
         */
        $sql = "SELECT a.*,
                       jl.title        AS job_title,
                       jl.job_type,
                       jl.location_city,
                       jl.is_remote,
                       jl.slug         AS job_slug,
                       jl.status       AS job_status,
                       ep.company_name,
                       ep.logo_path    AS company_logo,
                       ep.slug         AS company_slug,
                       isc.id              AS interview_id,
                       isc.scheduled_at    AS interview_date,
                       isc.response_deadline AS interview_response_deadline,
                       isc.interview_type,
                       isc.meeting_link,
                       isc.status          AS interview_status,
                       isc.confirmed_at    AS interview_confirmed_at
                  FROM applications a
                  JOIN job_listings jl       ON jl.id  = a.job_id
                  JOIN employer_profiles ep  ON ep.id  = jl.employer_id
                  LEFT JOIN interview_schedules isc
                         ON isc.application_id = a.id
                        AND isc.status IN ('scheduled','confirmed')
                 WHERE a.seeker_id = ?
                 ORDER BY a.applied_at DESC";

        return $this->paginate($sql, [$seekerId], $page, $perPage);
    }

    // ── Next scheduled interview for seeker ─────────────────────────────────
    public function getNextInterview(int $seekerId): array|false
    {
        return $this->db->fetchOne(
            "SELECT isc.*, a.id AS application_id,
                    jl.title AS job_title, jl.slug AS job_slug,
                    ep.company_name, ep.logo_path AS company_logo
               FROM interview_schedules isc
               JOIN applications a ON a.id = isc.application_id
               JOIN job_listings jl ON jl.id = a.job_id
               JOIN employer_profiles ep ON ep.id = jl.employer_id
              WHERE a.seeker_id = ?
                AND isc.status IN ('scheduled','confirmed')
                AND isc.scheduled_at >= NOW()
              ORDER BY isc.scheduled_at ASC
              LIMIT 1",
            [$seekerId]
        );
    }

    // ── Single application detail ─────────────────────────────────────────────
    public function getDetail(int $appId, int $seekerId): array|false
    {
        return $this->db->fetchOne(
            "SELECT a.*,
                    jl.title AS job_title, jl.description AS job_description,
                    jl.location_city, jl.is_remote, jl.job_type, jl.salary_min,
                    jl.salary_max, jl.salary_currency, jl.salary_is_hidden,
                    ep.company_name, ep.logo_path AS company_logo
               FROM applications a
               JOIN job_listings jl       ON jl.id  = a.job_id
               JOIN employer_profiles ep  ON ep.id  = jl.employer_id
              WHERE a.id = ? AND a.seeker_id = ? LIMIT 1",
            [$appId, $seekerId]
        );
    }

    // ── Count by status for dashboard stats ───────────────────────────────────
    public function countByStatus(int $seekerId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT status, COUNT(*) as cnt
               FROM applications
              WHERE seeker_id = ?
              GROUP BY status",
            [$seekerId]
        );
        $map = [];
        foreach ($rows as $r) $map[$r['status']] = (int)$r['cnt'];
        return $map;
    }

    // ── Has the seeker already applied to this job? ────────────────────────────
    public function hasApplied(int $seekerId, int $jobId): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT id FROM applications WHERE seeker_id = ? AND job_id = ? LIMIT 1",
            [$seekerId, $jobId]
        );
    }

    // ── Submit a new application ──────────────────────────────────────────────
    public function apply(
        int $seekerId,
        int $jobId,
        ?string $coverLetterPath,
        ?string $coverLetterText,
        ?string $resumeSnapshotPath = null,
        ?string $resumeSnapshotOriginalName = null
    ): int
    {
        $id = $this->db->insert('applications', [
            'job_id'              => $jobId,
            'seeker_id'           => $seekerId,
            'cover_letter_path'   => $coverLetterPath,
            'cover_letter_text'   => $coverLetterText,
            'resume_snapshot_path'          => $resumeSnapshotPath,
            'resume_snapshot_original_name' => $resumeSnapshotOriginalName,
            'status'              => 'applied',
            'is_read_by_employer' => 0,
            'applied_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);

        // Increment job application counter
        $this->db->query(
            "UPDATE job_listings SET application_count = application_count + 1 WHERE id = ?",
            [$jobId]
        );

        return $id;
    }

    // ── Withdraw application ──────────────────────────────────────────────────
    public function withdraw(int $appId, int $seekerId): bool
    {
        $app = $this->db->fetchOne(
            "SELECT * FROM applications WHERE id = ? AND seeker_id = ?",
            [$appId, $seekerId]
        );
        if (!$app) return false;

        // Only allow withdrawing if not already decided
        if (in_array($app['status'], ['hired', 'offered'])) return false;

        $this->db->update('applications',
            ['status' => 'withdrawn', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$appId]
        );
        return true;
    }

    // ── Recent applications (for dashboard widget) ────────────────────────────
    public function getRecent(int $seekerId, int $limit = 5): array
    {
        return $this->db->fetchAll(
            "SELECT a.*, jl.title AS job_title, ep.company_name, ep.logo_path AS company_logo
               FROM applications a
               JOIN job_listings jl      ON jl.id  = a.job_id
               JOIN employer_profiles ep ON ep.id  = jl.employer_id
              WHERE a.seeker_id = ?
              ORDER BY a.applied_at DESC
              LIMIT ?",
            [$seekerId, $limit]
        );
    }
}
