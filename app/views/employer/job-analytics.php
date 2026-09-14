<div class="d-flex align-items-center gap-3 mb-4">
    <a href="<?= url('/employer/jobs') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
    <div>
        <h1 class="h4 fw-800 mb-0">Analytics — <?= e(truncate($job['title'],40)) ?></h1>
        <p class="text-muted small mb-0">Performance data for this job listing.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $analyticsCards = [
        ['label'=>'Total Views',      'value'=>number_format($stats['views']),        'icon'=>'eye',            'color'=>'#EBF5FF', 'ic'=>'#1A56DB'],
        ['label'=>'Applications',     'value'=>number_format($stats['applications']), 'icon'=>'file-earmark-text','color'=>'#F0FDF4','ic'=>'#057A55'],
        ['label'=>'Shortlisted',      'value'=>number_format($stats['shortlisted']),  'icon'=>'star',           'color'=>'#EDE9FE', 'ic'=>'#7C3AED'],
        ['label'=>'Interviews',       'value'=>number_format($stats['interviewed']),  'icon'=>'calendar-check', 'color'=>'#FEF3C7', 'ic'=>'#D97706'],
    ];
    foreach ($analyticsCards as $c): ?>
    <div class="col-6 col-md-3">
        <div class="card stat-card border-0" style="background:<?= $c['color'] ?>;">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon" style="background:rgba(0,0,0,.08);">
                    <i class="bi bi-<?= $c['icon'] ?>" style="color:<?= $c['ic'] ?>;"></i>
                </div>
                <div>
                    <div class="stat-value" style="color:<?= $c['ic'] ?>;"><?= $c['value'] ?></div>
                    <div class="stat-label"><?= $c['label'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($stats['by_status'])): ?>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-700 mb-0">Applications by Status</h6>
    </div>
    <div class="card-body">
        <?php foreach ($stats['by_status'] as $s): ?>
        <?php $pct = $stats['applications'] > 0 ? round($s['cnt']/$stats['applications']*100) : 0; ?>
        <div class="mb-3">
            <div class="d-flex justify-content-between small mb-1">
                <span class="fw-600"><?= status_label($s['status']) ?></span>
                <span class="text-muted"><?= $s['cnt'] ?> (<?= $pct ?>%)</span>
            </div>
            <div class="progress" style="height:8px;border-radius:4px;">
                <div class="progress-bar <?= status_badge($s['status']) ?>"
                     style="width:<?= $pct ?>%;border-radius:4px;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
