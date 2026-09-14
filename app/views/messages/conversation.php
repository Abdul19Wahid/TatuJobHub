<style>
.chat-wrap    { display:flex; flex-direction:column; height:calc(100vh - 280px);
                min-height:400px; max-height:700px; }
.chat-header  { flex-shrink:0; }
.chat-body    { flex:1; overflow-y:auto; padding:20px;
                background:#f8fafc; scroll-behavior:smooth; }
.chat-footer  { flex-shrink:0; }
.msg-row      { display:flex; margin-bottom:16px; gap:10px; }
.msg-row.mine { flex-direction:row-reverse; }
.msg-avatar   { width:36px; height:36px; border-radius:50%;
                display:flex; align-items:center; justify-content:center;
                font-weight:800; font-size:14px; flex-shrink:0;
                color:#fff; align-self:flex-end; }
.msg-bubble   { max-width:70%; padding:10px 14px; border-radius:16px;
                font-size:14px; line-height:1.6; word-break:break-word; }
.msg-row:not(.mine) .msg-bubble {
    background:#fff; border:1px solid #e5e7eb;
    border-bottom-left-radius:4px; }
.msg-row.mine .msg-bubble {
    background:#1A56DB; color:#fff;
    border-bottom-right-radius:4px; }
.msg-meta   { font-size:11px; opacity:.6; margin-top:4px;
              text-align:right; }
.msg-row:not(.mine) .msg-meta { text-align:left; }
.msg-name   { font-size:11px; font-weight:700; margin-bottom:2px;
              opacity:.7; }
.typing-dot { display:inline-block; width:7px; height:7px; border-radius:50%;
              background:#94a3b8; margin:0 2px;
              animation:bounce .8s infinite; }
.typing-dot:nth-child(2) { animation-delay:.15s; }
.typing-dot:nth-child(3) { animation-delay:.30s; }
@keyframes bounce { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-8px)} }
</style>

<div class="d-flex align-items-center gap-3 mb-3">
    <a href="<?= url('/messages') ?>" class="btn btn-light btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Inbox
    </a>
    <div class="min-w-0">
        <h1 class="h5 fw-800 mb-0"><?= e($otherName) ?></h1>
        <?php if($conv['job_title']): ?>
        <div>
            <a href="<?= url('/jobs/'.$conv['job_slug']) ?>"
               class="badge rounded-pill fw-500 text-decoration-none"
               style="background:#EBF5FF;color:#1A56DB;font-size:11px;">
                <i class="bi bi-briefcase me-1"></i><?= e(truncate($conv['job_title'],50)) ?>
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chat window -->
<div class="card border-0 shadow-sm chat-wrap" id="chatCard">

    <!-- Messages area -->
    <div class="chat-body" id="chatBody">
        <?php if(empty($messages)): ?>
        <div class="text-center text-muted py-4 small">
            <i class="bi bi-chat-dots" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
            No messages yet. Say hello!
        </div>
        <?php endif; ?>

        <?php foreach ($messages as $msg): ?>
        <?php $mine = ((int)$msg['sender_id'] === $myId); ?>
        <div class="msg-row <?= $mine ? 'mine' : '' ?>"
             data-msg-id="<?= $msg['id'] ?>">
            <div class="msg-avatar"
                 style="background:<?= $mine ? '#1A56DB' : '#7C3AED' ?>;">
                <?= strtoupper(substr($msg['sender_name'],0,1)) ?>
            </div>
            <div>
                <?php if(!$mine): ?>
                <div class="msg-name"><?= e($msg['sender_name']) ?></div>
                <?php endif; ?>
                <div class="msg-bubble"><?= nl2br(e($msg['body'])) ?></div>
                <div class="msg-meta"><?= time_ago($msg['created_at']) ?></div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Typing indicator (hidden by default) -->
        <div class="msg-row" id="typingIndicator" style="display:none;">
            <div class="msg-avatar" style="background:#7C3AED;">
                <?= strtoupper(substr($otherName,0,1)) ?>
            </div>
            <div>
                <div class="msg-name"><?= e($otherName) ?></div>
                <div class="msg-bubble" style="background:#fff;border:1px solid #e5e7eb;">
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                    <span class="typing-dot"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Message input -->
    <div class="chat-footer p-3 border-top bg-white">
        <form id="msgForm" class="d-flex gap-2 align-items-end">
            <?= csrf_field() ?>
            <textarea class="form-control" name="body" id="msgInput"
                      rows="1" placeholder="Type a message..."
                      maxlength="2000"
                      style="resize:none;border-radius:12px;font-size:14px;"
                      oninput="autoResize(this)"></textarea>
            <button type="submit" class="btn btn-primary flex-shrink-0"
                    style="border-radius:12px;width:44px;height:44px;padding:0;">
                <i class="bi bi-send-fill"></i>
            </button>
        </form>
        <div class="d-flex justify-content-between mt-1">
            <small class="text-muted" style="font-size:11px;">
                Press Enter to send, Shift+Enter for new line
            </small>
            <small id="charCount" class="text-muted" style="font-size:11px;">0/2000</small>
        </div>
    </div>
