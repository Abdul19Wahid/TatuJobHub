<div class="auth-page py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-6 col-xl-5">
<div class="auth-card">

    <div class="text-center mb-4">
        <a href="<?= url('/') ?>" class="auth-logo">
            <i class="bi bi-briefcase-fill me-1"></i><?= APP_NAME ?>
        </a>
        <div class="mx-auto mt-4 mb-3 rounded-circle bg-success d-flex align-items-center
                    justify-content-center" style="width:60px;height:60px;">
            <i class="bi bi-shield-lock-fill text-white fs-4"></i>
        </div>
        <h1 class="h4 fw-800 mb-1">Set a new password</h1>
        <p class="text-muted small">Choose a strong password for your account.</p>
    </div>

    <form method="POST" action="<?= url('/reset-password') ?>" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">

        <div class="mb-3">
            <label class="form-label">New Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" class="form-control" name="password"
                       id="npw" placeholder="Min. 8 characters"
                       autofocus required oninput="checkStr(this.value)">
                <button type="button" class="password-toggle input-group-text"
                        onclick="togglePw('npw',this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <div id="sBar" style="height:4px;border-radius:2px;margin-top:6px;width:0;background:#e5e7eb;transition:all .3s;"></div>
            <small id="sLbl" style="font-size:11.5px;"></small>
        </div>

        <div class="mb-4">
            <label class="form-label">Confirm New Password <span class="text-danger">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input type="password" class="form-control" name="password_confirmation"
                       id="npw2" placeholder="Repeat your password" required>
                <button type="button" class="password-toggle input-group-text"
                        onclick="togglePw('npw2',this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <small id="mMsg" style="font-size:11.5px;display:block;margin-top:4px;"></small>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="bi bi-check2-circle me-2"></i>Update Password
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="<?= url('/login') ?>" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>

</div>
</div>
</div>
</div>

<script>
function togglePw(id, btn) {
    const el = document.getElementById(id);
    const isText = el.type === 'text';
    el.type = isText ? 'password' : 'text';
    btn.querySelector('i').className = isText ? 'bi bi-eye' : 'bi bi-eye-slash';
}
function checkStr(v) {
    let s = 0;
    if (v.length >= 8)           s++;
    if (/[A-Z]/.test(v))         s++;
    if (/[0-9]/.test(v))         s++;
    if (/[^A-Za-z0-9]/.test(v))  s++;
    const c = ['','#ef4444','#f59e0b','#3b82f6','#10b981'][s];
    const bar = document.getElementById('sBar');
    bar.style.width = (s*25)+'%'; bar.style.background = c;
    const lbl = document.getElementById('sLbl');
    lbl.textContent = ['','Weak','Fair','Good','✓ Strong'][s];
    lbl.style.color = c;
}
document.getElementById('npw2').addEventListener('input', function() {
    const m = document.getElementById('mMsg');
    if (!this.value) { m.textContent=''; return; }
    const ok = this.value === document.getElementById('npw').value;
    m.textContent = ok ? '✓ Passwords match' : '✗ Passwords do not match';
    m.style.color = ok ? '#10b981' : '#ef4444';
});
</script>
