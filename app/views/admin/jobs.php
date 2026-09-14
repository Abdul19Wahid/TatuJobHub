<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Job Moderation</h1>
        <p class="text-muted small mb-0">Review and moderate all job listings on the platform.</p>
    </div>
</div>

<!-- Status tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php foreach (['active'=>'Active','closed'=>'Closed','expired'=>'Expired','removed'=>'Removed'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>"
       class="btn btn-sm <?= $status === $v ? 'btn-primary' : 'btn-light' ?>">
        <?= $l ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Job Title</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Posted</th>
                    <th>Applications</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                <tr><td colspan="6" class="text-center py-4 text-muted">No jobs found.</td></tr>
                <?php else: ?>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td>
                        <div class="fw-700 small"><?= e(truncate($job['title'], 50)) ?></div>
                        <div class="text-muted" style="font-size:11px;">
                            <?= $job['is_remote'] ? 'Remote' : e($job['location_city'] ?? 'N/A') ?>
                            · <?= status_label($job['experience_level']) ?>
                        </div>
                    </td>
                    <td class="small"><?= e($job['company_name']) ?></td>
                    <td>
                        <span class="badge bg-light text-dark fw-500">
                            <?= status_label($job['job_type']) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= format_date($job['created_at'], 'M d, Y') ?></td>
                    <td class="fw-600"><?= number_format($job['application_count']) ?></td>
                    <td>
                        <?php if ($job['status'] === 'active'): ?>
                        <form method="POST" action="<?= url('/admin/jobs/' . $job['id'] . '/remove') ?>"
                              data-no-loading class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    data-confirm="Remove this job listing from the platform?">
                                <i class="bi bi-slash-circle me-1"></i>Remove
                            </button>
                        </form>
                        <?php elseif ($job['status'] === 'removed'): ?>
                        <span class="badge bg-secondary">Removed</span>
                        <?php else: ?>
                        <span class="badge bg-secondary"><?= ucfirst($job['status']) ?></span>
                        <?php endif; ?>
                    </td>
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
            <a class="page-link border-0" href="?status=<?= $status ?>&page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
