<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-800 mb-0">Notifications</h1>
        <p class="text-muted small mb-0">Stay updated on applications and activity.</p>
    </div>
</div>

<?php if (empty($notifs)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-bell text-muted" style="font-size:3rem;"></i>
        <h5 class="fw-700 mt-3">No notifications yet</h5>
        <p class="text-muted">You'll be notified when candidates apply to your jobs.</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="list-group list-group-flush rounded-3">
        <?php foreach ($notifs as $n): ?>
        <div class="list-group-item border-0 px-4 py-3 <?= !$n['is_read'] ? 'bg-light' : '' ?>">
            <div class="d-flex gap-3 align-items-start">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                     style="width:40px;height:40px;background:#EBF5FF;">
                    <i class="bi bi-<?= $n['type'] === 'application_received' ? 'file-earmark-check' : 'bell' ?> text-primary"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-600 small"><?= e($n['title']) ?></div>
                    <div class="text-muted small"><?= e($n['message']) ?></div>
                    <div class="text-muted mt-1" style="font-size:11px;"><?= time_ago($n['created_at']) ?></div>
                </div>
                <?php if (!$n['is_read']): ?>
                <span class="badge bg-primary rounded-circle" style="width:8px;height:8px;display:inline-block;padding:0;"></span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
