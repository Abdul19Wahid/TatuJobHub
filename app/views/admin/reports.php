<?php
// ── Prepare chart data as JSON ─────────────────────────────────────────────
$roleColors  = ['seeker'=>'#1A56DB','employer'=>'#7C3AED','admin'=>'#DC2626'];
$statusColors= ['applied'=>'#6B7280','under_review'=>'#0891B2','shortlisted'=>'#1A56DB',
                 'interview_scheduled'=>'#D97706','offered'=>'#059669','hired'=>'#057A55',
                 'rejected'=>'#DC2626','withdrawn'=>'#374151'];
$typeColors  = ['full_time'=>'#1A56DB','part_time'=>'#059669','contract'=>'#D97706',
                'internship'=>'#7C3AED','freelance'=>'#DC2626','volunteer'=>'#6B7280'];

$roleLabels = array_column($usersByRole,'role');
$roleCounts = array_column($usersByRole,'cnt');
$roleBg     = array_map(fn($r) => $roleColors[$r] ?? '#6B7280', $roleLabels);

$statusLabels = array_map('status_label', array_column($appsByStatus,'status'));
$statusCounts = array_column($appsByStatus,'cnt');
$statusBg     = array_map(fn($r) => $statusColors[$r] ?? '#6B7280', array_column($appsByStatus,'status'));

$typeLabels = array_map('status_label', array_column($jobsByType,'job_type'));
$typeCounts = array_column($jobsByType,'cnt');
$typeBg     = array_map(fn($r) => $typeColors[$r] ?? '#6B7280', array_column($jobsByType,'job_type'));

$indLabels = array_column($topIndustries,'industry');
$indCounts = array_column($topIndustries,'cnt');

// Fill 30-day trend
$regMap = []; foreach ($dailyRegistrations as $r) $regMap[$r['day']] = (int)$r['cnt'];
$appMap = []; foreach ($dailyApplications  as $r) $appMap[$r['day']] = (int)$r['cnt'];
$trendLabels = $trendReg = $trendApp = [];
for ($i = 29; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $trendLabels[] = date('M d', strtotime($day));
    $trendReg[]    = $regMap[$day] ?? 0;
    $trendApp[]    = $appMap[$day] ?? 0;
}

$monthLabels = array_map(fn($r) => date('M Y', strtotime($r['month'].'-01')), $monthlyJobs);
$monthCounts = array_column($monthlyJobs,'cnt');

$funnelLabels = ['Applied','Reviewed','Shortlisted','Interviewed','Offered','Hired'];
$funnelData   = [
    (int)($funnel['total_applied'] ?? 0),
    (int)($funnel['reviewed']      ?? 0),
    (int)($funnel['shortlisted']   ?? 0),
    (int)($funnel['interviewed']   ?? 0),
    (int)($funnel['offered']       ?? 0),
    (int)($funnel['hired']         ?? 0),
];
$funnelBg = ['#1A56DB','#0891B2','#7C3AED','#D97706','#059669','#057A55'];
?>

<div class="mb-4">
    <h1 class="h4 fw-800 mb-0">Reports & Analytics</h1>
    <p class="text-muted small mb-0">Live platform statistics with interactive charts.</p>
</div>

<!-- Export buttons -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-700 mb-0"><i class="bi bi-download me-2 text-primary"></i>Export Data (CSV)</h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-2 border text-center h-100">
                    <i class="bi bi-people text-primary" style="font-size:2rem;"></i>
                    <h6 class="fw-700 mt-2 mb-1">Users</h6>
                    <p class="text-muted small mb-3">All registered users with roles and status</p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                        <a href="<?= url('/admin/export/users') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-download me-1"></i>All Users
                        </a>
                        <a href="<?= url('/admin/export/users?role=seeker') ?>" class="btn btn-sm btn-outline-primary">Seekers</a>
                        <a href="<?= url('/admin/export/users?role=employer') ?>" class="btn btn-sm btn-outline-primary">Employers</a>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-2 border text-center h-100">
                    <i class="bi bi-briefcase text-success" style="font-size:2rem;"></i>
                    <h6 class="fw-700 mt-2 mb-1">Job Listings</h6>
                    <p class="text-muted small mb-3">All jobs with views, applications and status</p>
                    <a href="<?= url('/admin/export/jobs') ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-download me-1"></i>Export Jobs
                    </a>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-2 border text-center h-100">
                    <i class="bi bi-file-earmark-text text-warning" style="font-size:2rem;"></i>
                    <h6 class="fw-700 mt-2 mb-1">Applications</h6>
                    <p class="text-muted small mb-3">All applications with seeker and job details</p>
                    <a href="<?= url('/admin/export/applications') ?>" class="btn btn-sm btn-warning text-dark">
                        <i class="bi bi-download me-1"></i>Export Applications
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart 1: 30-day activity trend -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="fw-700 mb-0">
            <i class="bi bi-graph-up me-2 text-primary"></i>Activity Trend — Last 30 Days
        </h6>
        <div class="d-flex gap-3 small">
            <span><span class="badge me-1" style="background:#1A56DB;">&nbsp;&nbsp;</span>Registrations</span>
            <span><span class="badge me-1" style="background:#059669;">&nbsp;&nbsp;</span>Applications</span>
        </div>
    </div>
    <div class="card-body p-4">
        <canvas id="trendChart" height="80"></canvas>
    </div>
