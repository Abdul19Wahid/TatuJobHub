<?php
$isEdit = ($mode ?? 'create') === 'edit';
$j      = $job ?? [];
$jobTypes = ['full_time'=>'Full-Time','part_time'=>'Part-Time','contract'=>'Contract',
             'internship'=>'Internship','freelance'=>'Freelance','volunteer'=>'Volunteer'];
$expLevels = ['entry'=>'Entry Level','junior'=>'Junior','mid'=>'Mid-Level',
              'senior'=>'Senior','lead'=>'Lead','executive'=>'Executive'];
$periods   = ['hourly'=>'Per Hour','monthly'=>'Per Month','yearly'=>'Per Year'];
$currencies= ['USD'=>'USD ($)','GHS'=>'GHS (₵)','EUR'=>'EUR (€)','GBP'=>'GBP (£)','NGN'=>'NGN (₦)'];
$industries= ['Information Technology','Finance','Healthcare','Education','Sales & Marketing',
              'Engineering','Legal','Logistics','Design','Human Resources','Manufacturing',
              'Hospitality','Media & Communications','Real Estate','Other'];
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('/employer/jobs') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <div>
        <h1 class="h4 fw-800 mb-0"><?= $isEdit ? 'Edit Job Listing' : 'Post a New Job' ?></h1>
        <p class="text-muted small mb-0">
            <?= $isEdit ? 'Update the details of your job listing.' : 'Fill in the details below to publish your job.' ?>
        </p>
    </div>
</div>

<form method="POST"
      action="<?= $isEdit ? url('/employer/jobs/' . $j['id'] . '/update') : url('/employer/jobs') ?>">
    <?= csrf_field() ?>

<div class="row g-4">

<!-- ── LEFT COLUMN ──────────────────────────────────────────────────────── -->
<div class="col-lg-8">

    <!-- Basic details -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-briefcase me-2 text-primary"></i>Job Details</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Job Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="title"
                           placeholder="e.g. Senior PHP Developer"
                           value="<?= e($j['title'] ?? '') ?>" required maxlength="160">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Job Type <span class="text-danger">*</span></label>
                    <select class="form-select" name="job_type" required>
                        <?php foreach ($jobTypes as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($j['job_type'] ?? 'full_time') === $v ? 'selected' : '' ?>>
                            <?= $l ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Experience Level <span class="text-danger">*</span></label>
                    <select class="form-select" name="experience_level" required>
                        <?php foreach ($expLevels as $v => $l): ?>
                        <option value="<?= $v ?>" <?= ($j['experience_level'] ?? 'entry') === $v ? 'selected' : '' ?>>
                            <?= $l ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Industry</label>
                    <select class="form-select" name="industry">
                        <option value="">Select industry</option>
                        <?php foreach ($industries as $ind): ?>
                        <option value="<?= $ind ?>" <?= ($j['industry'] ?? '') === $ind ? 'selected' : '' ?>>
                            <?= $ind ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Number of Vacancies</label>
                    <input type="number" class="form-control" name="vacancies"
                           min="1" max="999" value="<?= e($j['vacancies'] ?? 1) ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">Required Skills <span class="text-muted small fw-400">(comma-separated)</span></label>
                    <input type="text" class="form-control" name="skills"
                           placeholder="e.g. PHP, MySQL, Laravel, JavaScript"
                           value="<?= e($j['skills_csv'] ?? '') ?>">
                    <div class="form-text">Separate each skill with a comma</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Description -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-file-text me-2 text-primary"></i>Job Description</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" name="description" rows="7" required
                              placeholder="Provide a detailed overview of this role..."><?= e($j['description'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Responsibilities</label>
                    <textarea class="form-control" name="responsibilities" rows="4"
                              placeholder="List the key responsibilities..."><?= e($j['responsibilities'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Requirements</label>
                    <textarea class="form-control" name="requirements" rows="4"
                              placeholder="List the qualifications and requirements..."><?= e($j['requirements'] ?? '') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Benefits <span class="text-muted small fw-400">(optional)</span></label>
                    <textarea class="form-control" name="benefits" rows="3"
                              placeholder="e.g. Health insurance, remote work, annual bonus..."><?= e($j['benefits'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ── RIGHT COLUMN ──────────────────────────────────────────────────────── -->
<div class="col-lg-4">

    <!-- Location -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-geo-alt me-2 text-primary"></i>Location</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" name="is_remote"
                               id="isRemote" value="1"
                               <?= !empty($j['is_remote']) ? 'checked' : '' ?>
                               onchange="toggleLocation(this.checked)">
                        <label class="form-check-label fw-600" for="isRemote">
                            Remote Position
                        </label>
                    </div>
                </div>
                <div id="locationFields" style="<?= !empty($j['is_remote']) ? 'opacity:.5;' : '' ?>">
                    <div class="col-12 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="location_city"
                               placeholder="e.g. Accra"
                               value="<?= e($j['location_city'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="location_country"
                               placeholder="e.g. Ghana"
                               value="<?= e($j['location_country'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Salary -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-cash-stack me-2 text-primary"></i>Salary</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="salary_is_hidden"
                               id="salaryHidden" value="1"
                               <?= !empty($j['salary_is_hidden']) ? 'checked' : '' ?>
                               onchange="document.getElementById('salaryFields').style.opacity=this.checked?'.4':'1'">
                        <label class="form-check-label small text-muted" for="salaryHidden">
                            Show "Competitive" instead of range
                        </label>
                    </div>
                </div>
                <div id="salaryFields" style="<?= !empty($j['salary_is_hidden']) ? 'opacity:.4;' : '' ?>">
                    <div class="col-12 mb-3">
                        <label class="form-label">Currency</label>
                        <select class="form-select" name="salary_currency">
                            <?php foreach ($currencies as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($j['salary_currency'] ?? 'USD') === $v ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6 mb-3" style="display:inline-block;width:48%;">
                        <label class="form-label">Min Salary</label>
                        <input type="number" class="form-control" name="salary_min"
                               placeholder="e.g. 2000"
                               value="<?= e($j['salary_min'] ?? '') ?>">
                    </div>
                    <div class="col-6 mb-3" style="display:inline-block;width:48%;margin-left:4%;">
                        <label class="form-label">Max Salary</label>
                        <input type="number" class="form-control" name="salary_max"
                               placeholder="e.g. 4000"
                               value="<?= e($j['salary_max'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Period</label>
                        <select class="form-select" name="salary_period">
                            <?php foreach ($periods as $v => $l): ?>
                            <option value="<?= $v ?>" <?= ($j['salary_period'] ?? 'monthly') === $v ? 'selected' : '' ?>>
                                <?= $l ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Application settings -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-gear me-2 text-primary"></i>Settings</h6>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Application Deadline</label>
                <input type="date" class="form-control" name="application_deadline"
                       value="<?= e($j['application_deadline'] ?? '') ?>"
                       min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                <div class="form-text">Leave blank for no deadline</div>
            </div>
        </div>
    </div>

    <!-- Submit -->
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg fw-700">
            <i class="bi bi-<?= $isEdit ? 'check2' : 'rocket-takeoff' ?> me-2"></i>
            <?= $isEdit ? 'Save Changes' : 'Publish Job Listing' ?>
        </button>
        <a href="<?= url('/employer/jobs') ?>" class="btn btn-light">Cancel</a>
    </div>

</div>
</div>
</form>

<script>
function toggleLocation(isRemote) {
    document.getElementById('locationFields').style.opacity = isRemote ? '.5' : '1';
}
</script>
