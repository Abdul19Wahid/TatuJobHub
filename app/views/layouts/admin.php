<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="_csrf"    content="<?= Session::csrf() ?>">
    <meta name="base-url" content="<?= BASE_URL ?>">
    <title><?= e($title ?? 'Admin') ?> — <?= APP_NAME ?></title>
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
        <a href="<?= url('/admin/dashboard') ?>" class="d-flex align-items-center">
            <img src="/public/assets/img/tatu_logo.png" alt="TaTU Logo" style="height:42px;width:auto;object-fit:contain;">
        </a>
    </div>
    <?php
    $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    function al(string $href, string $icon, string $label, string $uri): string {
        $p = parse_url($href, PHP_URL_PATH);
        $a = ($uri === $p || str_starts_with($uri, $p.'/'));
        return '<li><a href="'.$href.'" class="'.($a?'active':'').'"><i class="bi bi-'.$icon.'"></i>'.$label.'</a></li>';
    }
    ?>
    <nav class="sidebar-nav flex-grow-1">
        <div class="sidebar-section">Overview</div>
        <ul class="list-unstyled mb-0">
            <?= al(url('/admin/dashboard'),    'speedometer2',   'Dashboard',        $uri) ?>
            <?= al(url('/admin/reports'),       'bar-chart',      'Reports & Export', $uri) ?>
        </ul>
        <div class="sidebar-section mt-3">Moderation</div>
        <ul class="list-unstyled mb-0">
            <?= al(url('/admin/users'),         'people',         'Users',            $uri) ?>
            <?= al(url('/admin/verifications'), 'shield-check',   'Verifications',    $uri) ?>
            <?= al(url('/admin/jobs'),          'briefcase',      'Job Listings',     $uri) ?>
            <?= al(url('/admin/reviews'),       'star',           'Reviews',          $uri) ?>
        </ul>
        <div class="sidebar-section mt-3">System</div>
        <ul class="list-unstyled mb-0">
            <?= al(url('/admin/logs'),          'journal-text',   'Audit Log',        $uri) ?>
            <?= al(url('/account/settings'),    'gear',           'My Settings',      $uri) ?>
        </ul>
        <div class="sidebar-section mt-3">Quick Links</div>
        <ul class="list-unstyled mb-0">
            <?= al(url('/jobs'),                'search',         'View Job Board',   $uri) ?>
            <?= al(url('/companies'),           'building',       'View Companies',   $uri) ?>
        </ul>
    </nav>
    <div class="p-3 border-top border-secondary mt-auto">
        <?php $u = auth(); ?>
        <div class="d-flex align-items-center gap-2 mb-3">
            <div class="rounded-circle bg-danger text-white d-flex align-items-center
                        justify-content-center fw-700 flex-shrink-0"
                 style="width:36px;height:36px;font-size:14px;">
                <?= strtoupper(substr($u['name'],0,1)) ?>
            </div>
            <div style="min-width:0;">
                <div class="text-white fw-600 small text-truncate"><?= e($u['name']) ?></div>
                <div class="small" style="color:rgba(255,255,255,.4);font-size:11px;">Administrator</div>
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
            <span class="navbar-brand d-flex align-items-center">
                <img src="/public/assets/img/tatu_logo.png" alt="TaTU Logo" style="height:42px;width:auto;object-fit:contain;">
            </span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= url('/admin/verifications') ?>" class="btn btn-warning btn-sm">
                <i class="bi bi-shield-check me-1"></i>Verify
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
    <a href="<?= url('/admin/dashboard') ?>"><i class="bi bi-speedometer2"></i>Home</a>
    <a href="<?= url('/admin/users') ?>"><i class="bi bi-people"></i>Users</a>
    <a href="<?= url('/admin/verifications') ?>"><i class="bi bi-shield-check"></i>Verify</a>
    <a href="<?= url('/admin/reports') ?>"><i class="bi bi-bar-chart"></i>Reports</a>
    <a href="<?= url('/admin/logs') ?>"><i class="bi bi-journal-text"></i>Logs</a>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/main.js') ?>"></script>
<script>
(function(){const s=localStorage.getItem('tatuTheme')||'light';document.documentElement.setAttribute('data-theme',s);const i=document.getElementById('darkIcon');if(i)i.className=s==='dark'?'bi bi-sun-fill':'bi bi-moon-fill';})();
function toggleDark(){const c=document.documentElement.getAttribute('data-theme')||'light';const t=c==='dark'?'light':'dark';document.documentElement.setAttribute('data-theme',t);localStorage.setItem('tatuTheme',t);const i=document.getElementById('darkIcon');if(i)i.className=t==='dark'?'bi bi-sun-fill':'bi bi-moon-fill';}
</script>
</body>
</html>
