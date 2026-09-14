<div class="d-flex align-items-center gap-3 mb-4">
    <a href="javascript:history.back()" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <h1 class="h4 fw-800 mb-0">Application — <?= e($app['seeker_name']) ?></h1>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 text-center mb-4">
            <?php if ($app['avatar_path']): ?>
            <img src="<?= url('/file?path='.urlencode($app['avatar_path'])) ?>"
                 class="avatar mx-auto mb-3" style="width:72px;height:72px;" alt="">
            <?php else: ?>
            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center
                        justify-content-center fw-800 mb-3"
                 style="width:72px;height:72px;font-size:28px;">
                <?= strtoupper(substr($app['seeker_name'],0,1)) ?>
            </div>
            <?php endif; ?>
            <h5 class="fw-800 mb-1"><?= e($app['seeker_name']) ?></h5>
            <p class="text-muted small mb-1"><?= e($app['seeker_email']) ?></p>
            <p class="text-muted small mb-3"><?= e(truncate($app['headline']??'',60)) ?></p>
            <span class="badge <?= status_badge($app['status']) ?> rounded-pill px-3 py-2 mb-3">
                <?= status_label($app['status']) ?>
            </span>
            <?php if ($app['resume_snapshot_path']): ?>
            <a href="<?= url('/file?path='.urlencode($app['resume_snapshot_path'])) ?>"
               target="_blank" class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-file-earmark-pdf me-1"></i>Download Resume
                <?php if ($app['resume_snapshot_original_name']): ?>
                <span class="d-block small text-muted"><?= e($app['resume_snapshot_original_name']) ?></span>
                <?php endif; ?>
            </a>
            <?php else: ?>
            <div class="text-muted small">No resume attached to this application</div>
            <?php endif; ?>
        </div>

        <!-- Update status -->
        <div class="card border-0 shadow-sm p-4">
            <h6 class="fw-700 mb-3">Update Status</h6>
            <form method="POST" action="<?= url('/employer/applications/'.$app['id'].'/status') ?>">
                <?= csrf_field() ?>
                <select class="form-select form-select-sm mb-2" name="status">
                    <?php foreach (['applied'=>'New','under_review'=>'Under Review','shortlisted'=>'Shortlisted',
                        'interview_scheduled'=>'Schedule Interview','offered'=>'Offer','hired'=>'Hired','rejected'=>'Reject'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= $app['status']===$v?'selected':'' ?>><?= $l ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm w-100">Update</button>
            </form>
        </div>
    </div>

    <div class="col-md-8 d-flex flex-column gap-4">
        <!-- Skills -->
        <?php if (!empty($skills)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="fw-700 mb-0">Skills</h6></div>
            <div class="card-body d-flex flex-wrap gap-2">
                <?php foreach ($skills as $sk): ?>
                <span class="badge rounded-pill fw-500"
                      style="background:#EBF5FF;color:#1A56DB;padding:6px 12px;font-size:12px;">
                    <?= e($sk['skill_name']) ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Cover letter -->
        <?php if ($app['cover_letter_text']): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3"><h6 class="fw-700 mb-0">Cover Letter</h6></div>
            <div class="card-body">
                <p class="small text-muted mb-0" style="line-height:1.8;"><?= nl2br(e($app['cover_letter_text'])) ?></p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Employer notes -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0"><i class="bi bi-lock me-2 text-muted"></i>Internal Notes (private)</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/employer/applications/'.$app['id'].'/notes') ?>">
                    <?= csrf_field() ?>
                    <textarea class="form-control mb-2" name="employer_notes" rows="3"
                              placeholder="Add private notes about this candidate..."><?= e($app['employer_notes']??'') ?></textarea>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Save Notes</button>
                </form>
            </div>
        </div>
    </div>
</div>
