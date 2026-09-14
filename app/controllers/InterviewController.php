<?php
require_once ROOT_PATH . '/core/Controller.php';
require_once ROOT_PATH . '/app/helpers/NotificationService.php';

class InterviewController extends Controller
{
    /*
     * Lazily flips overdue, unanswered interviews to 'expired'. There's no
     * cron/queue in this stack, so this runs on every read instead — cheap,
     * indexed, and self-correcting each time either side loads a page that
     * touches interviews.
     */
    private function expireOverdue(Database $db): void
    {
        $db->query(
            "UPDATE interview_schedules
                SET status = 'expired', updated_at = NOW()
              WHERE status = 'scheduled'
                AND response_deadline IS NOT NULL
                AND response_deadline < NOW()"
        );
    }

    public function index(): void
    {
        $this->requireRole('employer');
        $db = Database::getInstance();
        $this->expireOverdue($db);
        $ep = $db->fetchOne("SELECT * FROM employer_profiles WHERE user_id = ?", [Session::id()]);

        $interviews = $ep ? $db->fetchAll(
            "SELECT isc.*, a.id AS app_id,
                    u.full_name AS seeker_name, jl.title AS job_title
               FROM interview_schedules isc
               JOIN applications a          ON a.id  = isc.application_id
               JOIN job_listings jl         ON jl.id = a.job_id
               JOIN job_seeker_profiles jsp ON jsp.id = a.seeker_id
               JOIN users u                 ON u.id  = jsp.user_id
              WHERE jl.employer_id = ?
              ORDER BY isc.scheduled_at DESC",
            [$ep['id']]
        ) : [];

        $this->view('employer/interviews', [
            'title'      => 'Interviews',
            'interviews' => $interviews,
        ], 'employer');
    }

    public function schedule(): void
    {
        $this->requireRole('employer');
        $this->verifyCsrf();

        $db    = Database::getInstance();
        $appId = (int)$this->input('application_id');

        $scheduledAt = $this->input('scheduled_at');
        $deadline    = trim((string)$this->input('response_deadline'));
        $deadline    = $deadline !== '' ? $deadline : null;

        /*
         * Response deadline must fall on or before the interview itself —
         * asking a candidate to confirm after the interview already
         * happened doesn't make sense.
         */
        if ($deadline !== null && $scheduledAt && strtotime($deadline) > strtotime($scheduledAt)) {
            $this->flash('error', 'Response deadline must be on or before the interview date & time.');
            $this->back();
            return;
        }

        $db->insert('interview_schedules', [
            'application_id'    => $appId,
            'scheduled_at'      => $scheduledAt,
            'response_deadline' => $deadline,
            'duration_mins'     => (int)$this->input('duration_mins', 60),
            'interview_type'    => $this->input('interview_type', 'video'),
            'meeting_link'      => $this->input('meeting_link'),
            'location_address'=> $this->input('location_address'),
            'instructions'   => $this->input('instructions'),
            'status'         => 'scheduled',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        $db->update('applications',
            ['status' => 'interview_scheduled', 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$appId]
        );

        // Notify seeker
        $app = $db->fetchOne(
            "SELECT a.seeker_id, jl.title AS job_title, ep.company_name,
                    u.id AS seeker_user_id
               FROM applications a
               JOIN job_listings jl ON jl.id = a.job_id
               JOIN employer_profiles ep ON ep.id = jl.employer_id
               JOIN job_seeker_profiles jsp ON jsp.id = a.seeker_id
               JOIN users u ON u.id = jsp.user_id
              WHERE a.id = ? LIMIT 1",
            [$appId]
        );
        if ($app) {
            NotificationService::interviewScheduled(
                $app['seeker_user_id'],
                $app['job_title'],
                $app['company_name'],
                $this->input('scheduled_at'),
                $this->input('interview_type', 'video'),
                $this->input('meeting_link')
            );
        }

        $this->flash('success', 'Interview scheduled! The candidate has been notified.');
        $this->back();
    }

    public function cancel(array $params): void
    {
        $this->requireRole('employer');
        $this->verifyCsrf();
        $db = Database::getInstance();
        $db->update('interview_schedules',
            ['status' => 'cancelled', 'cancelled_reason' => $this->input('reason'),
             'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [(int)$params['id']]
        );
        $this->flash('success', 'Interview cancelled.');
        $this->back();
    }

    // ── Seeker: confirm an interview invite ────────────────────────────────
    public function confirm(array $params): void
    {
        $this->requireRole('seeker');
        $this->verifyCsrf();

        $db   = Database::getInstance();
        $this->expireOverdue($db);

        $interview = $this->ownedByCurrentSeeker($db, (int)$params['id']);
        if (!$interview) { $this->abort(404); return; }

        if ($interview['status'] !== 'scheduled') {
            $this->flash('error', 'This interview can no longer be confirmed.');
            $this->back();
            return;
        }

        $db->update('interview_schedules',
            ['status' => 'confirmed', 'confirmed_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$interview['id']]
        );

        $this->flash('success', 'Interview confirmed.');
        $this->back();
    }

    // ── Seeker: decline an interview invite ────────────────────────────────
    public function decline(array $params): void
    {
        $this->requireRole('seeker');
        $this->verifyCsrf();

        $db = Database::getInstance();
        $this->expireOverdue($db);

        $interview = $this->ownedByCurrentSeeker($db, (int)$params['id']);
        if (!$interview) { $this->abort(404); return; }

        if ($interview['status'] !== 'scheduled') {
            $this->flash('error', 'This interview can no longer be declined.');
            $this->back();
            return;
        }

        $db->update('interview_schedules',
            ['status' => 'declined', 'declined_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')],
            'id = ?', [$interview['id']]
        );

        $this->flash('success', 'Interview declined.');
        $this->back();
    }

    // ── Ownership check: interview must belong to the logged-in seeker ────
    private function ownedByCurrentSeeker(Database $db, int $interviewId): array|false
    {
        return $db->fetchOne(
            "SELECT isc.* FROM interview_schedules isc
               JOIN applications a          ON a.id  = isc.application_id
               JOIN job_seeker_profiles jsp ON jsp.id = a.seeker_id
              WHERE isc.id = ? AND jsp.user_id = ?",
            [$interviewId, Session::id()]
        );
    }
}
