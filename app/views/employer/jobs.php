<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">My Job Listings</h1>
        <p class="text-muted small mb-0">Manage all your job postings in one place.</p>
    </div>
    <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Post New Job
    </a>
</div>

<?php if (empty($jobs)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php empty_state('jobs', 'No job listings yet', 'Post your first job and start connecting with qualified candidates in Tamale and beyond.',
          ['label'=>'Post Your First Job', 'url'=>'/employer/jobs/create', 'icon'=>'plus-circle'], 'lg'); ?>
    </div>
</div>
<?php else: ?>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th style="width:40%;">Job Title</th>
                    <th>Status</th>
                    <th>Applications</th>
                    <th>Views</th>
                    <th>Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td>
                        <div class="fw-700"><?= e($job['title']) ?></div>
                        <div class="text-muted small d-flex flex-wrap gap-2 mt-1">
                            <span><?= status_label($job['job_type']) ?></span>
                            <span>·</span>
                            <span><?= $job['is_remote'] ? 'Remote' : e($job['location_city'] ?? 'N/A') ?></span>
                            <?php if ($job['expires_at']): ?>
                            <span>·</span>
                            <span class="<?= strtotime($job['expires_at']) < strtotime('+3 days') ? 'text-danger fw-600' : '' ?>">
                                Expires <?= format_date($job['expires_at'], 'M d, Y') ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= status_badge($job['status']) ?> rounded-pill">
                            <?= ucfirst($job['status']) ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= url('/employer/jobs/' . $job['id'] . '/applicants') ?>"
                           class="fw-700 text-decoration-none">
                            <?= $job['app_count'] ?>
                        </a>
                        <?php if (($job['shortlist_count'] ?? 0) > 0): ?>
                        <div class="text-success small"><?= $job['shortlist_count'] ?> shortlisted</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="fw-600"><?= number_format($job['view_count']) ?></span>
                    </td>
                    <td>
                        <span class="text-muted small"><?= format_date($job['created_at'], 'M d, Y') ?></span>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <!-- View applicants -->
                            <a href="<?= url('/employer/jobs/' . $job['id'] . '/applicants') ?>"
                               class="btn btn-sm btn-outline-primary"
                               data-bs-toggle="tooltip" title="View Applicants">
                                <i class="bi bi-people"></i>
                            </a>
                            <!-- Edit -->
                            <?php if (in_array($job['status'], ['active','draft','closed'])): ?>
                            <a href="<?= url('/employer/jobs/' . $job['id'] . '/edit') ?>"
                               class="btn btn-sm btn-outline-secondary"
                               data-bs-toggle="tooltip" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <?php endif; ?>
                            <!-- Close / Repost -->
                            <?php if ($job['status'] === 'active'): ?>
                            <form method="POST" action="<?= url('/employer/jobs/' . $job['id'] . '/close') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-warning"
                                        data-confirm="Close this job listing?"
                                        data-bs-toggle="tooltip" title="Close Job">
                                    <i class="bi bi-pause-circle"></i>
                                </button>
                            </form>
                            <?php elseif (in_array($job['status'], ['closed','expired'])): ?>
                            <form method="POST" action="<?= url('/employer/jobs/' . $job['id'] . '/repost') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-success"
                                        data-bs-toggle="tooltip" title="Repost for 30 days">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <!-- Delete -->
                            <form method="POST" action="<?= url('/employer/jobs/' . $job['id'] . '/delete') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="Permanently delete this job listing? This cannot be undone."
                                        data-bs-toggle="tooltip" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
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
<?php endif; ?>
