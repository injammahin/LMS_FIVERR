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

    const languageMatches = voices.filter(v => (v.lang || '').toLowerCase().startsWith(lang.toLowerCase().slice(0, 2)));

    const pool = languageMatches.length ? languageMatches : voices;

    const preferredNames = [
        'Google UK English Female',
        'Google US English',
        'Samantha',
        'Victoria',
        'Karen',
        'Moira',
        'Tessa',
        'Veena',
        'Zira',
        'Jenny',
        'Aria',
        'Sonia',
        'Sara',
        'Emma',
        'Ava',
        'Olivia'
    ];

    for (const preferred of preferredNames) {
        const match = pool.find(v => (v.name || '').toLowerCase().includes(preferred.toLowerCase()));
        if (match) return match;
    }

    const femaleHint = pool.find(v =>
        /(female|woman|girl|zira|jenny|aria|samantha|victoria|karen|moira|tessa|veena|sonia|sara|emma|ava|olivia)/i.test(v.name || '')
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
        this.spokenText = normalizeSpeechText(this.target.innerText || this.target.textContent || '');
        this.chunks = splitSpeechIntoChunks(this.spokenText, 170);

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
        window.speechSynthesis.cancel();
        this.clearHighlight();

        this.currentChunkIndex = 0;
        this.state = 'playing';
        this.target.classList.add('tts-reading-active');
        this.setStatus(this.voice ? `Reading slowly with ${this.voice.name}...` : 'Reading slowly...');
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
        };

        utterance.onboundary = (event) => {
            if (typeof event.charIndex !== 'number') return;

            const globalCharIndex = chunk.start + event.charIndex;
            const wordIndex = this.findWordIndex(globalCharIndex);

            if (wordIndex >= 0) {
                this.highlightWord(wordIndex);
            }
        };

        utterance.onpause = () => {
            this.state = 'paused';
            this.setStatus('Paused.');
            this.updateUi();
        };

        utterance.onresume = () => {
            this.state = 'playing';
            this.setStatus(this.voice ? `Reading slowly with ${this.voice.name}...` : 'Reading slowly...');
            this.updateUi();
        };

        utterance.onend = () => {
            if (this.state === 'idle') return;

            const nextIndex = index + 1;

            if (nextIndex >= this.chunks.length) {
                this.finish();
                return;
            }

            const delay = getPauseDuration(chunk.text);

            this.pendingTimeout = window.setTimeout(() => {
                this.currentChunkIndex = nextIndex;
                this.speakChunk(nextIndex);
            }, delay);
        };

        utterance.onerror = () => {
            this.state = 'idle';
            this.target.classList.remove('tts-reading-active');
            this.setStatus('Unable to read aloud on this browser/device.');
            this.clearHighlight(false);
            this.updateUi();
        };

        window.speechSynthesis.speak(utterance);
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

    findWordIndex(charIndex) {
        if (!this.wordOffsets.length) return -1;

        let low = 0;
        let high = this.wordOffsets.length - 1;
        let answer = 0;

        while (low <= high) {
            const mid = Math.floor((low + high) / 2);

            if (this.wordOffsets[mid] <= charIndex) {
                answer = mid;
                low = mid + 1;
            } else {
                high = mid - 1;
            }
        }

        return answer;
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