</div>

<!-- Row 2: Doughnut + monthly bar -->
<div class="row g-4 mb-4">

    <!-- Chart 2: Users by role -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0"><i class="bi bi-people me-2 text-primary"></i>Users by Role</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center p-4">
                <canvas id="roleChart" width="220" height="220" style="max-width:220px;"></canvas>
                <div class="d-flex flex-wrap justify-content-center gap-4 mt-3">
                    <?php foreach ($usersByRole as $r): ?>
                    <div class="text-center">
                        <div class="fw-800" style="font-size:1.4rem;color:<?= $roleColors[$r['role']] ?? '#6B7280' ?>;">
                            <?= number_format($r['cnt']) ?>
                        </div>
                        <div class="text-muted small"><?= ucfirst($r['role']) ?>s</div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 3: Jobs by type -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Jobs by Type</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center p-4">
                <canvas id="typeChart" width="220" height="220" style="max-width:220px;"></canvas>
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                    <?php foreach ($jobsByType as $i => $j): ?>
                    <span class="badge rounded-pill fw-500"
                          style="background:<?= $typeBg[$i] ?? '#6B7280' ?>;font-size:11px;padding:5px 10px;">
                        <?= status_label($j['job_type']) ?>: <?= $j['cnt'] ?>
                    </span>
                    <?php endforeach; ?>
                    <?php if(empty($jobsByType)): ?>
                    <p class="text-muted small mb-0">No active jobs yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart 4: Monthly postings -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0"><i class="bi bi-calendar3 me-2 text-primary"></i>Jobs Posted (6 Months)</h6>
            </div>
            <div class="card-body p-4">
                <?php if(empty($monthlyJobs)): ?>
                <div class="text-center py-4 text-muted small">No job postings yet.</div>
                <?php else: ?>
                <canvas id="monthlyChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Horizontal bar charts -->
<div class="row g-4 mb-4">

    <!-- Chart 5: Applications by status -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0"><i class="bi bi-bar-chart me-2 text-primary"></i>Applications by Status</h6>
            </div>
            <div class="card-body p-4">
                <?php if(empty($appsByStatus)): ?>
                <div class="text-center py-4 text-muted small">No applications yet.</div>
                <?php else: ?>
                <canvas id="statusChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chart 6: Top industries -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0"><i class="bi bi-building me-2 text-primary"></i>Top Industries (Active Jobs)</h6>
            </div>
            <div class="card-body p-4">
                <?php if(empty($topIndustries)): ?>
                <div class="text-center py-4 text-muted small">No industry data yet.</div>
                <?php else: ?>
                <canvas id="industryChart"></canvas>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Recruitment funnel -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-700 mb-0">
            <i class="bi bi-filter me-2 text-primary"></i>Recruitment Funnel — All Time
        </h6>
    </div>
    <div class="card-body p-4">
        <div class="row g-3 align-items-end text-center">
            <?php
            $funnelTotal = $funnelData[0] ?: 1;
            foreach ($funnelLabels as $idx => $label):
                $count = $funnelData[$idx];
                $color = $funnelBg[$idx];
                $pct   = $funnelTotal > 0 ? round($count / $funnelTotal * 100) : 0;
                $barH  = max(12, (int)($pct * 1.2 + 12));
            ?>
            <div class="col-2">
                <div class="fw-800 mb-2" style="font-size:1.5rem;color:<?= $color ?>;">
                    <?= number_format($count) ?>
                </div>
                <div class="progress mb-2 mx-auto" style="height:<?= $barH ?>px;border-radius:6px;width:80%;">
                    <div class="progress-bar" style="width:100%;background:<?= $color ?>;border-radius:6px;"></div>
                </div>
                <div class="fw-700 small" style="color:<?= $color ?>;"><?= $label ?></div>
                <div class="text-muted" style="font-size:11px;"><?= $pct ?>% of applied</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', 'DM Sans', sans-serif";
