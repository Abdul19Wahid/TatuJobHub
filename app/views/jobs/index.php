<?php
$jobTypes  = ['full_time'=>'Full-Time','part_time'=>'Part-Time','contract'=>'Contract',
              'internship'=>'Internship','freelance'=>'Freelance'];
$expLevels = ['entry'=>'Entry Level','junior'=>'Junior','mid'=>'Mid-Level',
              'senior'=>'Senior','lead'=>'Lead','executive'=>'Executive'];
$industries= ['Information Technology','Finance','Healthcare','Education','Sales & Marketing',
              'Engineering','Legal','Logistics','Design','Human Resources',
              'Manufacturing','Real Estate','Other'];

// Build active filter chips
$activeFilters = [];
if (!empty($filters['q']))                $activeFilters['q']                = ['label'=>'Keyword: '.e($filters['q']),        'key'=>'q'];
if (!empty($filters['location']))         $activeFilters['location']         = ['label'=>'Location: '.e($filters['location']), 'key'=>'location'];
if (!empty($filters['job_type']))         $activeFilters['job_type']         = ['label'=>$jobTypes[$filters['job_type']]??'',  'key'=>'job_type'];
if (!empty($filters['experience_level'])) $activeFilters['experience_level'] = ['label'=>$expLevels[$filters['experience_level']]??'', 'key'=>'experience_level'];
if (!empty($filters['industry']))         $activeFilters['industry']         = ['label'=>e($filters['industry']),              'key'=>'industry'];
if ($filters['is_remote'] !== '')         $activeFilters['is_remote']        = ['label'=>$filters['is_remote']==='1'?'Remote Only':'On-site', 'key'=>'is_remote'];
if (!empty($filters['salary_min']))       $activeFilters['salary_min']       = ['label'=>'Min: GHS '.number_format((float)$filters['salary_min']), 'key'=>'salary_min'];
?>

