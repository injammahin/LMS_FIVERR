@php
    // Auto-detect course id from route param if not passed
    $autoCourseId = null;
    $courseParam = request()->route('course');
    if (is_object($courseParam) && isset($courseParam->id)) $autoCourseId = $courseParam->id;
    elseif (is_numeric($courseParam)) $autoCourseId = (int) $courseParam;

    $courseId = $courseId ?? $autoCourseId;

    // Choose stream url (logged-in or public)
    $streamUrl = $streamUrl ?? (auth()->check() ? route('ai.chat.stream') : route('ai.public.stream'));

    $topics = $topics ?? [
        'How to enroll?',
        'How to reset password?',
        'How to submit assignment?',
        'How to attempt quiz?',
        'How to get certificate?',
        'Contact support',
    ];
@endphp

<style>
/* =========================
   THEME VARIABLES
========================= */
:root{
  --ai-primary: #2563eb;      /* blue-600 */
  --ai-primary-2: #1d4ed8;    /* blue-700 */
  --ai-soft: #eff6ff;         /* blue-50 */
  --ai-border: #e5e7eb;       /* gray-200 */
  --ai-text: #0f172a;         /* slate-900 */
  --ai-muted: #64748b;        /* slate-500 */
  --ai-bg: #ffffff;
  --ai-shadow: 0 18px 45px rgba(0,0,0,.25);
}

/* =========================
   FAB BUTTON
========================= */
.ai-fab{position:fixed;right:18px;bottom:18px;z-index:9999}
.ai-fab button{
  width:56px;height:56px;border-radius:999px;
  background: var(--ai-primary);
  color:#fff;border:0;
  box-shadow:0 14px 32px rgba(37,99,235,.35);
  font-weight:900;cursor:pointer;
  display:flex;align-items:center;justify-content:center;
  transition:transform .15s ease, box-shadow .15s ease, background .15s ease;
}
.ai-fab button:hover{transform:translateY(-2px);background:var(--ai-primary-2)}
.ai-fab button:active{transform:translateY(0)}
.ai-pulse{
  position:absolute;inset:-3px;border-radius:999px;
  background: rgba(37,99,235,.22);
  animation: aiPulse 1.8s infinite;
  z-index:-1;
}
@keyframes aiPulse{
  0%{transform:scale(1);opacity:.75}
  100%{transform:scale(1.35);opacity:0}
}

/* =========================
   PANEL LAYOUT (NO OVERLAP)
   - flex column
   - body scrolls
   - footer stays at bottom
========================= */
.ai-panel{
  position:fixed;right:18px;bottom:86px;
  width:390px;max-width:calc(100vw - 36px);
  height:560px;max-height:calc(100vh - 120px);
  border-radius:22px;overflow:hidden;
  z-index:9999;
  background:var(--ai-bg);
  box-shadow: var(--ai-shadow);

  display:none;
  flex-direction:column;

  opacity:0;
  transform: translateY(12px) scale(.98);
  transform-origin: bottom right;
}
.ai-panel.ai-open{
  display:flex;
  animation: aiIn .22s ease forwards;
}
.ai-panel.ai-closing{
  animation: aiOut .18s ease forwards;
}
@keyframes aiIn{
  to{opacity:1;transform: translateY(0) scale(1)}
}
@keyframes aiOut{
  to{opacity:0;transform: translateY(12px) scale(.98)}
}

/* =========================
   HEADER
========================= */
.ai-head{
  background:linear-gradient(135deg,var(--ai-primary),var(--ai-primary-2));
  color:#fff;
  padding:18px 18px 14px;
  position:relative;
}
.ai-head h3{margin:0;font-size:20px;font-weight:900}
.ai-head p{margin:6px 0 0;opacity:.95;font-size:13px}
.ai-close{
  position:absolute;top:14px;right:14px;
  background:rgba(255,255,255,.18);
  border:0;color:#fff;
  width:36px;height:36px;border-radius:999px;
  cursor:pointer;font-size:18px;line-height:1;
  display:flex;align-items:center;justify-content:center;
}

