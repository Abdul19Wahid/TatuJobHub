<?php $old = Session::get('_old_input', []); Session::forget('_old_input'); ?>
<style>
.auth-split{min-height:calc(100vh - 66px);display:flex;}
.auth-brand{flex:0 0 420px;background:linear-gradient(160deg,#1e1b4b 0%,#1A56DB 100%);
            display:flex;flex-direction:column;justify-content:center;padding:60px 48px;
            color:#fff;position:relative;overflow:hidden;}
.auth-brand::before{content:'';position:absolute;top:-80px;right:-80px;width:280px;height:280px;
                    border-radius:50%;background:rgba(255,255,255,.05);}
.auth-form-side{flex:1;display:flex;align-items:center;justify-content:center;
                padding:40px 24px;background:#fff;overflow-y:auto;}
.auth-form-wrap{width:100%;max-width:420px;}
.role-card{border:2px solid var(--border);border-radius:14px;padding:18px 16px;cursor:pointer;
           transition:.2s;display:flex;align-items:center;gap:14px;}
.role-card:hover,.role-card.selected{border-color:var(--primary);background:var(--primary-light);}
.role-card input{display:none;}
.role-icon{width:46px;height:46px;border-radius:10px;display:flex;align-items:center;
           justify-content:center;font-size:1.4rem;flex-shrink:0;}
@media(max-width:767px){.auth-brand{display:none;}.auth-split{display:block;background:#f9fafb;}}
</style>

<div class="auth-split">
  <!-- Brand panel -->
  <div class="auth-brand">
    <div style="position:relative;z-index:1;">
      <div class="mb-5">
        <a href="<?= url('/') ?>" class="text-white text-decoration-none fw-800 d-flex align-items-center gap-2"
           style="font-size:1.2rem;">
          <i class="bi bi-briefcase-fill"></i><?= APP_NAME ?>
        </a>
      </div>
      <h2 class="fw-800 mb-3" style="font-size:1.8rem;line-height:1.2;letter-spacing:-.5px;">
        Start your journey<br>today — it's free
      </h2>
      <p class="mb-5 opacity-75" style="line-height:1.7;font-size:14px;">
        Join thousands of professionals and top companies already on <?= APP_NAME ?>.
      </p>
      <div class="d-flex flex-column gap-3">
        <?php foreach([
          ['🔍','Seekers','Search & apply to curated jobs','#EBF5FF','#1A56DB'],
          ['🏢','Employers','Post jobs & find top talent','#EDE9FE','#7C3AED'],
        ] as [$icon,$title,$desc,$bg,$col]): ?>
        <div style="background:rgba(255,255,255,.1);border-radius:12px;padding:16px;display:flex;gap:12px;align-items:center;">
          <div style="width:40px;height:40px;border-radius:8px;background:rgba(255,255,255,.15);
                      display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
            <?= $icon ?>
          </div>
          <div>
            <div class="fw-700 small"><?= $title ?></div>
            <div style="font-size:12px;opacity:.75;"><?= $desc ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Form panel -->
  <div class="auth-form-side">
    <div class="auth-form-wrap">
      <div class="d-md-none text-center mb-4">
        <a href="<?= url('/') ?>" class="auth-logo">
          <i class="bi bi-briefcase-fill me-1"></i><?= APP_NAME ?>
        </a>
      </div>

      <h1 class="fw-800 mb-1" style="font-size:1.5rem;letter-spacing:-.5px;">Create your account</h1>
      <p class="text-muted small mb-4">Fill in the details below to get started.</p>

      <form method="POST" action="<?= url('/register') ?>" novalidate id="regForm">
        <?= csrf_field() ?>

        <!-- Role selector -->
        <div class="mb-4">
          <label class="form-label fw-600">I am a…</label>
          <div class="d-flex gap-3">
            <label class="role-card flex-1 w-50 <?= ($old['role']??'')==='employer'?'selected':'' ?>"
                   id="card_employer" onclick="selectRole('employer')">
              <input type="radio" name="role" value="employer"
                     <?= ($old['role']??'')==='employer'?'checked':'' ?>>
              <div class="role-icon" style="background:#EDE9FE;">🏢</div>
              <div>
                <div class="fw-700 small">Employer</div>
                <div class="text-muted" style="font-size:11px;">Hire top talent</div>
              </div>
            </label>
            <label class="role-card flex-1 w-50 <?= ($old['role']??'')==='seeker'?'selected':'' ?>"
                   id="card_seeker" onclick="selectRole('seeker')">
              <input type="radio" name="role" value="seeker"
                     <?= ($old['role']??'seeker')==='seeker'?'checked':'' ?>>
              <div class="role-icon" style="background:#EBF5FF;">👤</div>
              <div>
                <div class="fw-700 small">Job Seeker</div>
                <div class="text-muted" style="font-size:11px;">Find my next role</div>
              </div>
            </label>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control" name="full_name"
                   placeholder="Your full name"
                   value="<?= e($old['full_name'] ?? '') ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" name="email"
                   placeholder="you@example.com"
                   value="<?= e($old['email'] ?? '') ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" name="password"
                   id="regPw" placeholder="Min 8 characters" required>
            <button type="button" class="input-group-text password-toggle" onclick="togglePw()">
              <i class="bi bi-eye" id="pwIcon"></i>
            </button>
          </div>
          <div class="d-flex gap-1 mt-2" id="pwStrength"></div>
        </div>

        <div class="mb-3">
          <label class="form-label">Phone Number <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-phone"></i></span>
            <input type="tel" class="form-control" name="phone_number"
                   placeholder="e.g. 0241234567"
                   value="<?= e($old['phone_number'] ?? '') ?>" required>
          </div>
          <div class="form-text">Used for SMS notifications. Ghana format: 024XXXXXXX</div>
        </div>

        <div class="mb-4">
          <label class="form-label">Confirm Password</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" class="form-control" name="password_confirmation"
                   placeholder="Repeat password" required>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-lg fw-700">
          Create Account <i class="bi bi-arrow-right ms-1"></i>
        </button>

        <p class="text-center text-muted mt-3 small">
          By registering you agree to our
          <a href="#" class="text-primary text-decoration-none">Terms of Service</a> and
          <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>.
        </p>
      </form>

      <div class="auth-divider my-4">or</div>

      <a href="<?= url('/auth/google?role=seeker') ?>" id="googleSignupBtn"
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
        Already have an account?
        <a href="<?= url('/login') ?>" class="text-primary fw-700 text-decoration-none">Sign in</a>
      </p>
    </div>
  </div>
</div>

<script>
function selectRole(r){
  document.querySelectorAll('.role-card').forEach(c=>c.classList.remove('selected'));
  document.getElementById('card_'+r).classList.add('selected');
  document.querySelector(`input[value="${r}"]`).checked=true;
  const gBtn=document.getElementById('googleSignupBtn');
  if(gBtn) gBtn.href = gBtn.href.split('?')[0] + '?role=' + r;
}
function togglePw(){
  const el=document.getElementById('regPw'),ic=document.getElementById('pwIcon');
  const t=el.type==='text';el.type=t?'password':'text';ic.className=t?'bi bi-eye':'bi bi-eye-slash';
}
// Password strength indicator
document.getElementById('regPw')?.addEventListener('input',function(){
  const pw=this.value,bar=document.getElementById('pwStrength');
  let s=0;
  if(pw.length>=8)s++;if(/[A-Z]/.test(pw))s++;if(/[0-9]/.test(pw))s++;if(/[^A-Za-z0-9]/.test(pw))s++;
  const cols=['#EF4444','#F59E0B','#10B981','#059669'];
  const lbls=['Weak','Fair','Good','Strong'];
  bar.innerHTML=`<span style="flex:1;height:4px;border-radius:4px;background:${cols[s-1]||'#e5e7eb'};transition:.3s;"></span>
    <span style="font-size:11px;color:${cols[s-1]||'#9ca3af'};font-weight:600;">${s>0?lbls[s-1]:''}</span>`;
});
// Pre-select role from URL param
const rp=new URLSearchParams(window.location.search).get('role');
if(rp==='employer')selectRole('employer');
</script>