<style>
/* ── Job listing page overrides ─────────────────────────── */
.jl-hero{background:linear-gradient(135deg,#1A56DB 0%,#1347c8 55%,#1e1b4b 100%);padding:36px 0 32px;}
.jl-search-bar{background:#fff;border-radius:14px;padding:6px;
               box-shadow:0 8px 32px rgba(0,0,0,.25);max-width:100%;}
.jl-search-bar input{border:none;outline:none;font-size:14.5px;background:transparent;padding:8px 10px;}
.jl-search-bar .divider{width:1px;background:#e5e7eb;margin:6px 0;}
.jl-search-bar .btn{border-radius:10px;height:44px;font-size:14px;min-width:100px;}
.suggest-box{position:absolute;top:100%;left:0;right:0;z-index:20;margin-top:6px;
           background:#fff;border:1px solid rgba(15,23,42,.12);border-radius:12px;
           box-shadow:0 16px 44px rgba(15,23,42,.08);overflow:hidden;display:none;}
.suggest-item{padding:12px 14px;cursor:pointer;font-size:14px;color:#111827;
            display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(15,23,42,.06);}
.suggest-item:last-child{border-bottom:none;}
.suggest-item:hover,.suggest-item.active{background:#eff6ff;}
.suggest-icon{font-size:14px;}

/* Filter panel */
.filter-panel{position:sticky;top:80px;}
.filter-section{padding:14px 0;border-bottom:1px solid var(--border);}
.filter-section:last-child{border-bottom:none;}
.filter-label{font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;
              color:#9ca3af;margin-bottom:10px;display:block;}
.filter-check .form-check{margin-bottom:6px;}
.filter-check .form-check-label{font-size:13.5px;color:#374151;cursor:pointer;}
.filter-check .form-check-input:checked{background-color:var(--primary);border-color:var(--primary);}
.filter-check .form-check-input{cursor:pointer;}

/* Active filter chips */
.filter-chip{display:inline-flex;align-items:center;gap:6px;background:var(--primary-light);
             color:var(--primary);font-size:12px;font-weight:600;padding:4px 10px;
             border-radius:50px;border:1px solid rgba(26,86,219,.2);}
.filter-chip .remove{background:none;border:none;color:var(--primary);padding:0;
                     line-height:1;cursor:pointer;font-size:14px;opacity:.7;}
.filter-chip .remove:hover{opacity:1;}

/* Job cards */
.job-card-v2{border:1.5px solid var(--border);border-radius:14px;background:#fff;
             transition:all .2s;margin-bottom:12px;overflow:hidden;}
.job-card-v2:hover{border-color:var(--primary);box-shadow:0 6px 24px rgba(26,86,219,.1);
                   transform:translateY(-1px);}
.job-card-v2.featured{border-color:#f59e0b;border-left:4px solid #f59e0b;}
.job-logo{width:52px;height:52px;border-radius:12px;object-fit:contain;
          border:1px solid var(--border);padding:5px;background:#fff;flex-shrink:0;}
.job-logo-placeholder{width:52px;height:52px;border-radius:12px;background:var(--primary-light);
                       color:var(--primary);display:flex;align-items:center;
                       justify-content:center;font-weight:800;font-size:20px;flex-shrink:0;}
.job-tag{display:inline-flex;align-items:center;gap:4px;background:#F9FAFB;
         border:1px solid var(--border);color:#374151;font-size:12px;font-weight:500;
         padding:3px 9px;border-radius:50px;}
.job-tag.remote{background:#F0FDF4;border-color:#bbf7d0;color:#057A55;}
.job-tag.salary{background:#F0FDF4;border-color:#bbf7d0;color:#057A55;font-weight:600;}
.job-tag.featured{background:#FEF3C7;border-color:#fde68a;color:#92400e;}
.save-btn{width:34px;height:34px;border-radius:8px;border:1.5px solid var(--border);
          background:#fff;display:flex;align-items:center;justify-content:center;
          cursor:pointer;transition:.2s;flex-shrink:0;}
.save-btn:hover{border-color:var(--primary);background:var(--primary-light);}
.save-btn.saved{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}

/* Sort + toolbar */
.results-toolbar{background:#fff;border:1.5px solid var(--border);border-radius:12px;
                 padding:10px 16px;margin-bottom:16px;}
.sort-select{border:none;outline:none;font-size:13.5px;font-weight:600;
             color:var(--primary);background:transparent;cursor:pointer;padding:0;}

/* Pagination */
.jl-pagination .page-link{border-radius:10px!important;margin:0 3px;border:1.5px solid var(--border);
                            color:#374151;font-weight:600;font-size:13.5px;padding:7px 13px;
                            min-width:38px;text-align:center;}
.jl-pagination .page-item.active .page-link{background:var(--primary);border-color:var(--primary);color:#fff;}
.jl-pagination .page-link:hover{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}

/* Empty state */
.empty-state{border:2px dashed var(--border);border-radius:16px;padding:60px 24px;text-align:center;}

/* Mobile filter drawer */
@media(max-width:991px){
  .filter-panel{position:fixed;top:0;left:0;width:300px;height:100vh;background:#fff;
                z-index:1050;overflow-y:auto;transform:translateX(-100%);
                transition:transform .3s;box-shadow:4px 0 24px rgba(0,0,0,.15);padding:20px;}
  .filter-panel.open{transform:translateX(0);}
  .filter-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1040;}
  .filter-overlay.open{display:block;}
}
</style>

<!-- HERO SEARCH BAR -->
<div class="jl-hero">
  <div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="text-white fw-800 mb-0" style="font-size:1.5rem;">
        <?php if(!empty($filters['q'])): ?>
          Results for "<span style="color:#93c5fd;"><?= e($filters['q']) ?></span>"
        <?php else: ?>
          Browse All Jobs
        <?php endif; ?>
      </h1>
      <span class="text-white opacity-75 small"><?= number_format($paging['total']) ?> jobs found</span>
    </div>

    <form id="jobSearchForm" action="<?= url('/jobs') ?>" method="GET">
      <!-- Preserve non-search filters across searches -->
      <?php foreach(['job_type','experience_level','is_remote','industry','salary_min','sort'] as $k): ?>
        <?php if(!empty($filters[$k])): ?><input type="hidden" name="<?= $k ?>" value="<?= e($filters[$k]) ?>"><?php endif; ?>
      <?php endforeach; ?>

<div class="jl-search-bar d-flex align-items-center gap-1" style="position:relative;">
          <i class="bi bi-search text-muted ms-2 flex-shrink-0"></i>
          <div style="flex:1;position:relative;">
            <input type="text" name="q" class="flex-grow-1"
                   placeholder="Job title, keyword or company..."
                   value="<?= e($filters['q']) ?>" id="jobSearchInput"
                   data-suggest-endpoint="<?= url('/jobs/suggest') ?>"
                   autocomplete="off">
            <div id="suggestBox" class="suggest-box"></div>
          </div>
        <div class="divider d-none d-md-block"></div>
        <i class="bi bi-geo-alt text-muted d-none d-md-block flex-shrink-0"></i>
        <input type="text" name="location" class="flex-grow-1 d-none d-md-block"
               placeholder="City or country..."
               value="<?= e($filters['location']) ?>">
        <button type="submit" class="btn btn-primary fw-700 me-1">
          Search
          <span id="searchSpinner" class="spinner-border spinner-border-sm ms-1 d-none"></span>
        </button>
      </div>

      <!-- Mobile location row -->
      <div class="d-flex d-md-none mt-2 gap-2">
        <div class="input-group">
          <span class="input-group-text bg-white border-0"><i class="bi bi-geo-alt text-muted"></i></span>
          <input type="text" name="location" class="form-control border-0" placeholder="City or country..."
                 value="<?= e($filters['location']) ?>" style="background:#fff;border-radius:0 10px 10px 0;">
        </div>
      </div>
    </form>
  </div>
</div>

<!-- MOBILE FILTER OVERLAY + DRAWER -->
<div class="filter-overlay" id="filterOverlay" onclick="closeFilters()"></div>

<div class="container py-4">
<div class="row g-4">

<!-- ── FILTER SIDEBAR ────────────────────────────────────────────── -->
<div class="col-lg-3">
  <div class="filter-panel" id="filterPanel">

    <!-- Mobile header -->
    <div class="d-flex align-items-center justify-content-between mb-3 d-lg-none">
      <h6 class="fw-800 mb-0">Filters</h6>
      <button class="btn btn-sm btn-light" onclick="closeFilters()">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-700 mb-0">Filters</h6>
        <?php if(!empty($activeFilters)): ?>
        <a href="<?= url('/jobs') ?>" class="text-muted small text-decoration-none fw-600">
          Clear all
        </a>
        <?php endif; ?>
      </div>
      <div class="card-body p-3">

        <!-- Job Type -->
        <div class="filter-section">
          <span class="filter-label">Job Type</span>
          <div class="filter-check">
            <?php foreach($jobTypes as $v=>$l): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="job_type" form="jobSearchForm"
                     value="<?= $v ?>" id="jt_<?= $v ?>"
                     <?= $filters['job_type']===$v?'checked':'' ?>
                     onchange="submitFilters()">
              <label class="form-check-label" for="jt_<?= $v ?>"><?= $l ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Experience Level -->
        <div class="filter-section">
          <span class="filter-label">Experience Level</span>
          <div class="filter-check">
            <?php foreach($expLevels as $v=>$l): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="experience_level" form="jobSearchForm"
                     value="<?= $v ?>" id="exp_<?= $v ?>"
                     <?= $filters['experience_level']===$v?'checked':'' ?>
                     onchange="submitFilters()">
              <label class="form-check-label" for="exp_<?= $v ?>"><?= $l ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Work Mode -->
        <div class="filter-section">
          <span class="filter-label">Work Mode</span>
          <div class="filter-check">
            <?php foreach([''=> 'Any Mode','1'=>'Remote Only','0'=>'On-site Only'] as $v=>$l): ?>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="is_remote" form="jobSearchForm"
                     value="<?= $v ?>" id="rm_<?= $v ?>"
                     <?= $filters['is_remote']===$v?'checked':'' ?>
                     onchange="submitFilters()">
              <label class="form-check-label" for="rm_<?= $v ?>"><?= $l ?></label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Industry -->
        <div class="filter-section">
          <span class="filter-label">Industry</span>
          <select class="form-select form-select-sm" name="industry" form="jobSearchForm"
                  onchange="submitFilters()" style="border-radius:8px;">
            <option value="">All Industries</option>
            <?php foreach($industries as $ind): ?>
            <option value="<?= $ind ?>" <?= $filters['industry']===$ind?'selected':'' ?>><?= $ind ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Minimum Salary -->
        <div class="filter-section">
          <span class="filter-label">Minimum Salary (GHS)</span>
          <select class="form-select form-select-sm" name="salary_min" form="jobSearchForm"
                  onchange="submitFilters()" style="border-radius:8px;">
            <?php
            $salaryOpts = ['' => 'Any Salary', '1000'=>'GHS 1,000+', '3000'=>'GHS 3,000+',
                           '5000'=>'GHS 5,000+', '8000'=>'GHS 8,000+', '12000'=>'GHS 12,000+',
                           '20000'=>'GHS 20,000+'];
            foreach($salaryOpts as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($filters['salary_min']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>

      </div>
    </div>

    <!-- Set Alert shortcut (seekers only) -->
    <?php if(is_logged_in() && Session::isSeeker()): ?>
    <a href="<?= url('/seeker/alerts') ?>"
       class="btn btn-outline-primary w-100 mt-3 fw-600" style="border-radius:12px;">
      <i class="bi bi-bell me-2"></i>Create Job Alert
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ── RESULTS ────────────────────────────────────────────────────── -->
<div class="col-lg-9">

  <!-- Active filter chips -->
  <?php if(!empty($activeFilters)): ?>
  <div class="d-flex flex-wrap gap-2 mb-3" id="filterChips">
    <?php foreach($activeFilters as $chip): ?>
    <?php
      $removeParams = array_merge($filters, [$chip['key'] => '']);
      $removeUrl    = url('/jobs?'.http_build_query(array_filter($removeParams, fn($v)=>$v!=='')));
    ?>
    <a href="<?= $removeUrl ?>" class="filter-chip text-decoration-none">
      <?= $chip['label'] ?>
      <span class="remove">×</span>
    </a>
    <?php endforeach; ?>
    <a href="<?= url('/jobs') ?>" class="filter-chip text-decoration-none"
       style="background:#fee2e2;color:#dc2626;border-color:rgba(220,38,38,.2);">
      Clear all ×
    </a>
  </div>
  <?php endif; ?>

  <!-- Toolbar: count + sort + mobile filter button -->
  <div class="results-toolbar d-flex align-items-center justify-content-between gap-2 flex-wrap">
    <div class="d-flex align-items-center gap-3">
      <!-- Mobile filter toggle -->
      <button class="btn btn-sm btn-outline-primary d-lg-none fw-600" onclick="openFilters()">
        <i class="bi bi-sliders me-1"></i>Filters
        <?php if(!empty($activeFilters)): ?>
        <span class="badge bg-primary ms-1" style="font-size:10px;"><?= count($activeFilters) ?></span>
        <?php endif; ?>
      </button>
      <span class="text-muted small">
        <?php if($paging['total']>0): ?>
          <strong class="text-dark"><?= number_format($paging['total']) ?></strong> jobs found
          <?php if(!empty($filters['q'])): ?>
            for <strong>"<?= e($filters['q']) ?>"</strong>
          <?php endif; ?>
        <?php else: ?>
          No results
        <?php endif; ?>
      </span>
    </div>
    <div class="d-flex align-items-center gap-1 text-muted small">
      <i class="bi bi-sort-down-alt"></i>
      <select class="sort-select" onchange="sortResults(this.value)">
        <option value="newest"      <?= ($filters['sort']??'')==='newest'     ?'selected':'' ?>>Newest First</option>
        <option value="salary_high" <?= ($filters['sort']??'')==='salary_high'?'selected':'' ?>>Highest Salary</option>
        <option value="salary_low"  <?= ($filters['sort']??'')==='salary_low' ?'selected':'' ?>>Lowest Salary</option>
      </select>
    </div>
  </div>

  <!-- Job Cards -->
  <div id="jobResults">
    <?php if(empty($jobs)): ?>
    <div class="empty-state">
      <i class="bi bi-search text-muted d-block mb-3" style="font-size:3rem;opacity:.4;"></i>
      <h5 class="fw-800 mb-2">No jobs found</h5>
      <p class="text-muted mb-4">
        <?php if(!empty($activeFilters)): ?>
          Try removing some filters or changing your search terms.
        <?php else: ?>
          No jobs have been posted yet — check back soon!
        <?php endif; ?>
      </p>
      <?php if(!empty($activeFilters)): ?>
      <a href="<?= url('/jobs') ?>" class="btn btn-primary px-5 fw-600">Clear All Filters</a>
      <?php endif; ?>
    </div>

    <?php else: ?>
    <?php foreach($jobs as $job): ?>
    <div class="job-card-v2 <?= $job['is_featured']?'featured':'' ?>">
      <div class="p-4">
        <div class="d-flex gap-3 align-items-start">

          <!-- Logo -->
          <?php if(!empty($job['company_logo'])): ?>
          <img src="<?= url('/file?path='.urlencode($job['company_logo'])) ?>"
               class="job-logo" alt="<?= e($job['company_name']) ?>">
          <?php else: ?>
          <div class="job-logo-placeholder">
            <?= strtoupper(substr($job['company_name'],0,1)) ?>
          </div>
          <?php endif; ?>

          <!-- Content -->
          <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
              <div class="min-w-0">
                <h5 class="fw-700 mb-0 fs-6 text-truncate">
                  <a href="<?= url('/jobs/'.$job['slug']) ?>"
                     class="text-dark text-decoration-none stretched-link">
                    <?= e($job['title']) ?>
                  </a>
                </h5>
                <div class="text-muted small fw-500"><?= e($job['company_name']) ?></div>
              </div>
              <!-- Save button (outside stretched-link) -->
              <div style="position:relative;z-index:2;flex-shrink:0;">
                <?php if(is_logged_in() && Session::isSeeker()): ?>
                <form method="POST"
                      action="<?= url(in_array($job['id'],$savedIds)?'/seeker/unsave-job/'.$job['id']:'/seeker/save-job/'.$job['id']) ?>"
                      data-no-loading>
                  <?= csrf_field() ?>
                  <button type="submit"
                          class="save-btn <?= in_array($job['id'],$savedIds)?'saved':'' ?>"
                          title="<?= in_array($job['id'],$savedIds)?'Remove from saved':'Save job' ?>">
                    <i class="bi bi-bookmark<?= in_array($job['id'],$savedIds)?'-fill':'' ?>"
                       style="font-size:15px;"></i>
                  </button>
                </form>
                <?php else: ?>
                <a href="<?= url('/login') ?>" class="save-btn" style="position:relative;z-index:2;"
                   title="Login to save">
                  <i class="bi bi-bookmark" style="font-size:15px;color:var(--muted);"></i>
                </a>
                <?php endif; ?>
              </div>
            </div>

            <!-- Tags row -->
            <div class="d-flex flex-wrap gap-2 mt-2">
              <span class="job-tag <?= $job['is_remote']?'remote':'' ?>">
                <i class="bi bi-geo-alt" style="font-size:11px;"></i>
                <?= $job['is_remote'] ? 'Remote' : e($job['location_city']??'N/A') ?>
              </span>
              <span class="job-tag"><?= $jobTypes[$job['job_type']]??status_label($job['job_type']) ?></span>
              <span class="job-tag"><?= $expLevels[$job['experience_level']]??status_label($job['experience_level']) ?></span>
              <?php if(!$job['salary_is_hidden'] && $job['salary_min']): ?>
              <span class="job-tag salary">
                <i class="bi bi-cash" style="font-size:11px;"></i>
                <?= salary_range($job['salary_min'],$job['salary_max'],$job['salary_currency']) ?>
              </span>
              <?php endif; ?>
              <?php if($job['is_featured']): ?>
              <span class="job-tag featured">⭐ Featured</span>
              <?php endif; ?>
              <?php if(!empty($job['industry'])): ?>
              <span class="job-tag" style="background:#EDE9FE;border-color:#c4b5fd;color:#7C3AED;">
                <?= e($job['industry']) ?>
              </span>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Footer row -->
        <div class="d-flex align-items-center justify-content-between mt-3 pt-3 border-top">
          <span class="text-muted" style="font-size:12px;">
            <i class="bi bi-clock me-1"></i><?= time_ago($job['created_at']) ?>
          </span>
          <?php if($job['expires_at']): ?>
          <span class="text-muted" style="font-size:12px;">
            <i class="bi bi-calendar-x me-1"></i>
            Closes <?= date('d M Y', strtotime($job['expires_at'])) ?>
          </span>
          <?php endif; ?>
          <a href="<?= url('/jobs/'.$job['slug']) ?>"
             class="btn btn-primary btn-sm px-4 fw-600"
             style="position:relative;z-index:2;border-radius:8px;">
            View Job
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if($paging['pages'] > 1): ?>
    <nav class="mt-4" aria-label="Job results pages">
      <ul class="pagination justify-content-center jl-pagination flex-wrap">
        <!-- Prev -->
        <li class="page-item <?= $paging['current_page']<=1?'disabled':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($filters,['page'=>$paging['current_page']-1])) ?>">
            <i class="bi bi-chevron-left"></i>
          </a>
        </li>

        <!-- First page + ellipsis -->
        <?php if($paging['current_page'] > 3): ?>
        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters,['page'=>1])) ?>">1</a></li>
        <?php if($paging['current_page'] > 4): ?>
        <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif; ?>
        <?php endif; ?>

        <!-- Page window -->
        <?php for($i=max(1,$paging['current_page']-2); $i<=min($paging['pages'],$paging['current_page']+2); $i++): ?>
        <li class="page-item <?= $i===$paging['current_page']?'active':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($filters,['page'=>$i])) ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>

        <!-- Last page + ellipsis -->
        <?php if($paging['current_page'] < $paging['pages']-2): ?>
        <?php if($paging['current_page'] < $paging['pages']-3): ?>
        <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif; ?>
        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($filters,['page'=>$paging['pages']])) ?>"><?= $paging['pages'] ?></a></li>
        <?php endif; ?>

        <!-- Next -->
        <li class="page-item <?= $paging['current_page']>=$paging['pages']?'disabled':'' ?>">
          <a class="page-link" href="?<?= http_build_query(array_merge($filters,['page'=>$paging['current_page']+1])) ?>">
            <i class="bi bi-chevron-right"></i>
          </a>
        </li>
      </ul>
      <p class="text-center text-muted small mt-2">
        Page <?= $paging['current_page'] ?> of <?= $paging['pages'] ?>
        &nbsp;·&nbsp; <?= number_format($paging['total']) ?> total jobs
      </p>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
  </div><!-- #jobResults -->

</div><!-- col results -->
</div><!-- row -->
</div><!-- container -->

<script>
// ── Filter helpers ────────────────────────────────────────────────────────────
function submitFilters() {
    document.getElementById('searchSpinner').classList.remove('d-none');
    document.getElementById('jobSearchForm').submit();
}

function sortResults(val) {
    const params = new URLSearchParams(window.location.search);
    params.set('sort', val);
    params.delete('page');
    window.location.href = '<?= url('/jobs') ?>?' + params.toString();
}

// ── Mobile filter drawer ──────────────────────────────────────────────────────
function openFilters() {
    document.getElementById('filterPanel').classList.add('open');
    document.getElementById('filterOverlay').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeFilters() {
    document.getElementById('filterPanel').classList.remove('open');
    document.getElementById('filterOverlay').classList.remove('open');
    document.body.style.overflow = '';
}

// ── Show spinner on search submit ─────────────────────────────────────────────
document.getElementById('jobSearchForm').addEventListener('submit', function() {
    document.getElementById('searchSpinner').classList.remove('d-none');
});
</script>

<script src="<?= asset('js/job-search.js') ?>"></script>
<script src="<?= asset('js/autocomplete.js') ?>"></script>
