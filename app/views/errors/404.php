<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>404 Not Found</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>body{background:#f8fafc;} .ep{min-height:100vh;display:flex;align-items:center;justify-content:center;} .ec{font-size:7rem;font-weight:800;color:#e2e8f0;line-height:1;}</style>
</head>
<body>
<div class="ep"><div class="text-center px-4">
    <div class="ec">404</div>
    <div style="font-size:3rem;color:#1A56DB;margin-bottom:12px;"><i class="bi bi-search"></i></div>
    <h1 class="h3 fw-800 mb-2">Page Not Found</h1>
    <p class="text-muted mb-4">The page you are looking for doesn't exist or has been moved.</p>
    <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-primary px-4">
            <i class="bi bi-house me-1"></i> Go Home
        </a>
        <a href="<?= defined('BASE_URL') ? BASE_URL . '/jobs' : '/jobs' ?>" class="btn btn-outline-primary px-4">
            <i class="bi bi-briefcase me-1"></i> Browse Jobs
        </a>
    </div>
</div></div>
</body></html>
