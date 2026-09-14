<?php require_once VIEW_PATH . '/partials/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="d-flex align-items-center justify-content-between mb-4">
                <h4 class="fw-700 mb-0">Notifications</h4>
                <?php if (!empty($notifications)): ?>
                <button id="btnClearRead" class="btn btn-sm btn-outline-secondary">
                    Clear read
                </button>
                <?php endif; ?>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-bell-slash fs-1 d-block mb-3"></i>
                    You have no notifications yet.
                </div>
            <?php else: ?>
                <div class="notification-list" id="notifList">
                    <?php foreach ($notifications as $n): ?>
                    <div class="notification-item card mb-2 <?= $n['is_read'] ? 'opacity-75' : 'border-primary' ?>"
                         data-id="<?= $n['id'] ?>">
                        <div class="card-body py-3 d-flex gap-3 align-items-start">

                            <div class="notif-icon flex-shrink-0">
                                <?= notifIcon($n['type']) ?>
                            </div>

                            <div class="flex-grow-1">
                                <div class="fw-600 small"><?= e($n['title']) ?></div>
                                <div class="text-muted small"><?= e($n['message']) ?></div>
                                <div class="text-muted" style="font-size:.75rem">
                                    <?= e($n['created_at']) ?>
                                </div>
                            </div>

                            <div class="d-flex gap-2 flex-shrink-0">
                                <?php if ($n['link']): ?>
                                <a href="<?= e($n['link']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                <?php endif; ?>
                                <button class="btn btn-sm btn-outline-danger btn-delete-notif"
                                        data-id="<?= $n['id'] ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($p = 1; $p <= $pagination['total_pages']; $p++): ?>
                        <li class="page-item <?= $p === $pagination['page'] ? 'active' : '' ?>">
                            <a class="page-link" href="<?= url('/notifications?page=' . $p) ?>">
                                <?= $p ?>
                            </a>
                        </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Delete single notification
    document.querySelectorAll('.btn-delete-notif').forEach(btn => {
        btn.addEventListener('click', function () {
            const id   = this.dataset.id;
            const item = this.closest('.notification-item');
            fetch('<?= url('/notifications/delete') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id
            }).then(() => item.remove());
        });
    });

    // Clear all read
    const btnClear = document.getElementById('btnClearRead');
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            fetch('<?= url('/notifications/clear-read') ?>', { method: 'POST' })
                .then(() => {
                    document.querySelectorAll('.notification-item.opacity-75').forEach(el => el.remove());
                });
        });
    }
});
</script>

<?php require_once VIEW_PATH . '/partials/footer.php'; ?>

<?php
// ── Helper: icon per notification type ───────────────────────────────────────
function notifIcon(string $type): string
{
    $map = [
        'application_received' => '<span class="badge bg-primary rounded-circle p-2"><i class="bi bi-person-fill"></i></span>',
        'application_status'   => '<span class="badge bg-info rounded-circle p-2"><i class="bi bi-clipboard-check-fill"></i></span>',
        'interview_scheduled'  => '<span class="badge bg-success rounded-circle p-2"><i class="bi bi-calendar-check-fill"></i></span>',
        'interview_reminder'   => '<span class="badge bg-warning rounded-circle p-2"><i class="bi bi-alarm-fill"></i></span>',
        'message_received'     => '<span class="badge bg-secondary rounded-circle p-2"><i class="bi bi-chat-fill"></i></span>',
        'company_verified'     => '<span class="badge bg-success rounded-circle p-2"><i class="bi bi-patch-check-fill"></i></span>',
        'job_expired'          => '<span class="badge bg-danger rounded-circle p-2"><i class="bi bi-briefcase-fill"></i></span>',
        'general'              => '<span class="badge bg-dark rounded-circle p-2"><i class="bi bi-bell-fill"></i></span>',
    ];
    return $map[$type] ?? $map['general'];
}
?>
