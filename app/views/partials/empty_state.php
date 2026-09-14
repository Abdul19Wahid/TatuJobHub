<?php
/**
 * Reusable Empty State Component
 *
 * Usage:
 *   include partial('empty_state', [
 *     'type'    => 'jobs',           // jobs | applications | messages | users | candidates | verified | search | saved
 *     'title'   => 'No jobs yet',    // optional override
 *     'message' => 'Custom text',    // optional override
 *     'cta'     => ['label'=>'Post a Job', 'url'=>'/employer/jobs/create'],  // optional
 *     'size'    => 'md',             // sm | md | lg  (default md)
 *     'border'  => true,             // wrap in dashed border card (default false)
 *   ]);
 *
 * OR shorthand helper: empty_state('jobs', 'Title', 'Message', ['label'=>..,'url'=>..])
 */

// Defaults
$type    = $type    ?? 'default';
$size    = $size    ?? 'md';
$border  = $border  ?? false;
$cta     = $cta     ?? null;

// Size map
$sizes = [
  'sm' => ['svg'=>60,  'title'=>'fw-700 h6 mb-1',   'msg'=>'small', 'pad'=>'py-4'],
  'md' => ['svg'=>88,  'title'=>'fw-700 h5 mb-2',   'msg'=>'',      'pad'=>'py-5'],
  'lg' => ['svg'=>110, 'title'=>'fw-800 h4 mb-2',   'msg'=>'fs-6',  'pad'=>'py-5 px-4'],
];
$s = $sizes[$size] ?? $sizes['md'];

// Illustration library — inline SVGs, no external dependencies
$illustrations = [

  'jobs' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="15" y="28" width="90" height="60" rx="8" fill="#EBF5FF" stroke="#BFDBFE" stroke-width="1.5"/>
    <rect x="15" y="28" width="90" height="16" rx="8" fill="#BFDBFE"/>
    <rect x="15" y="36" width="90" height="8" fill="#BFDBFE"/>
    <rect x="26" y="55" width="40" height="6" rx="3" fill="#93C5FD"/>
    <rect x="26" y="67" width="28" height="5" rx="2.5" fill="#BFDBFE"/>
    <rect x="26" y="78" width="34" height="5" rx="2.5" fill="#BFDBFE"/>
    <circle cx="88" cy="68" r="14" fill="#DBEAFE"/>
    <path d="M84 68l3 3 6-6" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <rect x="44" y="20" width="32" height="12" rx="4" fill="#3B82F6"/>
    <rect x="52" y="23" width="16" height="6" rx="2" fill="#fff" opacity=".7"/>
  </svg>',

  'applications' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="20" y="15" width="80" height="70" rx="8" fill="#F5F3FF" stroke="#DDD6FE" stroke-width="1.5"/>
    <rect x="32" y="28" width="35" height="5" rx="2.5" fill="#C4B5FD"/>
    <rect x="32" y="38" width="55" height="4" rx="2" fill="#EDE9FE"/>
    <rect x="32" y="46" width="48" height="4" rx="2" fill="#EDE9FE"/>
    <rect x="32" y="54" width="52" height="4" rx="2" fill="#EDE9FE"/>
    <circle cx="83" cy="72" r="18" fill="#7C3AED"/>
    <path d="M76 72l5 5 9-9" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M60 14 Q65 8 70 14" stroke="#7C3AED" stroke-width="1.5" fill="none" stroke-linecap="round"/>
  </svg>',

  'messages' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="12" y="20" width="65" height="44" rx="10" fill="#EBF5FF" stroke="#BFDBFE" stroke-width="1.5"/>
    <circle cx="30" cy="36" r="5" fill="#93C5FD"/>
    <circle cx="44" cy="36" r="5" fill="#BFDBFE"/>
    <circle cx="58" cy="36" r="5" fill="#DBEAFE"/>
    <path d="M12 52 L20 62 L12 64 Z" fill="#EBF5FF" stroke="#BFDBFE" stroke-width="1"/>
    <rect x="50" y="44" width="58" height="38" rx="10" fill="#EDE9FE" stroke="#DDD6FE" stroke-width="1.5"/>
    <rect x="60" y="54" width="35" height="4" rx="2" fill="#C4B5FD"/>
    <rect x="60" y="62" width="28" height="4" rx="2" fill="#DDD6FE"/>
    <path d="M108 82 L100 88 L108 90 Z" fill="#EDE9FE" stroke="#DDD6FE" stroke-width="1"/>
  </svg>',

  'candidates' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="32" r="18" fill="#DBEAFE" stroke="#93C5FD" stroke-width="1.5"/>
    <circle cx="60" cy="28" r="8" fill="#93C5FD"/>
    <path d="M42 50 Q60 42 78 50 L80 72 Q60 78 40 72 Z" fill="#BFDBFE"/>
    <circle cx="28" cy="40" r="11" fill="#EDE9FE" stroke="#DDD6FE" stroke-width="1.5"/>
    <circle cx="28" cy="37" r="5" fill="#C4B5FD"/>
    <path d="M17 52 Q28 47 39 52" stroke="#DDD6FE" stroke-width="6" stroke-linecap="round" fill="none"/>
    <circle cx="92" cy="40" r="11" fill="#DCFCE7" stroke="#BBF7D0" stroke-width="1.5"/>
    <circle cx="92" cy="37" r="5" fill="#86EFAC"/>
    <path d="M81 52 Q92 47 103 52" stroke="#BBF7D0" stroke-width="6" stroke-linecap="round" fill="none"/>
  </svg>',

  'saved' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="18" y="18" width="84" height="64" rx="10" fill="#FFFBEB" stroke="#FDE68A" stroke-width="1.5"/>
    <path d="M47 18 L47 52 L60 44 L73 52 L73 18" fill="#FDE68A" stroke="#F59E0B" stroke-width="1.5" stroke-linejoin="round"/>
    <rect x="30" y="62" width="60" height="5" rx="2.5" fill="#FDE68A"/>
    <rect x="38" y="72" width="44" height="4" rx="2" fill="#FEF3C7"/>
    <circle cx="60" cy="35" r="6" fill="#F59E0B" opacity=".3"/>
  </svg>',

  'users' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="34" r="20" fill="#DBEAFE" stroke="#93C5FD" stroke-width="1.5"/>
    <circle cx="60" cy="29" r="9" fill="#60A5FA"/>
    <path d="M35 62 Q60 52 85 62 L88 80 Q60 88 32 80 Z" fill="#BFDBFE"/>
    <circle cx="28" cy="72" r="8" fill="#EDE9FE"/>
    <circle cx="92" cy="72" r="8" fill="#DCFCE7"/>
    <path d="M24 72 l4 4 8-8" stroke="#7C3AED" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M88 72 l4 4 8-8" stroke="#059669" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>',

  'verified' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="60" cy="50" r="36" fill="#DCFCE7" stroke="#BBF7D0" stroke-width="1.5"/>
    <circle cx="60" cy="50" r="26" fill="#BBF7D0"/>
    <path d="M46 50 l10 10 18-20" stroke="#059669" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
    <circle cx="87" cy="22" r="8" fill="#FEF3C7" stroke="#FDE68A" stroke-width="1"/>
    <path d="M87 19 v4 m0 3 v.5" stroke="#F59E0B" stroke-width="1.5" stroke-linecap="round"/>
  </svg>',

  'search' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <circle cx="50" cy="44" r="26" fill="#EBF5FF" stroke="#BFDBFE" stroke-width="1.5"/>
    <circle cx="50" cy="44" r="16" fill="#fff" stroke="#93C5FD" stroke-width="1.5"/>
    <path d="M61 55 L80 74" stroke="#3B82F6" stroke-width="3.5" stroke-linecap="round"/>
    <path d="M44 38 Q50 34 56 38" stroke="#93C5FD" stroke-width="1.5" stroke-linecap="round" fill="none"/>
    <path d="M44 50 Q50 54 56 50" stroke="#BFDBFE" stroke-width="1.5" stroke-linecap="round" fill="none"/>
  </svg>',

  'default' => '<svg viewBox="0 0 120 100" fill="none" xmlns="http://www.w3.org/2000/svg">
    <rect x="20" y="20" width="80" height="60" rx="10" fill="#F3F4F6" stroke="#E5E7EB" stroke-width="1.5"/>
    <rect x="34" y="36" width="52" height="6" rx="3" fill="#D1D5DB"/>
    <rect x="34" y="48" width="38" height="5" rx="2.5" fill="#E5E7EB"/>
    <rect x="34" y="58" width="44" height="5" rx="2.5" fill="#E5E7EB"/>
  </svg>',
];

