<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('/admin/users') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <h1 class="h4 fw-800 mb-0">User — <?= e($user['full_name']) ?></h1>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center fw-800 mb-3"
                     style="width:72px;height:72px;font-size:28px;background:#EBF5FF;color:#1A56DB;">
                    <?= strtoupper(substr($user['full_name'],0,1)) ?>
                </div>
                <h5 class="fw-800 mb-1"><?= e($user['full_name']) ?></h5>
                <p class="text-muted small mb-2"><?= e($user['email']) ?></p>
                <span class="badge rounded-pill px-3 py-2 mb-3"
                      style="background:#EBF5FF;color:#1A56DB;">
                    <?= ucfirst($user['role']) ?>
                </span>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $user['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                    <span class="badge <?= $user['email_verified'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $user['email_verified'] ? 'Email Verified' : 'Unverified' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-700 mb-0">Account Details</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-borderless small mb-0 user-detail-table">
                    <tr><th class="text-muted" style="width:180px;">User ID</th><td><?= $user['id'] ?></td></tr>
                    <tr><th class="text-muted">Role</th><td><?= ucfirst($user['role']) ?></td></tr>
                    <tr><th class="text-muted">Joined</th><td><?= format_date($user['created_at'], 'F j, Y H:i') ?></td></tr>
                    <tr><th class="text-muted">Last Login</th><td><?= $user['last_login_at'] ? format_date($user['last_login_at'], 'F j, Y H:i') : 'Never' ?></td></tr>
                    <?php if ($profile && $user['role'] === 'employer'): ?>
                    <tr><th class="text-muted">Company</th><td><?= e($profile['company_name']) ?></td></tr>
                    <tr><th class="text-muted">Verified</th><td><?= ucfirst($profile['verification_status']) ?></td></tr>
                    <?php elseif ($profile && $user['role'] === 'seeker'): ?>
                    <tr><th class="text-muted">Location</th><td><?= e($profile['location_city'] ?? '—') ?></td></tr>
                    <tr><th class="text-muted">Experience</th><td><?= $profile['years_experience'] ?? 0 ?> years</td></tr>
                    <?php endif; ?>
                </table>
                </div>
            </div>
        </div>
    </div>
</div>
