<?php
function e(mixed $v): string { return htmlspecialchars((string)$v, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

function url(string $path = ''): string { return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/'); }

function asset(string $path): string { return url('assets/' . ltrim($path, '/')); }

function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . e(Session::csrf()) . '">'; }

function flash_messages(): string
{
    $msgs = Session::getFlash();
    if (empty($msgs)) return '';
    $map = ['success'=>'alert-success','error'=>'alert-danger','warning'=>'alert-warning','info'=>'alert-info'];
    $ico = ['success'=>'check-circle-fill','error'=>'x-circle-fill','warning'=>'exclamation-triangle-fill','info'=>'info-circle-fill'];
    $html = '<div class="flash-messages">';
    foreach ($msgs as $m) {
        $t = $m['type'];
        $html .= '<div class="alert ' . ($map[$t]??'alert-secondary') . ' alert-dismissible fade show d-flex align-items-center gap-2">'
               . '<i class="bi bi-' . ($ico[$t]??'bell') . '"></i>'
               . '<span>' . e($m['message']) . '</span>'
               . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    return $html . '</div>';
}

function format_date(string $d, string $fmt = 'M d, Y'): string { return $d ? date($fmt, strtotime($d)) : '—'; }

function time_ago(string $d): string
{
    $diff = time() - strtotime($d);
    if ($diff < 60)      return 'Just now';
    if ($diff < 3600)    return (int)($diff/60) . ' minutes ago';
    if ($diff < 86400)   return (int)($diff/3600) . ' hours ago';
    if ($diff < 604800)  return (int)($diff/86400) . ' days ago';
    if ($diff < 2592000) return (int)($diff/604800) . ' weeks ago';
    return (int)($diff/2592000) . ' months ago';
}

function truncate(string $t, int $len = 100): string
{
    $t = strip_tags($t);
    return mb_strlen($t) <= $len ? $t : mb_substr($t, 0, $len) . '…';
}

function slug(string $t): string
{
    $t = mb_strtolower(trim($t));
    $t = preg_replace('/[^a-z0-9\s-]/', '', $t);
    return trim(preg_replace('/[\s-]+/', '-', $t), '-');
}

function salary_range(?float $min, ?float $max, string $cur = 'USD', bool $hidden = false): string
{
    if ($hidden) return 'Competitive';
    if ($min && $max) return $cur . ' ' . number_format($min) . ' – ' . number_format($max);
    if ($min) return $cur . ' ' . number_format($min) . '+';
    return 'Not specified';
}

function status_badge(string $s): string
{
    return match($s) {
        'applied'             => 'bg-secondary',
        'under_review'        => 'bg-info text-dark',
        'shortlisted'         => 'bg-primary',
        'interview_scheduled' => 'bg-warning text-dark',
        'offered','hired'     => 'bg-success',
        'rejected'            => 'bg-danger',
        'withdrawn'           => 'bg-dark',
        'active'              => 'bg-success',
        'closed','expired'    => 'bg-secondary',
        default               => 'bg-light text-dark',
    };
}

function status_label(string $s): string
{
    return match($s) {
        'applied'             => 'Applied',
        'under_review'        => 'Under Review',
        'shortlisted'         => 'Shortlisted',
        'interview_scheduled' => 'Interview Scheduled',
        'offered'             => 'Offer Received',
        'hired'               => 'Hired',
        'rejected'            => 'Not Selected',
        'withdrawn'           => 'Withdrawn',
        'full_time'           => 'Full-Time',
        'part_time'           => 'Part-Time',
        'contract'            => 'Contract',
        'internship'          => 'Internship',
        'entry'               => 'Entry Level',
        'junior'              => 'Junior',
        'mid'                 => 'Mid-Level',
        'senior'              => 'Senior',
        default               => ucfirst(str_replace('_',' ',$s)),
    };
}

function dd(mixed ...$args): never
{
    echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:1rem;border-radius:6px;font-size:13px;">';
    foreach ($args as $a) var_dump($a);
    echo '</pre>';
    exit;
}

/**
 * Render an empty state partial inline.
 * @param string      $type    jobs|applications|messages|candidates|saved|users|verified|search|default
 * @param string|null $title   Override default title
 * @param string|null $message Override default message
 * @param array|null  $cta     ['label'=>'...', 'url'=>'...', 'icon'=>'...']  (icon optional)
 * @param string      $size    sm|md|lg
 * @param bool        $border  Wrap in dashed-border card
 */
function empty_state(
    string  $type    = 'default',
    ?string $title   = null,
    ?string $message = null,
    ?array  $cta     = null,
    string  $size    = 'md',
    bool    $border  = false
): void {
    extract(compact('type','title','message','cta','size','border'));
    include ROOT_PATH . '/app/views/partials/empty_state.php';
}

function auth(): ?array       { return Session::user(); }
function auth_id(): int       { return Session::id(); }
function is_logged_in(): bool { return Session::isLoggedIn(); }
function is_seeker(): bool    { return Session::isSeeker(); }
function is_employer(): bool  { return Session::isEmployer(); }
function is_admin(): bool     { return Session::isAdmin(); }
