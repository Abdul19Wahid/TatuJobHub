<?php
require_once ROOT_PATH . '/core/Controller.php';

class MessageController extends Controller
{
    private Database $db;

    public function __construct()
    {
        $this->requireLogin();
        $this->db = Database::getInstance();
    }

    // ── Inbox — list all conversations for current user ───────────────────
    public function inbox(): void
    {
        $userId = Session::id();
        $role   = Session::role();

        // Get conversations where this user is either employer or seeker
        $conversations = $this->db->fetchAll(
            "SELECT c.*,
                    -- other party info
                    other_u.id        AS other_id,
                    other_u.full_name AS other_name,
                    other_u.role      AS other_role,
                    -- job context
                    jl.title          AS job_title,
                    jl.slug           AS job_slug,
                    -- last message preview
                    (SELECT m.body FROM messages m
                      WHERE m.conversation_id = c.id
                      ORDER BY m.created_at DESC LIMIT 1) AS last_body,
                    (SELECT m.created_at FROM messages m
                      WHERE m.conversation_id = c.id
                      ORDER BY m.created_at DESC LIMIT 1) AS last_at,
                    -- unread count for this user
                    (SELECT COUNT(*) FROM messages m
                      WHERE m.conversation_id = c.id
                        AND m.sender_id != ?
                        AND m.is_read = 0) AS unread_count
               FROM conversations c
               JOIN users other_u ON other_u.id = IF(c.employer_id = ?, c.seeker_id, c.employer_id)
               LEFT JOIN job_listings jl ON jl.id = c.job_id
              WHERE c.employer_id = ? OR c.seeker_id = ?
              ORDER BY c.last_message_at DESC",
            [$userId, $userId, $userId, $userId]
        );

        $totalUnread = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages m
               JOIN conversations c ON c.id = m.conversation_id
              WHERE (c.employer_id = ? OR c.seeker_id = ?)
                AND m.sender_id != ? AND m.is_read = 0",
            [$userId, $userId, $userId]
        );