Chart.defaults.color = '#6B7280';

// ── 1. 30-day trend ────────────────────────────────────────────────────────
new Chart(document.getElementById('trendChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($trendLabels) ?>,
    datasets: [
      {
        label: 'Registrations',
        data: <?= json_encode($trendReg) ?>,
        borderColor: '#1A56DB', backgroundColor: 'rgba(26,86,219,.1)',
        borderWidth: 2.5, fill: true, tension: 0.4,
        pointRadius: 3, pointBackgroundColor: '#1A56DB',
      },
      {
        label: 'Applications',
        data: <?= json_encode($trendApp) ?>,
        borderColor: '#059669', backgroundColor: 'rgba(5,150,105,.08)',
        borderWidth: 2.5, fill: true, tension: 0.4,
        pointRadius: 3, pointBackgroundColor: '#059669',
      }
    ]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index', intersect: false },
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { maxTicksLimit: 10, font: { size: 11 } } },
      y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0, font: { size: 11 } } }
    }
  }
});

// ── 2. Users by role doughnut ──────────────────────────────────────────────
new Chart(document.getElementById('roleChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode(array_map('ucfirst', $roleLabels)) ?>,
    datasets: [{
      data: <?= json_encode($roleCounts) ?>,
      backgroundColor: <?= json_encode($roleBg) ?>,
      borderWidth: 3, borderColor: '#fff',
    }]
  },
  options: {
    cutout: '68%',
    plugins: { legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 14 } } }
  }
});

// ── 3. Jobs by type doughnut ───────────────────────────────────────────────
<?php if (!empty($jobsByType)): ?>
new Chart(document.getElementById('typeChart'), {
  type: 'doughnut',
  data: {
    labels: <?= json_encode($typeLabels) ?>,
    datasets: [{
      data: <?= json_encode($typeCounts) ?>,
      backgroundColor: <?= json_encode($typeBg) ?>,
      borderWidth: 3, borderColor: '#fff',
    }]
  },
  options: {
    cutout: '68%',
    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } } }
  }
});
<?php endif; ?>

// ── 4. Monthly job postings ────────────────────────────────────────────────
<?php if (!empty($monthlyJobs)): ?>
new Chart(document.getElementById('monthlyChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($monthLabels) ?>,
    datasets: [{
      label: 'Jobs Posted',
      data: <?= json_encode($monthCounts) ?>,
      backgroundColor: 'rgba(26,86,219,.75)',
      borderRadius: 8, borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false } },
      y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } }
    }
  }
});
<?php endif; ?>

// ── 5. Applications by status (horizontal) ────────────────────────────────
<?php if (!empty($appsByStatus)): ?>
new Chart(document.getElementById('statusChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($statusLabels) ?>,
    datasets: [{
      label: 'Applications',
      data: <?= json_encode($statusCounts) ?>,
      backgroundColor: <?= json_encode($statusBg) ?>,
      borderRadius: 6, borderSkipped: false,
    }]
  },
  options: {
    indexAxis: 'y', responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
      y: { grid: { display: false } }
    }
  }
});
<?php endif; ?>

// ── 6. Top industries (horizontal) ────────────────────────────────────────
<?php if (!empty($topIndustries)): ?>
new Chart(document.getElementById('industryChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($indLabels) ?>,
    datasets: [{
      label: 'Open Jobs',
      data: <?= json_encode($indCounts) ?>,
      backgroundColor: [
        'rgba(26,86,219,.8)','rgba(124,58,237,.8)','rgba(5,122,85,.8)',
        'rgba(217,119,6,.8)','rgba(220,38,38,.8)','rgba(8,145,178,.8)',
        'rgba(107,114,128,.8)','rgba(55,48,163,.8)'
      ],
      borderRadius: 6, borderSkipped: false,
    }]
  },
  options: {
    indexAxis: 'y', responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f1f5f9' } },
      y: { grid: { display: false }, ticks: { font: { size: 11 } } }
    }
  }
});
<?php endif; ?>
</script>
