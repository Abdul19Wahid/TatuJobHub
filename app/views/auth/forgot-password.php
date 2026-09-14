<div class="auth-page py-5">
<div class="container">
<div class="row justify-content-center">
<div class="col-12 col-md-8 col-lg-6 col-xl-5">
<div class="auth-card">

    <div class="text-center mb-4">
        <a href="<?= url('/') ?>" class="auth-logo">
            <i class="bi bi-briefcase-fill me-1"></i><?= APP_NAME ?>
        </a>
        <div class="mx-auto mt-4 mb-3 rounded-circle bg-primary d-flex align-items-center
                    justify-content-center" style="width:60px;height:60px;">
            <i class="bi bi-key-fill text-white fs-4"></i>
        </div>
        <h1 class="h4 fw-800 mb-1">Forgot your password?</h1>
        <p class="text-muted small">Enter your email and we'll send you a reset link.</p>
    </div>

    <form method="POST" action="<?= url('/forgot-password') ?>" novalidate>
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="form-label">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" class="form-control" name="email"
                       placeholder="you@example.com" autofocus required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 btn-lg">
            <i class="bi bi-send me-2"></i>Send Reset Link
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="<?= url('/login') ?>" class="text-muted text-decoration-none small">
            <i class="bi bi-arrow-left me-1"></i> Back to Sign In
        </a>
    </div>

    <div class="mt-4 p-3 rounded-3 border bg-light">
        <p class="small fw-600 text-muted mb-1"><i class="bi bi-info-circle me-1 text-primary"></i>Didn't get the email?</p>
        <ul class="small text-muted mb-0 ps-3">
            <li>Check your spam or junk folder</li>
            <li>Make sure you used the right email</li>
            <li>The link expires after <strong>1 hour</strong></li>
        </ul>
    </div>

</div>
</div>
</div>
</div>
