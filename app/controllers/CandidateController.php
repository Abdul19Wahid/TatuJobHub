<?php
require_once ROOT_PATH . '/core/Controller.php';

class CandidateController extends Controller
{
    public function search(): void
    {
        $this->requireRole('employer');
        $db = Database::getInstance();

        $q      = $this->query('q');
        $skill  = $this->query('skill');
        $loc    = $this->query('location');

        $where  = ['u.role = ?', 'u.is_active = 1', 'jsp.is_open_to_work = 1'];
        $params = ['seeker'];

        if ($q)     { $where[] = '(u.full_name LIKE ? OR jsp.headline LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        if ($loc)   { $where[] = 'jsp.location_city LIKE ?';   $params[] = "%$loc%"; }
        if ($skill) { $where[] = 'EXISTS(SELECT 1 FROM seeker_skills ss WHERE ss.seeker_id=jsp.id AND ss.skill_name LIKE ?)'; $params[] = "%$skill%"; }

        $sql = "SELECT jsp.*, u.full_name, u.email
                  FROM job_seeker_profiles jsp
                  JOIN users u ON u.id = jsp.user_id
                 WHERE " . implode(' AND ', $where) . "
                 ORDER BY jsp.updated_at DESC";

        $page  = $this->currentPage();
        $limit = 12;
        $total = (int)$db->fetchColumn("SELECT COUNT(*) FROM ($sql) AS _c", $params);
        $data  = $db->fetchAll("$sql LIMIT $limit OFFSET " . (($page-1)*$limit), $params);

        $this->view('employer/candidates', [
            'title'      => 'Candidate Search',
            'candidates' => $data,
            'paging'     => ['total'=>$total,'pages'=>(int)ceil($total/$limit),'current_page'=>$page,'per_page'=>$limit],
            'filters'    => compact('q','skill','loc'),
        ], 'employer');
    }

    public function show(array $params): void
    {
        $this->requireRole('employer');
        $db      = Database::getInstance();
        $seekerId = (int)$params['id'];

        $profile = $db->fetchOne(
            "SELECT jsp.*, u.full_name, u.email
               FROM job_seeker_profiles jsp
               JOIN users u ON u.id = jsp.user_id
              WHERE jsp.id = ? AND jsp.is_open_to_work = 1 LIMIT 1",
            [$seekerId]
        );
        if (!$profile) $this->abort(404);

        $skills = $db->fetchAll("SELECT * FROM seeker_skills WHERE seeker_id = ?", [$seekerId]);
        $exp    = $db->fetchAll("SELECT * FROM seeker_work_experience WHERE seeker_id = ? ORDER BY is_current DESC, start_date DESC", [$seekerId]);
        $edu    = $db->fetchAll("SELECT * FROM seeker_education WHERE seeker_id = ? ORDER BY end_year DESC", [$seekerId]);

        $this->view('employer/candidate-profile', [
            'title'   => 'Candidate — ' . $profile['full_name'],
            'profile' => $profile,
            'skills'  => $skills,
            'exp'     => $exp,
            'edu'     => $edu,
        ], 'employer');
    }
}
