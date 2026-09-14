<style>
/* ── Hero ── */
.hero-v2{min-height:92vh;display:flex;align-items:center;position:relative;overflow:hidden;
         background:linear-gradient(135deg,#0F172A 0%,#1e1b4b 40%,#1A56DB 100%);}
.hero-v2::before{content:'';position:absolute;inset:0;
  background:radial-gradient(ellipse 80% 60% at 60% 40%,rgba(79,142,247,.18) 0%,transparent 70%),
             radial-gradient(ellipse 50% 50% at 10% 80%,rgba(124,58,237,.15) 0%,transparent 60%);}
.floating-card{position:absolute;background:rgba(255,255,255,.08);backdrop-filter:blur(10px);
               border:1px solid rgba(255,255,255,.15);border-radius:14px;padding:14px 18px;
               display:flex;align-items:center;gap:10px;color:#fff;font-size:13px;font-weight:600;
               animation:floatCard 6s ease-in-out infinite;}
.floating-card:nth-child(2){animation-delay:2s;} .floating-card:nth-child(3){animation-delay:4s;}
@keyframes floatCard{0%,100%{transform:translateY(0);}50%{transform:translateY(-10px);}}
.hero-search{background:rgba(255,255,255,.08);backdrop-filter:blur(20px);
             border:1.5px solid rgba(255,255,255,.2);border-radius:16px;padding:8px;
             max-width:680px;margin:0 auto;}
.hero-search input{background:transparent;border:none;outline:none;color:#fff;font-size:15px;
                   padding:10px 12px;width:100%;}
.hero-search input::placeholder{color:rgba(255,255,255,.5);}
.hero-search .divider{width:1px;background:rgba(255,255,255,.2);height:30px;flex-shrink:0;}
.hero-stat{text-align:center;}
.hero-stat .n{font-size:1.6rem;font-weight:800;color:#fff;line-height:1;}
.hero-stat .l{font-size:11px;color:rgba(255,255,255,.6);margin-top:2px;}
.trust-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.1);
             border:1px solid rgba(255,255,255,.2);border-radius:50px;padding:6px 14px;
             color:rgba(255,255,255,.85);font-size:12px;font-weight:600;}
/* ── Sections ── */
.sec-tag{font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;
         color:var(--primary);margin-bottom:6px;}
.sec-h{font-size:clamp(1.4rem,3vw,2rem);font-weight:800;letter-spacing:-.5px;}
/* ── Category cards ── */
.cat-v2{border:1.5px solid var(--border);border-radius:16px;padding:20px;text-align:center;
        background:#fff;text-decoration:none;color:inherit;display:flex;flex-direction:column;
        align-items:center;gap:8px;transition:.2s;}
.cat-v2:hover{border-color:var(--primary);background:var(--primary-light);color:var(--primary);transform:translateY(-3px);box-shadow:0 8px 24px rgba(26,86,219,.12);}
.cat-v2 .cat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;}
/* ── Job cards v3 ── */
.job-v3{border:1.5px solid var(--border);border-radius:16px;background:#fff;
        text-decoration:none;color:inherit;display:block;padding:22px;transition:.2s;}
