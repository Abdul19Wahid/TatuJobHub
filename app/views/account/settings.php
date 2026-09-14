<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Account Settings</h1>
        <p class="text-muted small mb-0">Manage your login credentials and preferences.</p>
    </div>
</div>

<div class="row g-4">
<div class="col-lg-8">

    <!-- Change Password -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0">
                <i class="bi bi-lock me-2 text-primary"></i>Change Password
            </h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('/account/password') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Current Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="current_password"
                                   placeholder="Your current password" required id="curPw">
                            <button type="button" class="password-toggle input-group-text"
                                    onclick="togglePw('curPw',this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" name="new_password"
                                   placeholder="Min. 8 characters" required id="newPw"
                                   oninput="checkStr(this.value)">
                            <button type="button" class="password-toggle input-group-text"
                                    onclick="togglePw('newPw',this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div id="strBar" style="height:3px;border-radius:2px;margin-top:5px;
                                                width:0;background:#e5e7eb;transition:all .3s;"></div>
                        <small id="strLbl" style="font-size:11px;"></small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" name="confirm_password"
                                   placeholder="Repeat new password" required id="conPw">
                            <button type="button" class="password-toggle input-group-text"
                                    onclick="togglePw('conPw',this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <small id="matchMsg" style="font-size:11px;display:block;margin-top:3px;"></small>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>Update Password
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Email -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0">
                <i class="bi bi-envelope me-2 text-primary"></i>Change Email Address
            </h6>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">
                Current email: <strong><?= e($user['email']) ?></strong>
            </p>
            <form method="POST" action="<?= url('/account/email') ?>">
                <?= csrf_field() ?>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">New Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" name="new_email"
                                   placeholder="new@example.com" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Confirm with Password <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" name="confirm_password"
                                   placeholder="Your current password" required id="emailPw">
                            <button type="button" class="password-toggle input-group-text"
                                    onclick="togglePw('emailPw',this)">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2 me-1"></i>Update Email
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Notification Preferences -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-700 mb-0">
                <i class="bi bi-bell me-2 text-primary"></i>Notification Preferences
            </h6>
        </div>
        <div class="card-body">
            <?php $prefs = Session::get('notif_prefs', [
                'email_applications' => true,
                'email_alerts'       => true,
                'email_interviews'   => true,
            ]); ?>
            <form method="POST" action="<?= url('/account/notifications') ?>">
                <?= csrf_field() ?>
                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-2 border">
                        <div>
                            <div class="fw-600 small">Application Updates</div>
                            <div class="text-muted" style="font-size:12px;">
                                Email me when my application status changes
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="email_applications" id="sw1" value="1"
                                   <?= !empty($prefs['email_applications']) ? 'checked' : '' ?>
                                   style="width:44px;height:22px;cursor:pointer;">
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-2 border">
                        <div>
                            <div class="fw-600 small">Job Alerts</div>
                            <div class="text-muted" style="font-size:12px;">
                                Email me when matching jobs are posted
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="email_alerts" id="sw2" value="1"
                                   <?= !empty($prefs['email_alerts']) ? 'checked' : '' ?>
                                   style="width:44px;height:22px;cursor:pointer;">
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-2 border">
                        <div>
                            <div class="fw-600 small">Interview Reminders</div>
                            <div class="text-muted" style="font-size:12px;">
                                Email me when an interview is scheduled
                            </div>
                        </div>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="email_interviews" id="sw3" value="1"
                                   <?= !empty($prefs['email_interviews']) ? 'checked' : '' ?>
                                   style="width:44px;height:22px;cursor:pointer;">
                        </div>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check2 me-1"></i>Save Preferences
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card border-0 shadow-sm" style="border-left:4px solid #dc2626 !important;">
        <div class="card-header py-3" style="background:#fef2f2;border-bottom:1px solid #fecaca;">
            <h6 class="fw-700 mb-0 text-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>Danger Zone
            </h6>
        </div>
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-700">Delete My Account</div>
                    <div class="text-muted small">
                        Permanently delete your account and all associated data.
                        This action <strong>cannot be undone</strong>.
                    </div>
                </div>
                <button type="button" class="btn btn-danger flex-shrink-0"
                        data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="bi bi-trash me-2"></i>Delete Account
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Right: Account Info -->
<div class="col-lg-4">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 text-center">
            <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center
                        justify-content-center fw-800 mb-3"
                 style="width:72px;height:72px;font-size:28px;">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <h6 class="fw-800 mb-1"><?= e($user['full_name']) ?></h6>
            <p class="text-muted small mb-1"><?= e($user['email']) ?></p>
            <span class="badge rounded-pill px-3 py-1 mb-3"
                  style="background:#EBF5FF;color:#1A56DB;font-size:11px;">
                <?= ucfirst($user['role']) ?>
            </span>
            <div class="d-flex flex-column gap-2 text-start mt-3 pt-3 border-top">
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Member since</span>
                    <span class="fw-600"><?= format_date($user['created_at'], 'M Y') ?></span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Email verified</span>
                    <span class="fw-600 <?= $user['email_verified'] ? 'text-success' : 'text-warning' ?>">
                        <?= $user['email_verified'] ? '✓ Yes' : '⚠ No' ?>
                    </span>
                </div>
                <div class="d-flex justify-content-between small">
                    <span class="text-muted">Last login</span>
                    <span class="fw-600">
                        <?= $user['last_login_at'] ? time_ago($user['last_login_at']) : 'N/A' ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger border-0 rounded-3 mb-4">
                    <strong>Warning:</strong> This will permanently delete your account,
                    profile, applications, and all associated data. This cannot be undone.
                </div>
                <form method="POST" action="<?= url('/account/delete') ?>" id="deleteForm">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Enter your password to confirm deletion:
                        </label>
                        <input type="password" class="form-control" name="confirm_password"
                               placeholder="Your password" required id="delPw">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-600 small text-muted">
                            Type <strong>DELETE</strong> to confirm:
                        </label>
                        <input type="text" class="form-control" id="deleteConfirm"
                               placeholder="Type DELETE" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1"
                                data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger flex-grow-1"
                                id="deleteBtn" disabled>
                            <i class="bi bi-trash me-2"></i>Delete Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Password visibility toggle
function togglePw(id, btn) {
    const el = document.getElementById(id);
    const isText = el.type === 'text';
    el.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Password strength
function checkStr(v) {
    let s = 0;
    if (v.length >= 8) s++; if (/[A-Z]/.test(v)) s++;
    if (/[0-9]/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v)) s++;
    const c = ['','#ef4444','#f59e0b','#3b82f6','#10b981'][s];
    const bar = document.getElementById('strBar');
    bar.style.width = (s*25)+'%'; bar.style.background = c;
    const lbl = document.getElementById('strLbl');
    lbl.textContent = ['','Weak','Fair','Good','✓ Strong'][s];
    lbl.style.color = c;
}

// Password match
document.getElementById('conPw').addEventListener('input', function() {
    const m = document.getElementById('matchMsg');
    if (!this.value) { m.textContent = ''; return; }
    const ok = this.value === document.getElementById('newPw').value;
    m.textContent = ok ? '✓ Passwords match' : '✗ Do not match';
    m.style.color = ok ? '#10b981' : '#ef4444';
});

// Delete confirmation
document.getElementById('deleteConfirm').addEventListener('input', function() {
    document.getElementById('deleteBtn').disabled = this.value !== 'DELETE';
});
</script>
