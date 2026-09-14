<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Ratings &amp; Reviews</h1>
        <p class="text-muted small mb-0">What job seekers are saying about your company.</p>
    </div>
    <?php if ($stats['total'] > 0): ?>
    <span class="rating-badge">
        <i class="bi bi-star-fill"></i> <?= number_format($stats['avg_rating'], 1) ?>
        <span class="text-muted fw-500" style="font-size:12px;">
            (<?= $stats['total'] ?> review<?= $stats['total'] > 1 ? 's' : '' ?>)
        </span>
    </span>
    <?php endif; ?>
</div>

<?php if (empty($reviews)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-star text-muted" style="font-size:3rem;"></i>
        <h5 class="fw-700 mt-3">No reviews yet</h5>
        <p class="text-muted mb-0">
            Reviews appear here once a job seeker who has applied to one of your
            listings leaves a rating on your company page.
        </p>
    </div>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
    <?php foreach ($reviews as $rv): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
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
                        <?php if ($rv['status'] === 'hidden'): ?>
                        <span class="badge bg-secondary ms-2" style="font-size:10px;">
                            Hidden by admin
                        </span>
                        <?php endif; ?>
                    </div>
                    <?php if ($rv['review_text']): ?>
                    <p class="mb-0 small" style="color:#374151;line-height:1.7;">
                        <?= nl2br(e($rv['review_text'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<p class="text-muted small mt-3">
    <i class="bi bi-info-circle me-1"></i>
    Reviews are submitted by verified applicants and can't be edited or removed by
    employers. If a review violates our guidelines, contact support to have it reviewed.
</p>
<?php endif; ?>
