<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Candidate Search</h1>
        <p class="text-muted small mb-0">Find candidates who are open to work.</p>
    </div>
</div>

<!-- Search form -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" name="q"
                       placeholder="Name or headline..." value="<?= e($filters['q']) ?>">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="skill"
                       placeholder="Skill (e.g. PHP)" value="<?= e($filters['skill']) ?>">
            </div>
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" name="location"
                       placeholder="City" value="<?= e($filters['loc']) ?>">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary">
                    <i class="bi bi-search me-1"></i>Search
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (empty($candidates)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-people text-muted" style="font-size:3rem;"></i>
        <h5 class="fw-700 mt-3">No candidates found</h5>
        <p class="text-muted">Try different search terms.</p>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($candidates as $c): ?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex gap-3 mb-3">
                    <?php if ($c['avatar_path']): ?>
                    <img src="<?= url('/file?path='.urlencode($c['avatar_path'])) ?>"
                         class="avatar flex-shrink-0" style="width:48px;height:48px;" alt="">
                    <?php else: ?>
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center
                                justify-content-center fw-800 flex-shrink-0"
                         style="width:48px;height:48px;font-size:18px;">
                        <?= strtoupper(substr($c['full_name'],0,1)) ?>
                    </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                        <div class="fw-700"><?= e($c['full_name']) ?></div>
                        <div class="text-muted small"><?= e(truncate($c['headline']??'',55)) ?></div>
                        <?php if ($c['location_city']): ?>
                        <div class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($c['location_city']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-1 mb-3">
                    <?php if ($c['years_experience']): ?>
                    <span class="badge bg-light text-dark small"><?= $c['years_experience'] ?> yrs exp</span>
                    <?php endif; ?>
                    <span class="badge bg-success bg-opacity-10 text-success small">Open to Work</span>
                </div>
                <a href="<?= url('/employer/candidates/'.$c['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
                    View Profile
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if ($paging['pages'] > 1): ?>
<nav class="mt-4">
    <ul class="pagination justify-content-center gap-1">
        <?php for ($i=1; $i<=$paging['pages']; $i++): ?>
        <li class="page-item <?= $i===$paging['current_page']?'active':'' ?>">
            <a class="page-link border-0" href="?page=<?= $i ?>&q=<?= urlencode($filters['q']) ?>&skill=<?= urlencode($filters['skill']) ?>&location=<?= urlencode($filters['loc']) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>
