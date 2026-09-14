<style>
@media (max-width: 400px) {
    .status-update-form .d-flex.gap-2 { flex-direction: column; }
    .status-update-form .status-select { width: 100%; }
    .status-update-form button[type="submit"] { width: 100%; }
}
</style>

<!-- Back + header -->
<div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
    <a href="<?= url('/employer/jobs') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Back to Jobs
    </a>
    <div>
        <h1 class="h4 fw-800 mb-0">Applicants — <?= e($job['title']) ?></h1>
        <p class="text-muted small mb-0"><?= count($applicants) ?> total applications</p>
    </div>
</div>

<?php if (empty($applicants)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php empty_state('candidates', 'No applications yet', 'Candidates will appear here once they apply to this job.',
          null, 'lg'); ?>
    </div>
</div>
<?php else: ?>

<!-- Filter tabs -->
<?php
$statuses = ['all'=>'All','applied'=>'New','under_review'=>'Reviewing',
             'shortlisted'=>'Shortlisted','interview_scheduled'=>'Interview',
             'offered'=>'Offered','rejected'=>'Rejected'];
$activeStatus = $_GET['status'] ?? 'all';
$filtered = $activeStatus === 'all' ? $applicants
    : array_filter($applicants, fn($a) => $a['status'] === $activeStatus);
?>
<div class="d-flex gap-2 flex-wrap mb-4">
    <?php foreach ($statuses as $val => $label): ?>
    <?php $cnt = $val === 'all' ? count($applicants)
                : count(array_filter($applicants, fn($a) => $a['status'] === $val)); ?>
    <a href="?status=<?= $val ?>"
       class="btn btn-sm <?= $activeStatus === $val ? 'btn-primary' : 'btn-light' ?>">
        <?= $label ?>
        <?php if ($cnt > 0): ?>
        <span class="badge <?= $activeStatus === $val ? 'bg-white text-primary' : 'bg-secondary' ?> ms-1">
            <?= $cnt ?>
        </span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<div class="d-flex flex-column gap-3">
    <?php foreach ($filtered as $app): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">

                <!-- Candidate info -->
                <div class="col-12 col-md-4 d-flex gap-3 align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center
                                justify-content-center fw-800 flex-shrink-0"
                         style="width:48px;height:48px;font-size:18px;">
                        <?php if ($app['avatar_path']): ?>
                        <img src="<?= url('/file?path=' . urlencode($app['avatar_path'])) ?>"
                             style="width:48px;height:48px;border-radius:50%;object-fit:cover;" alt="">
                        <?php else: ?>
                        <?= strtoupper(substr($app['seeker_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <div class="fw-700"><?= e($app['seeker_name']) ?></div>
                        <div class="text-muted small"><?= e($app['seeker_email']) ?></div>
                        <div class="text-muted small">
                            <?= e(truncate($app['headline'] ?? '', 45)) ?>
                        </div>
                        <div class="text-muted small">
                            <?php if ($app['location_city']): ?>
                                <i class="bi bi-geo-alt me-1"></i><?= e($app['location_city']) ?>
                            <?php endif; ?>
                            <?php if ($app['years_experience']): ?>
                                · <?= $app['years_experience'] ?> yrs exp
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Status + date -->
                <div class="col-6 col-md-2 text-center">
                    <span class="badge <?= status_badge($app['status']) ?> rounded-pill px-3 py-2">
                        <?= status_label($app['status']) ?>
                    </span>
                    <div class="text-muted small mt-1"><?= time_ago($app['applied_at']) ?></div>
                </div>

                <!-- Resume download (attached to this application) -->
                <div class="col-6 col-md-2 text-center">
                    <?php if ($app['resume_snapshot_path']): ?>
                    <a href="<?= url('/file?path=' . urlencode($app['resume_snapshot_path'])) ?>"
                       target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Resume
                    </a>
                    <?php else: ?>
                    <span class="text-muted small">No resume</span>
                    <?php endif; ?>
                </div>

                <!-- Status update -->
                <div class="col-12 col-md-4">
                    <form method="POST"
                          action="<?= url('/employer/applications/' . $app['id'] . '/status') ?>"
                          class="status-update-form" data-no-loading>
                        <?= csrf_field() ?>
                        <div class="d-flex gap-2 mb-2">
                            <select class="form-select form-select-sm status-select" name="status"
                                    onchange="previewBadge(this)">
                                <?php
                                $statOpts = [
                                    'applied'              => '📥 New Application',
                                    'under_review'         => '👁 Under Review',
                                    'shortlisted'          => '⭐ Shortlisted',
                                    'interview_scheduled'  => '📅 Interview Scheduled',
                                    'offered'              => '🎁 Offer Sent',
                                    'hired'                => '✅ Hired',
                                    'rejected'             => '❌ Rejected',
                                ];
                                foreach ($statOpts as $v => $l): ?>
                                <option value="<?= $v ?>" <?= $app['status'] === $v ? 'selected' : '' ?>>
                                    <?= $l ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary flex-shrink-0 fw-600">Save</button>
                        </div>
                        <!-- Optional note to candidate -->
                        <div>
                            <button type="button"
                                    class="btn btn-link btn-sm text-muted p-0 note-toggle"
                                    onclick="toggleNote(this)"
                                    style="font-size:12px;text-decoration:none;">
                                <i class="bi bi-chat-left-text me-1"></i>Add a note for candidate
                            </button>
                            <div class="note-wrap mt-1" style="display:none;">
                                <textarea class="form-control form-control-sm" name="status_note"
                                          rows="2" maxlength="500"
                                          placeholder="Optional message shown to the candidate (e.g. 'Please bring your portfolio')..."
                                          style="font-size:12px;resize:none;"><?= e($app['status_note'] ?? '') ?></textarea>
                                <div class="text-muted mt-1" style="font-size:11px;">
                                    This note will appear on the candidate's application timeline.
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Cover letter preview toggle -->
                    <?php if ($app['cover_letter_text']): ?>
                    <button class="btn btn-link btn-sm text-muted p-0 mt-1"
                            data-bs-toggle="collapse"
                            data-bs-target="#cl_<?= $app['id'] ?>">
                        <i class="bi bi-chat-text me-1"></i>Cover Letter
                    </button>
                    <div class="collapse mt-2" id="cl_<?= $app['id'] ?>">
                        <div class="p-3 rounded-2 bg-light border small text-muted" style="line-height:1.6;">
                            <?= nl2br(e(truncate($app['cover_letter_text'], 400))) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Message candidate button -->
                    <button class="btn btn-sm btn-outline-info mt-2"
                            onclick="openMsgModal(<?= $app['seeker_id'] ?>, '<?= e(addslashes($app['seeker_name'])) ?>', <?= $job['id'] ?>, '<?= e(addslashes($job['title'])) ?>')">
                        <i class="bi bi-chat-dots me-1"></i>Message
                    </button>
                    <!-- Schedule interview button -->
                    <?php if(in_array($app['status'],['shortlisted','under_review','applied'])): ?>
                    <button class="btn btn-sm btn-outline-success mt-2"
                            onclick="openInterviewModal(<?= $app['id'] ?>, '<?= e(addslashes($app['seeker_name'])) ?>')">
                        <i class="bi bi-calendar-plus me-1"></i>Schedule Interview
                    </button>
                    <?php endif; ?>
                </div>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary flex-shrink-0">Update</button>
                    </form>

                    <!-- Cover letter preview toggle -->
                    <?php if ($app['cover_letter_text']): ?>
                    <button class="btn btn-link btn-sm text-muted p-0 mt-1"
                            data-bs-toggle="collapse"
                            data-bs-target="#cl_<?= $app['id'] ?>">
                        <i class="bi bi-chat-text me-1"></i>Cover Letter
                    </button>
                    <div class="collapse mt-2" id="cl_<?= $app['id'] ?>">
                        <div class="p-3 rounded-2 bg-light border small text-muted" style="line-height:1.6;">
                            <?= nl2br(e(truncate($app['cover_letter_text'], 400))) ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Message candidate button -->
                    <button class="btn btn-sm btn-outline-info mt-2"
                            onclick="openMsgModal(<?= $app['seeker_id'] ?>, '<?= e(addslashes($app['seeker_name'])) ?>', <?= $job['id'] ?>, '<?= e(addslashes($job['title'])) ?>')">
                        <i class="bi bi-chat-dots me-1"></i>Message
                    </button>
                    <!-- Schedule interview button -->
                    <?php if(in_array($app['status'],['shortlisted','under_review','applied'])): ?>
                    <button class="btn btn-sm btn-outline-success mt-2"
                            onclick="openInterviewModal(<?= $app['id'] ?>, '<?= e(addslashes($app['seeker_name'])) ?>')">
                        <i class="bi bi-calendar-plus me-1"></i>Schedule Interview
                    </button>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include ROOT_PATH . '/app/views/messages/modal.php'; ?>

<!-- ── INTERVIEW SCHEDULING MODAL ───────────────────────────────────────── -->
<div class="modal fade" id="interviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800">
                    <i class="bi bi-calendar-check me-2 text-primary"></i>Schedule Interview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="text-muted small mb-3">
                    Scheduling interview for: <strong id="modalCandidateName"></strong>
                </p>
                <form method="POST" action="<?= url('/employer/interviews') ?>" id="interviewForm">
                    <?= csrf_field() ?>
                    <input type="hidden" name="application_id" id="modalAppId">

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label">Date & Time <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="scheduled_at"
                                   required min="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Duration</label>
                            <select class="form-select" name="duration_mins">
                                <option value="30">30 minutes</option>
                                <option value="45">45 minutes</option>
                                <option value="60" selected>1 hour</option>
                                <option value="90">1.5 hours</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Interview Type <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-2" id="typeButtons">
                                <?php foreach(['video'=>'📹 Video','phone'=>'📞 Phone','on_site'=>'🏢 On-Site','technical'=>'💻 Technical','panel'=>'👥 Panel'] as $v=>$l): ?>
                                <label class="btn btn-sm btn-outline-primary fw-500"
                                       style="cursor:pointer;border-radius:20px;">
                                    <input type="radio" name="interview_type" value="<?= $v ?>"
                                           <?= $v==='video'?'checked':'' ?>
                                           class="d-none" onchange="updateTypeStyle()">
                                    <?= $l ?>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-12" id="meetingLinkWrap">
                            <label class="form-label">Meeting Link <span class="text-muted small">(for video)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-camera-video"></i></span>
                                <input type="url" class="form-control" name="meeting_link"
                                       placeholder="https://meet.google.com/...">
                            </div>
                        </div>
                        <div class="col-12" id="locationWrap" style="display:none;">
                            <label class="form-label">Location / Address</label>
                            <input type="text" class="form-control" name="location_address"
                                   placeholder="Office address or room number">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Instructions for Candidate <span class="text-muted small">(optional)</span></label>
                            <textarea class="form-control" name="instructions" rows="3"
                                      placeholder="Any preparation notes, what to bring, dress code..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="button" class="btn btn-light flex-grow-1"
                                data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary flex-grow-1 fw-700">
                            <i class="bi bi-calendar-check me-2"></i>Schedule Interview
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Open modal with candidate info
function openInterviewModal(appId, candidateName) {
    document.getElementById('modalAppId').value       = appId;
    document.getElementById('modalCandidateName').textContent = candidateName;
    new bootstrap.Modal(document.getElementById('interviewModal')).show();
}

// Show/hide meeting link vs location based on type
function updateTypeStyle() {
    const type     = document.querySelector('input[name="interview_type"]:checked')?.value;
    const needLink = ['video','technical'].includes(type);
    const needLoc  = type === 'on_site';

    document.getElementById('meetingLinkWrap').style.display = needLink ? 'block' : 'none';
    document.getElementById('locationWrap').style.display    = needLoc  ? 'block' : 'none';

    // Style active radio button labels
    document.querySelectorAll('#typeButtons label').forEach(lbl => {
        const inp = lbl.querySelector('input');
        lbl.classList.toggle('btn-primary',       inp.checked);
        lbl.classList.toggle('btn-outline-primary',!inp.checked);
    });
}
updateTypeStyle();

// Toggle note textarea visibility
function toggleNote(btn) {
    const wrap = btn.closest('div').querySelector('.note-wrap') ||
                 btn.parentElement.querySelector('.note-wrap');
    const hidden = wrap.style.display === 'none';
    wrap.style.display = hidden ? 'block' : 'none';
    btn.innerHTML = hidden
        ? '<i class="bi bi-x-circle me-1"></i>Hide note'
        : '<i class="bi bi-chat-left-text me-1"></i>Add a note for candidate';
    if (hidden) wrap.querySelector('textarea')?.focus();
}

// Auto-show note area if an existing note is present
document.querySelectorAll('.note-wrap').forEach(wrap => {
    const ta = wrap.querySelector('textarea');
    if (ta && ta.value.trim()) {
        wrap.style.display = 'block';
        const btn = wrap.closest('div').querySelector('.note-toggle');
        if (btn) btn.innerHTML = '<i class="bi bi-x-circle me-1"></i>Hide note';
    }
});
</script>
