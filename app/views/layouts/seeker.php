<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="_csrf"    content="<?= Session::csrf() ?>">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title><?= e($title ?? 'Dashboard') ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
    <script>window.__loggedIn = true;</script>
</head>
<body>
<div class="dashboard-wrapper">

<!-- SIDEBAR -->
<aside class="sidebar d-none d-lg-flex flex-column">
    <div class="sidebar-logo">
        <a href="<?= url('/') ?>" class="d-flex align-items-center gap-2 fw-800 text-primary text-decoration-none"><img src="/public/assets/img/tatu_logo.png" alt="TaTU Logo" style="height:42px;width:auto;object-fit:contain;"><?= APP_NAME ?></a>
    </div>
    <?php
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    function sl(string $href, string $icon, string $label, string $uri): string {
        $p = parse_url($href, PHP_URL_PATH);
        $a = ($uri === $p || str_starts_with($uri, $p.'/'));
        return '<li><a href="'.$href.'" class="'.($a?'active':'').'"><i class="bi bi-'.$icon.'"></i>'.$label.'</a></li>';
    }
    ?>
    <nav class="sidebar-nav flex-grow-1">
        <div class="sidebar-section">Main</div>
        <ul class="list-unstyled mb-0">
            <?= sl(url('/seeker/dashboard'),    'speedometer2',     'Dashboard',      $uri) ?>
            <?= sl(url('/jobs'),                'search',           'Find Jobs',      $uri) ?>
            <?= sl(url('/companies'),           'building',         'Companies',      $uri) ?>
            <?= sl(url('/seeker/saved-jobs'),   'bookmark',         'Saved Jobs',     $uri) ?>
            <?= sl(url('/seeker/applications'), 'file-earmark-text','Applications',   $uri) ?>
        </ul>
        <div class="sidebar-section mt-3">Profile</div>
        <ul class="list-unstyled mb-0">
            <?= sl(url('/seeker/profile'),      'person-circle',    'My Profile',     $uri) ?>
            <?= sl(url('/seeker/resume/preview'),'file-earmark-person','My Resume',   $uri) ?>
            <?= sl(url('/seeker/alerts'),       'bell',             'Job Alerts',     $uri) ?>
            <?= sl(url('/seeker/notifications'),'envelope',         'Notifications',  $uri) ?>
        </ul>
        <div class="sidebar-section mt-3">Account</div>
        <ul class="list-unstyled mb-0">
            <?= sl(url('/account/settings'),    'gear',             'Settings',       $uri) ?>
        </ul>
    </nav>
    <div class="p-3 border-top border-secondary mt-auto">
        <?php $u = auth(); ?>
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center
                        justify-content-center fw-700 flex-shrink-0"
                 style="width:36px;height:36px;font-size:14px;">
                <?= strtoupper(substr($u['name'],0,1)) ?>
            </div>
            <div style="min-width:0;">
                <div class="text-white fw-600 small text-truncate"><?= e($u['name']) ?></div>
                <div class="small text-truncate" style="color:rgba(255,255,255,.45);font-size:11px;"><?= e($u['email']) ?></div>
            </div>
        </div>
        <a href="<?= url('/logout') ?>" class="btn btn-sm w-100 text-start"
           style="color:rgba(255,255,255,.5);background:rgba(255,255,255,.06);border:none;">
            <i class="bi bi-box-arrow-right me-2"></i>Sign Out
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="flex-grow-1 d-flex flex-column" style="min-width:0;">

    <!-- Mobile top bar -->
    <nav class="navbar bg-white border-bottom d-lg-none px-3 py-2 sticky-top">
        <div class="d-flex align-items-center gap-2">
            <button class="sidebar-toggle" onclick="document.body.classList.toggle('mobile-menu-open')">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand fw-800 text-primary d-flex align-items-center gap-2" href="<?= url('/') ?>">
                <img src="/public/assets/img/tatu_logo.png" alt="TaTU Logo" style="height:42px;width:auto;object-fit:contain;">
                <?= APP_NAME ?>
            </a>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/seeker/notifications') ?>" class="btn btn-light btn-sm position-relative">
                <i class="bi bi-bell"></i>
                <span class="notif-count-badge badge bg-danger rounded-pill"
                      style="position:absolute;top:-4px;right:-4px;font-size:9px;
                             min-width:16px;height:16px;display:none;align-items:center;justify-content:center;padding:0 3px;">
                </span>
            </a>
            <a href="<?= url('/account/settings') ?>" class="btn btn-light btn-sm">
                <i class="bi bi-gear"></i>
            </a>
        </div>
    </nav>
    <div class="sidebar-overlay" onclick="document.body.classList.remove('mobile-menu-open')"></div>

    <?php if (Session::hasFlash()): ?>
    <div class="px-4 pt-3"><?= flash_messages() ?></div>
    <?php endif; ?>

    <main class="dashboard-content flex-grow-1"><?= $content ?></main>
</div>
</div>

<!-- Bottom Nav (mobile) -->
<nav class="bottom-nav">
    <a href="<?= url('/seeker/dashboard') ?>"><i class="bi bi-speedometer2"></i>Home</a>
    <a href="<?= url('/jobs') ?>"><i class="bi bi-search"></i>Jobs</a>
    <a href="<?= url('/seeker/applications') ?>"><i class="bi bi-file-earmark-text"></i>Applied</a>
    <a href="<?= url('/seeker/profile') ?>"><i class="bi bi-person-circle"></i>Profile</a>
    <a href="<?= url('/messages') ?>" style="position:relative;">
        <i class="bi bi-chat-dots"></i>Messages
        <span class="msg-unread-badge badge bg-danger rounded-pill"
              style="position:absolute;top:2px;right:8px;font-size:9px;min-width:14px;
                     height:14px;display:none;align-items:center;justify-content:center;padding:0 3px;">
        </span>
    </a>
    <a href="<?= url('/account/settings') ?>"><i class="bi bi-gear"></i>Settings</a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script>
(function(){const s=localStorage.getItem('tatuTheme')||'light';document.documentElement.setAttribute('data-theme',s);const i=document.getElementById('darkIcon');if(i)i.className=s==='dark'?'bi bi-sun-fill':'bi bi-moon-fill';})();
function toggleDark(){const c=document.documentElement.getAttribute('data-theme')||'light';const t=c==='dark'?'light':'dark';document.documentElement.setAttribute('data-theme',t);localStorage.setItem('tatuTheme',t);const i=document.getElementById('darkIcon');if(i)i.className=t==='dark'?'bi bi-sun-fill':'bi bi-moon-fill';}
</script>
<?php include ROOT_PATH . '/app/views/partials/chatbot.php'; ?>
</body>
</html>
