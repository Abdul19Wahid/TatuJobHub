<?php
/**
 * sitemap.php — Dynamic XML sitemap generator
 *
 * Served directly at /sitemap.php (referenced as-is from robots.txt).
 * No rewrite rule needed — this avoids relying on mod_rewrite being
 * available, which has been unreliable on InfinityFree for this project.
 */

header('Content-Type: application/xml; charset=utf-8');

define('ROOT_PATH', __DIR__);
$dbCfg = require ROOT_PATH . '/config/database.php';

// Use the real production domain, not the app's internal base_url
// (which includes the /config/public routing path, not meant for public links).
$baseUrl = 'https://tatujobhub.xo.je';

// ---- DB CONNECTION --------------------------------------------------------
try {
    $pdo = new PDO(
        "mysql:host={$dbCfg['host']};port={$dbCfg['port']};dbname={$dbCfg['dbname']};charset=utf8mb4",
        $dbCfg['username'],
        $dbCfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit;
}

// ---- STATIC PAGES -----------------------------------------------------
$staticPages = [
    ['loc' => '/',         'priority' => '1.0', 'changefreq' => 'daily'],
    ['loc' => '/jobs',     'priority' => '0.9', 'changefreq' => 'daily'],
    ['loc' => '/companies','priority' => '0.6', 'changefreq' => 'weekly'],
    ['loc' => '/about',    'priority' => '0.5', 'changefreq' => 'monthly'],
    ['loc' => '/contact',  'priority' => '0.4', 'changefreq' => 'monthly'],
];

// ---- DYNAMIC JOB LISTING PAGES -----------------------------------------
$stmt = $pdo->query("
    SELECT slug, updated_at
    FROM job_listings
    WHERE status = 'active' AND (expires_at IS NULL OR expires_at > NOW())
    ORDER BY updated_at DESC
");
$jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---- DYNAMIC COMPANY PAGES ---------------------------------------------
$stmt = $pdo->query("
    SELECT slug, updated_at
    FROM employer_profiles
    WHERE slug IS NOT NULL AND verification_status = 'approved'
");
$companies = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>{$baseUrl}{$page['loc']}</loc>\n";
    echo "    <changefreq>{$page['changefreq']}</changefreq>\n";
    echo "    <priority>{$page['priority']}</priority>\n";
    echo "  </url>\n";
}

foreach ($jobs as $job) {
    $lastmod = date('Y-m-d', strtotime($job['updated_at']));
    echo "  <url>\n";
    echo "    <loc>{$baseUrl}/jobs/" . rawurlencode($job['slug']) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
}

foreach ($companies as $company) {
    $lastmod = !empty($company['updated_at']) ? date('Y-m-d', strtotime($company['updated_at'])) : date('Y-m-d');
    echo "  <url>\n";
    echo "    <loc>{$baseUrl}/companies/" . rawurlencode($company['slug']) . "</loc>\n";
    echo "    <lastmod>{$lastmod}</lastmod>\n";
    echo "    <changefreq>monthly</changefreq>\n";
    echo "    <priority>0.5</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';