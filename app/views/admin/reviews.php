<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Review Moderation</h1>
        <p class="text-muted small mb-0">Moderate employer ratings and reviews submitted by job seekers.</p>
    </div>
</div>

<!-- Status tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php foreach (['' => 'All', 'visible' => 'Visible', 'hidden' => 'Hidden'] as $v => $l): ?>
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
                    <th>Employer</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Review</th>
                    <th>Posted</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                <tr><td colspan="7" class="text-center py-4 text-muted">No reviews found.</td></tr>
                <?php else: ?>
                <?php foreach ($items as $rv): ?>
                <tr>
                    <td class="fw-700 small"><?= e($rv['company_name']) ?></td>
                    <td class="small"><?= e($rv['seeker_name']) ?></td>
                    <td>
                        <span class="star-display">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= $i <= (int)$rv['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                            <?php endfor; ?>
                        </span>
                    </td>
                    <td class="small" style="max-width:280px;">
                        <?= $rv['review_text'] ? e(truncate($rv['review_text'], 100)) : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td class="text-muted small"><?= format_date($rv['created_at'], 'M d, Y') ?></td>
                    <td>
                        <?php if ($rv['status'] === 'visible'): ?>
                        <span class="badge bg-success">Visible</span>
                        <?php else: ?>
                        <span class="badge bg-secondary">Hidden</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <?php if ($rv['status'] === 'visible'): ?>
                            <form method="POST" action="<?= url('/admin/reviews/' . $rv['id'] . '/hide') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                        data-confirm="Hide this review from public view?">
                                    <i class="bi bi-eye-slash"></i>
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST" action="<?= url('/admin/reviews/' . $rv['id'] . '/unhide') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-success"
                                        data-confirm="Restore this review to public view?">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="<?= url('/admin/reviews/' . $rv['id'] . '/delete') ?>"
                                  data-no-loading class="d-inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        data-confirm="Permanently delete this review? This cannot be undone.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
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
