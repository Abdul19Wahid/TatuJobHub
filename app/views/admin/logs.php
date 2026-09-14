<div class="mb-4">
    <h1 class="h4 fw-800 mb-0">Audit Log</h1>
    <p class="text-muted small mb-0">A complete trail of all administrator actions.</p>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Target</th>
                    <th>Description</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No audit logs yet.</td></tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="text-muted small text-nowrap"><?= format_date($log['created_at'], 'M d, H:i') ?></td>
                    <td class="small fw-600"><?= e($log['admin_name'] ?? 'Deleted Admin') ?></td>
                    <td>
                        <?php
                        $actionColors = [
                            'approve' => 'bg-success', 'reject' => 'bg-danger',
                            'delete'  => 'bg-danger',  'toggle' => 'bg-warning text-dark',
                            'remove'  => 'bg-secondary',
                        ];
                        $col = 'bg-light text-dark';
                        foreach ($actionColors as $k => $v) {
                            if (str_contains($log['action'], $k)) { $col = $v; break; }
                        }
                        ?>
                        <span class="badge <?= $col ?> rounded-pill small">
                            <?= e(str_replace('_', ' ', $log['action'])) ?>
                        </span>
                    </td>
                    <td class="small text-muted">
                        <?php if ($log['entity_type'] && $log['entity_id']): ?>
                        <?= e($log['entity_type']) ?> #<?= $log['entity_id'] ?>
                        <?php else: ?>
                        —
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted" style="max-width:300px;">
                        <?= e(truncate($log['description'] ?? '', 80)) ?>
                    </td>
                    <td class="small text-muted font-monospace"><?= e($log['ip_address'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($paging['pages'] > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center gap-1">
        <?php for ($i = 1; $i <= $paging['pages']; $i++): ?>
        <li class="page-item <?= $i === $paging['current_page'] ? 'active' : '' ?>">
            <a class="page-link border-0" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
