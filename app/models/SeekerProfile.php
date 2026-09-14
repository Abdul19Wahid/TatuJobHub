<?php
require_once ROOT_PATH . '/core/Model.php';

class SeekerProfile extends Model
{
    protected string $table = 'job_seeker_profiles';

    // ── Fetch full profile by user_id ─────────────────────────────────────────
    public function getByUserId(int $userId): array|false
    {
        return $this->db->fetchOne(
            "SELECT jsp.*, u.full_name, u.email, u.role
               FROM job_seeker_profiles jsp
               JOIN users u ON u.id = jsp.user_id
              WHERE jsp.user_id = ? LIMIT 1",
            [$userId]
        );
    }

    // ── Update profile fields ─────────────────────────────────────────────────
    public function updateByUserId(int $userId, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->update('job_seeker_profiles', $data, 'user_id = ?', [$userId]);
    }

    // ── Avatar ────────────────────────────────────────────────────────────────
    public function updateAvatar(int $userId, string $path): void
    {
        $this->db->update('job_seeker_profiles',
            ['avatar_path' => $path, 'updated_at' => date('Y-m-d H:i:s')],
            'user_id = ?', [$userId]
        );
    }

    // ── Resume ────────────────────────────────────────────────────────────────
    public function updateResume(int $userId, string $path, string $originalName): void
    {
        $this->db->update('job_seeker_profiles',
            ['resume_path' => $path, 'resume_original_name' => $originalName,
             'updated_at'  => date('Y-m-d H:i:s')],
            'user_id = ?', [$userId]
        );
    }

    // ── Skills ────────────────────────────────────────────────────────────────
    public function getSkills(int $seekerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM seeker_skills WHERE seeker_id = ? ORDER BY skill_name ASC",
            [$seekerId]
        );
    }

    public function addSkill(int $seekerId, string $skill, string $proficiency): int
    {
        // Prevent duplicates
        $exists = $this->db->fetchColumn(
            "SELECT id FROM seeker_skills WHERE seeker_id = ? AND skill_name = ?",
            [$seekerId, trim($skill)]
        );
        if ($exists) return (int)$exists;

        return $this->db->insert('seeker_skills', [
            'seeker_id'   => $seekerId,
            'skill_name'  => trim($skill),
            'proficiency' => $proficiency,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteSkill(int $skillId, int $seekerId): void
    {
        // Verify ownership before deleting
        $this->db->delete('seeker_skills',
            'id = ? AND seeker_id = ?', [$skillId, $seekerId]
        );
    }

    // ── Work experience ───────────────────────────────────────────────────────
    public function getExperience(int $seekerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM seeker_work_experience
              WHERE seeker_id = ?
              ORDER BY is_current DESC, end_date DESC, start_date DESC",
            [$seekerId]
        );
    }

    public function addExperience(int $seekerId, array $data): int
    {
        return $this->db->insert('seeker_work_experience', [
            'seeker_id'    => $seekerId,
            'job_title'    => $data['job_title'],
            'company_name' => $data['company_name'],
            'location'     => $data['location'] ?? null,
            'start_date'   => $data['start_date'],
            'end_date'     => !empty($data['is_current']) ? null : ($data['end_date'] ?? null),
            'is_current'   => !empty($data['is_current']) ? 1 : 0,
            'description'  => $data['description'] ?? null,
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteExperience(int $expId, int $seekerId): void
    {
        $this->db->delete('seeker_work_experience',
            'id = ? AND seeker_id = ?', [$expId, $seekerId]
        );
    }

    // ── Education ─────────────────────────────────────────────────────────────
    public function getEducation(int $seekerId): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM seeker_education
              WHERE seeker_id = ?
              ORDER BY is_current DESC, end_year DESC",
            [$seekerId]
        );
    }

    public function addEducation(int $seekerId, array $data): int
    {
        return $this->db->insert('seeker_education', [
            'seeker_id'      => $seekerId,
            'institution'    => $data['institution'],
            'degree'         => $data['degree'],
            'field_of_study' => $data['field_of_study'] ?? null,
            'start_year'     => $data['start_year'],
            'end_year'       => !empty($data['is_current']) ? null : ($data['end_year'] ?? null),
            'is_current'     => !empty($data['is_current']) ? 1 : 0,
            'grade'          => $data['grade'] ?? null,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    public function deleteEducation(int $eduId, int $seekerId): void
    {
        $this->db->delete('seeker_education',
            'id = ? AND seeker_id = ?', [$eduId, $seekerId]
        );
    }

    // ── Profile completion % ──────────────────────────────────────────────────
    public function completionScore(array $profile, array $skills, array $exp, array $edu): int
    {
        $checks = [
            !empty($profile['headline'])    => 15,
            !empty($profile['bio'])         => 10,
            !empty($profile['phone'])       => 5,
            !empty($profile['location_city']) => 5,
            !empty($profile['avatar_path']) => 10,
            !empty($profile['resume_path']) => 20,
            count($skills) > 0             => 15,
            count($exp)    > 0             => 10,
            count($edu)    > 0             => 10,
        ];
        $score = 0;
        foreach ($checks as $condition => $points) {
            if ($condition) $score += $points;
        }
        return min(100, $score);
    }
}
