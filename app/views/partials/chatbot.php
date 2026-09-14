<?php
/**
 * TatuJobHub AI Career Assistant
 * Include this at the bottom of your main layout, just before </body>
 * Usage: <?php require_once VIEW_PATH . '/partials/chatbot.php'; ?>
 */
?>
<style>
#chatBtn{position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;
         background:linear-gradient(135deg,#1A56DB,#7C3AED);border:none;color:#fff;
         box-shadow:0 4px 20px rgba(26,86,219,.4);cursor:pointer;z-index:1000;
         display:flex;align-items:center;justify-content:center;font-size:1.3rem;
         transition:.2s;outline:none;}
#chatBtn:hover{transform:scale(1.1);box-shadow:0 6px 28px rgba(26,86,219,.5);}
#chatBtn .badge-dot{position:absolute;top:2px;right:2px;width:12px;height:12px;
                    border-radius:50%;background:#10B981;border:2px solid #fff;}
#chatWindow{position:fixed;bottom:92px;right:24px;width:360px;height:520px;
            background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.2);
            z-index:999;display:none;flex-direction:column;overflow:hidden;
            border:1.5px solid rgba(26,86,219,.15);}
#chatWindow.open{display:flex;}
@media(max-width:480px){
  #chatWindow{left:12px;right:12px;width:auto;bottom:84px;height:70vh;max-height:520px;}
  #chatBtn{bottom:16px;right:16px;}
}
.chat-head{background:linear-gradient(135deg,#1A56DB,#7C3AED);padding:16px 18px;
           display:flex;align-items:center;gap:12px;flex-shrink:0;}
.chat-head .avatar{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.2);
                   display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.chat-head .info .name{color:#fff;font-weight:700;font-size:14px;line-height:1;}
.chat-head .info .status{color:rgba(255,255,255,.7);font-size:11px;margin-top:2px;}
.chat-head .close-btn{margin-left:auto;background:none;border:none;color:rgba(255,255,255,.7);
                      font-size:1.1rem;cursor:pointer;padding:4px;line-height:1;}
.chat-head .close-btn:hover{color:#fff;}
#chatMessages{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;
              background:#F9FAFB;scrollbar-width:thin;}
.msg{max-width:82%;display:flex;flex-direction:column;gap:2px;}
.msg.bot{align-self:flex-start;}
.msg.user{align-self:flex-end;}
.msg .bubble{padding:10px 14px;border-radius:14px;font-size:13.5px;line-height:1.5;}
.msg.bot .bubble{background:#fff;border:1.5px solid #e5e7eb;color:#111827;border-radius:4px 14px 14px 14px;
                 box-shadow:0 1px 4px rgba(0,0,0,.06);}
.msg.user .bubble{background:linear-gradient(135deg,#1A56DB,#7C3AED);color:#fff;
                  border-radius:14px 4px 14px 14px;}
.msg .ts{font-size:10px;color:#9ca3af;padding:0 4px;}
.msg.user .ts{text-align:right;}
.typing-bubble .bubble{display:flex;align-items:center;gap:4px;padding:12px 16px;}
.typing-dot{width:6px;height:6px;border-radius:50%;background:#9ca3af;
            animation:typingBounce .8s infinite;flex-shrink:0;}
.typing-dot:nth-child(2){animation-delay:.15s;}
.typing-dot:nth-child(3){animation-delay:.3s;}
@keyframes typingBounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-6px)}}
.quick-chips{display:flex;flex-wrap:wrap;gap:6px;padding:10px 16px 4px;flex-shrink:0;background:#F9FAFB;}
.chip-btn{background:#fff;border:1.5px solid #e5e7eb;color:#374151;font-size:12px;
          padding:5px 12px;border-radius:50px;cursor:pointer;transition:.15s;font-weight:500;}
.chip-btn:hover{border-color:var(--primary);color:var(--primary);background:#EBF5FF;}
.chat-input{padding:12px 14px;border-top:1.5px solid #e5e7eb;display:flex;gap:8px;
            align-items:flex-end;flex-shrink:0;background:#fff;}
.chat-input textarea{flex:1;border:1.5px solid #e5e7eb;border-radius:12px;padding:9px 12px;
                     font-size:13.5px;resize:none;outline:none;line-height:1.4;
                     font-family:inherit;max-height:100px;min-height:38px;}
.chat-input textarea:focus{border-color:var(--primary);}
.chat-send{width:38px;height:38px;border-radius:10px;background:var(--primary);border:none;
           color:#fff;display:flex;align-items:center;justify-content:center;
           cursor:pointer;flex-shrink:0;transition:.15s;}
.chat-send:hover{background:#1347c8;}
.chat-send:disabled{background:#93c5fd;cursor:not-allowed;}
@media(max-width:420px){
  #chatWindow{right:8px;left:8px;width:auto;bottom:86px;}
}
</style>

<!-- Chat toggle button -->
<button id="chatBtn" onclick="toggleChat()" title="Career Assistant">
  <i class="bi bi-chat-dots-fill" id="chatIcon"></i>
  <span class="badge-dot"></span>
</button>

<!-- Chat window -->
<div id="chatWindow">
  <div class="chat-head">
    <div class="avatar">🤖</div>
    <div class="info">
      <div class="name">TatuJobHub Assistant</div>
      <div class="status">● Online — Career AI</div>
    </div>
    <button class="close-btn" onclick="toggleChat()"><i class="bi bi-x-lg"></i></button>
  </div>

  <div id="chatMessages">
    <!-- Welcome message injected by JS -->
  </div>

  <div class="quick-chips" id="quickChips">
    <button class="chip-btn" onclick="sendQuick('Find jobs for me')">🔍 Find Jobs</button>
    <button class="chip-btn" onclick="sendQuick('How do I write a good CV?')">📄 CV Tips</button>
    <button class="chip-btn" onclick="sendQuick('Interview preparation tips')">🎯 Interview Prep</button>
    <button class="chip-btn" onclick="sendQuick('How does TatuJobHub work?')">❓ How it works</button>
  </div>

  <div class="chat-input">
    <textarea id="chatInput" placeholder="Ask me anything about jobs or your career..."
              rows="1" onkeydown="handleKey(event)" oninput="autoResize(this)"></textarea>
    <button class="chat-send" id="sendBtn" onclick="sendMessage()">
      <i class="bi bi-send-fill" style="font-size:14px;"></i>
    </button>
  </div>
</div>

<script>
const CHAT_API = '<?= url('/chatbot/message') ?>';
const USER_NAME = '<?= is_logged_in() ? e(auth()['name']) : 'there' ?>';
const USER_ROLE = '<?= is_logged_in() ? auth()['role'] : 'guest' ?>';

let chatOpen = false;
let isTyping  = false;

const SYSTEM_PROMPT = `You are TatuJobHub's friendly AI career assistant for Ghana's job market.
Your job is to help users with: finding jobs, writing CVs/resumes, interview preparation,
career advice, salary negotiation, and using the TatuJobHub platform.

Platform context:
- TatuJobHub is a Ghana-based job portal at tatujobhub.xo.je
- Users can be job seekers, employers, or admins
- Key pages: /jobs (browse jobs), /companies (browse companies), /register (sign up), /login
- Seekers can apply for jobs, upload resumes, set job alerts, and message employers
- Employers can post jobs, review applicants, schedule interviews

Current user: ${USER_NAME} (${USER_ROLE})

Rules:
- Be concise, warm, and practical — max 3-4 sentences per reply unless asked for more
- Always suggest relevant TatuJobHub pages when appropriate (e.g. "Browse jobs at /jobs")
- For Ghanaian context: mention GHS for salary, reference Accra/Kumasi/Ghana job market
- Never make up job listings — guide users to search on the platform
- If asked to do something unrelated to careers/jobs/the platform, politely redirect`;

function toggleChat(){
  chatOpen = !chatOpen;
  document.getElementById('chatWindow').classList.toggle('open', chatOpen);
  document.getElementById('chatIcon').className = chatOpen ? 'bi bi-x-lg' : 'bi bi-chat-dots-fill';
  document.querySelector('#chatBtn .badge-dot').style.display = chatOpen ? 'none' : '';
  if(chatOpen && document.getElementById('chatMessages').children.length === 0) showWelcome();
  if(chatOpen) setTimeout(()=>document.getElementById('chatInput').focus(), 300);
}

function showWelcome(){
  const greet = USER_ROLE === 'seeker'
    ? `Hi ${USER_NAME}! 👋 I'm your TatuJobHub career assistant. I can help you find jobs, improve your CV, or prepare for interviews. What can I help you with today?`
    : USER_ROLE === 'employer'
    ? `Hi ${USER_NAME}! 👋 I'm your TatuJobHub assistant. I can help with writing job descriptions, hiring tips, or anything about the platform. How can I help?`
    : `Hi there! 👋 I'm TatuJobHub's career assistant. I can help you find jobs in Ghana, write a great CV, or prepare for interviews. What would you like to know?`;
  addMessage('bot', greet);
}

function addMessage(role, text, time){
  const msgs = document.getElementById('chatMessages');
  const now  = time || new Date().toLocaleTimeString('en-US',{hour:'2-digit',minute:'2-digit'});
  const div  = document.createElement('div');
  div.className = `msg ${role}`;
  // Convert **bold** and line breaks
  const html = text.replace(/\*\*(.*?)\*\*/g,'<strong>$1</strong>').replace(/\n/g,'<br>');
  div.innerHTML = `<div class="bubble">${html}</div><div class="ts">${now}</div>`;
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
  return div;
}

function showTyping(){
  const msgs = document.getElementById('chatMessages');
  const div  = document.createElement('div');
  div.className = 'msg bot typing-bubble';
  div.id = 'typingIndicator';
  div.innerHTML = `<div class="bubble"><span class="typing-dot"></span><span class="typing-dot"></span><span class="typing-dot"></span></div>`;
  msgs.appendChild(div);
  msgs.scrollTop = msgs.scrollHeight;
}

function removeTyping(){
  const el = document.getElementById('typingIndicator');
  if(el) el.remove();
}

const chatHistory = [];

async function sendMessage(){
  const input = document.getElementById('chatInput');
  const text  = input.value.trim();
  if(!text || isTyping) return;

  input.value = '';
  autoResize(input);
  document.getElementById('quickChips').style.display = 'none';
  addMessage('user', text);
  chatHistory.push({role:'user', content: text});

  isTyping = true;
  document.getElementById('sendBtn').disabled = true;
  showTyping();

  try {
    const res = await fetch(CHAT_API, {
      method: 'POST',
      headers: {'Content-Type':'application/json', 'X-Requested-With':'XMLHttpRequest'},
      body: JSON.stringify({message: text, history: chatHistory.slice(-10)})
    });
    const data = await res.json();
    removeTyping();
    const reply = data.reply || "Sorry, I couldn't process that. Please try again.";
    addMessage('bot', reply);
    chatHistory.push({role:'assistant', content: reply});
  } catch(e) {
    removeTyping();
    addMessage('bot', "I'm having trouble connecting right now. Please try again in a moment.");
  }

  isTyping = false;
  document.getElementById('sendBtn').disabled = false;
  input.focus();
}

function sendQuick(text){
  document.getElementById('chatInput').value = text;
  sendMessage();
}

function handleKey(e){
  if(e.key === 'Enter' && !e.shiftKey){ e.preventDefault(); sendMessage(); }
}

function autoResize(el){
  el.style.height = 'auto';
  el.style.height = Math.min(el.scrollHeight, 100) + 'px';
}

// Store system prompt for use by controller
window._chatSystemPrompt = SYSTEM_PROMPT;
</script>
