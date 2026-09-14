<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Saved Jobs</h1>
        <p class="text-muted small mb-0">Jobs you've bookmarked for later.</p>
    </div>
    <a href="<?= url('/jobs') ?>" class="btn btn-primary">
        <i class="bi bi-search me-2"></i>Browse More Jobs
    </a>
</div>

<?php if (empty($saved)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php empty_state('saved', 'No saved jobs yet', 'Tap the bookmark icon on any job listing to save it here for later.',
          ['label'=>'Find Jobs to Save', 'url'=>'/jobs', 'icon'=>'search'], 'lg'); ?>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($saved as $job): ?>
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm job-card h-100">
            <div class="card-body p-4">
                <div class="d-flex gap-3 mb-3">
                    <div class="logo-placeholder flex-shrink-0">
                        <?php if ($job['company_logo']): ?>
                            <img src="<?= url('/file?path=' . urlencode($job['company_logo'])) ?>"
                                 alt="" class="company-logo">
                        <?php else: ?>
                            <?= strtoupper(substr($job['company_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="fw-700 mb-0">
                            <a href="<?= url('/jobs/' . $job['job_slug']) ?>"
                               class="text-dark text-decoration-none">
                                <?= e($job['title']) ?>
                            </a>
                        </h6>
                        <div class="text-muted small"><?= e($job['company_name']) ?></div>
                    </div>
                    <!-- Unsave button -->
                    <form method="POST" action="<?= url('/seeker/unsave-job/' . $job['job_id']) ?>"
                          data-no-loading class="flex-shrink-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-light text-warning"
                                data-confirm="Remove from saved jobs?"
                                title="Remove bookmark">
                            <i class="bi bi-bookmark-fill"></i>
                        </button>
                    </form>
                </div>

                <div class="d-flex flex-wrap gap-2 mb-3">
                    <span class="badge bg-light text-dark fw-500">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= $job['is_remote'] ? 'Remote' : e($job['location_city'] ?? 'N/A') ?>
                    </span>
                    <span class="badge bg-light text-dark fw-500">
                        <?= status_label($job['job_type']) ?>
                    </span>
                    <?php if (!$job['salary_is_hidden'] && $job['salary_min']): ?>
                    <span class="badge bg-light text-dark fw-500">
                        <i class="bi bi-currency-dollar me-1"></i>
                        <?= salary_range($job['salary_min'], $job['salary_max'], $job['salary_currency']) ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted">
                        <i class="bi bi-bookmark me-1"></i>Saved <?= time_ago($job['saved_at']) ?>
                    </small>
                    <?php if ($job['job_status'] === 'active'): ?>
                    <a href="<?= url('/jobs/' . $job['job_slug']) ?>"
                       class="btn btn-primary btn-sm">
                        Apply Now <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <?php else: ?>
                    <span class="badge bg-secondary">Closed</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php if (!empty($recommendations)): ?>
<div class="mt-5">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <div>
            <h2 class="h5 fw-800 mb-0">Recommended for you</h2>
            <p class="text-muted small mb-0">Jobs matching your saved interests.</p>
        </div>
        <a href="<?= url('/jobs') ?>" class="btn btn-outline-primary btn-sm">
            Browse More Jobs
        </a>
    </div>
    <div class="row g-3">
        <?php foreach ($recommendations as $job): ?>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h6 class="fw-700 mb-2">
                        <a href="<?= url('/jobs/' . $job['slug']) ?>" class="text-dark text-decoration-none">
                            <?= e($job['title']) ?>
                        </a>
                    </h6>
                    <div class="text-muted small mb-3"><?= e($job['company_name']) ?></div>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge bg-light text-dark fw-500"><?= $job['is_remote'] ? 'Remote' : e($job['location_city'] ?? 'N/A') ?></span>
                        <span class="badge bg-light text-dark fw-500"><?= status_label($job['job_type']) ?></span>
                    </div>
                    <a href="<?= url('/jobs/' . $job['slug']) ?>" class="btn btn-sm btn-primary">
                        View Job
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endif; ?>
