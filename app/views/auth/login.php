<?php $old = Session::get('_old_input', []); Session::forget('_old_input'); ?>
<style>
.auth-split{min-height:calc(100vh - 66px);display:flex;}
.auth-brand{flex:0 0 440px;position:relative;overflow:hidden;
            background:linear-gradient(160deg,#0F172A 0%,#1e1b4b 50%,#1A56DB 100%);
            display:flex;flex-direction:column;justify-content:center;padding:60px 48px;color:#fff;}
.auth-brand .orb{position:absolute;border-radius:50%;filter:blur(60px);opacity:.3;}
.auth-form-side{flex:1;display:flex;align-items:center;justify-content:center;
                padding:40px 24px;background:#fff;overflow-y:auto;}
[data-theme="dark"] .auth-form-side{background:#0F172A;}
.auth-form-wrap{width:100%;max-width:400px;}
.feature-row{display:flex;align-items:center;gap:12px;margin-bottom:16px;}
.feature-icon{width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.12);
              display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.1rem;}
.social-proof{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);
              border-radius:14px;padding:16px;margin-top:32px;}
.avatar-stack{display:flex;margin-bottom:8px;}
.avatar-stack span{width:30px;height:30px;border-radius:50%;border:2px solid rgba(255,255,255,.3);
                   display:flex;align-items:center;justify-content:center;font-weight:700;
                   font-size:11px;margin-left:-8px;color:#fff;}
.avatar-stack span:first-child{margin-left:0;}
@media(max-width:767px){.auth-brand{display:none;}.auth-split{background:var(--bg);display:block;}}
</style>

<div class="auth-split">
  <!-- Brand side -->
  <div class="auth-brand">
    <div class="orb" style="width:300px;height:300px;background:#4F8EF7;top:-80px;right:-80px;"></div>
    <div class="orb" style="width:200px;height:200px;background:#7C3AED;bottom:-60px;left:-60px;"></div>

    <div style="position:relative;z-index:1;">
      <a href="<?= url('/') ?>" class="text-white text-decoration-none fw-800 d-flex align-items-center gap-2 mb-5"
         style="font-size:1.2rem;">
        <i class="bi bi-briefcase-fill"></i><?= APP_NAME ?>
      </a>
      <h2 class="fw-800 mb-3" style="font-size:1.9rem;letter-spacing:-.5px;line-height:1.2;">
        Ghana's #1<br>Job Platform
      </h2>
      <p class="mb-5" style="opacity:.75;font-size:14px;line-height:1.7;">
        Connect with top employers and discover thousands of opportunities across every industry.
      </p>
      <?php foreach([
        ['🎯','Personalised job recommendations'],
        ['⚡','Instant email job alerts'],
        ['🏢','Verified companies only'],
        ['📄','One-click apply with your profile'],
      ] as [$ic,$tx]): ?>
      <div class="feature-row">
        <div class="feature-icon"><?= $ic ?></div>
        <span style="font-size:13.5px;opacity:.9;"><?= $tx ?></span>
      </div>
      <?php endforeach; ?>

      <div class="social-proof">
        <div class="avatar-stack">
          <?php foreach([['K','#1A56DB'],['A','#7C3AED'],['K','#057A55'],['E','#D97706']] as [$l,$c]): ?>
          <span style="background:<?= $c ?>"><?= $l ?></span>
          <?php endforeach; ?>
        </div>
        <div style="font-size:13px;opacity:.85;">
          <strong>500+</strong> professionals joined this month
        </div>
      </div>
    </div>
  </div>

  <!-- Form side -->
  <div class="auth-form-side">
    <div class="auth-form-wrap">
      <div class="d-md-none text-center mb-4">
        <a href="<?= url('/') ?>" class="auth-logo">
          <i class="bi bi-briefcase-fill me-1"></i><?= APP_NAME ?>
        </a>
      </div>

      <h1 class="fw-800 mb-1" style="font-size:1.6rem;letter-spacing:-.5px;">Welcome back 👋</h1>
      <p class="text-muted small mb-4">Sign in to continue to your account.</p>

      <form method="POST" action="<?= url('/login') ?>" novalidate id="loginForm">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email"
                   placeholder="you@example.com"
                   value="<?= e($old['email'] ?? '') ?>" autofocus required>
          </div>
        </div>

        <div class="mb-1">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password"
                   id="pw" placeholder="Your password" required>
            <button type="button" class="password-toggle input-group-text" onclick="togglePw('pw','pwI')">
              <i class="bi bi-eye" id="pwI"></i>
            </button>
          </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
          <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label small" for="remember">Remember me</label>
          </div>
          <a href="<?= url('/forgot-password') ?>" class="text-primary text-decoration-none small fw-600">
            Forgot password?
          </a>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-700" style="height:48px;font-size:15px;">
          Sign In <i class="bi bi-arrow-right ms-1"></i>
        </button>
      </form>

      <?php if(defined('APP_DEBUG') && APP_DEBUG): ?>
      <div class="mt-4 p-3 rounded-3 border" style="background:var(--bg);">
        <p class="fw-700 small mb-2 text-muted"><i class="bi bi-info-circle me-1"></i>Demo accounts — use the password shown for each role.</p>
        <div class="d-flex flex-column gap-1">
          <?php foreach([
            ['admin@jobportal.com','🔑 Admin','Admin@1234'],
            ['kwame@example.com','🧑 Seeker','Seeker@1234'],
            ['ama@techcorp.com','🏢 Employer','Employer@1234'],
          ] as [$e,$l,$p]): ?>
          <button class="btn btn-sm btn-outline-secondary text-start" onclick="fillLogin('<?= $e ?>','<?= $p ?>')">
            <?= $l ?> · <span class="text-muted"><?= $e ?></span>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="auth-divider my-4">or</div>

      <a href="<?= url('/auth/google') ?>"
         class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2 fw-600 mb-4"
         style="height:46px;font-size:14px;">
        <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
          <path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/>
          <path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.84.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.98v2.33A9 9 0 0 0 9 18z"/>
          <path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.98A9 9 0 0 0 0 9c0 1.45.35 2.83.98 4.03l2.97-2.33z"/>
          <path fill="#EA4335" d="M9 3.58c1.32 0 2.51.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .98 4.97l2.97 2.33C4.66 5.17 6.65 3.58 9 3.58z"/>
        </svg>
        Continue with Google
      </a>

      <p class="text-center text-muted small mb-0">
        Don't have an account?
        <a href="<?= url('/register') ?>" class="text-primary fw-700 text-decoration-none">Create one free →</a>
      </p>
    </div>
  </div>
</div>

<script>
function togglePw(id,iconId){
  const el=document.getElementById(id),ic=document.getElementById(iconId);
  const t=el.type==='text'; el.type=t?'password':'text';
  ic.className=t?'bi bi-eye':'bi bi-eye-slash';
}
function fillLogin(email,password){
  document.querySelector('input[name="email"]').value=email;
  document.getElementById('pw').value=password;
}
</script>