/* =========================
   CONTENT AREA
========================= */
.ai-content{
  flex:1;
  display:flex;
  flex-direction:column;
  min-height:0; /* IMPORTANT for proper scrolling */
  background: linear-gradient(180deg, #ffffff, #fafcff);
}

/* TOPICS (collapsible) */
.ai-topics{
  padding:14px 14px 0;
}
.ai-topics h4{
  margin:0 0 10px;color:var(--ai-muted);
  font-size:12px;letter-spacing:.08em;font-weight:900;
}
.ai-topic{
  width:100%;
  display:flex;justify-content:space-between;align-items:center;
  border:1px solid var(--ai-border);
  border-radius:999px;
  padding:11px 14px;margin-bottom:10px;
  background:#fff;
  cursor:pointer;
  transition: transform .12s ease, background .12s ease, border-color .12s ease;
}
.ai-topic:hover{background:var(--ai-soft);border-color:rgba(37,99,235,.25);transform:translateY(-1px)}
.ai-topic span{color:var(--ai-text);font-size:13px}
.ai-topic i{color:#94a3b8;font-style:normal;font-weight:900}

/* messages scroll area */
.ai-body{
  flex:1;
  min-height:0;
  overflow:auto;
  padding:12px 14px 14px;
}
.ai-msg{margin:10px 0;display:flex}
.ai-msg.user{justify-content:flex-end}
.ai-bub{
  max-width:86%;
  padding:10px 12px;border-radius:14px;
  font-size:13px;line-height:1.45;
  white-space:pre-wrap;
  word-wrap:break-word;
}
.ai-msg.user .ai-bub{background:var(--ai-primary);color:#fff}
.ai-msg.ai .ai-bub{background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0}

/* typing bubble */
.ai-typing{
  display:inline-flex;
  gap:4px;
  align-items:center;
}
.ai-dot{
  width:6px;height:6px;border-radius:999px;
  background:#94a3b8;
  animation: aiDot 1.1s infinite;
}
.ai-dot:nth-child(2){animation-delay:.15s}
.ai-dot:nth-child(3){animation-delay:.3s}
@keyframes aiDot{
  0%, 60%, 100% { transform: translateY(0); opacity: .5; }
  30% { transform: translateY(-4px); opacity: 1; }
}

/* =========================
   FOOTER (fixed by layout, no absolute)
========================= */
.ai-foot{
  border-top:1px solid var(--ai-border);
  padding:12px 12px;
  background:#fff;
  display:flex;
  gap:10px;
}
.ai-inp{
  flex:1;
  border:1px solid var(--ai-border);
  border-radius:999px;
  padding:11px 14px;
  font-size:13px;
  outline:none;
}
.ai-inp:focus{
  border-color: rgba(37,99,235,.55);
  box-shadow: 0 0 0 4px rgba(37,99,235,.12);
}
.ai-send{
  border:0;
  background:var(--ai-primary);
  color:#fff;
  border-radius:999px;
  padding:11px 18px;
  font-weight:900;
  cursor:pointer;
  transition: background .12s ease, transform .12s ease;
  white-space:nowrap;
}
.ai-send:hover{background:var(--ai-primary-2);transform:translateY(-1px)}
.ai-send:disabled{opacity:.6;cursor:not-allowed;transform:none}

/* =========================
   MOBILE FIX
========================= */
@media (max-width: 480px){
  .ai-panel{
    right:10px;left:10px;
    width:auto;
    bottom:78px;
    height:calc(100vh - 120px);
    max-height:none;
    border-radius:18px;
  }
  .ai-fab{right:14px;bottom:14px}
}
</style>

<div class="ai-fab">
    <span class="ai-pulse" aria-hidden="true"></span>
    <button id="aiFabBtn" type="button" aria-label="Open chat">💬</button>
</div>

<div id="aiPanel"
     class="ai-panel"
     data-stream-url="{{ $streamUrl }}"
     data-course-id="{{ $courseId }}">
    <div class="ai-head">
        <button class="ai-close" id="aiCloseBtn" type="button">×</button>
        <h3>Hi there 👋</h3>
        <p>How can we help you today?</p>
    </div>

    <div class="ai-content">
        <div class="ai-topics" id="aiTopics">
            <h4>SELECT A TOPIC</h4>
            @foreach($topics as $t)
                <button type="button" class="ai-topic" data-topic="{{ $t }}">
                    <span>{{ $t }}</span> <i>→</i>
                </button>
            @endforeach
        </div>

        <div class="ai-body" id="aiBody"></div>
    </div>

    <div class="ai-foot">
        <input class="ai-inp" id="aiInput" placeholder="Type your message..." autocomplete="off" />
        <button class="ai-send" id="aiSendBtn" type="button">Send</button>
    </div>
</div>

<script>
(function(){
    const fab = document.getElementById('aiFabBtn');
    const panel = document.getElementById('aiPanel');
    const closeBtn = document.getElementById('aiCloseBtn');
    const body = document.getElementById('aiBody');
    const input = document.getElementById('aiInput');
    const send = document.getElementById('aiSendBtn');
    const topicsWrap = document.getElementById('aiTopics');

    let isStreaming = false;

    function openPanel(){
        // ✅ make sure it's visible again after close
        panel.style.display = 'flex';

        panel.classList.remove('ai-closing');
        panel.classList.add('ai-open');

        setTimeout(() => input.focus(), 120);
    }

    function closePanel(){
        panel.classList.add('ai-closing');

        // don't remove ai-open immediately; let animation run
        setTimeout(() => {
            panel.classList.remove('ai-open');
            panel.classList.remove('ai-closing');

            // ✅ hide after animation (optional)
            panel.style.display = 'none';
        }, 190);
    }

    function ensurePanelShown(){
        if (!panel.classList.contains('ai-open')) openPanel();
    }

    function scrollToBottom(){
        body.scrollTop = body.scrollHeight;
    }

    function addMsg(role, text){
        const row = document.createElement('div');
        row.className = 'ai-msg ' + (role === 'user' ? 'user' : 'ai');
        const bub = document.createElement('div');
        bub.className = 'ai-bub';
        bub.textContent = text;
        row.appendChild(bub);
        body.appendChild(row);
        scrollToBottom();
        return bub;
    }

    function addTypingBubble(){
        const row = document.createElement('div');
        row.className = 'ai-msg ai';
        const bub = document.createElement('div');
        bub.className = 'ai-bub';

        const wrap = document.createElement('span');
        wrap.className = 'ai-typing';
        wrap.innerHTML = '<span class="ai-dot"></span><span class="ai-dot"></span><span class="ai-dot"></span>';

        bub.appendChild(wrap);
        row.appendChild(bub);
        body.appendChild(row);
        scrollToBottom();
        return { row, bub };
    }

    function collapseTopics(){
        if (!topicsWrap) return;
        topicsWrap.style.display = 'none';
    }

    async function sendMessage(msg){
        msg = (msg || input.value || '').trim();
        if(!msg || isStreaming) return;

        ensurePanelShown();
        collapseTopics();

        addMsg('user', msg);
        input.value = '';

        const typing = addTypingBubble();

        const streamUrl = panel.getAttribute('data-stream-url');
        const courseId = panel.getAttribute('data-course-id');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        isStreaming = true;
        send.disabled = true;
        input.disabled = true;

        let aiText = '';
        let gotFirstDelta = false;

        try{
            const res = await fetch(streamUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    ...(csrf ? {"X-CSRF-TOKEN": csrf} : {})
                },
                body: JSON.stringify({
                    message: msg,
                    course_id: courseId ? Number(courseId) : null,
                })
            });

            if(!res.ok){
                typing.bub.textContent = "Server error. Please try again.";
                return;
            }

            if(!res.body){
                typing.bub.textContent = "Streaming not supported. Please refresh.";
                return;
            }

            const reader = res.body.getReader();
            const decoder = new TextDecoder("utf-8");
            let buffer = "";

            while(true){
                const {value, done} = await reader.read();
                if(done) break;

                buffer += decoder.decode(value, {stream:true});
                const parts = buffer.split("\n\n");
                buffer = parts.pop();

                for(const part of parts){
                    const lines = part.split("\n").map(s => s.trim()).filter(Boolean);
                    let eventName = "";
                    let dataLine = "";

                    for(const line of lines){
                        if(line.startsWith("event:")) eventName = line.slice(6).trim();
                        if(line.startsWith("data:")) dataLine = line.slice(5).trim();
                    }

                    if(eventName === "delta" && dataLine){
                        try{
                            const obj = JSON.parse(dataLine);
                            const delta = obj.delta || '';
                            if(delta){
                                aiText += delta;

                                // first delta => replace typing dots with real text
                                if(!gotFirstDelta){
                                    gotFirstDelta = true;
                                    typing.bub.textContent = '';
                                }
                                typing.bub.textContent += delta;
                                scrollToBottom();
                            }
                        }catch(e){}
                    }

                    if(eventName === "done"){
                        return;
                    }
                }
            }
        } catch (e){
            typing.bub.textContent = "Network error. Please try again.";
        } finally {
            isStreaming = false;
            send.disabled = false;
            input.disabled = false;
            input.focus();
        }
    }

    fab.addEventListener('click', openPanel);
    closeBtn.addEventListener('click', closePanel);

    send.addEventListener('click', () => sendMessage());
    input.addEventListener('keydown', (e) => {
        if(e.key === 'Enter') sendMessage();
    });

    topicsWrap?.addEventListener('click', (e) => {
        const btn = e.target.closest('.ai-topic');
        if(!btn) return;
        sendMessage(btn.getAttribute('data-topic'));
    });
})();
</script>