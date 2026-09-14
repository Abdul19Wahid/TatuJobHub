<?php
$statusCounts = $statusCounts ?? [];
$profile = $profile ?? ['full_name' => 'Candidate', 'avatar_path' => null, 'headline' => null, 'is_open_to_work' => 0];
$score = $score ?? 0;
$totalApps   = array_sum($statusCounts);
$shortlisted = $statusCounts['shortlisted'] ?? 0;
$interviews  = $statusCounts['interview_scheduled'] ?? 0;
$offers      = ($statusCounts['offered'] ?? 0) + ($statusCounts['hired'] ?? 0);
$firstName   = explode(' ', trim($profile['full_name']))[0] ?: 'Candidate';
$initials    = strtoupper(substr($profile['full_name'] ?: 'C', 0, 1));
$activeApps  = $totalApps - ($statusCounts['rejected'] ?? 0) - ($statusCounts['withdrawn'] ?? 0);
$appliedPct  = $totalApps ? round((($statusCounts['applied'] ?? 0) / $totalApps) * 100) : 0;
$shortlistedPct = $totalApps ? round(($shortlisted / $totalApps) * 100) : 0;
$interviewsPct  = $totalApps ? round(($interviews / $totalApps) * 100) : 0;
$offersPct      = $totalApps ? round(($offers / $totalApps) * 100) : 0;
$nextInterviewLabel = '';
$nextInterviewUrgent = false;
$nextInterviewToday = false;
if (!empty($nextInterview['scheduled_at'])) {
    $when = strtotime($nextInterview['scheduled_at']);
    $now = time();
    $today = strtotime('today');
    $tomorrow = strtotime('tomorrow');
    $dayAfterTomorrow = strtotime('tomorrow +1 day');
    if ($when >= $now && $when < $tomorrow) {
        $nextInterviewLabel = 'Today at ' . date('H:i', $when);
    } elseif ($when >= $tomorrow && $when < $dayAfterTomorrow) {
        $nextInterviewLabel = 'Tomorrow at ' . date('H:i', $when);
    } else {
        $nextInterviewLabel = date('d M Y · H:i', $when);
    }
    $nextInterviewUrgent = $when >= $now && $when <= $now + 86400;
    $nextInterviewToday = date('Y-m-d', $when) === date('Y-m-d', $now);
}
?>
<style>
.dash-hero{background:linear-gradient(135deg,#1A56DB 0%,#1347c8 60%,#1e1b4b 100%);
           border-radius:20px;padding:28px 32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden;}
.dash-hero::after{content:'';position:absolute;right:-40px;top:-40px;width:200px;height:200px;
                  border-radius:50%;background:rgba(255,255,255,.05);}
.score-ring{position:relative;width:80px;height:80px;flex-shrink:0;}
.score-ring svg{transform:rotate(-90deg);}
.score-ring .score-text{position:absolute;inset:0;display:flex;align-items:center;
                         justify-content:center;font-weight:800;font-size:18px;color:#fff;}
.pipeline{display:flex;align-items:center;gap:0;}
.pipe-step{flex:1;text-align:center;padding:14px 8px;position:relative;}
.pipe-step:not(:last-child)::after{content:'';position:absolute;right:0;top:50%;
  transform:translateY(-50%);width:0;height:0;
  border-top:8px solid transparent;border-bottom:8px solid transparent;
  border-left:10px solid #e5e7eb;z-index:1;}
.pipe-step .num{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1.6rem;line-height:1;}
.pipe-step .lbl{font-size:11px;color:#6b7280;margin-top:2px;font-weight:500;}
.pipe-step.active-step{background:var(--primary-light);border-radius:12px;}
.pipe-step.active-step .num{color:var(--primary);}
.quick-action{display:flex;flex-direction:column;align-items:center;gap:6px;
              text-decoration:none;padding:14px 10px;border-radius:14px;
              border:1.5px solid var(--border);background:#fff;
              transition:.2s;text-align:center;font-size:12px;font-weight:600;color:#374151;}
.quick-action:hover{border-color:var(--primary);background:var(--primary-light);color:var(--primary);transform:translateY(-2px);}
.quick-action i{font-size:1.4rem;}
.app-row{display:flex;align-items:center;gap:12px;padding:14px 20px;
         border-bottom:1px solid #f3f4f6;transition:.15s;}
.app-row:last-child{border-bottom:none;}
.app-row:hover{background:#f9fafb;}
</style>

<!-- Hero greeting -->
<div class="dash-hero">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <p class="mb-1 opacity-75 small">👋 Welcome back</p>
      <h1 class="fw-800 mb-1" style="font-size:1.6rem;letter-spacing:-.5px;">
        <?= e($firstName) ?>!
      </h1>
      <p class="mb-0 opacity-75 small">
        <?php if($totalApps > 0): ?>
          You have <?= $totalApps ?> application<?= $totalApps>1?'s':'' ?> out — keep going!
        <?php else: ?>
          Ready to find your dream job? Start exploring today.
        <?php endif; ?>
      </p>
    </div>
    <div class="d-flex align-items-center gap-3">
      <!-- Profile completion ring -->
      <div class="score-ring">
        <?php $r = 34; $c = 2*M_PI*$r; $dash = ($score/100)*$c; ?>
        <svg width="80" height="80" viewBox="0 0 80 80">
          <circle cx="40" cy="40" r="<?=$r?>" fill="none" stroke="rgba(255,255,255,.2)" stroke-width="7"/>
          <circle cx="40" cy="40" r="<?=$r?>" fill="none" stroke="#fff" stroke-width="7"
                  stroke-dasharray="<?= round($dash,1) ?> <?= round($c,1) ?>"
                  stroke-linecap="round"/>
        </svg>
        <div class="score-text"><?= $score ?>%</div>
      </div>
      <div>
        <div class="fw-700 small">Profile</div>
        <div class="opacity-75" style="font-size:11px;">completion</div>
        <?php if($score < 100): ?>
        <a href="<?= url('/seeker/profile') ?>" class="btn btn-sm mt-1"
           style="background:rgba(255,255,255,.2);color:#fff;font-size:11px;padding:3px 10px;border-radius:20px;">
          Complete ↗
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Application pipeline -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:16px;overflow:hidden;">
  <div class="card-header bg-white border-bottom py-3 px-4">
    <h6 class="fw-700 mb-0">Application Pipeline</h6>
  </div>
  <div class="card-body p-0">
    <div class="pipeline">
      <?php foreach([
        ['label'=>'Applied',      'val'=>$totalApps,   'color'=>'#1A56DB'],
        ['label'=>'Shortlisted',  'val'=>$shortlisted, 'color'=>'#7C3AED'],
        ['label'=>'Interviews',   'val'=>$interviews,  'color'=>'#D97706'],
        ['label'=>'Offers',       'val'=>$offers,      'color'=>'#057A55'],
      ] as $step): ?>
      <div class="pipe-step <?= $step['val']>0?'active-step':'' ?>">
        <div class="num" style="color:<?= $step['val']>0 ? $step['color'] : '#d1d5db' ?>;">
          <?= $step['val'] ?>
        </div>
        <div class="lbl"><?= $step['label'] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="row g-3 mb-4">
  <?php foreach([
    ['icon'=>'search',              'label'=>'Find Jobs',       'url'=>'/jobs',                'color'=>'#1A56DB'],
    ['icon'=>'file-earmark-person', 'label'=>'My Resume',       'url'=>'/seeker/resume/preview','color'=>'#7C3AED'],
    ['icon'=>'bookmark',            'label'=>'Saved Jobs',      'url'=>'/seeker/saved-jobs',   'color'=>'#D97706'],
    ['icon'=>'bell',                'label'=>'Job Alerts',      'url'=>'/seeker/alerts',        'color'=>'#057A55'],
    ['icon'=>'envelope',            'label'=>'Messages',        'url'=>'/messages',             'color'=>'#0891B2'],
    ['icon'=>'person-circle',       'label'=>'Edit Profile',    'url'=>'/seeker/profile',       'color'=>'#DC2626'],
  ] as $qa): ?>
  <div class="col-4 col-md-2">
    <a href="<?= url($qa['url']) ?>" class="quick-action w-100">
      <i class="bi bi-<?= $qa['icon'] ?>" style="color:<?= $qa['color'] ?>;"></i>
      <?= $qa['label'] ?>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="row g-4">
  <!-- Recent applications -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Recent Applications</h6>
        <a href="<?= url('/seeker/applications') ?>" class="btn btn-sm btn-light fw-600">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($recentApps)): ?>
        <?php empty_state('applications', 'No applications yet', 'Start exploring jobs and apply — your applications will show up here.',
          ['label'=>'Browse Jobs', 'url'=>'/jobs', 'icon'=>'search'], 'md'); ?>
        <?php else: ?>
          <?php foreach($recentApps as $app): ?>
          <div class="app-row">
            <div class="flex-shrink-0">
              <?php if($app['company_logo']): ?>
              <img src="<?= url('/file?path='.urlencode($app['company_logo'])) ?>"
                   style="width:44px;height:44px;border-radius:10px;object-fit:contain;
                          border:1px solid #e5e7eb;padding:4px;background:#fff;" alt="">
              <?php else: ?>
              <div class="d-flex align-items-center justify-content-center fw-800"
                   style="width:44px;height:44px;border-radius:10px;
                          background:#EBF5FF;color:#1A56DB;font-size:18px;">
                <?= strtoupper(substr($app['company_name'],0,1)) ?>
              </div>
              <?php endif; ?>
            </div>
            <div class="flex-grow-1 min-w-0">
              <div class="fw-700 small text-truncate"><?= e($app['job_title']) ?></div>
              <div class="text-muted" style="font-size:12px;"><?= e($app['company_name']) ?></div>
            </div>
            <div class="text-end flex-shrink-0">
              <span class="badge <?= status_badge($app['status']) ?> rounded-pill"
                    style="font-size:11px;">
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

  <!-- Right column -->
  <div class="col-lg-4 d-flex flex-column gap-3">

    <!-- Profile card -->
    <div class="card border-0 shadow-sm text-center" style="border-radius:16px;">
      <div class="card-body p-4">
        <?php if($profile['avatar_path']): ?>
        <img src="<?= url('/file?path='.urlencode($profile['avatar_path'])) ?>"
             class="avatar mb-3" style="width:72px;height:72px;" alt="">
        <?php else: ?>
        <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center
                    justify-content-center fw-800 mb-3"
             style="width:72px;height:72px;font-size:26px;">
          <?= $initials ?>
        </div>
        <?php endif; ?>
        <h6 class="fw-800 mb-1"><?= e($profile['full_name']) ?></h6>
        <p class="text-muted small mb-3">
          <?= $profile['headline'] ? e(truncate($profile['headline'],60)) : 'Add a headline to your profile' ?>
        </p>
        <?php if($profile['is_open_to_work']): ?>
        <div class="badge mb-3"
             style="background:#F0FDF4;color:#057A55;font-size:11px;padding:6px 12px;border-radius:50px;">
          <i class="bi bi-circle-fill me-1" style="font-size:7px;"></i>Open to Work
        </div><br>
        <?php endif; ?>
        <a href="<?= url('/seeker/profile') ?>" class="btn btn-outline-primary btn-sm w-100 fw-600">
          Edit Profile
        </a>
      </div>
    </div>

    <?php if (!empty($reminderCount)): ?>
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
        <div>
          <div class="text-uppercase text-secondary small fw-600 mb-2">Interview Reminder</div>
          <div class="fw-700 d-flex align-items-center gap-2">
            <span>You have <?= $reminderCount ?> unread reminder<?= $reminderCount > 1 ? 's' : '' ?></span>
            <?php if ($nextInterviewUrgent): ?>
            <span class="badge bg-danger text-white" style="font-size:10px;">Within 24h</span>
            <?php endif; ?>
          </div>
          <?php if (!empty($nextInterview)): ?>
          <div class="text-muted small d-flex align-items-center gap-2">
            <?php if ($nextInterviewToday): ?>
            <i class="bi bi-exclamation-circle-fill text-danger"></i>
            <?php endif; ?>
            <span>Next interview: <?= e($nextInterviewLabel) ?></span>
          </div>
          <?php else: ?>
          <div class="text-muted small">Check your notifications for interview details.</div>
          <?php endif; ?>
        </div>
        <a href="<?= url('/notifications') ?>" class="btn btn-sm btn-outline-warning fw-600">
          View reminders
        </a>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($nextInterview)): ?>
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <div>
          <h6 class="fw-700 mb-0"><i class="bi bi-calendar-check me-2 text-warning"></i>Upcoming Interview</h6>
          <div class="small text-muted">Next scheduled interview for your application</div>
        </div>
        <span class="badge bg-light text-dark fw-600" style="font-size:11px;">
          <?= ucfirst(str_replace('_', ' ', $nextInterview['interview_type'] ?? 'video')) ?>
        </span>
      </div>
      <div class="card-body p-4">
        <div class="fw-700 mb-1 text-truncate"><?= e($nextInterview['job_title']) ?></div>
        <div class="text-muted small mb-3"><?= e($nextInterview['company_name']) ?></div>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="badge bg-light text-dark fw-600"><?= date('d M Y · H:i', strtotime($nextInterview['scheduled_at'])) ?></span>
          <span class="badge bg-light text-dark fw-600"><?= ucfirst(str_replace('_', ' ', $nextInterview['interview_type'] ?? 'video')) ?></span>
        </div>
        <?php if (!empty($nextInterview['meeting_link'])): ?>
        <a href="<?= e($nextInterview['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-outline-success w-100 fw-600 mb-2">
          <i class="bi bi-box-arrow-up-right me-1"></i>Join interview
        </a>
        <?php elseif (!empty($nextInterview['location_address'])): ?>
        <div class="small text-muted mb-3">
          Location: <?= e($nextInterview['location_address']) ?>
        </div>
        <?php endif; ?>
        <a href="<?= url('/seeker/applications') ?>" class="btn btn-sm btn-primary w-100 fw-600">
          View application
        </a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Application progress -->
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <div>
          <h6 class="fw-700 mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Application Progress</h6>
          <div class="small text-muted">Where your applications stand right now</div>
        </div>
        <span class="badge bg-light text-dark fw-600" style="font-size:11px;">
          <?= $totalApps ?> total
        </span>
      </div>
      <div class="card-body p-4">
        <?php $appliedPct = $totalApps ? round((($statusCounts['applied'] ?? 0) / $totalApps) * 100) : 0; ?>
        <?php $shortlistedPct = $totalApps ? round((($statusCounts['shortlisted'] ?? 0) / $totalApps) * 100) : 0; ?>
        <?php $interviewsPct = $totalApps ? round((($statusCounts['interview_scheduled'] ?? 0) / $totalApps) * 100) : 0; ?>
        <?php $offersCount = ($statusCounts['offered'] ?? 0) + ($statusCounts['hired'] ?? 0); ?>
        <?php $offersPct = $totalApps ? round(($offersCount / $totalApps) * 100) : 0; ?>
        <div class="mb-3" style="font-size:13px;color:#475569;">
          <div class="d-flex justify-content-between mb-2">
            <span>Applications in progress</span>
            <strong><?= $activeApps = $totalApps - ($statusCounts['rejected'] ?? 0) - ($statusCounts['withdrawn'] ?? 0) ?> open</strong>
          </div>
          <div style="height:10px;border-radius:999px;background:#E5E7EB;overflow:hidden;">
            <div style="width:<?= $appliedPct ?>%;background:#1A56DB;height:100%;display:inline-block;"></div>
            <div style="width:<?= $shortlistedPct ?>%;background:#7C3AED;height:100%;display:inline-block;"></div>
            <div style="width:<?= $interviewsPct ?>%;background:#D97706;height:100%;display:inline-block;"></div>
            <div style="width:<?= $offersPct ?>%;background:#057A55;height:100%;display:inline-block;"></div>
          </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach([
            ['label'=>'Applied','count'=>$statusCounts['applied'] ?? 0,'color'=>'#1A56DB'],
            ['label'=>'Shortlisted','count'=>$statusCounts['shortlisted'] ?? 0,'color'=>'#7C3AED'],
            ['label'=>'Interviews','count'=>$statusCounts['interview_scheduled'] ?? 0,'color'=>'#D97706'],
            ['label'=>'Offers','count'=>($statusCounts['offered'] ?? 0) + ($statusCounts['hired'] ?? 0),'color'=>'#057A55'],
          ] as $stat): ?>
          <div class="rounded-3 p-2" style="background:#F8FAFC;flex:1 1 45%;min-width:120px;">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <span class="small text-muted"><?= $stat['label'] ?></span>
              <span class="fw-700" style="color:<?= $stat['color'] ?>;"><?= $stat['count'] ?></span>
            </div>
            <div class="progress" style="height:6px;background:#E5E7EB;border-radius:99px;">
              <div class="progress-bar" role="progressbar"
                   style="width:<?= $totalApps ? round(($stat['count'] / $totalApps) * 100) : 0 ?>%;background:<?= $stat['color'] ?>;"
                   aria-valuenow="<?= $stat['count'] ?>" aria-valuemin="0" aria-valuemax="<?= $totalApps ?>">
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Skills -->
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-lightning-fill me-2 text-warning"></i>Skills</h6>
        <a href="<?= url('/seeker/profile#skills') ?>" class="btn btn-sm btn-light">Manage</a>
      </div>
      <div class="card-body p-3">
        <?php if(empty($skills)): ?>
        <?php empty_state('default', 'No skills added', 'Adding skills helps employers find you faster.',
          ['label'=>'Add Skills', 'url'=>'/seeker/profile#skills', 'icon'=>'plus-circle'], 'sm'); ?>
        <?php else: ?>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach(array_slice($skills,0,8) as $sk): ?>
          <span class="badge rounded-pill fw-500"
                style="background:#EBF5FF;color:#1A56DB;padding:6px 12px;font-size:12px;">
            <?= e($sk['skill_name']) ?>
          </span>
          <?php endforeach; ?>
          <?php if(count($skills) > 8): ?>
          <span class="badge rounded-pill bg-light text-muted fw-500"
                style="padding:6px 12px;font-size:12px;">
            +<?= count($skills)-8 ?> more
          </span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>
