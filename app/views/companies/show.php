<div class="container py-4">
<div class="row g-4">

<!-- ── MAIN CONTENT ─────────────────────────────────────────────────────── -->
<div class="col-lg-8">

    <!-- Company header card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex gap-4 align-items-start mb-4">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <?php if ($company['logo_path']): ?>
                    <img src="<?= url('/file?path=' . urlencode($company['logo_path'])) ?>"
                         alt="<?= e($company['company_name']) ?>"
                         style="width:80px;height:80px;border-radius:16px;
                                object-fit:contain;border:1px solid #e5e7eb;
                                padding:8px;background:#fff;">
                    <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center fw-800 rounded-3"
                         style="width:80px;height:80px;font-size:30px;
                                background:#EBF5FF;color:#1A56DB;">
                        <?= strtoupper(substr($company['company_name'], 0, 1)) ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="flex-grow-1">
                    <h1 class="h3 fw-800 mb-1"><?= e($company['company_name']) ?></h1>
                    <div class="d-flex flex-wrap gap-2 text-muted small mb-3">
                        <?php if ($company['industry']): ?>
                        <span><i class="bi bi-building me-1"></i><?= e($company['industry']) ?></span>
                        <?php endif; ?>
                        <?php if ($company['location_city']): ?>
                        <span>·</span>
                        <span>
                            <i class="bi bi-geo-alt me-1"></i>
                            <?= e($company['location_city']) ?>
                            <?= $company['location_country'] ? ', ' . e($company['location_country']) : '' ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($company['company_size']): ?>
                        <span>·</span>
                        <span><i class="bi bi-people me-1"></i><?= e($company['company_size']) ?> employees</span>
                        <?php endif; ?>
                        <?php if ($company['founded_year']): ?>
                        <span>·</span>
                        <span><i class="bi bi-calendar me-1"></i>Founded <?= e($company['founded_year']) ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Quick stats -->
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="text-center px-3 py-2 rounded-2" style="background:#EBF5FF;">
                            <div class="fw-800 text-primary" style="font-size:1.2rem;">
                                <?= $stats['active_jobs'] ?>
                            </div>
                            <div class="text-muted" style="font-size:11px;">Open Jobs</div>
                        </div>
                        <div class="text-center px-3 py-2 rounded-2" style="background:#F0FDF4;">
                            <div class="fw-800 text-success" style="font-size:1.2rem;">
                                <?= number_format($company['profile_views'] + 1) ?>
                            </div>
                            <div class="text-muted" style="font-size:11px;">Profile Views</div>
                        </div>
                        <?php if ($stats['total_hired'] > 0): ?>
                        <div class="text-center px-3 py-2 rounded-2" style="background:#EDE9FE;">
                            <div class="fw-800" style="font-size:1.2rem;color:#7c3aed;">
                                <?= $stats['total_hired'] ?>
                            </div>
                            <div class="text-muted" style="font-size:11px;">Hired</div>
                        </div>
                        <?php endif; ?>
                        <?php if ($reviewStats['total'] > 0): ?>
                        <div class="text-center px-3 py-2 rounded-2" style="background:#FFF7E6;">
                            <div class="fw-800" style="font-size:1.2rem;color:#B45309;">
                                <?= number_format($reviewStats['avg_rating'], 1) ?>
                                <i class="bi bi-star-fill" style="font-size:.9rem;"></i>
                            </div>
                            <div class="text-muted" style="font-size:11px;">
                                <?= $reviewStats['total'] ?> Review<?= $reviewStats['total'] > 1 ? 's' : '' ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Links row -->
            <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                <?php if ($company['website']): ?>
                <a href="<?= e($company['website']) ?>" target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-globe me-1"></i>Website
                </a>
                <?php endif; ?>
                <?php if ($company['linkedin_url']): ?>
                <a href="<?= e($company['linkedin_url']) ?>" target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-linkedin me-1"></i>LinkedIn
                </a>
                <?php endif; ?>
                <?php if ($company['twitter_url']): ?>
                <a href="<?= e($company['twitter_url']) ?>" target="_blank" rel="noopener"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-twitter-x me-1"></i>Twitter
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- About section -->
    <?php if ($company['description']): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-700 mb-0">About <?= e($company['company_name']) ?></h5>
        </div>
        <div class="card-body">
            <div style="line-height:1.9;color:#374151;font-size:15px;">
                <?= nl2br(e($company['description'])) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Open positions -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-700 mb-0">
                <i class="bi bi-briefcase me-2 text-primary"></i>
                Open Positions
                <?php if ($stats['active_jobs'] > 0): ?>
                <span class="badge bg-primary rounded-pill ms-2"><?= $stats['active_jobs'] ?></span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($jobs)): ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox text-muted" style="font-size:2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">
                    No open positions at the moment. Check back later!
                </p>
            </div>
            <?php else: ?>
            <div class="list-group list-group-flush">
                <?php foreach ($jobs as $job): ?>
                <a href="<?= url('/jobs/' . $job['slug']) ?>"
                   class="list-group-item list-group-item-action border-0 px-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-700"><?= e($job['title']) ?></div>
                            <div class="d-flex flex-wrap gap-2 mt-1">
                                <span class="badge bg-light text-dark fw-500" style="font-size:11px;">
                                    <?= status_label($job['job_type']) ?>
                                </span>
                                <span class="badge bg-light text-dark fw-500" style="font-size:11px;">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?= $job['is_remote'] ? '<span class="text-success">Remote</span>'
                                        : e($job['location_city'] ?? 'N/A') ?>
                                </span>
                                <span class="badge bg-light text-dark fw-500" style="font-size:11px;">
                                    <?= status_label($job['experience_level']) ?>
                                </span>
                                <?php if (!$job['salary_is_hidden'] && $job['salary_min']): ?>
                                <span class="badge fw-500"
                                      style="background:#F0FDF4;color:#057A55;font-size:11px;">
                                    <?= salary_range($job['salary_min'], $job['salary_max'],
                                        $job['salary_currency']) ?>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex-shrink-0 text-end">
                            <div class="text-muted small"><?= time_ago($job['created_at']) ?></div>
                            <span class="text-primary small fw-600 mt-1 d-block">
                                Apply <i class="bi bi-arrow-right"></i>
                            </span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ratings & Reviews -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-700 mb-0">
                <i class="bi bi-star me-2 text-primary"></i>
                Ratings &amp; Reviews
                <?php if ($reviewStats['total'] > 0): ?>
                <span class="rating-badge ms-2">
                    <i class="bi bi-star-fill"></i> <?= number_format($reviewStats['avg_rating'], 1) ?>
                    <span class="text-muted fw-500" style="font-size:12px;">
                        (<?= $reviewStats['total'] ?>)
                    </span>
                </span>
                <?php endif; ?>
            </h5>

            <?php if ($canReview): ?>
            <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#reviewForm">
                <i class="bi bi-pencil me-1"></i>Write a Review
            </button>
            <?php elseif ($alreadyReviewed): ?>
            <span class="badge bg-light text-dark fw-500">
                <i class="bi bi-check-circle me-1 text-success"></i>You've reviewed this employer
            </span>
            <?php endif; ?>
        </div>

        <?php if ($canReview): ?>
        <div class="collapse" id="reviewForm">
            <div class="card-body border-bottom bg-light">
                <form method="POST" action="<?= url('/seeker/reviews') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="employer_id" value="<?= (int)$company['id'] ?>">
                    <input type="hidden" name="redirect_slug" value="<?= e($company['slug']) ?>">

                    <label class="form-label d-block">Your Rating</label>
                    <div class="star-rating-input mb-3">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="star<?= $i ?>" value="<?= $i ?>"
                               <?= $i === 5 ? 'checked' : '' ?>>
                        <label for="star<?= $i ?>"><i class="bi bi-star-fill"></i></label>
                        <?php endfor; ?>
                    </div>

                    <label class="form-label">Your Review (optional)</label>
                    <textarea class="form-control mb-3" name="review_text" rows="3"
                              placeholder="Share your experience applying to or working with this employer..."></textarea>

                    <button type="submit" class="btn btn-primary btn-sm px-4">
                        <i class="bi bi-send me-1"></i>Submit Review
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card-body p-0">
            <?php if (empty($reviewList)): ?>
            <div class="text-center py-5">
                <i class="bi bi-chat-square-text text-muted" style="font-size:2.5rem;"></i>
                <p class="text-muted mt-2 mb-0">No reviews yet. Be the first to share your experience!</p>
            </div>
            <?php else: ?>
            <div class="d-flex flex-column gap-3 p-4">
                <?php foreach ($reviewList as $rv): ?>
                <div class="review-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="review-avatar">
                            <?= strtoupper(substr($rv['seeker_name'], 0, 1)) ?>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
                                <div class="fw-700 small"><?= e($rv['seeker_name']) ?></div>
                                <div class="text-muted" style="font-size:11px;"><?= time_ago($rv['created_at']) ?></div>
                            </div>
                            <div class="star-display mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= $i <= (int)$rv['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <?php if ($rv['review_text']): ?>
                            <p class="mb-0 small" style="color:#374151;line-height:1.7;">
                                <?= nl2br(e($rv['review_text'])) ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- ── SIDEBAR ───────────────────────────────────────────────────────────── -->
