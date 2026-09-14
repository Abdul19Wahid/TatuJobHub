<style>
.admin-statcard{border-radius:16px;padding:20px;border:none;text-decoration:none;
                display:block;transition:.2s;height:100%;}
.admin-statcard:hover{transform:translateY(-2px);box-shadow:0 8px 28px rgba(0,0,0,.1);}
.admin-statcard .stat-val{font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;
                           font-size:1.9rem;line-height:1;margin-bottom:4px;}
.admin-statcard .stat-lbl{font-size:12px;font-weight:500;opacity:.75;}
.admin-statcard .stat-icon{width:44px;height:44px;border-radius:12px;
                            background:rgba(255,255,255,.25);
                            display:flex;align-items:center;justify-content:center;font-size:1.2rem;}
.admin-statcard .trend{font-size:11px;margin-top:6px;font-weight:600;}
.pending-badge{display:inline-flex;align-items:center;gap:6px;background:#FEF3C7;
               color:#92400e;font-size:12px;font-weight:700;padding:4px 12px;
               border-radius:50px;border:1px solid #fde68a;}
.activity-row{display:flex;align-items:center;gap:12px;padding:12px 20px;
              border-bottom:1px solid #f3f4f6;}
.activity-row:last-child{border-bottom:none;}
.activity-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
.verif-row{display:flex;align-items:center;gap:12px;padding:14px 20px;
           border-bottom:1px solid #f3f4f6;}
.verif-row:last-child{border-bottom:none;}
</style>

<!-- Page header -->
<div class="d-flex align-items-start justify-content-between mb-4 flex-wrap gap-3">
  <div>
    <h1 class="h4 fw-800 mb-1">Admin Dashboard</h1>
    <p class="text-muted small mb-0">
      <i class="bi bi-calendar3 me-1"></i><?= date('l, F j, Y') ?>
    </p>
  </div>
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <?php if($stats['pending_verify']>0): ?>
    <a href="<?= url('/admin/verifications') ?>" class="pending-badge text-decoration-none">
      <i class="bi bi-shield-exclamation"></i>
      <?= $stats['pending_verify'] ?> pending verification<?= $stats['pending_verify']>1?'s':'' ?>
    </a>
    <?php endif; ?>
    <a href="<?= url('/admin/reports') ?>" class="btn btn-primary fw-600">
      <i class="bi bi-graph-up me-2"></i>Reports
    </a>
  </div>
</div>

<!-- Stat cards row 1 -->
<div class="row g-3 mb-4">
  <?php
  $cards=[
    ['val'=>number_format($stats['total_users']),    'lbl'=>'Total Users',      'icon'=>'people-fill',       'bg'=>'#1A56DB','link'=>'/admin/users'],
    ['val'=>number_format($stats['total_seekers']),  'lbl'=>'Job Seekers',      'icon'=>'person-badge-fill', 'bg'=>'#7C3AED','link'=>'/admin/users?role=seeker'],
    ['val'=>number_format($stats['total_employers']),'lbl'=>'Employers',        'icon'=>'building-fill',     'bg'=>'#0891B2','link'=>'/admin/users?role=employer'],
    ['val'=>number_format($stats['active_jobs']),    'lbl'=>'Active Jobs',      'icon'=>'briefcase-fill',    'bg'=>'#D97706','link'=>'/admin/jobs'],
    ['val'=>number_format($stats['total_apps']),     'lbl'=>'Total Applications','icon'=>'send-fill',        'bg'=>'#057A55','link'=>'/admin/reports'],
    ['val'=>$stats['pending_verify'],                'lbl'=>'Pending Verif.',   'icon'=>'shield-fill-check', 'bg'=>'#DC2626','link'=>'/admin/verifications'],
    ['val'=>$stats['new_users_today'],               'lbl'=>'New Users Today',  'icon'=>'person-plus-fill',  'bg'=>'#059669','link'=>'/admin/users'],
    ['val'=>$stats['apps_today'],                    'lbl'=>'Applications Today','icon'=>'file-earmark-plus','bg'=>'#2563EB','link'=>'/admin/reports'],
  ];
  foreach($cards as $c): ?>
  <div class="col-6 col-md-3">
    <a href="<?= url($c['link']) ?>" class="admin-statcard text-white text-decoration-none"
       style="background:<?= $c['bg'] ?>;">
      <div class="d-flex align-items-start justify-content-between mb-2">
        <div class="stat-icon"><i class="bi bi-<?= $c['icon'] ?>"></i></div>
        <i class="bi bi-arrow-right-short opacity-50 fs-5"></i>
      </div>
      <div class="stat-val"><?= $c['val'] ?></div>
      <div class="stat-lbl"><?= $c['lbl'] ?></div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts row -->
<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm" style="border-radius:16px;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-graph-up me-2 text-primary"></i>New Users — Last 7 Days</h6>
        <a href="<?= url('/admin/reports') ?>" class="btn btn-sm btn-light fw-600">Full Report</a>
      </div>
      <div class="card-body p-4">
        <canvas id="dashSparkReg" height="90"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm" style="border-radius:16px;height:100%;">
      <div class="card-header bg-white border-bottom py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>User Breakdown</h6>
      </div>
      <div class="card-body p-3 d-flex align-items-center justify-content-center">
        <canvas id="dashRoleChart" height="160"></canvas>
      </div>
    </div>
  </div>
</div>

<!-- Bottom row -->
<div class="row g-4">
  <!-- Recent registrations -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0"><i class="bi bi-person-plus me-2 text-primary"></i>Recent Registrations</h6>
        <a href="<?= url('/admin/users') ?>" class="btn btn-sm btn-light fw-600">View All</a>
      </div>
      <div class="card-body p-0">
        <?php foreach($recentUsers as $u):
          $roleColors=['seeker'=>['#EBF5FF','#1A56DB'],'employer'=>['#EDE9FE','#7C3AED'],'admin'=>['#FEE2E2','#DC2626']];
          [$rbg,$rcol]=$roleColors[$u['role']]??['#F3F4F6','#6B7280']; ?>
        <div class="activity-row">
          <div class="rounded-circle d-flex align-items-center justify-content-center fw-700 flex-shrink-0"
               style="width:38px;height:38px;font-size:13px;background:<?=$rbg?>;color:<?=$rcol?>;">
            <?= strtoupper(substr($u['full_name'],0,1)) ?>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-700 small text-truncate"><?= e($u['full_name']) ?></div>
            <div class="text-muted" style="font-size:12px;"><?= e($u['email']) ?></div>
          </div>
          <div class="text-end flex-shrink-0">
            <span class="badge rounded-pill fw-500"
                  style="background:<?=$rbg?>;color:<?=$rcol?>;font-size:11px;">
              <?= ucfirst($u['role']) ?>
            </span>
            <div class="text-muted mt-1" style="font-size:11px;"><?= time_ago($u['created_at']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Pending verifications -->
  <div class="col-lg-6">
    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
      <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3 px-4">
        <h6 class="fw-700 mb-0">
          <i class="bi bi-shield-check me-2 text-warning"></i>Pending Verifications
          <?php if($stats['pending_verify']>0): ?>
          <span class="badge bg-warning text-dark ms-1" style="font-size:11px;"><?= $stats['pending_verify'] ?></span>
          <?php endif; ?>
        </h6>
        <a href="<?= url('/admin/verifications') ?>" class="btn btn-sm btn-light fw-600">View All</a>
      </div>
      <div class="card-body p-0">
        <?php if(empty($pendingVerifications)): ?>
        <?php empty_state('verified', 'All caught up!', 'No pending company verifications to review right now.', null, 'md'); ?>
        <?php else: ?>
        <?php foreach($pendingVerifications as $cv): ?>
        <div class="verif-row">
          <div class="rounded-3 bg-light d-flex align-items-center justify-content-center
                      fw-800 flex-shrink-0" style="width:42px;height:42px;font-size:17px;color:#374151;">
            <?= strtoupper(substr($cv['company_name'],0,1)) ?>
          </div>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-700 small"><?= e($cv['company_name']) ?></div>
            <div class="text-muted" style="font-size:12px;"><?= e($cv['email']) ?></div>
            <div class="text-muted" style="font-size:11px;"><?= time_ago($cv['created_at']) ?></div>
          </div>
          <a href="<?= url('/admin/verifications') ?>"
             class="btn btn-sm btn-warning fw-600 flex-shrink-0" style="border-radius:8px;">
            Review
          </a>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.font.family="'Plus Jakarta Sans','DM Sans',sans-serif";
Chart.defaults.color='#6b7280';

// 7-day sparkline
(function(){
  const labels=[];
  for(let i=6;i>=0;i--){
    const d=new Date();d.setDate(d.getDate()-i);
    labels.push(d.toLocaleDateString('en-US',{month:'short',day:'numeric'}));
  }
  new Chart(document.getElementById('dashSparkReg'),{
    type:'bar',
    data:{
      labels,
      datasets:[{
        label:'New Users',
        data:<?php
          $db=Database::getInstance();
          $map=[];
          $rows=$db->fetchAll("SELECT DATE(created_at) AS day,COUNT(*) AS cnt FROM users WHERE created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY DATE(created_at)");
          foreach($rows as $r)$map[$r['day']]=(int)$r['cnt'];
          $out=[];
          for($i=6;$i>=0;$i--){$d=date('Y-m-d',strtotime("-$i days"));$out[]=$map[$d]??0;}
          echo json_encode($out);
        ?>,
        backgroundColor:'rgba(26,86,219,.15)',
        borderColor:'#1A56DB',
        borderWidth:2,
        borderRadius:8,
        hoverBackgroundColor:'rgba(26,86,219,.3)',
      },{
        label:'Applications',
        data:<?php
          $map2=[];
          $rows2=$db->fetchAll("SELECT DATE(applied_at) AS day,COUNT(*) AS cnt FROM applications WHERE applied_at>=DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY DATE(applied_at)");
          foreach($rows2 as $r)$map2[$r['day']]=(int)$r['cnt'];
          $out2=[];
          for($i=6;$i>=0;$i--){$d=date('Y-m-d',strtotime("-$i days"));$out2[]=$map2[$d]??0;}
          echo json_encode($out2);
        ?>,
        backgroundColor:'rgba(124,58,237,.12)',
        borderColor:'#7C3AED',
        borderWidth:2,
        borderRadius:8,
        hoverBackgroundColor:'rgba(124,58,237,.25)',
      }]
    },
    options:{
      responsive:true,
      plugins:{legend:{position:'top',labels:{usePointStyle:true,boxWidth:8,padding:16}}},
      scales:{
        x:{grid:{display:false},ticks:{font:{size:11}}},
        y:{beginAtZero:true,ticks:{precision:0},grid:{color:'#f8fafc'}}
      }
    }
  });
})();

// Role doughnut
new Chart(document.getElementById('dashRoleChart'),{
  type:'doughnut',
  data:{
    labels:['Seekers','Employers','Admins'],
    datasets:[{
      data:[<?= $stats['total_seekers'] ?>,<?= $stats['total_employers'] ?>,<?= max(0,(int)$stats['total_users']-(int)$stats['total_seekers']-(int)$stats['total_employers']) ?>],
      backgroundColor:['#1A56DB','#7C3AED','#DC2626'],
      borderWidth:3,borderColor:'#fff',hoverOffset:6,
    }]
  },
  options:{
    cutout:'68%',
    plugins:{legend:{position:'bottom',labels:{usePointStyle:true,boxWidth:8,padding:14,font:{size:12}}}}
  }
});
</script>
