<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>403 Forbidden</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>body{background:#f8fafc;} .ep{min-height:100vh;display:flex;align-items:center;justify-content:center;} .ec{font-size:7rem;font-weight:800;color:#e2e8f0;line-height:1;}</style>
</head>
<body>
<div class="ep"><div class="text-center px-4">
    <div class="ec">403</div>
    <div style="font-size:3rem;color:#dc2626;margin-bottom:12px;"><i class="bi bi-shield-x"></i></div>
    <h1 class="h3 fw-800 mb-2">Access Denied</h1>
    <p class="text-muted mb-4">You don't have permission to view this page.</p>
    <a href="<?= defined('BASE_URL') ? BASE_URL : '/' ?>" class="btn btn-primary px-4">
        <i class="bi bi-house me-1"></i> Go Home
    </a>
</div></div>
</body></html>
