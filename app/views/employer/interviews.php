<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h4 fw-800 mb-0">Interviews</h1>
        <p class="text-muted small mb-0">All scheduled interviews with candidates.</p>
    </div>
</div>

<?php if (empty($interviews)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-calendar-check text-muted" style="font-size:3rem;"></i>
        <h5 class="fw-700 mt-3">No interviews scheduled</h5>
        <p class="text-muted">Schedule interviews from the applicants page.</p>
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr><th>Candidate</th><th>Job</th><th>Date & Time</th><th>Type</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($interviews as $iv): ?>
                <tr>
                    <td class="fw-600 small"><?= e($iv['seeker_name']) ?></td>
                    <td class="text-muted small"><?= e(truncate($iv['job_title'],40)) ?></td>
                    <td class="small"><?= format_date($iv['scheduled_at'], 'M d, Y · H:i') ?></td>
                    <td><span class="badge bg-light text-dark"><?= ucfirst(str_replace('_',' ',$iv['interview_type'])) ?></span></td>
                    <td>
                        <span class="badge <?= match($iv['status']){
                            'scheduled'=>'bg-primary','completed'=>'bg-success',
                            'cancelled'=>'bg-secondary','no_show'=>'bg-danger',default=>'bg-light text-dark'
                        } ?>">
                            <?= ucfirst($iv['status']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($iv['meeting_link']): ?>
                        <a href="<?= e($iv['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-success me-1">
                            <i class="bi bi-camera-video me-1"></i>Join
                        </a>
                        <?php endif; ?>
                        <?php if ($iv['status'] === 'scheduled'): ?>
                        <form method="POST" action="<?= url('/employer/interviews/'.$iv['id'].'/cancel') ?>"
                              data-no-loading class="d-inline">
                            <?= csrf_field() ?>
                            <input type="hidden" name="reason" value="Cancelled by employer">
                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                    data-confirm="Cancel this interview?">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
