<?php
$jobTypes  = ['full_time'=>'Full-Time','part_time'=>'Part-Time','contract'=>'Contract',
              'internship'=>'Internship','freelance'=>'Freelance'];
$expLevels = ['entry'=>'Entry Level','junior'=>'Junior','mid'=>'Mid-Level',
              'senior'=>'Senior','lead'=>'Lead','executive'=>'Executive'];
?>
<style>
.jd-hero{background:linear-gradient(135deg,#1A56DB 0%,#1347c8 60%,#1e1b4b 100%);
         padding:36px 0 28px;margin-bottom:0;}
.jd-logo{width:72px;height:72px;border-radius:16px;object-fit:contain;
         border:3px solid rgba(255,255,255,.2);padding:6px;background:rgba(255,255,255,.15);flex-shrink:0;}
.jd-logo-placeholder{width:72px;height:72px;border-radius:16px;
                      background:rgba(255,255,255,.15);border:3px solid rgba(255,255,255,.2);
                      display:flex;align-items:center;justify-content:center;
                      font-weight:800;font-size:26px;color:#fff;flex-shrink:0;}
.jd-tag{display:inline-flex;align-items:center;gap:4px;
        background:rgba(255,255,255,.15);color:#fff;
        font-size:12px;font-weight:600;padding:4px 10px;border-radius:50px;
        border:1px solid rgba(255,255,255,.2);}
.jd-tag.salary{background:rgba(16,185,129,.25);border-color:rgba(16,185,129,.4);}
.jd-tag.remote{background:rgba(16,185,129,.25);border-color:rgba(16,185,129,.4);}
.stat-strip{display:grid;grid-template-columns:repeat(4,1fr);
            background:#fff;border-radius:16px;overflow:hidden;
            box-shadow:0 4px 20px rgba(0,0,0,.08);margin-top:-20px;position:relative;z-index:2;}
.stat-cell{padding:18px 12px;text-align:center;border-right:1px solid #f3f4f6;}
.stat-cell:last-child{border-right:none;}
.stat-cell .val{font-weight:800;font-size:1rem;color:#111827;line-height:1;}
.stat-cell .lbl{font-size:11px;color:#9ca3af;margin-top:3px;font-weight:500;}
.jd-section{margin-bottom:28px;}
.jd-section h5{font-weight:700;font-size:15px;margin-bottom:14px;
               padding-bottom:10px;border-bottom:2px solid #EBF5FF;color:#1A56DB;}
.jd-section ul{list-style:none;padding:0;margin:0;}
.jd-section ul li{padding:6px 0;padding-left:22px;position:relative;font-size:14.5px;color:#374151;}
.jd-section ul li::before{content:'✓';position:absolute;left:0;color:#1A56DB;font-weight:700;}
.apply-card{position:sticky;top:80px;border-radius:20px;overflow:hidden;
            border:2px solid var(--border);background:#fff;}
.apply-card .apply-top{background:linear-gradient(135deg,#EBF5FF,#EDE9FE);padding:24px;}
.deadline-bar{background:#FEF3C7;border-radius:10px;padding:10px 14px;
              display:flex;align-items:center;gap:8px;margin:16px 0;font-size:13px;}
.company-mini{border:1.5px solid var(--border);border-radius:14px;overflow:hidden;margin-top:16px;}
.company-mini-head{background:#F9FAFB;padding:14px 16px;border-bottom:1px solid var(--border);}
.share-btn{width:38px;height:38px;border-radius:10px;border:1.5px solid var(--border);
           background:#fff;display:flex;align-items:center;justify-content:center;
           cursor:pointer;transition:.2s;text-decoration:none;color:#374151;}
.share-btn:hover{border-color:var(--primary);background:var(--primary-light);color:var(--primary);}
</style>

<!-- Job hero header -->
<div class="jd-hero">
  <div class="container">
    <!-- Breadcrumb -->
    <nav class="mb-3" style="font-size:13px;">
      <a href="<?= url('/') ?>" class="text-white opacity-60 text-decoration-none">Home</a>
      <span class="text-white opacity-40 mx-2">/</span>
      <a href="<?= url('/jobs') ?>" class="text-white opacity-60 text-decoration-none">Jobs</a>
      <span class="text-white opacity-40 mx-2">/</span>
      <span class="text-white opacity-90"><?= e($job['title']) ?></span>
    </nav>

    <div class="d-flex gap-4 align-items-start flex-wrap">
      <!-- Logo -->
      <?php if($job['company_logo']): ?>
      <img src="<?= url('/file?path='.urlencode($job['company_logo'])) ?>"
           class="jd-logo" alt="<?= e($job['company_name']) ?>">
      <?php else: ?>
      <div class="jd-logo-placeholder"><?= strtoupper(substr($job['company_name'],0,1)) ?></div>
      <?php endif; ?>

      <div class="flex-grow-1 min-w-0">
        <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
          <?php if($job['is_featured']): ?>
          <span class="badge bg-warning text-dark fw-600" style="font-size:11px;">⭐ Featured</span>
          <?php endif; ?>
        </div>
        <h1 class="text-white fw-800 mb-1" style="font-size:clamp(1.3rem,3vw,1.8rem);letter-spacing:-.5px;">
          <?= e($job['title']) ?>
        </h1>
        <div class="d-flex align-items-center gap-1 mb-3" style="color:rgba(255,255,255,.75);font-size:14px;">
          <i class="bi bi-building"></i>
          <a href="<?= url('/companies/'.($job['company_slug']??'')) ?>"
             class="text-white opacity-75 text-decoration-none fw-600">
            <?= e($job['company_name']) ?>
          </a>
        </div>
        <!-- Tags -->
        <div class="d-flex flex-wrap gap-2">
          <span class="jd-tag <?= $job['is_remote']?'remote':'' ?>">
            <i class="bi bi-geo-alt" style="font-size:11px;"></i>
            <?= $job['is_remote'] ? 'Remote' : e($job['location_city']??'Ghana') ?>
          </span>
          <span class="jd-tag"><?= $jobTypes[$job['job_type']]??status_label($job['job_type']) ?></span>
          <span class="jd-tag"><?= $expLevels[$job['experience_level']]??status_label($job['experience_level']) ?></span>
          <?php if(!$job['salary_is_hidden'] && $job['salary_min']): ?>
          <span class="jd-tag salary">
            <i class="bi bi-cash" style="font-size:11px;"></i>
            <?= salary_range($job['salary_min'],$job['salary_max'],$job['salary_currency']) ?>
          </span>
          <?php endif; ?>
          <?php if($job['industry']): ?>
          <span class="jd-tag"><i class="bi bi-tag" style="font-size:11px;"></i><?= e($job['industry']) ?></span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container pb-5">
  <!-- Stat strip -->
  <div class="stat-strip mb-4 shadow-sm">
    <div class="stat-cell">
      <div class="val"><?= time_ago($job['created_at']) ?></div>
      <div class="lbl">Posted</div>
    </div>
    <div class="stat-cell">
      <div class="val"><?= $job['vacancies'] ?? 1 ?></div>
      <div class="lbl">Vacancies</div>
    </div>
    <div class="stat-cell">
      <div class="val"><?= number_format($job['view_count']??0) ?></div>
      <div class="lbl">Views</div>
    </div>
    <div class="stat-cell">
      <div class="val"><?= $job['application_deadline'] ? date('d M', strtotime($job['application_deadline'])) : 'Open' ?></div>
      <div class="lbl">Deadline</div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Main content -->
    <div class="col-lg-8">

      <!-- Description -->
      <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
        <div class="card-body p-4">

          <?php if($job['description']): ?>
          <div class="jd-section">
            <h5><i class="bi bi-file-text me-2"></i>Job Description</h5>
            <div style="color:#374151;line-height:1.8;font-size:14.5px;">
              <?= nl2br(e($job['description'])) ?>
            </div>
          </div>
          <?php endif; ?>

          <?php if($job['responsibilities']): ?>
          <div class="jd-section">
            <h5><i class="bi bi-check2-square me-2"></i>Responsibilities</h5>
            <ul>
              <?php foreach(array_filter(explode("\n",$job['responsibilities'])) as $r): ?>
              <li><?= e(trim($r,'- ')) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <?php if($job['requirements']): ?>
          <div class="jd-section">
            <h5><i class="bi bi-person-check me-2"></i>Requirements</h5>
            <ul>
              <?php foreach(array_filter(explode("\n",$job['requirements'])) as $r): ?>
              <li><?= e(trim($r,'- ')) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <?php if($job['benefits']): ?>
          <div class="jd-section">
            <h5><i class="bi bi-gift me-2"></i>Benefits & Perks</h5>
            <ul>
              <?php foreach(array_filter(explode("\n",$job['benefits'])) as $b): ?>
              <li><?= e(trim($b,'- ')) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
          <?php endif; ?>

          <!-- Required skills -->
          <?php if(!empty($job['skills'])): ?>
          <div class="jd-section mb-0">
            <h5><i class="bi bi-lightning-fill me-2"></i>Required Skills</h5>
            <div class="d-flex flex-wrap gap-2">
              <?php foreach(explode(',',$job['skills']) as $sk): ?>
              <span class="badge rounded-pill fw-500"
                    style="background:#EBF5FF;color:#1A56DB;padding:7px 14px;font-size:12.5px;">
                <?= e(trim($sk)) ?>
              </span>
              <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Share row -->
      <div class="d-flex align-items-center gap-3 mb-2">
        <span class="text-muted small fw-600">Share this job:</span>
        <?php $shareUrl = url('/jobs/'.$job['slug']); ?>
        <a class="share-btn" target="_blank"
           href="https://wa.me/?text=<?= urlencode($job['title'].' at '.$job['company_name'].' — '.$shareUrl) ?>"
           title="WhatsApp"><i class="bi bi-whatsapp text-success"></i></a>
        <a class="share-btn" target="_blank"
           href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($shareUrl) ?>"
           title="LinkedIn"><i class="bi bi-linkedin text-primary"></i></a>
        <a class="share-btn" target="_blank"
           href="https://twitter.com/intent/tweet?text=<?= urlencode('Hiring: '.$job['title'].' — '.$shareUrl) ?>"
           title="X / Twitter"><i class="bi bi-twitter-x"></i></a>
        <button class="share-btn" onclick="copyLink()" title="Copy link">
          <i class="bi bi-link-45deg" id="copyIcon"></i>
        </button>
        <span id="copyMsg" class="text-success small fw-600" style="display:none;">Copied!</span>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
      <div class="apply-card shadow-sm">
        <div class="apply-top">
          <?php if(!$job['salary_is_hidden'] && $job['salary_min']): ?>
          <div class="fw-700 mb-1" style="color:#057A55;font-size:1.1rem;">
            <?= salary_range($job['salary_min'],$job['salary_max'],$job['salary_currency']) ?>
            <?php if($job['salary_period']): ?><span class="fw-400 small text-muted">/ <?= $job['salary_period'] ?></span><?php endif; ?>
          </div>
          <?php else: ?>
          <div class="fw-700 mb-1" style="color:#374151;">Competitive Salary</div>
          <?php endif; ?>
          <div class="text-muted small"><?= $jobTypes[$job['job_type']]??'' ?> · <?= $expLevels[$job['experience_level']]??'' ?></div>
        </div>

        <div class="p-4">
          <?php if($job['application_deadline']): ?>
          <div class="deadline-bar">
            <i class="bi bi-calendar-x text-warning"></i>
            <span><strong>Closes:</strong> <?= date('d M Y', strtotime($job['application_deadline'])) ?></span>
          </div>
          <?php endif; ?>

          <?php if(!is_logged_in()): ?>
            <a href="<?= url('/login') ?>" class="btn btn-primary w-100 btn-lg fw-700 mb-2">
              <i class="bi bi-box-arrow-in-right me-2"></i>Login to Apply
            </a>
            <a href="<?= url('/register?role=seeker') ?>" class="btn btn-outline-primary w-100 fw-600">
              Create Account
            </a>
          <?php elseif(Session::isSeeker()): ?>
            <?php if($hasApplied): ?>
            <div class="alert border-0 text-center mb-2"
                 style="background:#F0FDF4;color:#057A55;border-radius:12px;">
              <i class="bi bi-check-circle-fill me-2"></i><strong>Application Submitted</strong>
            </div>
            <?php elseif($job['status']==='active'): ?>
            <button type="button" class="btn btn-primary w-100 btn-lg fw-700 mb-2"
                    data-bs-toggle="modal" data-bs-target="#applyModal">
              <i class="bi bi-send me-2"></i>Apply Now
            </button>
            <?php endif; ?>

            <!-- Save job -->
            <form method="POST"
                  action="<?= url(in_array($job['id'],$savedIds??[])?'/seeker/unsave-job/'.$job['id']:'/seeker/save-job/'.$job['id']) ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn w-100 fw-600"
                      style="border:1.5px solid var(--border);border-radius:10px;">
                <i class="bi bi-bookmark<?= in_array($job['id'],$savedIds??[])?'-fill text-primary':'' ?> me-2"></i>
                <?= in_array($job['id'],$savedIds??[]) ? 'Saved' : 'Save Job' ?>
              </button>
            </form>
          <?php elseif(Session::isEmployer()): ?>
            <div class="alert border-0 text-center" style="background:#EBF5FF;color:#1A56DB;border-radius:12px;">
              <i class="bi bi-info-circle me-1"></i>Employer accounts cannot apply.
            </div>
          <?php endif; ?>

          <!-- Company mini card -->
          <div class="company-mini mt-4">
            <div class="company-mini-head">
              <span class="fw-700 small"><i class="bi bi-building me-2 text-primary"></i>About the Company</span>
            </div>
            <div class="p-3">
              <div class="d-flex gap-3 align-items-center mb-2">
                <?php if($job['company_logo']): ?>
                <img src="<?= url('/file?path='.urlencode($job['company_logo'])) ?>"
                     style="width:42px;height:42px;border-radius:10px;object-fit:contain;
                            border:1px solid #e5e7eb;padding:4px;" alt="">
                <?php else: ?>
                <div class="d-flex align-items-center justify-content-center fw-800"
                     style="width:42px;height:42px;border-radius:10px;
                            background:#EBF5FF;color:#1A56DB;font-size:16px;">
                  <?= strtoupper(substr($job['company_name'],0,1)) ?>
                </div>
                <?php endif; ?>
                <div>
                  <div class="fw-700 small"><?= e($job['company_name']) ?></div>
                  <div class="text-muted" style="font-size:11px;"><?= e($job['industry']??'') ?></div>
                </div>
              </div>
              <a href="<?= url('/companies/'.($job['company_slug']??'')) ?>"
                 class="btn btn-sm btn-outline-primary w-100 fw-600 mt-1">
                View Company Profile
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Apply Modal -->
<?php if(Session::isSeeker() && !$hasApplied && $job['status']==='active'): ?>
<div class="modal fade" id="applyModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius:20px;overflow:hidden;">
      <div class="modal-header border-0" style="background:linear-gradient(135deg,#EBF5FF,#EDE9FE);padding:24px;">
        <div>
          <h5 class="fw-800 mb-1">Apply for <?= e($job['title']) ?></h5>
          <p class="text-muted small mb-0"><?= e($job['company_name']) ?></p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <form method="POST" action="<?= url('/jobs/'.$job['slug'].'/apply') ?>"
              enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label fw-600">Cover Letter <span class="text-muted fw-400">(optional)</span></label>
            <textarea class="form-control" name="cover_letter" rows="5"
                      placeholder="Introduce yourself and explain why you're a great fit..."
                      style="border-radius:10px;resize:none;"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600">
              Resume <span class="text-danger">*</span>
              <span class="text-muted fw-400">(PDF, DOC, DOCX — max 5MB)</span>
            </label>
            <input type="file" class="form-control" name="resume_file" required
                   accept=".pdf,.doc,.docx" style="border-radius:10px;">
            <div class="small text-muted mt-1">
              Please attach your resume for this application, even if you have one
              on your profile.
              <?php if(!empty($profile['resume_path'])): ?>
                Want to reuse the same file? <a href="<?= FileUpload::url($profile['resume_path']) ?>"
                target="_blank">Download your profile resume</a> first, then attach it here.
              <?php endif; ?>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100 btn-lg fw-700">
            <i class="bi bi-send me-2"></i>Submit Application
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function copyLink(){
  navigator.clipboard.writeText('<?= url('/jobs/'.$job['slug']) ?>').then(()=>{
    document.getElementById('copyIcon').className='bi bi-check-lg text-success';
    document.getElementById('copyMsg').style.display='inline';
    setTimeout(()=>{
      document.getElementById('copyIcon').className='bi bi-link-45deg';
      document.getElementById('copyMsg').style.display='none';
    },2000);
  });
}
</script>
