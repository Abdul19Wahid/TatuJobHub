<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">
            <i class="bi bi-chat-dots me-2 text-primary"></i>Messages
        </h1>
        <p class="text-muted small mb-0">
            Your conversations with <?= Session::role()==='employer' ? 'candidates' : 'employers' ?>.
        </p>
    </div>
    <?php if($totalUnread > 0): ?>
    <span class="badge bg-primary rounded-pill px-3 py-2">
        <?= $totalUnread ?> unread
    </span>
    <?php endif; ?>
</div>

<?php if (empty($conversations)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-chat-dots text-muted" style="font-size:3rem;"></i>
        <h5 class="fw-700 mt-3">No messages yet</h5>
        <p class="text-muted mb-3">
            <?php if(Session::role()==='seeker'): ?>
                Messages from employers about your applications will appear here.
            <?php else: ?>
                Start a conversation with a candidate from their profile or applicant card.
            <?php endif; ?>
        </p>
        <?php if(Session::role()==='employer'): ?>
        <a href="<?= url('/employer/candidates') ?>" class="btn btn-primary px-4">
            <i class="bi bi-people me-2"></i>Browse Candidates
        </a>
        <?php else: ?>
        <a href="<?= url('/jobs') ?>" class="btn btn-primary px-4">
            <i class="bi bi-search me-2"></i>Browse Jobs
        </a>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush rounded-3">
        <?php foreach ($conversations as $conv): ?>
        <?php $hasUnread = $conv['unread_count'] > 0; ?>
        <a href="<?= url('/messages/' . $conv['id']) ?>"
           class="list-group-item list-group-item-action border-0 px-4 py-3
                  <?= $hasUnread ? 'bg-light' : '' ?>"
           style="text-decoration:none;">
            <div class="d-flex align-items-center gap-3">
                <!-- Avatar -->
                <div class="rounded-circle text-white d-flex align-items-center
                            justify-content-center fw-800 flex-shrink-0"
                     style="width:48px;height:48px;font-size:18px;
                            background:<?= Session::role()==='employer' ? '#1A56DB' : '#7C3AED' ?>;">
                    <?= strtoupper(substr($conv['other_name'], 0, 1)) ?>
                </div>

                <!-- Content -->
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="fw-700 <?= $hasUnread ? 'text-dark' : '' ?>">
                            <?= e($conv['other_name']) ?>
                        </div>
                        <div class="text-muted flex-shrink-0" style="font-size:11px;">
                            <?= $conv['last_at'] ? time_ago($conv['last_at']) : '' ?>
                        </div>
                    </div>
                    <?php if($conv['job_title']): ?>
                    <div class="small mb-1">
                        <span class="badge rounded-pill fw-500"
                              style="background:#EBF5FF;color:#1A56DB;font-size:10px;">
                            <i class="bi bi-briefcase me-1"></i><?= e(truncate($conv['job_title'], 40)) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex align-items-center justify-content-between gap-2">
                        <div class="text-muted small text-truncate" style="max-width:320px;">
                            <?= e(truncate($conv['last_body'] ?? 'No messages yet', 70)) ?>
                        </div>
                        <?php if($hasUnread): ?>
                        <span class="badge bg-primary rounded-pill flex-shrink-0"
                              style="font-size:11px;min-width:22px;">
                            <?= $conv['unread_count'] ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
