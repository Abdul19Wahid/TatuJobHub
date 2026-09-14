<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Company Profile</h1>
        <p class="text-muted small mb-0">Keep your company info accurate to attract top candidates.</p>
    </div>
    <span class="badge <?= match($ep['verification_status']) {
        'approved' => 'bg-success',
        'pending'  => 'bg-warning text-dark',
        'rejected' => 'bg-danger',
        default    => 'bg-secondary'
    } ?> px-3 py-2">
        <?= ucfirst($ep['verification_status']) ?>
    </span>
</div>

<div class="row g-4">

<!-- LEFT -->
<div class="col-lg-4">
    <!-- Logo card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body text-center p-4">
            <div class="mb-3">
                <?php if ($ep['logo_path']): ?>
                    <img src="<?= url('/file?path=' . urlencode($ep['logo_path'])) ?>"
                         alt="Logo" style="width:100px;height:100px;border-radius:16px;object-fit:contain;border:1px solid #e5e7eb;padding:8px;">
                <?php else: ?>
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary text-white fw-800"
                         style="width:100px;height:100px;font-size:36px;">
                        <?= strtoupper(substr($ep['company_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>
            <h6 class="fw-800 mb-1"><?= e($ep['company_name']) ?></h6>
            <?php if ($ep['industry']): ?>
            <p class="text-muted small mb-3"><?= e($ep['industry']) ?></p>
            <?php endif; ?>

            <form method="POST" action="<?= url('/employer/profile/logo') ?>"
                  enctype="multipart/form-data" data-no-loading>
                <?= csrf_field() ?>
                <label class="btn btn-outline-primary btn-sm w-100" style="cursor:pointer;">
                    <i class="bi bi-camera me-1"></i>Upload Logo
                    <input type="file" name="logo" accept="image/*" class="d-none"
                           onchange="this.form.submit()">
                </label>
            </form>
            <p class="text-muted mt-2 mb-0" style="font-size:11px;">JPG, PNG, WEBP · Max 2MB</p>
        </div>
    </div>

    <!-- Verification card -->
    <?php if ($ep['verification_status'] !== 'approved'): ?>
    <div class="card border-0 shadow-sm mb-4"
         style="border-left:4px solid <?= $ep['verification_status']==='pending' ? '#F59E0B' : '#EF4444' ?>!important;">
        <div class="card-body p-4">
            <h6 class="fw-700 mb-2">
                <i class="bi bi-shield-check me-2 text-<?= $ep['verification_status']==='pending' ? 'warning' : 'danger' ?>"></i>
                <?= $ep['verification_status'] === 'pending' ? 'Pending Review' : 'Not Verified' ?>
            </h6>
            <p class="text-muted small mb-3">
                <?= $ep['verification_status'] === 'pending'
                    ? 'Your verification request is being reviewed.'
                    : 'Submit your business registration to unlock job posting.' ?>
            </p>

            <?php if ($ep['verification_status'] !== 'pending'): ?>
            <form method="POST" action="<?= url('/employer/profile/verify') ?>"
                  enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label small fw-600">Document Type</label>
                    <select class="form-select form-select-sm" name="document_type">
                        <option>Business Registration Certificate</option>
                        <option>Tax Identification Certificate</option>
                        <option>Company Incorporation Document</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-600">Upload Document</label>
                    <input type="file" class="form-control form-control-sm" name="document"
                           accept=".pdf,.jpg,.jpeg,.png">
                    <div class="form-text">PDF or image · Max 5MB</div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-600">Notes to Admin</label>
                    <textarea class="form-control form-control-sm" name="notes" rows="2"
                              placeholder="Any additional information..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-send me-2"></i>Submit for Verification
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- RIGHT -->
<div class="col-lg-8">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0"><i class="bi bi-building me-2 text-primary"></i>Company Information</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('/employer/profile') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Contact Person Name</label>
                        <input type="text" class="form-control" name="contact_name"
                               value="<?= e($ep['full_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="company_name"
                               value="<?= e($ep['company_name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Industry</label>
                        <select class="form-select" name="industry">
                            <option value="">Select industry</option>
                            <?php foreach (['Information Technology','Finance','Healthcare','Education',
                                'Sales & Marketing','Engineering','Legal','Logistics','Design',
                                'Manufacturing','Hospitality','Real Estate','Other'] as $ind): ?>
                            <option value="<?= $ind ?>" <?= ($ep['industry'] ?? '') === $ind ? 'selected' : '' ?>>
                                <?= $ind ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Company Size</label>
                        <select class="form-select" name="company_size">
                            <option value="">Select size</option>
                            <?php foreach (['1-10','11-50','51-200','201-500','501-1000','1000+'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($ep['company_size'] ?? '') === $s ? 'selected' : '' ?>>
                                <?= $s ?> employees
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" class="form-control" name="phone"
                               placeholder="+233 30 000 0000"
                               value="<?= e($ep['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Founded Year</label>
                        <input type="number" class="form-control" name="founded_year"
                               min="1800" max="<?= date('Y') ?>"
                               placeholder="e.g. 2010"
                               value="<?= e($ep['founded_year'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">City</label>
                        <input type="text" class="form-control" name="location_city"
                               placeholder="e.g. Accra"
                               value="<?= e($ep['location_city'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Country</label>
                        <input type="text" class="form-control" name="location_country"
                               placeholder="e.g. Ghana"
                               value="<?= e($ep['location_country'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Full Address</label>
                        <input type="text" class="form-control" name="full_address"
                               placeholder="Street address"
                               value="<?= e($ep['full_address'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Company Website</label>
                        <input type="url" class="form-control" name="website"
                               placeholder="https://yourcompany.com"
                               value="<?= e($ep['website'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">About the Company</label>
                        <textarea class="form-control" name="description" rows="5"
                                  maxlength="1000"
                                  placeholder="Tell candidates about your company, culture, and mission..."><?= e($ep['description'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-linkedin me-1 text-primary"></i>LinkedIn URL</label>
                        <input type="url" class="form-control" name="linkedin_url"
                               placeholder="https://linkedin.com/company/..."
                               value="<?= e($ep['linkedin_url'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><i class="bi bi-twitter me-1" style="color:#1DA1F2;"></i>Twitter URL</label>
                        <input type="url" class="form-control" name="twitter_url"
                               placeholder="https://twitter.com/..."
                               value="<?= e($ep['twitter_url'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-2"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
