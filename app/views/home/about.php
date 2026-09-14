<style>
.doc-hero{background:linear-gradient(135deg,#0F172A 0%,#1e1b4b 50%,#1A56DB 100%);
          padding:60px 0;color:#fff;}
.doc-section{padding:48px 0;border-bottom:1px solid var(--border);}
.doc-section:last-child{border-bottom:none;}
.feature-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-top:24px;}
.feature-item{background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:22px;}
.feature-item .fi-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;
                       justify-content:center;font-size:1.3rem;margin-bottom:12px;}
.tech-badge{display:inline-flex;align-items:center;gap:6px;background:#F9FAFB;
            border:1px solid var(--border);color:#374151;font-size:13px;font-weight:600;
            padding:6px 14px;border-radius:8px;margin:4px;}
.team-card{background:#fff;border:1.5px solid var(--border);border-radius:16px;padding:24px;text-align:center;}
.team-avatar{width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;
             font-weight:800;font-size:24px;color:#fff;margin:0 auto 14px;}
.phase-item{display:flex;gap:16px;padding:16px 0;border-bottom:1px solid #f3f4f6;}
.phase-item:last-child{border-bottom:none;}
.phase-num{width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;
           display:flex;align-items:center;justify-content:center;font-weight:700;
           font-size:13px;flex-shrink:0;margin-top:2px;}
[data-theme="dark"] .feature-item,[data-theme="dark"] .team-card{background:#1E293B;border-color:#374151;}
[data-theme="dark"] .tech-badge{background:#1E293B;border-color:#374151;color:#D1D5DB;}
</style>

<!-- Hero -->
<div class="doc-hero">
  <div class="container text-center">
    <div class="badge mb-3 px-3 py-2 fw-600"
         style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.3);
                border-radius:50px;font-size:13px;">
      Final Year Capstone Project — Diploma in Information Technology Programme
    </div>
    <h1 class="fw-800 mb-3" style="font-size:clamp(1.8rem,4vw,2.8rem);letter-spacing:-.5px;">
      TatuJobHub
    </h1>
    <p class="mb-2" style="opacity:.8;font-size:1.05rem;max-width:560px;margin:0 auto;">
      A Multi-Role Online Job Recruitment Portal for Ghana's Digital Economy
    </p>
    <p style="opacity:.6;font-size:14px;">
      Tamale Technical University · Department of Computer Science · 2025/2026
    </p>
  </div>
</div>

<div class="container py-5">

  <!-- Project Overview -->
  <div class="doc-section">
    <div class="row align-items-center g-4">
      <div class="col-lg-6">
        <p class="sec-tag mb-2">Project Overview</p>
        <h2 class="fw-800 mb-3" style="letter-spacing:-.5px;">About Tatu Job Hub</h2>
        <p class="text-muted" style="line-height:1.8;">
          Tatu Job Hub is a comprehensive web-based job recruitment platform developed as a final-year
          capstone project for the Diploma in Information Technology programme at
          <strong>Tamale Technical University</strong>. The system bridges the gap between
          job seekers and employers in Ghana's growing digital economy by providing a
          centralised, secure, and user-friendly recruitment ecosystem.
        </p>
        <p class="text-muted" style="line-height:1.8;">
          The platform supports three distinct user roles — Job Seekers, Employers, and
          Administrators — each with dedicated dashboards, workflows, and features tailored
          to their specific needs.
        </p>
      </div>
      <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
          <h6 class="fw-700 mb-3">Project Details</h6>
          <?php foreach([
            ['🏫','Institution',    'Tamale Technical University (TaTu)'],
            ['📚','Department',     'Computer Science'],
            ['🎓','Programme',      'Diploma in Information Technology'],
            ['📅','Academic Year',  '2025 / 2026'],
            ['🌐','Live URL',       'tatujobhub.xo.je'],
            ['⚙️','Methodology',   'Waterfall SDLC'],
            ['🔧','Tech Stack',     'PHP 8.2 · MySQL · Bootstrap 5'],
          ] as [$ic,$lbl,$val]): ?>
          <div class="d-flex gap-3 mb-2 pb-2 border-bottom">
            <span style="font-size:1.1rem;flex-shrink:0;"><?= $ic ?></span>
            <div>
              <div class="text-muted small"><?= $lbl ?></div>
              <div class="fw-600 small"><?= $val ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Key Features -->
  <div class="doc-section">
    <p class="sec-tag mb-2">System Capabilities</p>
    <h2 class="fw-800 mb-1" style="letter-spacing:-.5px;">Key Features</h2>
    <p class="text-muted small mb-0">TatuJobHub delivers a full-stack recruitment experience across three roles.</p>
    <div class="feature-grid">
      <?php foreach([
        ['bg'=>'#EBF5FF','c'=>'#1A56DB','icon'=>'🔍','title'=>'Job Search & Filtering',
         'desc'=>'Advanced search with filters for industry, job type, location, salary, and experience level.'],
        ['bg'=>'#F0FDF4','c'=>'#057A55','icon'=>'📄','title'=>'Resume Builder & PDF Export',
         'desc'=>'Seekers build a structured profile and download a professionally formatted PDF resume.'],
        ['bg'=>'#EDE9FE','c'=>'#7C3AED','icon'=>'📅','title'=>'Interview Scheduler',
         'desc'=>'Employers schedule interviews with date, time, and mode. Seekers receive instant notifications.'],
        ['bg'=>'#FEF3C7','c'=>'#D97706','icon'=>'🔔','title'=>'Job Alerts',
         'desc'=>'Seekers set keyword/industry alerts and receive instant email notifications for matching jobs.'],
        ['bg'=>'#FEE2E2','c'=>'#DC2626','icon'=>'💬','title'=>'In-App Messaging',
         'desc'=>'Real-time messaging between seekers and employers with AJAX-powered polling.'],
        ['bg'=>'#E0F2FE','c'=>'#0284C7','icon'=>'🤖','title'=>'AI Career Chatbot',
         'desc'=>'Built-in AI assistant helps seekers with career advice, CV tips, and job search guidance.'],
        ['bg'=>'#F0FDF4','c'=>'#057A55','icon'=>'🏢','title'=>'Company Profiles',
         'desc'=>'Employers create verified company profiles with logos, descriptions, and open positions.'],
        ['bg'=>'#EBF5FF','c'=>'#1A56DB','icon'=>'📊','title'=>'Admin Analytics',
         'desc'=>'Admin dashboard with Chart.js visualisations, audit logs, and CSV export capabilities.'],
        ['bg'=>'#EDE9FE','c'=>'#7C3AED','icon'=>'🌙','title'=>'Dark Mode',
         'desc'=>'Full dark theme toggle that persists across sessions using localStorage.'],
        ['bg'=>'#FEF3C7','c'=>'#D97706','icon'=>'🔐','title'=>'Secure Authentication',
         'desc'=>'BCrypt password hashing, CSRF protection, email verification, and session management.'],
        ['bg'=>'#FEE2E2','c'=>'#DC2626','icon'=>'📱','title'=>'Responsive Design',
         'desc'=>'Mobile-first Bootstrap 5 UI with adaptive layouts for phones, tablets, and desktops.'],
        ['bg'=>'#E0F2FE','c'=>'#0284C7','icon'=>'📈','title'=>'Application Tracking',
         'desc'=>'Visual 5-step application timeline: Applied → Reviewed → Shortlisted → Interview → Offer.'],
      ] as $f): ?>
      <div class="feature-item">
        <div class="fi-icon" style="background:<?= $f['bg'] ?>;color:<?= $f['c'] ?>;">
          <?= $f['icon'] ?>
        </div>
        <h6 class="fw-700 mb-1"><?= $f['title'] ?></h6>
        <p class="text-muted small mb-0" style="line-height:1.6;"><?= $f['desc'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tech Stack -->
  <div class="doc-section">
    <p class="sec-tag mb-2">Technologies Used</p>
    <h2 class="fw-800 mb-3" style="letter-spacing:-.5px;">Technology Stack</h2>
    <div class="row g-4">
      <?php foreach([
        ['Backend',  ['PHP 8.2','Custom MVC Framework','MySQL 8','PDO','PHPMailer','BCrypt']],
        ['Frontend', ['Bootstrap 5.3','Vanilla JavaScript','Chart.js','Bootstrap Icons','CSS Variables']],
        ['Tools',    ['FileZilla (Deployment)','XAMPP (Local Dev)','phpMyAdmin','Git','Anthropic Claude API']],
        ['Hosting',  ['InfinityFree (Live Server)','MySQL via sql109.infinityfree.com','Custom PHP Router']],
      ] as [$cat,$items]): ?>
      <div class="col-md-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:14px;">
          <h6 class="fw-700 mb-2"><?= $cat ?></h6>
          <div>
            <?php foreach($items as $item): ?>
            <span class="tech-badge"><?= $item ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Development Phases -->
  <div class="doc-section">
    <p class="sec-tag mb-2">Development Methodology</p>
    <h2 class="fw-800 mb-3" style="letter-spacing:-.5px;">Waterfall SDLC Phases</h2>
    <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
      <?php foreach([
        ['01','Requirements Analysis',
         'Conducted stakeholder interviews and surveys to identify system requirements for job seekers, employers, and administrators in Ghana\'s job market.'],
        ['02','System Design',
         'Designed entity-relationship diagrams, database schemas, wireframes, and system architecture using custom PHP MVC pattern.'],
        ['03','Implementation',
         'Built the full-stack portal using PHP 8.2, MySQL, Bootstrap 5, and Vanilla JavaScript. Developed 15+ controllers, 40+ views, and a custom routing engine.'],
        ['04','Testing',
         'Conducted 20+ test cases covering unit, integration, and user acceptance testing across all three user roles.'],
        ['05','Deployment',
         'Deployed to InfinityFree shared hosting via FileZilla. Configured Apache .htaccess, MySQL database, and PHPMailer SMTP.'],
        ['06','Maintenance',
         'Ongoing bug fixes, performance optimisations, and feature additions based on user feedback.'],
      ] as [$n,$title,$desc]): ?>
      <div class="phase-item">
        <div class="phase-num"><?= $n ?></div>
        <div>
          <div class="fw-700 small mb-1"><?= $title ?></div>
          <div class="text-muted small" style="line-height:1.7;"><?= $desc ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Team -->
  <div class="doc-section">
    <p class="sec-tag mb-2">Project Team</p>
    <h2 class="fw-800 mb-3" style="letter-spacing:-.5px;">Meet the Developers</h2>
    <div class="row g-4">
      <?php foreach([
        ['name'=>'Hardi Abdul-Wahidu',        'id'=>'DTIT240048','role'=>'Lead Developer & Project Manager','color'=>'#1A56DB','initial'=>'H'],
        ['name'=>'Abdulai Yakubu',            'id'=>'DTIT240038','role'=>'Backend Developer & Database Administrator','color'=>'#7C3AED','initial'=>'A'],
        ['name'=>'Issahak Yushaw Binnur',    'id'=>'DTIT240027','role'=>'Frontend Developer & UI/UX Designer','color'=>'#057A55','initial'=>'I'],
      ] as $m): ?>
      <div class="col-md-4">
        <div class="team-card">
          <div class="team-avatar" style="background:<?= $m['color'] ?>;"><?= $m['initial'] ?></div>
          <h6 class="fw-800 mb-1"><?= $m['name'] ?></h6>
          <div class="text-muted small mb-2"><?= $m['id'] ?></div>
          <span class="badge rounded-pill fw-500"
                style="background:<?= $m['color'] ?>22;color:<?= $m['color'] ?>;
                       padding:6px 14px;font-size:12px;">
            <?= $m['role'] ?>
          </span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="card border-0 shadow-sm mt-4 p-3" style="border-radius:14px;">
      <div class="row text-center g-3">
        <div class="col-6 col-md-3">
          <div class="fw-800 fs-4 text-primary">Prof.</div>
          <div class="small text-muted">Supervisor</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="fw-700">Abukari Abdul Aziz Danaa</div>
          <div class="small text-muted">Project Supervisor</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="fw-700">Tamale Technical University</div>
          <div class="small text-muted">Institution</div>
        </div>
        <div class="col-6 col-md-3">
          <div class="fw-700">2025 / 2026</div>
          <div class="small text-muted">Academic Year</div>
        </div>
      </div>
    </div>
  </div>

  <!-- User Guide -->
  <div class="doc-section">
    <p class="sec-tag mb-2">Quick Start Guide</p>
    <h2 class="fw-800 mb-3" style="letter-spacing:-.5px;">How to Use TatuJobHub</h2>
    <div class="row g-4">
      <?php foreach([
        ['role'=>'Job Seeker','color'=>'#1A56DB','bg'=>'#EBF5FF','steps'=>[
          'Register a free seeker account',
          'Complete your profile and upload CV',
          'Search and filter job listings',
          'Apply with one click via the apply modal',
          'Track your applications on the dashboard',
          'Set job alerts to get emailed for new matches',
        ]],
        ['role'=>'Employer','color'=>'#7C3AED','bg'=>'#EDE9FE','steps'=>[
          'Register an employer account with company name',
          'Complete your company profile and request verification',
          'Post job listings with full details and requirements',
          'Review applicants and update application status',
          'Schedule interviews and send notifications',
          'Track analytics for each job posting',
        ]],
        ['role'=>'Administrator','color'=>'#DC2626','bg'=>'#FEE2E2','steps'=>[
          'Log in at /login with admin credentials',
          'Review and approve/reject employer verifications',
          'Monitor all users, jobs, and applications',
          'View audit logs of all system actions',
          'Export data as CSV for reporting',
          'Manage system settings and user accounts',
        ]],
      ] as $guide): ?>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:14px;border-top:3px solid <?= $guide['color'] ?>!important;">
          <h6 class="fw-800 mb-3" style="color:<?= $guide['color'] ?>;"><?= $guide['role'] ?></h6>
          <ol class="ps-3 mb-0">
            <?php foreach($guide['steps'] as $step): ?>
            <li class="text-muted small mb-2" style="line-height:1.6;"><?= $step ?></li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>