        $layout = $this->layoutFor($role);
        $this->view('messages/inbox', [
            'title'        => 'Messages',
            'conversations'=> $conversations,
            'totalUnread'  => $totalUnread,
        ], $layout);
    }

    // ── Show conversation thread ──────────────────────────────────────────
    public function show(array $params): void
    {
        $convId = (int)($params['id'] ?? 0);
        $userId = Session::id();

        $conv = $this->db->fetchOne(
            "SELECT c.*,
                    eu.full_name AS employer_name,
                    su.full_name AS seeker_name,
                    jl.title     AS job_title,
                    jl.slug      AS job_slug
               FROM conversations c
               JOIN users eu ON eu.id = c.employer_id
               JOIN users su ON su.id = c.seeker_id
               LEFT JOIN job_listings jl ON jl.id = c.job_id
              WHERE c.id = ?
                AND (c.employer_id = ? OR c.seeker_id = ?)
              LIMIT 1",
            [$convId, $userId, $userId]
        );

        if (!$conv) $this->abort(404);

        // Mark all unread messages from the OTHER person as read
        $this->db->query(
            "UPDATE messages
                SET is_read = 1
              WHERE conversation_id = ?
                AND sender_id != ?
                AND is_read = 0",
            [$convId, $userId]
        );

        // Load messages
        $messages = $this->db->fetchAll(
            "SELECT m.*, u.full_name AS sender_name, u.role AS sender_role
               FROM messages m
               JOIN users u ON u.id = m.sender_id
              WHERE m.conversation_id = ?
              ORDER BY m.created_at ASC",
            [$convId]
        );

        $otherName = Session::role() === 'employer'
            ? $conv['seeker_name']
            : $conv['employer_name'];

        $layout = $this->layoutFor(Session::role());
        $this->view('messages/conversation', [
            'title'     => 'Chat with ' . $otherName,
            'conv'      => $conv,
            'messages'  => $messages,
            'otherName' => $otherName,
            'myId'      => $userId,
        ], $layout);
    }

    // ── Send a message ────────────────────────────────────────────────────
    public function send(array $params): void
    {
        $this->verifyCsrf();
        $convId = (int)($params['id'] ?? 0);
        $userId = Session::id();
        $body   = trim($this->input('body'));

        if (empty($body)) {
            $this->flash('error', 'Message cannot be empty.');
            $this->back();
            return;
        }

        if (strlen($body) > 2000) {
            $this->flash('error', 'Message too long (max 2000 characters).');
            $this->back();
            return;
        }

        // Verify user belongs to this conversation
        $conv = $this->db->fetchOne(
            "SELECT * FROM conversations
              WHERE id = ? AND (employer_id = ? OR seeker_id = ?) LIMIT 1",
            [$convId, $userId, $userId]
        );
        if (!$conv) $this->abort(403);

        // Insert message
        $this->db->insert('messages', [
            'conversation_id' => $convId,
            'sender_id'       => $userId,
            'body'            => $body,
            'is_read'         => 0,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        // Update last_message_at on conversation
        $this->db->update('conversations',
            ['last_message_at' => date('Y-m-d H:i:s')],
            'id = ?', [$convId]
        );

        // AJAX response
        if ($this->isAjax()) {
            $user = $this->db->fetchOne("SELECT full_name FROM users WHERE id = ?", [$userId]);
            $this->json([
                'success'     => true,
                'sender_name' => $user['full_name'],
                'body'        => $body,
                'created_at'  => date('Y-m-d H:i:s'),
                'my_message'  => true,
            ]);
        }

        $this->redirect(BASE_URL . '/messages/' . $convId);
    }

    // ── Start a new conversation (employer → seeker or seeker → employer) ─
    public function start(): void
    {
        $this->verifyCsrf();
        $userId   = Session::id();
        $role     = Session::role();
        $otherId  = (int)$this->input('other_id');
        $jobId    = $this->input('job_id') ? (int)$this->input('job_id') : null;
        $body     = trim($this->input('body'));

        if (empty($body)) {
            $this->flash('error', 'Message cannot be empty.');
            $this->back();
            return;
        }

        // Determine employer_id and seeker_id
        if ($role === 'employer') {
            $employerId = $userId;
            $seekerId   = $otherId;
        } else {
            $employerId = $otherId;
            $seekerId   = $userId;
        }

        // Find or create conversation
        $conv = $this->db->fetchOne(
            "SELECT * FROM conversations
              WHERE employer_id = ? AND seeker_id = ?
                AND (job_id = ? OR (job_id IS NULL AND ? IS NULL)) LIMIT 1",
            [$employerId, $seekerId, $jobId, $jobId]
        );

        if ($conv) {
            $convId = $conv['id'];
        } else {
            $convId = $this->db->insert('conversations', [
                'employer_id'     => $employerId,
                'seeker_id'       => $seekerId,
                'job_id'          => $jobId,
                'last_message_at' => date('Y-m-d H:i:s'),
                'created_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        // Insert first message
        $this->db->insert('messages', [
            'conversation_id' => $convId,
            'sender_id'       => $userId,
            'body'            => $body,
            'is_read'         => 0,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);

        $this->db->update('conversations',
            ['last_message_at' => date('Y-m-d H:i:s')],
            'id = ?', [$convId]
        );

        $this->flash('success', 'Message sent!');
        $this->redirect(BASE_URL . '/messages/' . $convId);
    }

    // ── AJAX: poll for new messages ────────────────────────────────────────
    public function poll(array $params): void
    {
        $this->requireLogin();
        $convId   = (int)($params['id'] ?? 0);
        $userId   = Session::id();
        $afterId  = (int)($this->query('after') ?? 0);

        // Verify access
        $conv = $this->db->fetchOne(
            "SELECT id FROM conversations WHERE id = ? AND (employer_id=? OR seeker_id=?)",
            [$convId, $userId, $userId]
        );
        if (!$conv) { $this->json(['messages' => []]); }

        $msgs = $this->db->fetchAll(
            "SELECT m.id, m.body, m.sender_id, m.created_at,
                    u.full_name AS sender_name, u.role AS sender_role
               FROM messages m
               JOIN users u ON u.id = m.sender_id
              WHERE m.conversation_id = ? AND m.id > ?
              ORDER BY m.created_at ASC",
            [$convId, $afterId]
        );

        // Mark polled messages as read
        if (!empty($msgs)) {
            $this->db->query(
                "UPDATE messages SET is_read=1
                  WHERE conversation_id=? AND sender_id!=? AND is_read=0",
                [$convId, $userId]
            );
        }

        $this->json([
            'messages' => array_map(fn($m) => [
                'id'          => $m['id'],
                'body'        => e($m['body']),
                'sender_name' => $m['sender_name'],
                'sender_role' => $m['sender_role'],
                'my_message'  => ((int)$m['sender_id'] === $userId),
                'created_at'  => $m['created_at'],
                'time'        => time_ago($m['created_at']),
            ], $msgs)
        ]);
    }

    // ── API: unread message count ─────────────────────────────────────────
    public function unreadCount(): void
    {
        if (!$this->isLoggedIn()) { $this->json(['count' => 0]); }
        $userId = Session::id();
        $count  = (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM messages m
               JOIN conversations c ON c.id = m.conversation_id
              WHERE (c.employer_id=? OR c.seeker_id=?)
                AND m.sender_id != ? AND m.is_read = 0",
            [$userId, $userId, $userId]
        );
        $this->json(['count' => $count]);
    }

    // ── Helper ────────────────────────────────────────────────────────────
    private function layoutFor(string $role): string
    {
        return match($role) {
            'employer' => 'employer',
            'admin'    => 'admin',
            default    => 'seeker',
        };
    }
}
