<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= APP_NAME ?> — Find Your Dream Job">
    <meta name="_csrf"    content="<?= Session::csrf() ?>">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title><?= e($title ?? 'Home') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script>window.__loggedIn = <?= is_logged_in() ? 'true' : 'false' ?>;</script>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg bg-white border-bottom sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-800 text-primary d-flex align-items-center gap-2" href="<?= url('/') ?>">
            <img src="<?= asset('img/tatu_logo.png') ?>" alt="Logo" style="height:42px;width:auto;object-fit:contain;" loading="lazy">
            <span><?= APP_NAME ?></span>
        </a>
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'],'/jobs') ? 'active':'' ?>"
                       href="<?= url('/jobs') ?>">Find Jobs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'],'/companies') ? 'active':'' ?>"
                       href="<?= url('/companies') ?>">Companies</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'],'/about') ? 'active':'' ?>"
                       href="<?= url('/about') ?>">About</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <!-- Dark mode toggle -->
                <button class="dark-toggle" onclick="toggleDark()" id="darkBtn" title="Toggle dark mode">
                    <i class="bi bi-moon-fill" id="darkIcon"></i>
                </button>

                <?php if (is_logged_in()): $u = auth(); ?>
                    <!-- Notification bell -->
                    <a href="<?= url('/'.$u['role'].'/notifications') ?>"
                       class="btn btn-light btn-sm position-relative">
                        <i class="bi bi-bell"></i>
                        <span class="notif-count-badge badge bg-danger rounded-pill"
                              style="position:absolute;top:-4px;right:-4px;font-size:9px;
                                     min-width:16px;height:16px;display:none;align-items:center;
                                     justify-content:center;padding:0 3px;"></span>
                    </a>
                    <!-- User dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2"
                                data-bs-toggle="dropdown">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center
                                        justify-content-center fw-700"
                                 style="width:30px;height:30px;font-size:12px;flex-shrink:0;">
                                <?= strtoupper(substr($u['name'],0,1)) ?>
                            </div>
                            <span class="d-none d-md-inline"><?= e(explode(' ',$u['name'])[0]) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-1">
                            <li>
                                <div class="dropdown-header">
                                    <div class="fw-700 text-dark small"><?= e($u['name']) ?></div>
                                    <div class="text-muted" style="font-size:11px;"><?= e($u['email']) ?></div>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item" href="<?= url('/'.$u['role'].'/dashboard') ?>">
                                <i class="bi bi-speedometer2 me-2 text-primary"></i>Dashboard</a></li>
                            <li><a class="dropdown-item" href="<?= url('/'.$u['role'].'/profile') ?>">
                                <i class="bi bi-person me-2 text-primary"></i>My Profile</a></li>
                            <?php if ($u['role']==='seeker'): ?>
                            <li><a class="dropdown-item" href="<?= url('/seeker/resume/preview') ?>" target="_blank">
                                <i class="bi bi-file-earmark-person me-2 text-success"></i>My Resume</a></li>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="<?= url('/account/settings') ?>">
                                <i class="bi bi-gear me-2 text-muted"></i>Account Settings</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>">
                                <i class="bi bi-box-arrow-right me-2"></i>Sign Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= url('/login') ?>"    class="btn btn-outline-primary btn-sm px-3">Sign In</a>
                    <a href="<?= url('/register') ?>" class="btn btn-primary btn-sm px-3">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- FLASH -->
<?php if (Session::hasFlash()): ?>
<div class="container mt-3"><?= flash_messages() ?></div>
<?php endif; ?>

<main><?= $content ?></main>

<!-- FOOTER -->
<footer class="bg-dark text-white mt-5 py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <div class="fw-800 fs-5 mb-2 d-flex align-items-center gap-2">
                    <img src="/public/assets/img/tatu_logo.png" alt="TaTU Logo" style="height:38px;width:auto;object-fit:contain;">
                    <?= APP_NAME ?>
                </div>
                <p class="text-secondary small">
                    Connecting talented professionals with great opportunities.
                </p>
            </div>
            <div class="col-md-3">
                <h6 class="fw-700 mb-3">For Job Seekers</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= url('/jobs') ?>"                  class="text-secondary text-decoration-none">Browse Jobs</a></li>
                    <li><a href="<?= url('/companies') ?>"             class="text-secondary text-decoration-none">Companies</a></li>
                    <li><a href="<?= url('/register?role=seeker') ?>"  class="text-secondary text-decoration-none">Create Profile</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="fw-700 mb-3">For Employers</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= url('/register?role=employer') ?>" class="text-secondary text-decoration-none">Post a Job</a></li>
                    <li><a href="<?= url('/login') ?>"                  class="text-secondary text-decoration-none">Sign In</a></li>
                </ul>
                <h6 class="fw-700 mb-3 mt-4">Support</h6>
                <ul class="list-unstyled small">
                    <li>
                        <a href="https://docs.google.com/forms/d/e/1FAIpQLSdbhcuv1fjJmLyAVUqkvTd9seLAgzrYQEvi6J8rxagNgimmAQ/viewform?usp=publish-editor"
                           target="_blank" rel="noopener noreferrer"
                           class="text-secondary text-decoration-none">
                            <i class="bi bi-chat-square-text me-1"></i>Give Feedback
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <small class="text-secondary">&copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.</small>
            <small class="text-secondary">Built As Our Final Year Project</small>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script>
// ── Dark mode ─────────────────────────────────────────────────────────────────
(function(){
    const saved = localStorage.getItem('tatuTheme') || 'light';
    applyTheme(saved);
})();
function applyTheme(t){
    document.documentElement.setAttribute('data-theme', t);
    const icon = document.getElementById('darkIcon');
    if(icon) icon.className = t==='dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    localStorage.setItem('tatuTheme', t);
}
function toggleDark(){
    const curr = document.documentElement.getAttribute('data-theme') || 'light';
    applyTheme(curr === 'dark' ? 'light' : 'dark');
}
</script>
<?php include ROOT_PATH . '/app/views/partials/chatbot.php'; ?>
</body>
</html>