<style>
.app-timeline{display:flex;align-items:center;gap:0;margin:12px 0;}
.tl-step{flex:1;display:flex;flex-direction:column;align-items:center;position:relative;}
.tl-step:not(:last-child)::after{content:'';position:absolute;top:18px;left:50%;width:100%;height:2px;background:#e5e7eb;z-index:0;}
.tl-step.done::after{background:#1A56DB;}
.tl-dot{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;position:relative;z-index:1;border:2px solid #e5e7eb;background:#fff;transition:.3s;}
.tl-step.done .tl-dot{background:#1A56DB;border-color:#1A56DB;color:#fff;}
.tl-step.active .tl-dot{background:#fff;border-color:#1A56DB;color:#1A56DB;box-shadow:0 0 0 4px rgba(26,86,219,.15);}
.tl-step.rejected .tl-dot{background:#FEE2E2;border-color:#EF4444;color:#EF4444;}
.tl-lbl{font-size:10px;font-weight:600;margin-top:6px;text-align:center;color:#9ca3af;}
.tl-step.done .tl-lbl{color:#1A56DB;}
.tl-step.active .tl-lbl{color:#1A56DB;font-weight:700;}
.tl-step.rejected .tl-lbl{color:#EF4444;}
.app-card{border:1.5px solid var(--border);border-radius:16px;background:#fff;margin-bottom:14px;overflow:hidden;transition:.2s;}
.app-card:hover{box-shadow:0 4px 20px rgba(26,86,219,.1);border-color:var(--primary);}
.app-card.status-rejected{border-left:4px solid #EF4444;}
.app-card.status-hired,.app-card.status-offered{border-left:4px solid #057A55;}
.app-card.status-interview{border-left:4px solid #D97706;}
.app-card.status-shortlisted{border-left:4px solid #7C3AED;}
[data-theme="dark"] .app-card{background:#1E293B;border-color:#374151;}
[data-theme="dark"] .tl-dot{background:#1E293B;border-color:#374151;}
[data-theme="dark"] .tl-step.done .tl-dot{background:#1A56DB;border-color:#1A56DB;}

/* ── Mobile: status timeline ──────────────────────────────── */
@media (max-width: 576px) {
  .app-timeline{overflow-x:auto;-webkit-overflow-scrolling:touch;padding-bottom:4px;}
  .tl-step{min-width:58px;flex:0 0 auto;}
  .tl-step:not(:last-child)::after{width:58px;}
  .tl-dot{width:30px;height:30px;}
  .tl-lbl{font-size:9px;}
  .app-card .p-4{padding:1rem!important;}
}
</style>

<?php
$applications = $applications ?? $apps ?? [];
/*
  FIX (2026-07-16): 'reviewed' renamed to 'under_review' throughout this file
  to match the actual DB enum value and the value set by the employer-side
  status dropdown (app/views/employer/applicants.php uses 'under_review').
  Previously this mismatch caused the timeline to silently stay stuck on
  "Applied" for any application the employer marked as Under Review.
*/
$statusOrder = ['applied'=>0,'under_review'=>1,'shortlisted'=>2,'interview_scheduled'=>3,'offered'=>4,'hired'=>4];
$statusSteps = [
  'applied'             =>['icon'=>'send-fill',          'label'=>'Applied'],
  'under_review'        =>['icon'=>'eye-fill',           'label'=>'Reviewed'],
  'shortlisted'         =>['icon'=>'star-fill',          'label'=>'Shortlisted'],
  'interview_scheduled' =>['icon'=>'calendar-check-fill','label'=>'Interview'],
  'offered'             =>['icon'=>'gift-fill',          'label'=>'Offered'],
];

function getStepState(string $appStatus, string $stepKey): string {
    global $statusOrder;
    $appPos  = $statusOrder[$appStatus] ?? 0;
    $stepPos = $statusOrder[$stepKey]   ?? 0;
    if ($appStatus === 'rejected' && $stepKey === 'applied') return 'done';
    if ($appStatus === 'rejected') return $stepPos === 0 ? 'done' : 'rejected-step';
    if ($appPos > $stepPos) return 'done';
    if ($appPos === $stepPos) return 'active';
    return '';
}
?>

<!-- Page header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
  <div>
    <h1 class="h4 fw-800 mb-1">My Applications</h1>
    <p class="text-muted small mb-0">Track the status of all your job applications</p>
  </div>
  <a href="<?= url('/jobs') ?>" class="btn btn-primary fw-600">
    <i class="bi bi-search me-2"></i>Find More Jobs
  </a>
</div>

<!-- Filter tabs -->
<div class="d-flex gap-2 flex-wrap mb-4" id="appFilters">
  <?php
  $currentFilter = $_GET['status'] ?? 'all';
  $filterCounts  = ['all' => count($applications)];
  foreach ($applications as $a) {
      $filterCounts[$a['status']] = ($filterCounts[$a['status']] ?? 0) + 1;
  }
  $filterTabs = [
    'all'                 => ['label'=>'All',        'color'=>'primary'],
    'applied'             => ['label'=>'Applied',    'color'=>'secondary'],
    'under_review'        => ['label'=>'Reviewing',  'color'=>'info'],
    'shortlisted'         => ['label'=>'Shortlisted','color'=>'purple'],
    'interview_scheduled' => ['label'=>'Interview',  'color'=>'warning'],
    'offered'             => ['label'=>'Offer',      'color'=>'success'],
    'rejected'            => ['label'=>'Rejected',   'color'=>'danger'],
  ];
  foreach ($filterTabs as $fKey => $fData):
    $cnt   = $filterCounts[$fKey] ?? 0;
    if ($fKey !== 'all' && $cnt === 0) continue;
    $active = $currentFilter === $fKey;
  ?>
  <a href="?status=<?= $fKey ?>"
     class="btn btn-sm fw-600 <?= $active ? 'btn-primary' : 'btn-outline-secondary' ?>">
    <?= $fData['label'] ?>
    <?php if($cnt > 0): ?>
    <span class="badge ms-1 <?= $active ? 'bg-white text-primary' : 'bg-primary text-white' ?>"
          style="font-size:10px;"><?= $cnt ?></span>
    <?php endif; ?>
  </a>
  <?php endforeach; ?>
</div>

<!-- Applications list -->
<?php
$filtered = $applications;
if ($currentFilter !== 'all') {
    $filtered = array_filter($applications, fn($a) => $a['status'] === $currentFilter);
}
?>

<?php if (empty($filtered)): ?>
<?php empty_state(
  'applications',
  $currentFilter === 'all' ? 'No applications yet' : 'No ' . ucfirst(str_replace('_',' ',$currentFilter)) . ' applications',
  $currentFilter === 'all'
    ? "You haven't applied to any jobs yet. Your applications will appear here once you start."
    : "You don't have any applications with this status.",
  $currentFilter === 'all' ? ['label'=>'Browse Jobs', 'url'=>'/jobs', 'icon'=>'search'] : null,
  'lg',
  true
); ?>
<?php else: ?>

<?php foreach ($filtered as $app): ?>
<div class="app-card status-<?= $app['status'] ?>">
  <div class="p-4">
    <div class="d-flex gap-3 align-items-start mb-3">
      <!-- Logo -->
      <div class="d-flex align-items-center justify-content-center fw-800 flex-shrink-0"
           style="width:48px;height:48px;border-radius:12px;background:var(--primary-light);
                  color:var(--primary);font-size:18px;">
        <?= strtoupper(substr($app['company_name'],0,1)) ?>
      </div>

      <div class="flex-grow-1 min-w-0">
        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
          <div>
            <h6 class="fw-700 mb-0">
              <a href="<?= url('/jobs/'.$app['job_slug']) ?>"
                 class="text-dark text-decoration-none">
                <?= e($app['job_title']) ?>
              </a>
            </h6>
            <div class="text-muted small"><?= e($app['company_name']) ?></div>
          </div>
          <span class="badge rounded-pill <?= status_badge($app['status']) ?> fw-600"
                style="font-size:11px;">
            <?= status_label($app['status']) ?>
          </span>
        </div>
      </div>
    </div>

    <!-- Status timeline -->
    <?php if ($app['status'] !== 'withdrawn'): ?>
    <div class="app-timeline">
      <?php foreach ($statusSteps as $sKey => $sData):
        $state = getStepState($app['status'], $sKey);
      ?>
      <div class="tl-step <?= $state ?>">
        <div class="tl-dot">
          <?php if($state === 'done'): ?>
          <i class="bi bi-check-lg" style="font-size:13px;"></i>
          <?php elseif($state === 'active'): ?>
          <i class="bi bi-<?= $sData['icon'] ?>" style="font-size:12px;"></i>
          <?php else: ?>
          <span style="width:8px;height:8px;border-radius:50%;background:currentColor;opacity:.3;"></span>
          <?php endif; ?>
        </div>
        <div class="tl-lbl"><?= $sData['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if ($app['status'] === 'interview_scheduled' && !empty($app['interview_date'])): ?>
    <div class="mt-3 p-3 rounded-3 border bg-light">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
        <div>
          <div class="text-uppercase text-secondary small fw-600 mb-1">Interview Schedule</div>
          <div class="fw-600"><?= date('d M Y · H:i', strtotime($app['interview_date'])) ?></div>
        </div>
        <span class="badge bg-white text-dark fw-600" style="font-size:11px;">
          <?= ucfirst(str_replace('_', ' ', $app['interview_type'] ?? 'video')) ?>
        </span>
      </div>
      <?php if (!empty($app['meeting_link'])): ?>
      <div class="d-flex flex-column gap-2">
        <div class="small text-muted">Meeting link</div>
        <a href="<?= e($app['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-outline-success text-truncate" style="max-width:100%;">
          <i class="bi bi-box-arrow-up-right me-1"></i>Join interview
        </a>
      </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Footer row -->
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-3 pt-3 border-top">
      <div class="d-flex gap-3 text-muted small flex-wrap">
        <span><i class="bi bi-calendar3 me-1"></i>Applied <?= time_ago($app['applied_at']) ?></span>
        <?php if(!empty($app['status_updated_at']) && $app['status'] !== 'applied'): ?>
        <span><i class="bi bi-arrow-clockwise me-1"></i>Updated <?= time_ago($app['status_updated_at']) ?></span>
        <?php endif; ?>
        <?php if($app['location_city']): ?>
        <span class="d-none d-md-inline">
          <i class="bi bi-geo-alt me-1"></i><?= e($app['location_city']) ?>
        </span>
        <?php endif; ?>
      </div>
      <div class="d-flex gap-2">
        <?php if($app['status'] === 'interview_scheduled' && $app['interview_date']): ?>
        <span class="badge bg-warning text-dark fw-600" style="font-size:11px;">
          <i class="bi bi-calendar-event me-1"></i>
          <?= date('d M Y', strtotime($app['interview_date'])) ?>
        </span>
        <?php endif; ?>
        <a href="<?= url('/jobs/'.$app['job_slug']) ?>"
           class="btn btn-sm btn-outline-primary fw-600">View Job</a>
        <?php /* FIX: 'reviewed' -> 'under_review' so the withdraw button
                 correctly disappears once an employer has started reviewing */ ?>
        <?php if(in_array($app['status'],['applied','under_review'])): ?>
        <form method="POST" action="<?= url('/seeker/withdraw/'.$app['id']) ?>"
              onsubmit="return confirm('Withdraw this application?')">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-outline-danger fw-600">Withdraw</button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if(!empty($app['status_note'])): ?>
    <div class="mt-3 px-3 py-2 rounded-2 d-flex align-items-start gap-2"
         style="background:#F0F9FF;border-left:3px solid #0EA5E9;">
      <i class="bi bi-chat-left-quote-fill text-info flex-shrink-0 mt-1" style="font-size:13px;"></i>
      <div>
        <div class="fw-600" style="font-size:11px;color:#0369A1;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">
          Note from employer
        </div>
        <div class="small text-dark"><?= e($app['status_note']) ?></div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