.job-v3:hover{border-color:var(--primary);box-shadow:0 8px 28px rgba(26,86,219,.1);transform:translateY(-2px);color:inherit;}
.job-v3.featured{border-color:#F59E0B;border-top:3px solid #F59E0B;}
.j-tag{display:inline-flex;align-items:center;gap:3px;font-size:11.5px;font-weight:600;
       padding:3px 9px;border-radius:50px;border:1px solid;}
.j-tag.type{background:#EBF5FF;color:#1A56DB;border-color:#bfdbfe;}
.j-tag.remote{background:#F0FDF4;color:#057A55;border-color:#bbf7d0;}
.j-tag.salary{background:#F0FDF4;color:#057A55;border-color:#bbf7d0;}
/* ── Testimonial ── */
.testi-card{background:#fff;border:1.5px solid var(--border);border-radius:18px;padding:24px;}
.testi-card .stars{color:#F59E0B;font-size:13px;margin-bottom:10px;}
/* ── How it works ── */
.step-card{text-align:center;padding:20px;}
.step-num{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;margin:0 auto 14px;}
/* ── Dark mode overrides ── */
[data-theme="dark"] .cat-v2,[data-theme="dark"] .job-v3,[data-theme="dark"] .testi-card{background:#1E293B!important;border-color:#374151!important;}
[data-theme="dark"] .hero-search input{color:#fff;}
[data-theme="dark"] .sec-h{color:#F9FAFB;}
/* ── Counter animation ── */
.count-up{display:inline-block;}
</style>

<!-- HERO -->
<section class="hero-v2">
  <!-- Floating ambient cards -->
  <div class="floating-card d-none d-xl-flex" style="top:18%;left:4%;">
    <div style="width:36px;height:36px;border-radius:10px;background:rgba(79,142,247,.3);display:flex;align-items:center;justify-content:center;">💼</div>
    <div><div>PHP Developer</div><div style="opacity:.6;font-size:11px;font-weight:400;">TechCorp · Accra</div></div>
  </div>
  <div class="floating-card d-none d-xl-flex" style="bottom:22%;left:3%;">
    <div style="width:36px;height:36px;border-radius:10px;background:rgba(124,58,237,.3);display:flex;align-items:center;justify-content:center;">✅</div>
    <div><div>Offer Accepted!</div><div style="opacity:.6;font-size:11px;font-weight:400;">Kwame A. · Today</div></div>
  </div>
  <div class="floating-card d-none d-xl-flex" style="top:25%;right:3%;">
    <div style="width:36px;height:36px;border-radius:10px;background:rgba(5,122,85,.3);display:flex;align-items:center;justify-content:center;">🏢</div>
    <div><div>50+ Companies</div><div style="opacity:.6;font-size:11px;font-weight:400;">Actively Hiring</div></div>
  </div>

  <div class="container position-relative py-5" style="z-index:2;">
    <div class="row justify-content-center text-center">
      <div class="col-12 col-lg-9">

        <!-- Trust badges -->
        <div class="d-flex justify-content-center flex-wrap gap-2 mb-4">
          <span class="trust-badge"><i class="bi bi-shield-check-fill text-success"></i>Verified Employers</span>
          <span class="trust-badge"><i class="bi bi-lightning-fill text-warning"></i>Instant Job Alerts</span>
          <span class="trust-badge"><i class="bi bi-geo-alt-fill" style="color:#f87171;"></i>Ghana's #1 Portal</span>
        </div>

        <h1 class="text-white fw-800 mb-3"
            style="font-size:clamp(2rem,6vw,3.2rem);letter-spacing:-1.5px;line-height:1.1;">
          Find Your <span style="background:linear-gradient(90deg,#93c5fd,#c4b5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">Dream Job</span><br>
          in Ghana Today
        </h1>
        <p class="mb-5" style="color:rgba(255,255,255,.7);font-size:1.05rem;max-width:500px;margin:0 auto 2rem;">
          Connect with top employers, discover opportunities across every industry, and take the next step in your career.
        </p>

        <!-- Search bar -->
        <form action="<?= url('/jobs') ?>" method="GET" class="mb-5 px-2">
          <div class="hero-search d-flex align-items-center gap-1 flex-wrap flex-md-nowrap">
            <i class="bi bi-search ms-2 flex-shrink-0" style="color:rgba(255,255,255,.5);"></i>
            <input type="text" name="q" placeholder="Job title, skill or company..."
                   value="<?= e($_GET['q'] ?? '') ?>">
            <div class="divider d-none d-md-flex"></div>
            <i class="bi bi-geo-alt d-none d-md-flex flex-shrink-0" style="color:rgba(255,255,255,.5);"></i>
            <input type="text" name="location" placeholder="City or country..."
                   class="d-none d-md-block" value="<?= e($_GET['location'] ?? '') ?>">
            <button type="submit" class="btn btn-primary fw-700 px-5 me-1 flex-shrink-0"
                    style="height:46px;border-radius:10px;">
              Search
            </button>
          </div>
          <!-- Popular searches -->
          <div class="mt-3 d-flex flex-wrap justify-content-center gap-2">
            <span class="text-white opacity-50 small">Popular:</span>
            <?php foreach(['Software Engineer','Finance','Healthcare','Marketing','Remote'] as $tag): ?>
            <a href="<?= url('/jobs?q='.urlencode($tag)) ?>"
               class="trust-badge" style="font-size:11px;padding:4px 12px;">
              <?= $tag ?>
            </a>
            <?php endforeach; ?>
          </div>
        </form>

        <!-- Stats with counter animation -->
        <div class="d-flex flex-wrap justify-content-center gap-4 gap-md-5">
          <?php foreach([
            ['n'=>$stats['active_jobs'], 'l'=>'Active Jobs',   'suffix'=>'+'],
            ['n'=>$stats['companies'],   'l'=>'Companies',     'suffix'=>'+'],
            ['n'=>$stats['seekers'],     'l'=>'Job Seekers',   'suffix'=>'+'],
            ['n'=>$stats['placements'],  'l'=>'Hired This Month','suffix'=>''],
          ] as $s): ?>
          <div class="hero-stat">
            <div class="n">
              <span class="count-up" data-target="<?= $s['n'] ?>"><?= number_format($s['n']) ?></span><?= $s['suffix'] ?>
            </div>
            <div class="l"><?= $s['l'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </div>

  <!-- Scroll indicator -->
  <div style="position:absolute;bottom:24px;left:50%;transform:translateX(-50%);
              color:rgba(255,255,255,.4);animation:floatCard 2s ease-in-out infinite;">
    <i class="bi bi-chevron-down fs-4"></i>
  </div>
</section>

<!-- BROWSE BY CATEGORY -->
<section class="py-5 bg-white" id="categories">
<div class="container">
  <div class="text-center mb-4">
    <div class="sec-tag">Explore Opportunities</div>
    <h2 class="sec-h">Browse by Category</h2>
    <p class="text-muted small">Find roles that match your skills and passion</p>
  </div>
  <?php
  $catDefs = [
    ['industry'=>'Information Technology','icon'=>'💻','color'=>'#EBF5FF','ic'=>'#1A56DB'],
    ['industry'=>'Finance',               'icon'=>'📊','color'=>'#F0FDF4','ic'=>'#057A55'],
    ['industry'=>'Healthcare',            'icon'=>'🏥','color'=>'#FEF3C7','ic'=>'#D97706'],
    ['industry'=>'Education',             'icon'=>'📚','color'=>'#EDE9FE','ic'=>'#7C3AED'],
    ['industry'=>'Sales & Marketing',     'icon'=>'📣','color'=>'#FEE2E2','ic'=>'#DC2626'],
    ['industry'=>'Engineering',           'icon'=>'⚙️','color'=>'#E0F2FE','ic'=>'#0284C7'],
    ['industry'=>'Design',                'icon'=>'🎨','color'=>'#FCE7F3','ic'=>'#DB2777'],
    ['industry'=>'Other',                 'icon'=>'💼','color'=>'#F3F4F6','ic'=>'#374151'],
  ];
  $catCounts = [];
  foreach($categories??[] as $c) $catCounts[$c['industry']] = $c['job_count'];
  ?>
  <div class="row g-3">
    <?php foreach($catDefs as $cat): ?>
    <div class="col-6 col-sm-4 col-md-3 col-lg-3">
      <a href="<?= url('/jobs?industry='.urlencode($cat['industry'])) ?>" class="cat-v2 h-100">
        <div class="cat-icon" style="background:<?= $cat['color'] ?>;color:<?= $cat['ic'] ?>;">
          <?= $cat['icon'] ?>
        </div>
        <div class="fw-700 small"><?= $cat['industry'] ?></div>
        <div class="text-muted" style="font-size:11px;">
          <?= isset($catCounts[$cat['industry']]) ? number_format($catCounts[$cat['industry']]).' jobs' : 'Browse jobs' ?>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</section>

<!-- FEATURED JOBS -->
<section class="py-5" style="background:var(--bg);">
<div class="container">
  <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <div class="sec-tag">Latest Openings</div>
      <h2 class="sec-h mb-0">Featured Jobs</h2>
    </div>
    <a href="<?= url('/jobs') ?>" class="btn btn-outline-primary fw-600">
      View All Jobs <i class="bi bi-arrow-right ms-1"></i>
    </a>
  </div>
  <?php if(!empty($featuredJobs)): ?>
  <div class="row g-3">
    <?php foreach($featuredJobs as $job): ?>
    <div class="col-12 col-md-6 col-xl-4">
      <a href="<?= url('/jobs/'.$job['slug']) ?>"
         class="job-v3 h-100 <?= $job['is_featured']?'featured':'' ?>">
        <div class="d-flex gap-3 mb-3">
          <?php if($job['company_logo']): ?>
          <img src="<?= url('/file?path='.urlencode($job['company_logo'])) ?>"
               style="width:50px;height:50px;border-radius:12px;object-fit:contain;
                      border:1px solid var(--border);padding:5px;background:#fff;flex-shrink:0;" alt="">
          <?php else: ?>
          <div class="d-flex align-items-center justify-content-center fw-800 flex-shrink-0"
               style="width:50px;height:50px;border-radius:12px;background:var(--primary-light);
                      color:var(--primary);font-size:18px;">
            <?= strtoupper(substr($job['company_name'],0,1)) ?>
          </div>
          <?php endif; ?>
          <div class="min-w-0 flex-grow-1">
            <div class="fw-700 text-truncate" style="font-size:15px;"><?= e($job['title']) ?></div>
            <div class="text-muted small"><?= e($job['company_name']) ?></div>
          </div>
          <?php if($job['is_featured']): ?>
          <span class="j-tag" style="background:#FEF3C7;color:#92400e;border-color:#fde68a;height:fit-content;flex-shrink:0;">⭐ Featured</span>
          <?php endif; ?>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
          <span class="j-tag type"><i class="bi bi-briefcase" style="font-size:10px;"></i><?= status_label($job['job_type']) ?></span>
          <?php if($job['is_remote']): ?>
          <span class="j-tag remote"><i class="bi bi-wifi" style="font-size:10px;"></i>Remote</span>
          <?php elseif($job['location_city']): ?>
          <span class="j-tag type"><i class="bi bi-geo-alt" style="font-size:10px;"></i><?= e($job['location_city']) ?></span>
          <?php endif; ?>
          <?php if(!$job['salary_is_hidden'] && $job['salary_min']): ?>
          <span class="j-tag salary"><i class="bi bi-cash" style="font-size:10px;"></i><?= salary_range($job['salary_min'],$job['salary_max'],$job['salary_currency']) ?></span>
          <?php endif; ?>
        </div>
        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
          <span class="text-muted" style="font-size:12px;"><i class="bi bi-clock me-1"></i><?= time_ago($job['created_at']) ?></span>
          <span class="fw-600 small text-primary">Apply Now →</span>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="text-center py-5">
    <div style="font-size:3rem;opacity:.3;">💼</div>
    <p class="text-muted mt-3 mb-3">Job listings coming soon!</p>
    <a href="<?= url('/jobs') ?>" class="btn btn-primary px-5 fw-600">Browse All Jobs</a>
  </div>
  <?php endif; ?>
</div>
</section>

<!-- HOW IT WORKS -->
<section class="py-5 bg-white">
<div class="container">
  <div class="text-center mb-5">
    <div class="sec-tag">Simple Process</div>
    <h2 class="sec-h">How Tatu Job Hub Works</h2>
  </div>
  <div class="row g-4">
    <?php foreach([
      ['n'=>'01','icon'=>'person-plus-fill','bg'=>'#EBF5FF','c'=>'#1A56DB','t'=>'Create Account','d'=>'Register as a job seeker or employer in under 2 minutes.'],
      ['n'=>'02','icon'=>'file-earmark-person-fill','bg'=>'#EDE9FE','c'=>'#7C3AED','t'=>'Build Your Profile','d'=>'Upload your CV, add skills and showcase your experience.'],
      ['n'=>'03','icon'=>'search-heart-fill','bg'=>'#F0FDF4','c'=>'#057A55','t'=>'Find & Apply','d'=>'Search thousands of jobs and apply with one click.'],
      ['n'=>'04','icon'=>'trophy-fill','bg'=>'#FEF3C7','c'=>'#D97706','t'=>'Get Hired','d'=>'Track applications and land your dream role.'],
    ] as $step): ?>
    <div class="col-6 col-md-3">
      <div class="step-card">
        <div class="step-num" style="background:<?= $step['bg'] ?>;color:<?= $step['c'] ?>;">
          <i class="bi bi-<?= $step['icon'] ?>" style="font-size:1.3rem;"></i>
        </div>
        <div class="fw-700 small text-uppercase mb-1" style="color:<?= $step['c'] ?>;letter-spacing:1px;font-size:10px;">Step <?= $step['n'] ?></div>
        <h6 class="fw-800 mb-1"><?= $step['t'] ?></h6>
        <p class="text-muted small mb-0"><?= $step['d'] ?></p>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</section>

<!-- TESTIMONIALS -->
<section class="py-5" style="background:var(--bg);">
<div class="container">
  <div class="text-center mb-4">
    <div class="sec-tag">Success Stories</div>
    <h2 class="sec-h">What Our Users Say</h2>
  </div>
  <div class="row g-3">
    <?php foreach([
      ['name'=>'Abdulai Yakubu','role'=>'Software Engineer','text'=>'I found my dream job within 2 weeks of signing up! The job alert feature notified me instantly when a matching role was posted.','avatar'=>'K','color'=>'#1A56DB'],
      ['name'=>'Abdul Hafis Mohammed','role'=>'HR Manager at TechCorp','text'=>'We hired 3 excellent developers through Tatu Job Hub. The applicant management system made reviewing candidates so easy.','avatar'=>'A','color'=>'#7C3AED'],
      ['name'=>'Kofi Mensah','role'=>'Marketing Executive','text'=>'Best job portal in Ghana. The profile builder helped me stand out and I got interview calls within days of posting my CV.','avatar'=>'K','color'=>'#057A55'],
      ['name'=>'Ama Owusu','role'=>'Frontend Developer','text'=>'The interview scheduling feature made coordinating with employers effortless. I landed an interview within days.','avatar'=>'A','color'=>'#F59E0B'],
      ['name'=>'Yaw Asante','role'=>'Product Manager','text'=>'Tatu Job Hub connected our startup with talented candidates quickly — a great platform for employers.','avatar'=>'Y','color'=>'#10B981'],
      ['name'=>'Efua Boateng','role'=>'Data Analyst','text'=>'The application process is smooth and professional. I secured a role matching my skills in under a month.','avatar'=>'E','color'=>'#EF4444'],
    ] as $t): ?>
    <div class="col-12 col-md-4">
      <div class="testi-card h-100">
        <div class="stars">★★★★★</div>
        <p class="text-muted small mb-3" style="line-height:1.7;">"<?= $t['text'] ?>"</p>
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center fw-800 text-white flex-shrink-0"
               style="width:42px;height:42px;background:<?= $t['color'] ?>;font-size:16px;">
            <?= $t['avatar'] ?>
          </div>
          <div>
            <div class="fw-700 small"><?= $t['name'] ?></div>
            <div class="text-muted" style="font-size:11px;"><?= $t['role'] ?></div>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</section>

<!-- TOP COMPANIES -->
<?php if(!empty($topCompanies)): ?>
<section class="py-5 bg-white">
<div class="container">
  <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <div class="sec-tag">Top Employers</div>
      <h2 class="sec-h mb-0">Hiring Right Now</h2>
    </div>
    <a href="<?= url('/companies') ?>" class="btn btn-outline-primary fw-600">All Companies →</a>
  </div>
  <div class="row g-3">
    <?php foreach($topCompanies as $co):
      $slug = $co['slug'] ?? (slug($co['company_name']).'-'.$co['id']); ?>
    <div class="col-12 col-md-6">
      <a href="<?= url('/companies/'.$slug) ?>"
         class="d-flex align-items-center gap-3 p-3 rounded-3 text-decoration-none"
         style="border:1.5px solid var(--border);background:#fff;transition:.2s;"
         onmouseover="this.style.borderColor='#1A56DB'"
         onmouseout="this.style.borderColor='var(--border)'">
        <div class="d-flex align-items-center justify-content-center fw-800 flex-shrink-0"
             style="width:50px;height:50px;border-radius:12px;background:var(--primary-light);color:var(--primary);font-size:18px;">
          <?= strtoupper(substr($co['company_name'],0,1)) ?>
        </div>
        <div class="flex-grow-1 min-w-0">
          <div class="fw-700 small text-dark text-truncate"><?= e($co['company_name']) ?></div>
          <div class="text-muted" style="font-size:11px;"><?= e($co['industry']??'') ?></div>
        </div>
        <?php if($co['job_count']>0): ?>
        <span class="j-tag remote flex-shrink-0"><?= $co['job_count'] ?> open</span>
        <?php endif; ?>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</section>
<?php endif; ?>

<!-- CTA BANNER -->
<section class="py-5" style="background:linear-gradient(135deg,#0F172A 0%,#1e1b4b 50%,#1A56DB 100%);">
<div class="container text-center text-white py-3">
  <div class="mb-3" style="font-size:2.5rem;">🚀</div>
  <h2 class="fw-800 mb-3" style="font-size:clamp(1.5rem,4vw,2.2rem);letter-spacing:-.5px;">
    Ready to take the next step?
  </h2>
  <p class="mb-4" style="opacity:.75;max-width:480px;margin:0 auto 1.5rem;">
    Join <?= number_format(($stats['seekers']??0)+($stats['companies']??0)) ?>+ professionals
    already using <?= APP_NAME ?> to find and post jobs.
  </p>
  <div class="d-flex gap-3 justify-content-center flex-wrap">
    <?php if(!is_logged_in()): ?>
    <a href="<?= url('/register?role=seeker') ?>" class="btn btn-warning btn-lg fw-700 px-5">
      <i class="bi bi-person-plus me-2"></i>Find a Job
    </a>
    <a href="<?= url('/register?role=employer') ?>" class="btn btn-outline-light btn-lg fw-700 px-5">
      <i class="bi bi-building me-2"></i>Post a Job
    </a>
    <?php else: ?>
    <a href="<?= url('/'.auth()['role'].'/dashboard') ?>" class="btn btn-warning btn-lg fw-700 px-5">
      <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
    </a>
    <a href="<?= url('/jobs') ?>" class="btn btn-outline-light btn-lg fw-700 px-5">
      <i class="bi bi-search me-2"></i>Browse Jobs
    </a>
    <?php endif; ?>
  </div>
</div>
</section>

<script>
// Counter animation
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('.count-up');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const el = entry.target;
                const target = parseInt(el.dataset.target) || 0;
                let current = 0;
                const duration = 1500;
                const step = Math.ceil(target / (duration / 16));
                const timer = setInterval(() => {
                    current = Math.min(current + step, target);
                    el.textContent = current.toLocaleString();
                    if (current >= target) clearInterval(timer);
                }, 16);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });
    counters.forEach(c => observer.observe(c));
});
</script>