<div class="col-lg-4">
    <div class="sticky-top" style="top:80px;">

        <!-- Company info card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0">Company Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <?php if ($company['industry']): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;">
                            <i class="bi bi-building text-primary"></i>
                        </div>
                        <div>
                            <div style="font-size:11px;" class="text-muted">Industry</div>
                            <div class="fw-600 small"><?= e($company['industry']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($company['company_size']): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;">
                            <i class="bi bi-people text-primary"></i>
                        </div>
                        <div>
                            <div style="font-size:11px;" class="text-muted">Company Size</div>
                            <div class="fw-600 small"><?= e($company['company_size']) ?> employees</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($company['founded_year']): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;">
                            <i class="bi bi-calendar text-primary"></i>
                        </div>
                        <div>
                            <div style="font-size:11px;" class="text-muted">Founded</div>
                            <div class="fw-600 small"><?= e($company['founded_year']) ?></div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($company['location_city']): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;">
                            <i class="bi bi-geo-alt text-primary"></i>
                        </div>
                        <div>
                            <div style="font-size:11px;" class="text-muted">Headquarters</div>
                            <div class="fw-600 small">
                                <?= e($company['location_city']) ?>
                                <?= $company['location_country'] ? ', ' . e($company['location_country']) : '' ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($company['website']): ?>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:36px;height:36px;">
                            <i class="bi bi-globe text-primary"></i>
                        </div>
                        <div>
                            <div style="font-size:11px;" class="text-muted">Website</div>
                            <a href="<?= e($company['website']) ?>" target="_blank"
                               class="fw-600 small text-primary text-decoration-none">
                                <?= e(parse_url($company['website'], PHP_URL_HOST) ?: $company['website']) ?>
                                <i class="bi bi-arrow-up-right ms-1" style="font-size:10px;"></i>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- CTA card -->
        <?php if ($stats['active_jobs'] > 0): ?>
        <div class="card border-0 shadow-sm"
             style="background:linear-gradient(135deg,#1A56DB,#1347c8);">
            <div class="card-body p-4 text-center text-white">
                <h6 class="fw-800 mb-2">
                    <?= $stats['active_jobs'] ?> open position<?= $stats['active_jobs'] > 1 ? 's' : '' ?>
                </h6>
                <p class="small mb-3" style="opacity:.85;">
                    Don't miss your chance to join <?= e($company['company_name']) ?>
                </p>
                <a href="#open-positions" class="btn btn-white fw-700 btn-sm px-4"
                   style="background:#fff;color:#1A56DB;">
                    View All Jobs <i class="bi bi-arrow-down ms-1"></i>
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 text-center">
                <i class="bi bi-bell text-primary" style="font-size:2rem;"></i>
                <h6 class="fw-700 mt-3 mb-2">No openings right now</h6>
                <p class="text-muted small mb-3">
                    Set up a job alert to be notified when they post.
                </p>
                <?php if (is_logged_in() && Session::isSeeker()): ?>
                <a href="<?= url('/seeker/alerts') ?>" class="btn btn-outline-primary btn-sm px-4">
                    <i class="bi bi-bell me-1"></i>Create Alert
                </a>
                <?php else: ?>
                <a href="<?= url('/register?role=seeker') ?>" class="btn btn-outline-primary btn-sm px-4">
                    Sign Up for Alerts
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

</div>
</div>
