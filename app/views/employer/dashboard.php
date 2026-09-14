<?php $firstName = explode(' ', auth()['name'])[0]; ?>
<style>
.emp-hero{background:linear-gradient(135deg,#1e1b4b 0%,#1347c8 60%,#1A56DB 100%);
          border-radius:20px;padding:28px 32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden;}
.emp-hero::before{content:'';position:absolute;right:-60px;bottom:-60px;width:220px;height:220px;
                  border-radius:50%;background:rgba(255,255,255,.06);}
.pipeline-bar{display:flex;height:10px;border-radius:50px;overflow:hidden;gap:2px;margin-bottom:8px;}
.pipeline-bar span{border-radius:50px;transition:.3s;}
.hire-funnel{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
.funnel-step{background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:16px;
             text-align:center;position:relative;}
.funnel-step .arrow{position:absolute;right:-8px;top:50%;transform:translateY(-50%);
                    width:14px;height:14px;background:#e5e7eb;clip-path:polygon(0 0,100% 50%,0 100%);z-index:1;}
.funnel-step:last-child .arrow{display:none;}
.funnel-num{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.7rem;line-height:1;}
.funnel-lbl{font-size:11px;color:#9ca3af;margin-top:3px;font-weight:500;}
.quick-post{background:linear-gradient(135deg,#EBF5FF,#EDE9FE);border-radius:14px;
            padding:20px;border:none;display:flex;align-items:center;gap:16px;}
.applicant-row{display:flex;align-items:center;gap:12px;padding:14px 20px;
               border-bottom:1px solid #f3f4f6;transition:.15s;}
.applicant-row:last-child{border-bottom:none;}
.applicant-row:hover{background:#f9fafb;}
.job-row{display:flex;align-items:center;gap:12px;padding:12px 20px;
         border-bottom:1px solid #f3f4f6;}
.job-row:last-child{border-bottom:none;}
</style>

<!-- Hero -->
<div class="emp-hero">
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div>
      <p class="mb-1 opacity-75 small">👋 Welcome back</p>
      <h1 class="fw-800 mb-1" style="font-size:1.6rem;letter-spacing:-.5px;">
        <?= e($firstName) ?>
      </h1>
      <div class="d-flex align-items-center gap-2">
        <span class="opacity-85 small"><?= e($ep['company_name']) ?></span>
        <?php if($ep['verification_status']==='approved'): ?>
        <span class="badge" style="background:rgba(16,185,129,.3);color:#6ee7b7;font-size:10px;">
          ✓ Verified
        </span>
        <?php elseif($ep['verification_status']==='pending'): ?>
        <span class="badge" style="background:rgba(245,158,11,.3);color:#fde68a;font-size:10px;">
          ⏳ Pending
        </span>
        <?php else: ?>
        <span class="badge" style="background:rgba(239,68,68,.3);color:#fca5a5;font-size:10px;">
          Unverified
        </span>
        <?php endif; ?>
      </div>
    </div>
    <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-warning fw-700 px-4">
      <i class="bi bi-plus-circle me-2"></i>Post a Job
    </a>
  </div>

  <!-- Stats inline -->
  <div class="row g-3 mt-3">
    <?php foreach([
      ['val'=>$stats['active_jobs'], 'lbl'=>'Active Jobs',   'icon'=>'briefcase'],
      ['val'=>$stats['total_apps'],  'lbl'=>'Total Apps',    'icon'=>'file-earmark-text'],
      ['val'=>$stats['new_apps'],    'lbl'=>'Unread Apps',   'icon'=>'envelope-open'],
      ['val'=>$stats['shortlisted'],'lbl'=>'Shortlisted',   'icon'=>'star'],
    ] as $s): ?>
    <div class="col-6 col-md-3">
      <div style="background:rgba(255,255,255,.12);border-radius:12px;padding:14px;">
        <div class="fw-800" style="font-size:1.5rem;line-height:1;"><?= $s['val'] ?></div>
        <div style="font-size:11px;opacity:.75;margin-top:2px;"><?= $s['lbl'] ?></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Verification alert if needed -->
<?php if($ep['verification_status']==='pending'): ?>
<div class="alert border-0 mb-4 rounded-3 d-flex align-items-center gap-3"
     style="background:#FFFBEB;border-left:4px solid #F59E0B!important;">
  <i class="bi bi-clock-history text-warning fs-4 flex-shrink-0"></i>
  <div>
    <div class="fw-700 small">Verification in progress</div>
    <div class="text-muted small">Admin is reviewing your company. You'll be notified once approved.</div>
  </div>
</div>
<?php elseif($ep['verification_status']!=='approved'): ?>
<div class="alert border-0 mb-4 rounded-3 d-flex align-items-center gap-3"
     style="background:#FEF2F2;border-left:4px solid #EF4444!important;">
  <i class="bi bi-shield-exclamation text-danger fs-4 flex-shrink-0"></i>
  <div class="flex-grow-1">
    <div class="fw-700 small">Company not verified</div>
    <div class="text-muted small">Submit your verification to unlock job posting.</div>
  </div>
  <a href="<?= url('/employer/profile') ?>" class="btn btn-sm btn-danger flex-shrink-0 fw-600">Verify Now</a>
</div>
<?php endif; ?>

<!-- Hiring funnel -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
  <div class="card-header bg-white border-bottom py-3 px-4">
    <h6 class="fw-700 mb-0">Hiring Pipeline</h6>
  </div>
  <div class="card-body p-4">
    <?php
    $fTotal = max(1, $stats['total_apps']);
    $fSteps = [
      ['label'=>'Applied',    'val'=>$stats['total_apps'],  'color'=>'#1A56DB'],
      ['label'=>'Shortlisted','val'=>$stats['shortlisted'], 'color'=>'#7C3AED'],
      ['label'=>'Interviews', 'val'=>$stats['interviews']??0,'color'=>'#D97706'],
      ['label'=>'Hired',      'val'=>$stats['hired']??0,    'color'=>'#057A55'],
    ];
    ?>
    <!-- Bar -->
    <div class="pipeline-bar mb-4">
      <?php foreach($fSteps as $fs): ?>
      <span style="width:<?= max(4,round(($fs['val']/$fTotal)*100)) ?>%;
                   background:<?= $fs['color'] ?>;opacity:.8;"></span>
      <?php endforeach; ?>
    </div>
    <!-- Steps -->
    <div class="hire-funnel">
      <?php foreach($fSteps as $i=>$fs): ?>
      <div class="funnel-step">
        <div class="funnel-num" style="color:<?= $fs['color'] ?>;"><?= $fs['val'] ?></div>
        <div class="funnel-lbl"><?= $fs['label'] ?></div>
        <?php if($i < count($fSteps)-1): ?><div class="arrow"></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Recent applicants -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-people me-2 text-primary"></i>Recent Applicants</h6>
        <a href="<?= url('/employer/jobs') ?>" class="btn btn-sm btn-light fw-600">All Jobs</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($recentApps)): ?>
        <?php empty_state('candidates', 'No applicants yet', 'Once job seekers apply to your listings, they\'ll appear here.',
          null, 'md'); ?>
        <?php else: ?>
        <?php foreach($recentApps as $app): ?>
        <div class="applicant-row <?= !$app['is_read_by_employer']?'':''; ?>">
          <?php if(!$app['is_read_by_employer']): ?>
          <span style="width:6px;height:6px;border-radius:50%;background:var(--primary);
                       flex-shrink:0;display:inline-block;"></span>
          <?php else: ?>
          <span style="width:6px;flex-shrink:0;display:inline-block;"></span>
          <?php endif; ?>
          <div class="rounded-circle d-flex align-items-center justify-content-center
                      fw-700 flex-shrink-0 text-white"
               style="width:38px;height:38px;font-size:14px;background:var(--primary);">
            <?= strtoupper(substr($app['seeker_name'],0,1)) ?>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-700 small"><?= e($app['seeker_name']) ?></div>
            <div class="text-muted" style="font-size:12px;">
              <?= e(truncate($app['headline']??'Applied',40)) ?>
              · <span class="text-primary fw-500"><?= e($app['job_title']) ?></span>
            </div>
          </div>
          <div class="text-end flex-shrink-0">
            <span class="badge <?= status_badge($app['status']) ?> rounded-pill" style="font-size:11px;">
              <?= status_label($app['status']) ?>
            </span>
            <div class="text-muted mt-1" style="font-size:11px;"><?= time_ago($app['applied_at']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Active jobs -->
  <div class="col-lg-5 d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Active Jobs</h6>
        <a href="<?= url('/employer/jobs') ?>" class="btn btn-sm btn-light fw-600">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($activeJobs)): ?>
        <?php empty_state('jobs', 'No active listings', 'Post a job to start receiving applications from qualified candidates.',
          ['label'=>'Post First Job', 'url'=>'/employer/jobs/create', 'icon'=>'plus-circle'], 'sm'); ?>
        <?php else: ?>
        <?php foreach($activeJobs as $job): ?>
        <div class="job-row">
          <div class="flex-grow-1 min-w-0">
            <div class="fw-700 small text-truncate"><?= e($job['title']) ?></div>
            <div class="text-muted" style="font-size:11px;">
              <i class="bi bi-people me-1"></i><?= $job['app_count'] ?> applicants
              · <i class="bi bi-eye me-1"></i><?= number_format($job['view_count']) ?> views
            </div>
          </div>
          <a href="<?= url('/employer/jobs/'.$job['id'].'/applicants') ?>"
             class="btn btn-sm btn-outline-primary fw-600 flex-shrink-0">Review</a>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick post nudge -->
    <div class="quick-post">
      <div class="flex-shrink-0" style="font-size:2rem;">🚀</div>
      <div class="flex-grow-1">
        <div class="fw-700 small mb-1">Post a new job</div>
        <div class="text-muted small mb-2">Reach thousands of qualified candidates.</div>
        <a href="<?= url('/employer/jobs/create') ?>" class="btn btn-primary btn-sm fw-600">Post Job</a>
      </div>
    </div>
  </div>
</div>