</div>

<script>
const CONV_ID   = <?= $conv['id'] ?>;
const MY_ID     = <?= $myId ?>;
const MY_INIT   = <?= json_encode(strtoupper(substr(auth()['name'],0,1))) ?>;
const OTHER_INIT= <?= json_encode(strtoupper(substr($otherName,0,1))) ?>;
const OTHER_NAME= <?= json_encode(e($otherName)) ?>;
const BASE_URL  = <?= json_encode(BASE_URL) ?>;

let lastMsgId   = <?= !empty($messages) ? end($messages)['id'] : 0 ?>;
let pollTimer;

// ── Auto-resize textarea ────────────────────────────────────────────────────
function autoResize(el) {
    el.style.height = 'auto';
    el.style.height = Math.min(el.scrollHeight, 120) + 'px';
    document.getElementById('charCount').textContent = el.value.length + '/2000';
}

// ── Scroll to bottom ────────────────────────────────────────────────────────
function scrollDown(smooth = true) {
    const body = document.getElementById('chatBody');
    body.scrollTo({ top: body.scrollHeight, behavior: smooth ? 'smooth' : 'instant' });
}
scrollDown(false);

// ── Render a message bubble ─────────────────────────────────────────────────
function renderMsg(msg) {
    const mine = msg.my_message;
    const div  = document.createElement('div');
    div.className = 'msg-row' + (mine ? ' mine' : '');
    div.setAttribute('data-msg-id', msg.id);
    div.innerHTML = `
        <div class="msg-avatar" style="background:${mine ? '#1A56DB' : '#7C3AED'};">
            ${mine ? MY_INIT : OTHER_INIT}
        </div>
        <div>
            ${!mine ? `<div class="msg-name">${OTHER_NAME}</div>` : ''}
            <div class="msg-bubble">${msg.body.replace(/\n/g,'<br>')}</div>
            <div class="msg-meta">${msg.time || 'Just now'}</div>
        </div>`;
    return div;
}

// ── Append messages to DOM ──────────────────────────────────────────────────
function appendMessages(msgs) {
    if (!msgs || !msgs.length) return;
    const body     = document.getElementById('chatBody');
    const typiDiv  = document.getElementById('typingIndicator');
    const wasBottom= body.scrollHeight - body.clientHeight - body.scrollTop < 60;

    msgs.forEach(msg => {
        if (document.querySelector(`[data-msg-id="${msg.id}"]`)) return;
        body.insertBefore(renderMsg(msg), typiDiv);
        lastMsgId = Math.max(lastMsgId, msg.id);
    });

    if (wasBottom) scrollDown();
}

// ── Send message via AJAX ───────────────────────────────────────────────────
document.getElementById('msgForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const input  = document.getElementById('msgInput');
    const body   = input.value.trim();
    if (!body) return;

    const csrf   = document.querySelector('input[name="_csrf"]')?.value || '';
    input.value  = '';
    input.style.height = 'auto';
    document.getElementById('charCount').textContent = '0/2000';

    try {
        const res  = await fetch(`${BASE_URL}/messages/${CONV_ID}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: `_csrf=${encodeURIComponent(csrf)}&body=${encodeURIComponent(body)}`
        });
        const data = await res.json();
        if (data.success) {
            appendMessages([{ ...data, id: Date.now(), my_message: true }]);
            // Will get proper id on next poll
        }
    } catch(err) {
        console.error('Send failed', err);
    }
});

// ── Enter to send, Shift+Enter for newline ──────────────────────────────────
document.getElementById('msgInput').addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        document.getElementById('msgForm').dispatchEvent(new Event('submit'));
    }
});

// ── Poll for new messages every 3 seconds ──────────────────────────────────
async function pollMessages() {
    try {
        const res  = await fetch(
            `${BASE_URL}/messages/${CONV_ID}/poll?after=${lastMsgId}`,
            { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
        );
        const data = await res.json();
        if (data.messages && data.messages.length) {
            appendMessages(data.messages);
        }
    } catch(err) {}
}

// Start polling after page load
pollTimer = setInterval(pollMessages, 3000);

// Stop polling when page is hidden (save resources)
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        clearInterval(pollTimer);
    } else {
        pollTimer = setInterval(pollMessages, 3000);
    }
});
</script>
