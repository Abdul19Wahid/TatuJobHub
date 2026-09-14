<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('/employer/candidates') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <h1 class="h4 fw-800 mb-0">Candidate Profile</h1>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center p-4">
            <?php if ($profile['avatar_path']): ?>
            <img src="<?= url('/file?path='.urlencode($profile['avatar_path'])) ?>"
                 class="avatar mx-auto mb-3" style="width:80px;height:80px;" alt="">
            <?php else: ?>
            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center
                        justify-content-center fw-800 mb-3"
                 style="width:80px;height:80px;font-size:30px;">
                <?= strtoupper(substr($profile['full_name'],0,1)) ?>
            </div>
            <?php endif; ?>
            <h5 class="fw-800 mb-1"><?= e($profile['full_name']) ?></h5>
            <p class="text-muted small mb-2"><?= e($profile['headline']??'') ?></p>
            <?php if ($profile['location_city']): ?>
            <p class="text-muted small"><i class="bi bi-geo-alt me-1"></i><?= e($profile['location_city']) ?></p>
            <?php endif; ?>
            <?php if ($profile['resume_path']): ?>
            <a href="<?= url('/file?path='.urlencode($profile['resume_path'])) ?>"
               target="_blank" class="btn btn-outline-primary btn-sm w-100 mt-2">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download Resume
            </a>
            <?php endif; ?>
            <?php if ($profile['linkedin_url']): ?>
            <a href="<?= e($profile['linkedin_url']) ?>" target="_blank"
               class="btn btn-outline-secondary btn-sm w-100 mt-2">
                <i class="bi bi-linkedin me-1"></i>LinkedIn
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-8 d-flex flex-column gap-4">
        <?php if ($profile['bio']): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="fw-700 mb-0">About</h6></div>
            <div class="card-body"><p class="text-muted small mb-0" style="line-height:1.7;"><?= nl2br(e($profile['bio'])) ?></p></div>
        </div>
        <?php endif; ?>

        <?php if (!empty($skills)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="fw-700 mb-0">Skills</h6></div>
            <div class="card-body d-flex flex-wrap gap-2">
                <?php foreach ($skills as $sk): ?>
                <span class="badge rounded-pill fw-500"
                      style="background:#EBF5FF;color:#1A56DB;padding:6px 12px;font-size:12px;border:1px solid #BFDBFE;">
                    <?= e($sk['skill_name']) ?>
                    <span class="opacity-75 ms-1" style="font-size:10px;"><?= ucfirst($sk['proficiency']) ?></span>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($exp)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="fw-700 mb-0">Experience</h6></div>
            <div class="card-body d-flex flex-column gap-3">
                <?php foreach ($exp as $e): ?>
                <div class="d-flex gap-3">
                    <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0 border"
                         style="width:40px;height:40px;"><i class="bi bi-building text-muted"></i></div>
                    <div>
                        <div class="fw-700 small"><?= e($e['job_title']) ?></div>
                        <div class="text-muted small"><?= e($e['company_name']) ?></div>
                        <div class="text-muted small">
                            <?= format_date($e['start_date'],'M Y') ?> —
                            <?= $e['is_current'] ? '<span class="text-success fw-600">Present</span>' : format_date($e['end_date']??'','M Y') ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($edu)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="fw-700 mb-0">Education</h6></div>
            <div class="card-body d-flex flex-column gap-3">
                <?php foreach ($edu as $ed): ?>
                <div class="d-flex gap-3">
                    <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:40px;height:40px;background:#EDE9FE;">
                        <i class="bi bi-mortarboard" style="color:#7c3aed;"></i>
                    </div>
                    <div>
                        <div class="fw-700 small"><?= e($ed['degree']) ?></div>
                        <div class="text-muted small"><?= e($ed['institution']) ?></div>
                        <div class="text-muted small"><?= $ed['start_year'] ?> — <?= $ed['is_current']?'<span class="text-success fw-600">Present</span>':($ed['end_year']??'?') ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include ROOT_PATH . '/app/views/messages/modal.php'; ?>
