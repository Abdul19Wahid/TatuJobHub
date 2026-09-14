<?php $sizes = ['1-10','11-50','51-200','201-500','501-1000','1000+']; ?>
<style>
.co-hero{background:linear-gradient(135deg,#1e1b4b 0%,#1A56DB 100%);
         padding:40px 0 60px;position:relative;overflow:hidden;}
.co-hero::before{content:'';position:absolute;right:-80px;bottom:-80px;width:320px;height:320px;
                 border-radius:50%;background:rgba(255,255,255,.05);}
.co-search{background:#fff;border-radius:14px;padding:6px;
           box-shadow:0 8px 32px rgba(0,0,0,.25);max-width:560px;}
.co-search input{border:none;outline:none;font-size:14.5px;background:transparent;padding:8px 10px;}
.co-search .btn{border-radius:10px;height:44px;}
.filter-panel{position:sticky;top:80px;}
.filter-label{font-size:10px;font-weight:700;letter-spacing:1.2px;
              text-transform:uppercase;color:#9ca3af;margin-bottom:10px;display:block;}
.co-card{border:1.5px solid var(--border);border-radius:16px;background:#fff;
         display:block;text-decoration:none;color:inherit;
         transition:all .2s;height:100%;padding:22px;}
.co-card:hover{border-color:var(--primary);box-shadow:0 8px 28px rgba(26,86,219,.12);
               transform:translateY(-2px);color:inherit;}
.co-logo{width:60px;height:60px;border-radius:14px;object-fit:contain;
         border:1.5px solid var(--border);padding:6px;background:#fff;flex-shrink:0;}
.co-logo-placeholder{width:60px;height:60px;border-radius:14px;background:var(--primary-light);
                     color:var(--primary);display:flex;align-items:center;justify-content:center;
                     font-weight:800;font-size:22px;flex-shrink:0;}
.chip{display:inline-flex;align-items:center;gap:4px;background:#F9FAFB;
      border:1px solid var(--border);color:#6b7280;font-size:12px;
      font-weight:500;padding:3px 10px;border-radius:50px;}
.chip.jobs{background:#F0FDF4;border-color:#bbf7d0;color:#057A55;font-weight:600;}
.chip.featured{background:#FEF3C7;border-color:#fde68a;color:#92400e;}
.results-toolbar{background:#fff;border:1.5px solid var(--border);
                 border-radius:12px;padding:10px 16px;margin-bottom:16px;}
.co-empty{border:2px dashed var(--border);border-radius:16px;padding:60px 24px;text-align:center;}
@media(max-width:991px){
  .filter-panel{position:fixed;top:0;left:0;width:290px;height:100vh;background:#fff;
                z-index:1050;overflow-y:auto;transform:translateX(-100%);
                transition:transform .3s;box-shadow:4px 0 24px rgba(0,0,0,.15);padding:20px;}
  .filter-panel.open{transform:translateX(0);}
  .filter-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:1040;}
  .filter-overlay.open{display:block;}
}
</style>

<!-- Hero -->
<div class="co-hero">
  <div class="container" style="position:relative;z-index:1;">
    <div class="text-center text-white mb-4">
      <p class="small fw-600 mb-1 opacity-75 text-uppercase" style="letter-spacing:1.5px;">Explore Employers</p>
      <h1 class="fw-800 mb-2" style="font-size:clamp(1.5rem,4vw,2.2rem);letter-spacing:-.5px;">
        Browse Companies
      </h1>
      <p class="opacity-75 mb-4">
        Discover <?= number_format($paging['total']) ?> companies actively hiring right now.
      </p>
    </div>
    <form action="<?= url('/companies') ?>" method="GET" class="d-flex justify-content-center">
      <?php if(!empty($filters['industry'])): ?>
      <input type="hidden" name="industry" value="<?= e($filters['industry']) ?>">
      <?php endif; ?>
      <div class="co-search d-flex align-items-center gap-1" style="width:100%;max-width:560px;">
        <i class="bi bi-search text-muted ms-2 flex-shrink-0"></i>
        <input type="text" name="q" class="flex-grow-1"
               placeholder="Company name or keyword..."
               value="<?= e($filters['search']) ?>">
        <button type="submit" class="btn btn-primary fw-700 me-1 px-4">Search</button>
      </div>
    </form>
  </div>
</div>

<!-- Mobile filter overlay -->
<div class="filter-overlay" id="filterOverlay" onclick="closeFilters()"></div>

<div class="container py-4">
<div class="row g-4">

<!-- Sidebar -->
<div class="col-lg-3">
  <div class="filter-panel" id="filterPanel">
    <!-- Mobile header -->
    <div class="d-flex align-items-center justify-content-between mb-3 d-lg-none">
      <h6 class="fw-800 mb-0">Filters</h6>
      <button class="btn btn-sm btn-light" onclick="closeFilters()"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-700 mb-0">Filters</h6>
        <a href="<?= url('/companies') ?>" class="small text-muted text-decoration-none fw-600">Clear</a>
      </div>
      <div class="card-body p-3">
        <form action="<?= url('/companies') ?>" method="GET" id="filterForm">
          <input type="hidden" name="q" value="<?= e($filters['search']) ?>">

          <div class="mb-4">
            <span class="filter-label">Industry</span>
            <?php foreach($industries as $ind): ?>
            <div class="form-check mb-1">
              <input class="form-check-input" type="radio" name="industry"
                     id="ind_<?= slug($ind['industry']) ?>"
                     value="<?= e($ind['industry']) ?>"
                     <?= $filters['industry']===$ind['industry']?'checked':'' ?>
                     onchange="this.form.submit()">
              <label class="form-check-label small" for="ind_<?= slug($ind['industry']) ?>">
                <?= e($ind['industry']) ?>
                <span class="text-muted">(<?= $ind['count'] ?>)</span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>

          <div>
            <span class="filter-label">Company Size</span>
            <?php foreach($sizes as $s): ?>
            <div class="form-check mb-1">
              <input class="form-check-input" type="radio" name="size"
                     id="sz_<?= str_replace(['+','-'],'_',$s) ?>"
                     value="<?= $s ?>"
                     <?= $filters['size']===$s?'checked':'' ?>
                     onchange="this.form.submit()">
              <label class="form-check-label small" for="sz_<?= str_replace(['+','-'],'_',$s) ?>">
                <?= $s ?> employees
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Results -->
<div class="col-lg-9">

  <!-- Toolbar -->
  <div class="results-toolbar d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-outline-primary d-lg-none fw-600" onclick="openFilters()">
        <i class="bi bi-sliders me-1"></i>Filters
      </button>
      <span class="text-muted small">
        <strong class="text-dark"><?= number_format($paging['total']) ?></strong> companies
        <?php if($filters['search']): ?>
          for <strong>"<?= e($filters['search']) ?>"</strong>
        <?php endif; ?>
      </span>
    </div>
    <?php if(!empty($filters['search'])||!empty($filters['industry'])||!empty($filters['size'])): ?>
    <a href="<?= url('/companies') ?>" class="btn btn-sm btn-light fw-600">
      <i class="bi bi-x me-1"></i>Clear filters
    </a>
    <?php endif; ?>
  </div>

  <?php if(empty($companies)): ?>
  <div class="co-empty">
    <i class="bi bi-building text-muted d-block mb-3" style="font-size:3rem;opacity:.4;"></i>
    <h5 class="fw-800 mb-2">No companies found</h5>
    <p class="text-muted mb-4">Try a different search term or clear the filters.</p>
    <a href="<?= url('/companies') ?>" class="btn btn-primary px-5 fw-600">Clear Filters</a>
  </div>
  <?php else: ?>

  <div class="row g-3">
    <?php foreach($companies as $co): ?>
    <div class="col-12 col-md-6">
      <a href="<?= url('/companies/'.($co['slug']??slug($co['company_name']).'-'.$co['id'])) ?>"
         class="co-card">
        <div class="d-flex gap-3 mb-3">
          <?php if($co['logo_path']): ?>
          <img src="<?= url('/file?path='.urlencode($co['logo_path'])) ?>"
               class="co-logo" alt="<?= e($co['company_name']) ?>">
          <?php else: ?>
          <div class="co-logo-placeholder">
            <?= strtoupper(substr($co['company_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div class="d-flex align-items-start justify-content-between gap-1">
              <h6 class="fw-800 mb-0 text-dark"><?= e($co['company_name']) ?></h6>
              <?php if($co['is_featured']): ?>
              <span class="chip featured flex-shrink-0">⭐ Featured</span>
              <?php endif; ?>
            </div>
            <div class="text-muted small mt-1">
              <?= e($co['industry']??'') ?>
              <?php if($co['location_city']): ?>
              · <i class="bi bi-geo-alt"></i> <?= e($co['location_city']) ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <?php if($co['description']): ?>
        <p class="text-muted small mb-3" style="line-height:1.6;display:-webkit-box;
           -webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
          <?= e($co['description']) ?>
        </p>
        <?php endif; ?>

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
          <div class="d-flex flex-wrap gap-2">
            <?php if($co['company_size']): ?>
            <span class="chip">
              <i class="bi bi-people" style="font-size:11px;"></i><?= e($co['company_size']) ?> employees
            </span>
            <?php endif; ?>
            <span class="chip <?= $co['active_job_count']>0?'jobs':'' ?>">
              <i class="bi bi-briefcase" style="font-size:11px;"></i>
              <?= $co['active_job_count']>0 ? $co['active_job_count'].' open '.($co['active_job_count']==1?'job':'jobs') : 'No openings' ?>
            </span>
          </div>
          <span class="text-primary small fw-700">
            View <i class="bi bi-arrow-right ms-1"></i>
          </span>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if($paging['pages']>1): ?>
  <nav class="mt-4">
    <ul class="pagination justify-content-center gap-1">
      <?php if($paging['current_page']>1): ?>
      <li class="page-item">
        <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($filters,['page'=>$paging['current_page']-1])) ?>">
          <i class="bi bi-chevron-left"></i>
        </a>
      </li>
      <?php endif; ?>
      <?php for($i=max(1,$paging['current_page']-2);$i<=min($paging['pages'],$paging['current_page']+2);$i++): ?>
      <li class="page-item <?=$i===$paging['current_page']?'active':''?>">
        <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($filters,['page'=>$i])) ?>"><?=$i?></a>
      </li>
      <?php endfor; ?>
      <?php if($paging['current_page']<$paging['pages']): ?>
      <li class="page-item">
        <a class="page-link rounded-3" href="?<?= http_build_query(array_merge($filters,['page'=>$paging['current_page']+1])) ?>">
          <i class="bi bi-chevron-right"></i>
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </nav>
  <?php endif; ?>
  <?php endif; ?>
</div>
</div>
</div>

<script>
function openFilters(){
  document.getElementById('filterPanel').classList.add('open');
  document.getElementById('filterOverlay').classList.add('open');
  document.body.style.overflow='hidden';
}
function closeFilters(){
  document.getElementById('filterPanel').classList.remove('open');
  document.getElementById('filterOverlay').classList.remove('open');
  document.body.style.overflow='';
}
</script>
