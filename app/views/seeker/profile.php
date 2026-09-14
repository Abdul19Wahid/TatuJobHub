<?php
$proficiencyColors = [
    'beginner'     => 'bg-secondary',
    'intermediate' => 'bg-info text-dark',
    'advanced'     => 'bg-primary',
    'expert'       => 'bg-success',
];
?>

<!-- Page header -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">My Profile</h1>
        <p class="text-muted small mb-0">Keep your profile up to date to attract employers.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <div class="text-muted small"><?= $score ?>% complete</div>
        <div class="progress flex-shrink-0" style="width:80px;height:8px;border-radius:4px;">
            <div class="progress-bar bg-<?= $score >= 80 ? 'success' : ($score >= 50 ? 'primary' : 'warning') ?>"
                 style="width:<?= $score ?>%;"></div>
        </div>
    </div>
</div>

<div class="row g-4">

<!-- ── LEFT: Avatar + basic info ─────────────────────────────────────────── -->
<div class="col-lg-4">

    <!-- Avatar card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center p-4">
            <div class="position-relative d-inline-block mb-3">
                <?php if ($profile['avatar_path']): ?>
                    <img src="<?= url('/file?path=' . urlencode($profile['avatar_path'])) ?>"
                         class="avatar" style="width:96px;height:96px;" alt="Profile photo">
                <?php else: ?>
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center
                                justify-content-center fw-800" style="width:96px;height:96px;font-size:36px;">
                        <?= strtoupper(substr($profile['full_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= url('/seeker/profile/avatar') ?>"
                  enctype="multipart/form-data" data-no-loading>
                <?= csrf_field() ?>
                <label class="btn btn-outline-primary btn-sm w-100 mb-2" style="cursor:pointer;">
                    <i class="bi bi-camera me-1"></i> Change Photo
                    <input type="file" name="avatar" accept="image/*" class="d-none"
                           onchange="this.form.submit()">
                </label>
            </form>
            <p class="text-muted mb-0" style="font-size:11px;">JPG, PNG, WEBP · Max 2MB</p>
        </div>
    </div>

    <!-- Resume card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-file-earmark-person me-2 text-primary"></i>Resume / CV</h6>
        </div>
        <div class="card-body">
            <?php if ($profile['resume_path']): ?>
            <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded-2 border bg-light">
                <i class="bi bi-file-earmark-pdf text-danger fs-4"></i>
                <div class="flex-grow-1 min-w-0">
                    <div class="fw-600 small text-truncate">
                        <?= e($profile['resume_original_name'] ?? 'resume') ?>
                    </div>
                    <div class="text-muted" style="font-size:11px;">Uploaded resume</div>
                </div>
                <a href="<?= url('/file?path=' . urlencode($profile['resume_path'])) ?>"
                   target="_blank" class="btn btn-sm btn-outline-primary flex-shrink-0">
                    <i class="bi bi-download"></i>
                </a>
            </div>
            <?php else: ?>
            <div class="text-center py-2 mb-3">
                <i class="bi bi-file-earmark-arrow-up text-muted" style="font-size:2rem;"></i>
                <p class="text-muted small mt-2 mb-0">No resume uploaded yet.</p>
            </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/seeker/profile/resume') ?>"
                  enctype="multipart/form-data" data-no-loading>
                <?= csrf_field() ?>
                <label class="btn btn-primary btn-sm w-100" style="cursor:pointer;">
                    <i class="bi bi-upload me-1"></i>
                    <?= $profile['resume_path'] ? 'Replace Resume' : 'Upload Resume' ?>
                    <input type="file" name="resume" accept=".pdf,.doc,.docx" class="d-none"
                           onchange="this.form.submit()">
                </label>
            </form>
            <p class="text-muted text-center mt-2 mb-0" style="font-size:11px;">PDF, DOC, DOCX · Max 5MB</p>
        </div>
    </div>

    <!-- Open to work toggle -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-700 small">Open to Work</div>
                <div class="text-muted" style="font-size:11px;">Let employers find you</div>
            </div>
            <form method="POST" action="<?= url('/seeker/profile') ?>" data-no-loading>
                <?= csrf_field() ?>
                <input type="hidden" name="full_name"   value="<?= e($profile['full_name']) ?>">
                <input type="hidden" name="_open_toggle" value="1">
                <?php if ($profile['is_open_to_work']): ?>
                    <input type="hidden" name="is_open_to_work" value="0">
                    <button class="btn btn-success btn-sm" type="submit">
                        <i class="bi bi-check-circle me-1"></i>Active
                    </button>
                <?php else: ?>
                    <input type="hidden" name="is_open_to_work" value="1">
                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                        <i class="bi bi-circle me-1"></i>Off
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>

</div>
<!-- ── /LEFT ──────────────────────────────────────────────────────────────── -->

<!-- ── RIGHT: All editable sections ─────────────────────────────────────── -->
<div class="col-lg-8 d-flex flex-column gap-4">

    <!-- Basic Info -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-person me-2 text-primary"></i>Basic Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('/seeker/profile') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" name="full_name"
                               value="<?= e($profile['full_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone"
                               placeholder="+233 20 000 0000"
                               value="<?= e($profile['phone'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Professional Headline</label>
                        <input type="text" class="form-control" name="headline"
                               placeholder="e.g. PHP Developer | 3 Years Experience | Open to Remote"
                               maxlength="160"
                               value="<?= e($profile['headline'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">About / Bio</label>
                        <textarea class="form-control" name="bio" rows="4" maxlength="1000"
                                  placeholder="Tell employers about yourself..."><?= e($profile['bio'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="location_city"
                               placeholder="e.g. Accra"
                               value="<?= e($profile['location_city'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="location_country"
                               placeholder="e.g. Ghana"
                               value="<?= e($profile['location_country'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" class="form-control" name="years_experience"
                               min="0" max="50"
                               value="<?= e($profile['years_experience'] ?? 0) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Min. Expected Salary</label>
                        <input type="number" class="form-control" name="expected_salary_min"
                               placeholder="e.g. 2000"
                               value="<?= e($profile['expected_salary_min'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Currency</label>
                        <select class="form-select" name="salary_currency">
                            <?php foreach (['USD','GHS','EUR','GBP','NGN'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($profile['salary_currency'] ?? 'USD') === $c ? 'selected' : '' ?>>
                                <?= $c ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Social links -->
                    <div class="col-12"><hr class="my-1"></div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-linkedin me-1 text-primary"></i>LinkedIn URL</label>
                        <input type="url" class="form-control" name="linkedin_url"
                               placeholder="https://linkedin.com/in/..."
                               value="<?= e($profile['linkedin_url'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-github me-1"></i>GitHub URL</label>
                        <input type="url" class="form-control" name="github_url"
                               placeholder="https://github.com/..."
                               value="<?= e($profile['github_url'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label"><i class="bi bi-globe me-1 text-success"></i>Portfolio URL</label>
                        <input type="url" class="form-control" name="portfolio_url"
                               placeholder="https://yoursite.com"
                               value="<?= e($profile['portfolio_url'] ?? '') ?>">
                    </div>

                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Skills -->
    <div class="card border-0 shadow-sm" id="skills">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-700 mb-0"><i class="bi bi-lightning me-2 text-primary"></i>Skills</h6>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                    data-bs-target="#addSkillForm">
                <i class="bi bi-plus me-1"></i>Add Skill
            </button>
        </div>
        <div class="card-body">

            <!-- Add skill form -->
            <div class="collapse mb-3" id="addSkillForm">
                <div class="p-3 rounded-3 border bg-light">
                    <form method="POST" action="<?= url('/seeker/skills') ?>" class="row g-2">
                        <?= csrf_field() ?>
                        <div class="col-md-5">
                            <input type="text" class="form-control form-control-sm" name="skill_name"
                                   placeholder="e.g. PHP, MySQL, React..." required maxlength="80"
                                   id="skillInput">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" name="proficiency">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate" selected>Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Add</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($skills)): ?>
                <p class="text-muted small text-center py-3 mb-0">No skills added yet. Add your first skill above.</p>
            <?php else: ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($skills as $sk): ?>
                    <div class="d-flex align-items-center gap-1 rounded-pill px-3 py-1 border"
                         style="background:#f8fafc;">
                        <span class="fw-500 small"><?= e($sk['skill_name']) ?></span>
                        <span class="badge <?= $proficiencyColors[$sk['proficiency']] ?? 'bg-secondary' ?> rounded-pill ms-1"
                              style="font-size:10px;">
                            <?= ucfirst($sk['proficiency']) ?>
                        </span>
                        <form method="POST" action="<?= url('/seeker/skills/delete') ?>"
                              class="d-inline ms-1" data-no-loading>
                            <?= csrf_field() ?>
                            <input type="hidden" name="skill_id" value="<?= $sk['id'] ?>">
                            <button type="submit" class="btn btn-link p-0 text-muted"
                                    style="font-size:12px;line-height:1;"
                                    data-confirm="Remove this skill?">
                                <i class="bi bi-x"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Work Experience -->
    <div class="card border-0 shadow-sm" id="experience">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-700 mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Work Experience</h6>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                    data-bs-target="#addExpForm">
                <i class="bi bi-plus me-1"></i>Add Experience
            </button>
        </div>
        <div class="card-body">

            <!-- Add experience form -->
            <div class="collapse mb-3" id="addExpForm">
                <div class="p-3 rounded-3 border bg-light">
                    <form method="POST" action="<?= url('/seeker/experience') ?>" class="row g-3">
                        <?= csrf_field() ?>
                        <div class="col-md-6">
                            <label class="form-label">Job Title *</label>
                            <input type="text" class="form-control form-control-sm" name="job_title"
                                   placeholder="e.g. PHP Developer" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company *</label>
                            <input type="text" class="form-control form-control-sm" name="company_name"
                                   placeholder="e.g. TechCorp Ghana" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control form-control-sm" name="location"
                                   placeholder="e.g. Accra, Ghana">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" class="form-control form-control-sm" name="start_date" required>
                        </div>
                        <div class="col-md-3" id="endDateWrap">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control form-control-sm" name="end_date" id="endDate">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current"
                                       id="isCurrent" value="1"
                                       onchange="toggleEndDate(this.checked)">
                                <label class="form-check-label small" for="isCurrent">
                                    I currently work here
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control form-control-sm" name="description" rows="2"
                                      placeholder="Brief description of your role..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">Save Experience</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($exp)): ?>
                <p class="text-muted small text-center py-3 mb-0">No work experience added yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($exp as $e): ?>
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0 rounded-2 bg-light d-flex align-items-center
                                    justify-content-center border" style="width:44px;height:44px;">
                            <i class="bi bi-building text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-700"><?= e($e['job_title']) ?></div>
                            <div class="text-muted small">
                                <?= e($e['company_name']) ?>
                                <?= $e['location'] ? ' · ' . e($e['location']) : '' ?>
                            </div>
                            <div class="text-muted small">
                                <?= format_date($e['start_date'], 'M Y') ?> —
                                <?= $e['is_current'] ? '<span class="text-success fw-600">Present</span>' : format_date($e['end_date'] ?? '', 'M Y') ?>
                            </div>
                            <?php if ($e['description']): ?>
                            <p class="text-muted small mt-1 mb-0"><?= e(truncate($e['description'], 120)) ?></p>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="<?= url('/seeker/experience/delete') ?>"
                              class="flex-shrink-0" data-no-loading>
                            <?= csrf_field() ?>
                            <input type="hidden" name="exp_id" value="<?= $e['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-light text-muted"
                                    data-confirm="Remove this experience?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Education -->
    <div class="card border-0 shadow-sm" id="education">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-700 mb-0"><i class="bi bi-mortarboard me-2 text-primary"></i>Education</h6>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse"
                    data-bs-target="#addEduForm">
                <i class="bi bi-plus me-1"></i>Add Education
            </button>
        </div>
        <div class="card-body">

            <!-- Add education form -->
            <div class="collapse mb-3" id="addEduForm">
                <div class="p-3 rounded-3 border bg-light">
                    <form method="POST" action="<?= url('/seeker/education') ?>" class="row g-3">
                        <?= csrf_field() ?>
                        <div class="col-md-6">
                            <label class="form-label">Institution *</label>
                            <input type="text" class="form-control form-control-sm" name="institution"
                                   placeholder="e.g. University of Ghana" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Degree *</label>
                            <input type="text" class="form-control form-control-sm" name="degree"
                                   placeholder="e.g. BSc Computer Science" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Field of Study</label>
                            <input type="text" class="form-control form-control-sm" name="field_of_study"
                                   placeholder="e.g. Software Engineering">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start Year *</label>
                            <input type="number" class="form-control form-control-sm" name="start_year"
                                   min="1950" max="<?= date('Y') ?>"
                                   placeholder="<?= date('Y') - 4 ?>" required>
                        </div>
                        <div class="col-md-3" id="endYearWrap">
                            <label class="form-label">End Year</label>
                            <input type="number" class="form-control form-control-sm" name="end_year"
                                   min="1950" max="2035" id="endYear"
                                   placeholder="<?= date('Y') ?>">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current"
                                       id="isCurrentEdu" value="1"
                                       onchange="document.getElementById('endYear').disabled = this.checked;">
                                <label class="form-check-label small" for="isCurrentEdu">
                                    I currently study here
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Grade / GPA</label>
                            <input type="text" class="form-control form-control-sm" name="grade"
                                   placeholder="e.g. First Class / 3.8">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">Save Education</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (empty($edu)): ?>
                <p class="text-muted small text-center py-3 mb-0">No education added yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($edu as $ed): ?>
                    <div class="d-flex gap-3">
                        <div class="flex-shrink-0 rounded-2 d-flex align-items-center justify-content-center border"
                             style="width:44px;height:44px;background:#EDE9FE;">
                            <i class="bi bi-mortarboard text-purple" style="color:#7c3aed;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-700"><?= e($ed['degree']) ?></div>
                            <div class="text-muted small"><?= e($ed['institution']) ?></div>
                            <div class="text-muted small">
                                <?= $ed['start_year'] ?> —
                                <?= $ed['is_current'] ? '<span class="text-success fw-600">Present</span>' : ($ed['end_year'] ?? '?') ?>
                                <?= $ed['grade'] ? ' · ' . e($ed['grade']) : '' ?>
                            </div>
                        </div>
                        <form method="POST" action="<?= url('/seeker/education/delete') ?>"
                              class="flex-shrink-0" data-no-loading>
                            <?= csrf_field() ?>
                            <input type="hidden" name="edu_id" value="<?= $ed['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-light text-muted"
                                    data-confirm="Remove this education entry?">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>
</div>

<script>
function toggleEndDate(isCurrent) {
    const el = document.getElementById('endDate');
    if (el) el.disabled = isCurrent;
}
</script>
