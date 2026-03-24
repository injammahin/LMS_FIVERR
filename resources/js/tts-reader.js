function getWordRegex() {
    return /[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu;
}

function normalizeSpeechText(text = '') {
    return text
        .replace(/\r\n/g, '\n')
        .replace(/\n{2,}/g, '. ')
        .replace(/\n/g, ' ')
        .replace(/\s+/g, ' ')
        .replace(/\s+([,.;!?])/g, '$1')
        .trim();
}

function isVisible(el) {
    const rect = el.getBoundingClientRect();
    return rect.top >= 80 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) - 80;
}

function getPauseDuration(chunkText) {
    const text = chunkText.trim();

    if (/[.!?]["')\]]?$/.test(text)) return 700;
    if (/[,;:]["')\]]?$/.test(text)) return 320;

    return 120;
}

function splitSpeechIntoChunks(fullText, maxLen = 180) {
    const chunks = [];
    let cursor = 0;

    const sentenceMatches = fullText.match(/[^.!?]+[.!?]?/g) || [fullText];

    sentenceMatches.forEach((sentenceRaw) => {
        const sentence = sentenceRaw.trim();
        if (!sentence) return;

        if (sentence.length <= maxLen) {
            const start = fullText.indexOf(sentence, cursor);
            if (start !== -1) {
                chunks.push({ text: sentence, start });
                cursor = start + sentence.length;
            }
            return;
        }

        const parts = sentence.match(/[^,;:]+[,;:]?|[^,;:]+$/g) || [sentence];

        parts.forEach((partRaw) => {
            const part = partRaw.trim();
            if (!part) return;

            const start = fullText.indexOf(part, cursor);
            if (start !== -1) {
                chunks.push({ text: part, start });
                cursor = start + part.length;
            }
        });
    });

    return chunks.length ? chunks : [{ text: fullText, start: 0 }];
}

function pickPreferredVoice(lang = 'en-US') {
    const voices = window.speechSynthesis.getVoices() || [];
    if (!voices.length) return null;

    const desiredLangPrefix = lang.toLowerCase().slice(0, 2);
    const languageMatches = voices.filter(v =>
        (v.lang || '').toLowerCase().startsWith(desiredLangPrefix)
    );

    const pool = languageMatches.length ? languageMatches : voices;

    // Strong preference: US first, not UK first
    const preferredNames = [
        'Google US English Female',
        'Google US English',
        'Microsoft Aria Online (Natural) - English (United States)',
        'Microsoft Jenny Online (Natural) - English (United States)',
        'Samantha',
        'Victoria',
        'Karen',
        'Zira',
        'Jenny',
        'Aria',
        'Emma',
        'Ava',
        'Olivia'
    ];

    for (const preferred of preferredNames) {
        const match = pool.find(v => (v.name || '').toLowerCase().includes(preferred.toLowerCase()));
        if (match) return match;
    }

    // Prefer US female-ish names before anything UK
    const femaleUS = pool.find(v => {
        const name = (v.name || '').toLowerCase();
        const voiceLang = (v.lang || '').toLowerCase();
        return voiceLang.includes('en-us') && /(female|zira|jenny|aria|samantha|victoria|karen|emma|ava|olivia)/i.test(name);
    });

    if (femaleUS) return femaleUS;

    const anyUS = pool.find(v => (v.lang || '').toLowerCase().includes('en-us'));
    if (anyUS) return anyUS;

    const femaleHint = pool.find(v =>
        /(female|woman|girl|zira|jenny|aria|samantha|victoria|karen|emma|ava|olivia)/i.test(v.name || '')
    );

    if (femaleHint) return femaleHint;

    return pool[0] || null;
}

class ReadAloudController {
    constructor(root) {
        this.root = root;
        this.targetSelector = root.dataset.ttsTarget;
        this.target = document.querySelector(this.targetSelector);

        this.playBtn = root.querySelector('[data-tts-action="play"]');
        this.pauseBtn = root.querySelector('[data-tts-action="pause"]');
        this.replayBtn = root.querySelector('[data-tts-action="replay"]');
        this.stopBtn = root.querySelector('[data-tts-action="stop"]');
        this.speedSelect = root.querySelector('[data-tts-speed]');
        this.statusEl = root.querySelector('[data-tts-status]');

        this.supported = 'speechSynthesis' in window && 'SpeechSynthesisUtterance' in window;

        this.wordSpans = [];
        this.wordOffsets = [];
        this.spokenText = '';
        this.chunks = [];
        this.currentChunkIndex = 0;
        this.currentWordIndex = -1;
        this.prepared = false;
        this.state = 'idle';
        this.utterance = null;
        this.pendingTimeout = null;
        this.voice = null;

        this.boundarySeen = false;
        this.fallbackTimers = [];
        this.baseWordsPerMinute = 150;

        if (!this.supported || !this.target) {
            this.disable('Read aloud is not available here.');
            return;
        }

        this.bindEvents();
        this.loadVoices();
        this.updateUi();

        if (typeof speechSynthesis !== 'undefined' && 'onvoiceschanged' in speechSynthesis) {
            speechSynthesis.onvoiceschanged = () => this.loadVoices();
        }

        window.addEventListener('beforeunload', () => {
            this.clearPendingTimeout();
            this.clearFallbackTimers();
            window.speechSynthesis.cancel();
        });
    }

    bindEvents() {
        this.playBtn?.addEventListener('click', () => {
            if (this.state === 'paused') {
                this.resume();
                return;
            }

            this.start();
        });

        this.pauseBtn?.addEventListener('click', () => this.pause());
        this.replayBtn?.addEventListener('click', () => this.replay());
        this.stopBtn?.addEventListener('click', () => this.stop());

        this.speedSelect?.addEventListener('change', () => {
            if (this.state === 'playing' || this.state === 'paused') {
                this.replay();
            }
        });
    }

    loadVoices() {
        this.voice = pickPreferredVoice(document.documentElement.lang || 'en-US');
    }

    disable(message) {
        this.setStatus(message);
        [this.playBtn, this.pauseBtn, this.replayBtn, this.stopBtn, this.speedSelect].forEach(el => {
            if (el) el.disabled = true;
        });
    }

    setStatus(text) {
        if (this.statusEl) {
            this.statusEl.textContent = text;
        }
    }

    updateUi() {
        const playing = this.state === 'playing';
        const paused = this.state === 'paused';
        const idle = this.state === 'idle' || this.state === 'ended';

        if (this.playBtn) {
            this.playBtn.disabled = false;
            const label = this.playBtn.querySelector('span');
            if (label) {
                label.textContent = paused ? 'Resume' : 'Play';
            }
        }

        if (this.pauseBtn) this.pauseBtn.disabled = !playing;
        if (this.replayBtn) this.replayBtn.disabled = !this.prepared;
        if (this.stopBtn) this.stopBtn.disabled = idle;
    }

    prepare() {
        if (this.prepared) return;

        this.wrapWords(this.target);

        this.wordSpans = Array.from(this.target.querySelectorAll('.tts-word'));

        // Build spoken text directly from the wrapped words so highlight + speech stay aligned
        this.spokenText = this.wordSpans
            .map(span => (span.textContent || '').trim())
            .filter(Boolean)
            .join(' ');

        // Keep natural chunking from original text as much as possible
        const originalReadable = normalizeSpeechText(this.target.innerText || this.target.textContent || '');
        this.chunks = splitSpeechIntoChunks(originalReadable || this.spokenText, 170);

        // Build offsets against the actual spoken text used for highlight mapping
        const regex = getWordRegex();
        this.wordOffsets = [];

        let match;
        while ((match = regex.exec(this.spokenText)) !== null) {
            this.wordOffsets.push(match.index);
        }

        this.prepared = true;
    }

    wrapWords(container) {
        const skipTags = ['SCRIPT', 'STYLE', 'NOSCRIPT', 'IFRAME', 'AUDIO', 'VIDEO', 'SVG', 'CANVAS', 'INPUT', 'TEXTAREA', 'SELECT', 'OPTION', 'BUTTON'];

        const walker = document.createTreeWalker(
            container,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: (node) => {
                    if (!node.nodeValue || !node.nodeValue.trim()) {
                        return NodeFilter.FILTER_REJECT;
                    }

                    const parent = node.parentElement;
                    if (!parent) return NodeFilter.FILTER_REJECT;
                    if (parent.closest('[data-tts-skip]')) return NodeFilter.FILTER_REJECT;
                    if (skipTags.includes(parent.tagName)) return NodeFilter.FILTER_REJECT;
                    if (parent.classList.contains('tts-word')) return NodeFilter.FILTER_REJECT;

                    return NodeFilter.FILTER_ACCEPT;
                }
            }
        );

        const textNodes = [];
        while (walker.nextNode()) {
            textNodes.push(walker.currentNode);
        }

        textNodes.forEach((textNode) => {
            const text = textNode.nodeValue;
            const fragment = document.createDocumentFragment();
            const regex = getWordRegex();

            let lastIndex = 0;
            let match;

            while ((match = regex.exec(text)) !== null) {
                if (match.index > lastIndex) {
                    fragment.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
                }

                const span = document.createElement('span');
                span.className = 'tts-word';
                span.textContent = match[0];
                fragment.appendChild(span);

                lastIndex = match.index + match[0].length;
            }

            if (lastIndex < text.length) {
                fragment.appendChild(document.createTextNode(text.slice(lastIndex)));
            }

            textNode.parentNode.replaceChild(fragment, textNode);
        });
    }

    start() {
        this.prepare();

        if (!this.spokenText) {
            this.setStatus('No readable description found.');
            return;
        }

        this.clearPendingTimeout();
        this.clearFallbackTimers();
        window.speechSynthesis.cancel();
        this.clearHighlight();

        this.currentChunkIndex = 0;
        this.boundarySeen = false;
        this.state = 'playing';
        this.target.classList.add('tts-reading-active');
        this.setStatus(this.voice ? `Reading with ${this.voice.name}...` : 'Reading...');
        this.updateUi();

        this.speakChunk(this.currentChunkIndex);
    }

    speakChunk(index) {
        if (this.state === 'idle') return;
        if (index >= this.chunks.length) {
            this.finish();
            return;
        }

        const chunk = this.chunks[index];
        const utterance = new SpeechSynthesisUtterance(chunk.text);

        utterance.lang = this.voice?.lang || document.documentElement.lang || 'en-US';
        utterance.rate = parseFloat(this.speedSelect?.value || '0.72');
        utterance.pitch = 1.03;
        utterance.volume = 1;

        if (this.voice) {
            utterance.voice = this.voice;
        }

        utterance.onstart = () => {
            this.utterance = utterance;
            this.state = 'playing';
            this.updateUi();

            // Start fallback highlighting in case boundary events do not fire
            this.startFallbackHighlightForChunk(chunk);
        };

        utterance.onboundary = (event) => {
            if (typeof event.charIndex !== 'number') return;

            this.boundarySeen = true;
            this.clearFallbackTimers();

            const chunkWordsBefore = this.countWordsBeforeChunk(index);
            const localWordIndex = this.findLocalWordIndex(chunk.text, event.charIndex);
            const globalWordIndex = chunkWordsBefore + localWordIndex;

            if (globalWordIndex >= 0) {
                this.highlightWord(globalWordIndex);
            }
        };

        utterance.onpause = () => {
            this.state = 'paused';
            this.setStatus('Paused.');
            this.updateUi();
        };

        utterance.onresume = () => {
            this.state = 'playing';
            this.setStatus(this.voice ? `Reading with ${this.voice.name}...` : 'Reading...');
            this.updateUi();
        };

        utterance.onend = () => {
            this.clearFallbackTimers();

            if (this.state === 'idle') return;

            const nextIndex = index + 1;

            if (nextIndex >= this.chunks.length) {
                this.finish();
                return;
            }

            const delay = getPauseDuration(chunk.text);

            this.pendingTimeout = window.setTimeout(() => {
                this.currentChunkIndex = nextIndex;
                this.boundarySeen = false;
                this.speakChunk(nextIndex);
            }, delay);
        };

        utterance.onerror = () => {
            this.clearFallbackTimers();
            this.state = 'idle';
            this.target.classList.remove('tts-reading-active');
            this.setStatus('Unable to read aloud on this browser/device.');
            this.clearHighlight(false);
            this.updateUi();
        };

        window.speechSynthesis.speak(utterance);
    }

    startFallbackHighlightForChunk(chunk) {
        this.clearFallbackTimers();

        const words = (chunk.text.match(getWordRegex()) || []);
        if (!words.length) return;

        const wordsBefore = this.countWordsBeforeChunk(this.currentChunkIndex);
        const rate = parseFloat(this.speedSelect?.value || '0.72');
        const wordsPerMinute = this.baseWordsPerMinute * Math.max(rate, 0.45);
        const msPerWord = Math.max(220, Math.round(60000 / wordsPerMinute));

        words.forEach((_, localIndex) => {
            const timer = window.setTimeout(() => {
                if (this.boundarySeen || this.state !== 'playing') return;
                this.highlightWord(wordsBefore + localIndex);
            }, localIndex * msPerWord);

            this.fallbackTimers.push(timer);
        });
    }

    clearFallbackTimers() {
        this.fallbackTimers.forEach(timer => clearTimeout(timer));
        this.fallbackTimers = [];
    }

    countWordsBeforeChunk(chunkIndex) {
        let count = 0;

        for (let i = 0; i < chunkIndex; i++) {
            count += (this.chunks[i].text.match(getWordRegex()) || []).length;
        }

        return count;
    }

    findLocalWordIndex(text, charIndex) {
        const regex = getWordRegex();
        let match;
        let wordIndex = 0;
        let lastWordIndex = 0;

        while ((match = regex.exec(text)) !== null) {
            const start = match.index;
            const end = start + match[0].length;

            if (charIndex <= end) {
                return wordIndex;
            }

            lastWordIndex = wordIndex;
            wordIndex++;
        }

        return lastWordIndex;
    }

    pause() {
        if (window.speechSynthesis.speaking && !window.speechSynthesis.paused) {
            window.speechSynthesis.pause();
        }
    }

    resume() {
        if (window.speechSynthesis.paused) {
            window.speechSynthesis.resume();
        }
    }

    replay() {
        this.stop(false);
        this.start();
    }

    stop(updateStatus = true) {
        this.clearPendingTimeout();
        this.clearFallbackTimers();
        window.speechSynthesis.cancel();
        this.state = 'idle';
        this.target.classList.remove('tts-reading-active');
        this.clearHighlight(false);

        if (updateStatus) {
            this.setStatus('Stopped.');
        }

        this.updateUi();
    }

    finish() {
        this.clearPendingTimeout();
        this.clearFallbackTimers();
        this.state = 'ended';
        this.setStatus('Finished reading.');
        this.target.classList.remove('tts-reading-active');
        this.clearHighlight(false);
        this.updateUi();
    }

    clearPendingTimeout() {
        if (this.pendingTimeout) {
            clearTimeout(this.pendingTimeout);
            this.pendingTimeout = null;
        }
    }

    clearHighlight(resetIndex = true) {
        this.wordSpans.forEach(span => span.classList.remove('tts-word-current'));
        this.target.classList.remove('tts-block-current');

        if (resetIndex) {
            this.currentWordIndex = -1;
        }
    }

    highlightWord(index) {
        if (index < 0 || index >= this.wordSpans.length) return;
        if (this.currentWordIndex === index) return;

        if (this.currentWordIndex >= 0 && this.wordSpans[this.currentWordIndex]) {
            this.wordSpans[this.currentWordIndex].classList.remove('tts-word-current');
        }

        this.currentWordIndex = index;

        const current = this.wordSpans[index];
        current.classList.add('tts-word-current');
        this.target.classList.add('tts-block-current');

        if (index % 3 === 0 && current && !isVisible(current)) {
            current.scrollIntoView({
                behavior: 'smooth',
                block: 'center',
                inline: 'nearest'
            });
        }
    }
}

function initReadAloud() {
    const roots = document.querySelectorAll('[data-tts-root]');
    roots.forEach((root) => {
        if (root.dataset.ttsInitialized === 'true') return;
        root.dataset.ttsInitialized = 'true';
        new ReadAloudController(root);
    });
}

document.addEventListener('DOMContentLoaded', initReadAloud);

export default initReadAloud;