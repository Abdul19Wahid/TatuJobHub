<?php
/**
 * Reusable "Start Conversation" modal
 *
 * Include this in any view and call openMsgModal(otherId, otherName, jobId, jobTitle)
 *
 * Usage in a view:
 *   <?php include ROOT_PATH . '/app/views/messages/modal.php'; ?>
 *   <button onclick="openMsgModal(<?= $user['id'] ?>, '<?= e($user['full_name']) ?>')">
 *       Message
 *   </button>
 */
?>
<!-- New Message Modal -->
<div class="modal fade" id="msgStartModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-800">
                    <i class="bi bi-chat-dots me-2 text-primary"></i>
                    Send Message to <span id="msgModalName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2">
                <form method="POST" action="<?= url('/messages') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="other_id" id="msgOtherId">
                    <input type="hidden" name="job_id"   id="msgJobId">

                    <div id="msgJobContext" class="mb-3" style="display:none;">
                        <div class="p-2 rounded-2 border bg-light small">
                            <i class="bi bi-briefcase me-1 text-primary"></i>
                            Regarding: <strong id="msgJobTitle"></strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Your Message</label>
                        <textarea class="form-control" name="body" rows="4"
                                  placeholder="Type your message here..."
                                  maxlength="2000" required id="msgBody"
                                  style="resize:none;"></textarea>
                        <small class="text-muted">Max 2000 characters</small>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light flex-grow-1"
                                data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary flex-grow-1 fw-700">
                            <i class="bi bi-send me-2"></i>Send Message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openMsgModal(otherId, otherName, jobId = null, jobTitle = null) {
    document.getElementById('msgOtherId').value  = otherId;
    document.getElementById('msgModalName').textContent = otherName;
    document.getElementById('msgJobId').value    = jobId || '';

    const ctx = document.getElementById('msgJobContext');
    if (jobId && jobTitle) {
        document.getElementById('msgJobTitle').textContent = jobTitle;
        ctx.style.display = 'block';
    } else {
        ctx.style.display = 'none';
    }

    document.getElementById('msgBody').value = '';
    new bootstrap.Modal(document.getElementById('msgStartModal')).show();
    setTimeout(() => document.getElementById('msgBody').focus(), 400);
}
</script>
