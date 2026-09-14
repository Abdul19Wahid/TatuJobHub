<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h4 fw-800 mb-0">Company Verifications</h1>
        <p class="text-muted small mb-0">Review and approve employer company documents.</p>
    </div>
</div>

<!-- Status tabs -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php foreach (['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $v=>$l): ?>
    <a href="?status=<?= $v ?>"
       class="btn btn-sm <?= $status === $v ? 'btn-primary' : 'btn-light' ?>">
        <?= $l ?>
        <?php if ($v === 'pending' && count($rows) > 0 && $status === 'pending'): ?>
        <span class="badge bg-white text-primary ms-1"><?= count($rows) ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($rows)): ?>
<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <i class="bi bi-shield-check text-muted" style="font-size:3rem;"></i>
        <h5 class="fw-700 mt-3">
            <?= $status === 'pending' ? 'No pending verifications' : 'No ' . $status . ' verifications' ?>
        </h5>
        <p class="text-muted">
            <?= $status === 'pending' ? 'All companies have been reviewed.' : 'Nothing to show here.' ?>
        </p>
    </div>
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
    <?php foreach ($rows as $cv): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row g-3 align-items-start">

                <!-- Company info -->
                <div class="col-12 col-md-4">
                    <div class="d-flex gap-3 align-items-center mb-2">
                        <div class="rounded-2 bg-light d-flex align-items-center justify-content-center fw-800 flex-shrink-0 border"
                             style="width:52px;height:52px;font-size:20px;">
                            <?php if ($cv['logo_path']): ?>
                            <img src="<?= url('/file?path='.urlencode($cv['logo_path'])) ?>"
                                 style="width:52px;height:52px;border-radius:8px;object-fit:contain;padding:4px;" alt="">
                            <?php else: ?>
                            <?= strtoupper(substr($cv['company_name'],0,1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="fw-700"><?= e($cv['company_name']) ?></div>
                            <div class="text-muted small"><?= e($cv['email']) ?></div>
                            <?php if ($cv['industry']): ?>
                            <div class="text-muted small"><?= e($cv['industry']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-clock me-1"></i>Submitted <?= time_ago($cv['created_at']) ?>
                    </div>
                    <?php if ($cv['document_type']): ?>
                    <div class="text-muted small mt-1">
                        <i class="bi bi-file-earmark me-1"></i><?= e($cv['document_type']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($cv['document_path']): ?>
                    <a href="<?= url('/file?path='.urlencode($cv['document_path'])) ?>"
                       target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                        <i class="bi bi-file-earmark-pdf me-1"></i>View Document
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Notes from employer -->
                <div class="col-12 col-md-4">
                    <?php if ($cv['notes']): ?>
                    <label class="form-label fw-600 small">Employer Notes</label>
                    <div class="p-3 rounded-2 bg-light border small text-muted" style="line-height:1.6;">
                        <?= nl2br(e($cv['notes'])) ?>
                    </div>
                    <?php endif; ?>
                    <?php if ($cv['admin_remarks']): ?>
                    <label class="form-label fw-600 small mt-2">Admin Remarks</label>
                    <div class="p-2 rounded-2 small text-muted border"><?= e($cv['admin_remarks']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Actions -->
                <div class="col-12 col-md-4">
                    <?php if ($status === 'pending'): ?>
                    <!-- Approve form -->
                    <form method="POST"
                          action="<?= url('/admin/verifications/' . $cv['id'] . '/approve') ?>"
                          class="mb-2" data-no-loading>
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="admin_remarks"
                                   placeholder="Admin remarks (optional)">
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 fw-700"
                                data-confirm="Approve this company verification?">
                            <i class="bi bi-check-circle me-2"></i>Approve Company
                        </button>
                    </form>

                    <!-- Reject form -->
                    <form method="POST"
                          action="<?= url('/admin/verifications/' . $cv['id'] . '/reject') ?>"
                          data-no-loading>
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <input type="text" class="form-control form-control-sm" name="admin_remarks"
                                   placeholder="Reason for rejection..." required>
                        </div>
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100"
                                data-confirm="Reject this verification?">
                            <i class="bi bi-x-circle me-2"></i>Reject
                        </button>
                    </form>
                    <?php else: ?>
                    <span class="badge <?= $cv['status']==='approved' ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2">
                        <?= ucfirst($cv['status']) ?>
                    </span>
                    <?php if ($cv['reviewed_at']): ?>
                    <div class="text-muted small mt-2">
                        Reviewed <?= time_ago($cv['reviewed_at']) ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
