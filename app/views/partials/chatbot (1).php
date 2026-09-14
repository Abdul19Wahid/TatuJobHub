<?php
/**
 * chatbot.php — Floating AI Career Assistant widget
 * Include this file near the closing </body> tag of your main layout, e.g.:
 *   <?php include ROOT_PATH . '/app/views/partials/chatbot.php'; ?>
 *
 * Talks to: POST /chatbot/message  (ChatbotController@message)
 * Sends:    { message: string, history: [{role, content}, ...] }
 * Expects:  { reply: string }
 */
?>
<style>
#twj-chat-toggle {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  width: 60px; height: 60px; border-radius: 50%;
  background: #2563eb; color: #fff; border: none; cursor: pointer;
  box-shadow: 0 4px 14px rgba(0,0,0,0.25);
  display: flex; align-items: center; justify-content: center;
  font-size: 26px; transition: transform 0.2s ease;
}
#twj-chat-toggle:hover { transform: scale(1.08); }

#twj-chat-window {
  position: fixed; bottom: 96px; right: 24px; z-index: 9999;
  width: 340px; max-width: calc(100vw - 32px);
  height: 460px; max-height: calc(100vh - 140px);
  background: #fff; border-radius: 14px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.2);
  display: none; flex-direction: column; overflow: hidden;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
}
#twj-chat-window.open { display: flex; }

#twj-chat-header {
  background: #2563eb; color: #fff; padding: 14px 16px;
  display: flex; justify-content: space-between; align-items: center;
  font-weight: 600; font-size: 15px;
}
#twj-chat-header button {
  background: none; border: none; color: #fff; font-size: 20px;
  cursor: pointer; line-height: 1; padding: 0;
}

#twj-chat-body {
  flex: 1; overflow-y: auto; padding: 14px;
  display: flex; flex-direction: column; gap: 10px;
  background: #f8fafc;
}
.twj-msg {
  max-width: 82%; padding: 9px 13px; border-radius: 12px;
  font-size: 13.5px; line-height: 1.4; word-wrap: break-word;
}
.twj-msg.bot {
  background: #e5edff; color: #1e3a8a; align-self: flex-start;
  border-bottom-left-radius: 3px;
}
.twj-msg.user {
  background: #2563eb; color: #fff; align-self: flex-end;
  border-bottom-right-radius: 3px;
}
.twj-msg.typing { color: #64748b; font-style: italic; }

#twj-chat-form {
  display: flex; border-top: 1px solid #e2e8f0; padding: 10px;
  gap: 8px; background: #fff;
}
#twj-chat-input {
  flex: 1; border: 1px solid #cbd5e1; border-radius: 20px;
  padding: 9px 14px; font-size: 13.5px; outline: none;
}
#twj-chat-input:focus { border-color: #2563eb; }
#twj-chat-send {
  background: #2563eb; color: #fff; border: none; border-radius: 50%;
  width: 38px; height: 38px; cursor: pointer; font-size: 16px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
#twj-chat-send:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<button id="twj-chat-toggle" aria-label="Open career assistant chat">💬</button>

<div id="twj-chat-window">
  <div id="twj-chat-header">
    <span>TatuJobHub Career Assistant</span>
    <button id="twj-chat-close" aria-label="Close chat">&times;</button>
  </div>
  <div id="twj-chat-body"></div>
  <form id="twj-chat-form">
    <input type="text" id="twj-chat-input" placeholder="Ask about jobs, CVs, interviews..." autocomplete="off" />
    <button type="submit" id="twj-chat-send">➤</button>
  </form>
</div>

<script>
(function () {
  const toggleBtn = document.getElementById('twj-chat-toggle');
  const closeBtn  = document.getElementById('twj-chat-close');
  const win       = document.getElementById('twj-chat-window');
  const body      = document.getElementById('twj-chat-body');
  const form      = document.getElementById('twj-chat-form');
  const input     = document.getElementById('twj-chat-input');
  const sendBtn   = document.getElementById('twj-chat-send');

  let history = [];
  let greeted = false;

  const baseUrlMeta = document.querySelector('meta[name="base-url"]');
  const baseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : '';

  function addMessage(text, role) {
    const div = document.createElement('div');
    div.className = 'twj-msg ' + (role === 'user' ? 'user' : 'bot');
    div.textContent = text;
    body.appendChild(div);
    body.scrollTop = body.scrollHeight;
    return div;
  }

  toggleBtn.addEventListener('click', function () {
    win.classList.toggle('open');
    if (win.classList.contains('open') && !greeted) {
      greeted = true;
      addMessage("Hello! 👋 I'm TatuJobHub's career assistant. Ask me about jobs, CVs, interviews, or how the platform works.", 'bot');
    }
  });

  closeBtn.addEventListener('click', function () {
    win.classList.remove('open');
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    const msg = input.value.trim();
    if (!msg) return;

    addMessage(msg, 'user');
    history.push({ role: 'user', content: msg });
    input.value = '';
    sendBtn.disabled = true;

    const typingEl = addMessage('Typing...', 'bot');
    typingEl.classList.add('typing');

    fetch(baseUrl + '/assistant/reply', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: msg, history: history })
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        typingEl.remove();
        const reply = (data && data.reply) ? data.reply : "Sorry, something went wrong. Please try again.";
        addMessage(reply, 'bot');
        history.push({ role: 'assistant', content: reply });
      })
      .catch(function () {
        typingEl.remove();
        addMessage("Sorry, I couldn't connect. Please check your internet and try again.", 'bot');
      })
      .finally(function () {
        sendBtn.disabled = false;
        input.focus();
      });
  });
})();
</script>
