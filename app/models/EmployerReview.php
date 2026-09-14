<?php
require_once ROOT_PATH . '/core/Model.php';

class EmployerReview extends Model
{
    protected string $table = 'employer_reviews';

    // ── Eligibility ──────────────────────────────────────────────────────────
    // A seeker may review an employer if they have at least one application
    // to a job posted by that employer, and haven't already reviewed them.
    public function eligibleApplication(int $seekerId, int $employerId): array|false
    {
        return $this->db->fetchOne(
            "SELECT a.id, a.status, jl.title
               FROM applications a
               JOIN job_listings jl ON jl.id = a.job_id
              WHERE a.seeker_id = ? AND jl.employer_id = ?
              ORDER BY a.applied_at DESC
              LIMIT 1",
            [$seekerId, $employerId]
        );
    }

    public function hasReviewed(int $seekerId, int $employerId): bool
    {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM employer_reviews WHERE seeker_id = ? AND employer_id = ?",
            [$seekerId, $employerId]
        );
    }

    // ── Create ───────────────────────────────────────────────────────────────
    // Named submit() (not create()) to avoid clashing with Model::create(array $data)
    public function submit(int $employerId, int $seekerId, int $applicationId,
                            int $rating, ?string $text): int
    {
        $rating = max(1, min(5, $rating));
        return $this->db->insert('employer_reviews', [
            'employer_id'    => $employerId,
            'seeker_id'      => $seekerId,
            'application_id' => $applicationId,
            'rating'         => $rating,
            'review_text'    => $text ?: null,
            'status'         => 'visible',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    // ── Public: company page ────────────────────────────────────────────────
    public function getStats(int $employerId): array
    {
        $row = $this->db->fetchOne(
            "SELECT COUNT(*) AS total, AVG(rating) AS avg_rating
               FROM employer_reviews
              WHERE employer_id = ? AND status = 'visible'",
            [$employerId]
        );
        return [
            'total'      => (int)($row['total'] ?? 0),
            'avg_rating' => $row && $row['total'] > 0 ? round((float)$row['avg_rating'], 1) : null,
        ];
    }

    public function getForEmployerPublic(int $employerId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT er.*, u.full_name AS seeker_name
               FROM employer_reviews er
               JOIN job_seeker_profiles sp ON sp.id = er.seeker_id
               JOIN users u ON u.id = sp.user_id
              WHERE er.employer_id = ? AND er.status = 'visible'
              ORDER BY er.created_at DESC
              LIMIT {$limit}",
            [$employerId]
        );
    }

    // ── Employer: own dashboard view ────────────────────────────────────────
    public function getForEmployer(int $employerId): array
    {
        return $this->db->fetchAll(
            "SELECT er.*, u.full_name AS seeker_name
               FROM employer_reviews er
               JOIN job_seeker_profiles sp ON sp.id = er.seeker_id
               JOIN users u ON u.id = sp.user_id
              WHERE er.employer_id = ?
              ORDER BY er.created_at DESC",
            [$employerId]
        );
    }

    // ── Admin: moderation ────────────────────────────────────────────────────
    public function moderationQuery(string $status = ''): array
    {
        $where  = [];
        $params = [];
        if ($status !== '') {
            $where[]  = 'er.status = ?';
            $params[] = $status;
        }
        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

        $sql = "SELECT er.*, ep.company_name, u.full_name AS seeker_name
                  FROM employer_reviews er
                  JOIN employer_profiles ep ON ep.id = er.employer_id
                  JOIN job_seeker_profiles sp ON sp.id = er.seeker_id
                  JOIN users u ON u.id = sp.user_id
                  {$whereSql}
                 ORDER BY er.created_at DESC";
        return [$sql, $params];
    }

    public function setStatus(int $id, string $status): void
    {
        $this->db->update('employer_reviews', ['status' => $status], 'id = ?', [$id]);
    }

    // Named destroy() (not delete()) since Model::delete(int $id) already
    // does exactly this — kept for clarity in the admin controller call site.
    public function destroy(int $id): void
    {
        $this->db->delete('employer_reviews', 'id = ?', [$id]);
    }
}
