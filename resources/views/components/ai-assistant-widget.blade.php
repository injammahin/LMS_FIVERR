@php
  $courseId = $courseId ?? null;
@endphp

<style>
.ai-fab{position:fixed;right:18px;bottom:18px;z-index:9999}
.ai-panel{position:fixed;right:18px;bottom:78px;width:360px;max-width:calc(100vw - 36px);height:520px;background:#0b1630;color:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,.35);overflow:hidden;display:none;z-index:9999}
.ai-head{padding:12px 14px;background:rgba(255,255,255,.06);display:flex;justify-content:space-between;align-items:center}
.ai-body{padding:12px;height:400px;overflow:auto}
.ai-foot{padding:12px;border-top:1px solid rgba(255,255,255,.08);display:flex;gap:8px}
.ai-input{flex:1;border-radius:12px;border:1px solid rgba(255,255,255,.15);background:rgba(255,255,255,.06);color:#fff;padding:10px}
.ai-btn{border:0;border-radius:12px;padding:10px 14px;background:#22c55e;color:#0b1630;font-weight:700}
.ai-msg{margin:10px 0;display:flex}
.ai-msg.user{justify-content:flex-end}
.ai-bubble{max-width:85%;padding:10px 12px;border-radius:14px;line-height:1.4;white-space:pre-wrap}
.ai-msg.user .ai-bubble{background:#22c55e;color:#06210f}
.ai-msg.ai .ai-bubble{background:rgba(255,255,255,.08)}
.ai-x{background:transparent;border:0;color:#fff;font-size:18px}
</style>

<div class="ai-fab">
  <button id="aiOpen" class="ai-btn">AI</button>
</div>

<div id="aiPanel" class="ai-panel" data-course-id="{{ $courseId }}">
  <div class="ai-head">
    <div>
      <div style="font-weight:800">AI Assistant</div>
      <div style="opacity:.75;font-size:12px">Answers only from admin training</div>
    </div>
    <button id="aiClose" class="ai-x">✕</button>
  </div>

  <div id="aiBody" class="ai-body"></div>

  <div class="ai-foot">
    <input id="aiInput" class="ai-input" placeholder="Ask something..." />
    <button id="aiSend" class="ai-btn">Send</button>
  </div>
</div>

<script>
(function(){
  const openBtn = document.getElementById('aiOpen');
  const closeBtn = document.getElementById('aiClose');
  const panel = document.getElementById('aiPanel');
  const body = document.getElementById('aiBody');
  const input = document.getElementById('aiInput');
  const send = document.getElementById('aiSend');
  const courseId = panel.getAttribute('data-course-id');

  function addMsg(role, text){
    const wrap = document.createElement('div');
    wrap.className = 'ai-msg ' + (role === 'user' ? 'user' : 'ai');
    const bub = document.createElement('div');
    bub.className = 'ai-bubble';
    bub.textContent = text;
    wrap.appendChild(bub);
    body.appendChild(wrap);
    body.scrollTop = body.scrollHeight;
    return bub;
  }

  function toggle(show){
    panel.style.display = show ? 'block' : 'none';
  }

  openBtn.addEventListener('click', () => toggle(true));
  closeBtn.addEventListener('click', () => toggle(false));

  async function sendMsg(){
    const msg = input.value.trim();
    if(!msg) return;

    addMsg('user', msg);
    input.value = '';
    const aiBubble = addMsg('ai', '');

    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    const res = await fetch("{{ route('ai.chat.stream') }}", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrf || ""
      },
      body: JSON.stringify({
        message: msg,
        course_id: courseId ? Number(courseId) : null
      })
    });

    if(!res.body){
      aiBubble.textContent = "Streaming not supported. Please refresh.";
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
            aiBubble.textContent += obj.delta || "";
            body.scrollTop = body.scrollHeight;
          }catch(e){}
        }

        if(eventName === "done"){
          return;
        }
      }
    }
  }

  send.addEventListener('click', sendMsg);
  input.addEventListener('keydown', (e) => {
    if(e.key === 'Enter') sendMsg();
  });
})();
</script>