// Default copy for each type
$defaults = [
  'jobs'         => ['title'=>'No job listings yet',         'message'=>'Post your first job to start receiving applications.'],
  'applications' => ['title'=>'No applications yet',          'message'=>"You haven't applied to any jobs. Start exploring opportunities."],
  'messages'     => ['title'=>'No messages yet',              'message'=>'Your inbox is empty. Start a conversation with an employer.'],
  'candidates'   => ['title'=>'No applicants yet',            'message'=>'Once candidates apply to your jobs, they\'ll appear here.'],
  'saved'        => ['title'=>'No saved jobs',                'message'=>'Bookmark jobs you like to revisit them later.'],
  'users'        => ['title'=>'No users yet',                 'message'=>'New registrations will appear here.'],
  'verified'     => ['title'=>'All caught up!',               'message'=>'No pending verifications to review right now.'],
  'search'       => ['title'=>'No results found',             'message'=>'Try adjusting your filters or search with different keywords.'],
  'default'      => ['title'=>'Nothing here yet',             'message'=>'Check back later or take an action to get started.'],
];

$d       = $defaults[$type] ?? $defaults['default'];
$title   = $title   ?? $d['title'];
$message = $message ?? $d['message'];
$svg     = $illustrations[$type] ?? $illustrations['default'];

$wrapStart = $border
  ? '<div style="border:2px dashed var(--border,#e5e7eb);border-radius:16px;background:var(--bg,#fff);">'
  : '';
$wrapEnd = $border ? '</div>' : '';
?>
<?= $wrapStart ?>
<div class="text-center <?= $s['pad'] ?>">
  <div style="width:<?= $s['svg'] ?>px;height:<?= $s['svg'] * 0.84 ?>px;margin:0 auto 18px;" aria-hidden="true">
    <?= $svg ?>
  </div>
  <p class="<?= $s['title'] ?> mb-2"><?= e($title) ?></p>
  <p class="text-muted <?= $s['msg'] ?> mb-<?= $cta ? '4' : '0' ?>" style="max-width:320px;margin-left:auto;margin-right:auto;">
    <?= e($message) ?>
  </p>
  <?php if ($cta): ?>
  <a href="<?= url($cta['url']) ?>" class="btn btn-primary fw-600 px-4 <?= $size === 'sm' ? 'btn-sm' : '' ?>">
    <?php if (!empty($cta['icon'])): ?>
    <i class="bi bi-<?= $cta['icon'] ?> me-2"></i>
    <?php endif; ?>
    <?= e($cta['label']) ?>
  </a>
  <?php endif; ?>
</div>
<?= $wrapEnd ?>
