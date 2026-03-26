const WORD_REGEX = /[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu;
const MAX_UTTERANCE_LENGTH = 30000;

function normalizeSpeechText(text = '') {
    return text
        .replace(/\r\n/g, '\n')
        .replace(/\u00A0/g, ' ')
        .replace(/\n{2,}/g, '. ')
        .replace(/\n/g, ' ')
        .replace(/\s+/g, ' ')
        .replace(/\s+([,.;!?])/g, '$1')
        .trim();
}

function pickPreferredVoice(lang = 'en-US') {
    const voices = window.speechSynthesis.getVoices() || [];
    if (!voices.length) return null;

    const desiredLangPrefix = lang.toLowerCase().slice(0, 2);
    const languageMatches = voices.filter(v =>
        (v.lang || '').toLowerCase().startsWith(desiredLangPrefix)
    );

    const pool = languageMatches.length ? languageMatches : voices;

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

    const anyUS = pool.find(v => (v.lang || '').toLowerCase().includes('en-us'));
    if (anyUS) return anyUS;

    return pool[0] || null;
}

function isVisibleInViewport(el, padding = 80) {
    const rect = el.getBoundingClientRect();
    const vh = window.innerHeight || document.documentElement.clientHeight;
    return rect.top >= padding && rect.bottom <= vh - padding;
}

function getWordOffsets(text) {
    const offsets = [];
    const regex = new RegExp(WORD_REGEX.source, WORD_REGEX.flags);
    let match;

    while ((match = regex.exec(text)) !== null) {
        offsets.push(match.index);
    }

    return offsets;
}

function findWordIndexByCharIndex(offsets, charIndex) {
    if (!offsets.length) return -1;
    if (charIndex <= offsets[0]) return 0;

    let low = 0;
    let high = offsets.length - 1;
    let answer = 0;

    while (low <= high) {
        const mid = Math.floor((low + high) / 2);

        if (offsets[mid] <= charIndex) {
            answer = mid;
            low = mid + 1;
        } else {
            high = mid - 1;
        }
    }

    return answer;
}

function splitLongUtterance(text, maxLen = MAX_UTTERANCE_LENGTH) {
    if (text.length <= maxLen) {
        return [{ text, globalStart: 0 }];
    }

    const parts = [];
    let cursor = 0;

    while (cursor < text.length) {
        let end = Math.min(cursor + maxLen, text.length);

        if (end < text.length) {
            const windowText = text.slice(cursor, end);

            let breakPos = Math.max(
                windowText.lastIndexOf('. '),
                windowText.lastIndexOf('! '),
                windowText.lastIndexOf('? '),
                windowText.lastIndexOf('; '),
                windowText.lastIndexOf(': '),
                windowText.lastIndexOf(', '),
                windowText.lastIndexOf(' ')
            );

            if (breakPos > 0) {
                end = cursor + breakPos + 1;
            }
        }

        let piece = text.slice(cursor, end);
        const leadingTrim = piece.match(/^\s*/)?.[0]?.length || 0;

        piece = piece.trim();

        if (piece) {
            parts.push({
                text: piece,
                globalStart: cursor + leadingTrim
            });
        }

        cursor = end;
    }

    return parts;
}

function isNodeActuallyReadable(textNode) {
    if (!textNode || textNode.nodeType !== Node.TEXT_NODE) return false;
    if (!textNode.nodeValue || !textNode.nodeValue.trim()) return false;

    const skipTags = new Set([
        'SCRIPT', 'STYLE', 'NOSCRIPT', 'IFRAME', 'AUDIO', 'VIDEO',
        'SVG', 'CANVAS', 'INPUT', 'TEXTAREA', 'SELECT', 'OPTION', 'BUTTON'
    ]);

    let el = textNode.parentElement;

    if (!el) return false;
    if (el.closest('[data-tts-skip]')) return false;

    while (el) {
        if (skipTags.has(el.tagName)) return false;
        if (el.classList?.contains('tts-word')) return false;
        if (el.getAttribute?.('aria-hidden') === 'true') return false;

        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden') return false;

        el = el.parentElement;
    }

    return true;
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
        this.segments = [];
        this.currentWordIndex = -1;
        this.currentSegmentIndex = 0;
        this.prepared = false;
        this.state = 'idle';
        this.utterance = null;
        this.voice = null;
        this.runId = 0;
        this.boundarySupportedForCurrentRun = false;

        this.boundaryWatchdog = null;
        this.fallbackTimer = null;
        this.fallbackStartTime = 0;
        this.fallbackWordStartIndex = 0;
        this.fallbackWordEndIndex = 0;
        this.fallbackWordsPerSecond = 2.8;

        if (!this.supported || !this.target) {
            this.disable('Read aloud is not available here.');
            return;
        }

        this.bindEvents();
        this.loadVoices();
        this.updateUi();

        if (typeof speechSynthesis !== 'undefined' && 'onvoiceschanged' in speechSynthesis) {
            speechSynthesis.addEventListener('voiceschanged', () => this.loadVoices());
        }

        window.addEventListener('beforeunload', () => {
            this.runId++;
            this.clearTimers();
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
        const stopped = this.state === 'idle' || this.state === 'ended';

        if (this.playBtn) {
            this.playBtn.disabled = false;
            const label = this.playBtn.querySelector('span');
            if (label) {
                label.textContent = paused ? 'Resume' : 'Play';
            }
        }

        if (this.pauseBtn) this.pauseBtn.disabled = !playing;
        if (this.replayBtn) this.replayBtn.disabled = !this.prepared;
        if (this.stopBtn) this.stopBtn.disabled = stopped;
    }

    prepare() {
        if (this.prepared) return;

        const originalVisibleText = normalizeSpeechText(
            this.target.innerText || this.target.textContent || ''
        );

        this.wrapWords(this.target);
        this.wordSpans = Array.from(this.target.querySelectorAll('.tts-word'));

        this.spokenText = originalVisibleText;
        this.wordOffsets = getWordOffsets(this.spokenText);

        if (this.wordOffsets.length !== this.wordSpans.length) {
            this.spokenText = this.wordSpans
                .map(span => (span.textContent || '').trim())
                .filter(Boolean)
                .join(' ');
            this.wordOffsets = getWordOffsets(this.spokenText);
        }

        this.segments = splitLongUtterance(this.spokenText, MAX_UTTERANCE_LENGTH);
        this.prepared = true;
    }

    wrapWords(container) {
        const walker = document.createTreeWalker(
            container,
            NodeFilter.SHOW_TEXT,
            {
                acceptNode: (node) => {
                    return isNodeActuallyReadable(node)
                        ? NodeFilter.FILTER_ACCEPT
                        : NodeFilter.FILTER_REJECT;
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
            const regex = new RegExp(WORD_REGEX.source, WORD_REGEX.flags);

            let lastIndex = 0;
            let match;

            while ((match = regex.exec(text)) !== null) {
                if (match.index > lastIndex) {
                    fragment.appendChild(
                        document.createTextNode(text.slice(lastIndex, match.index))
                    );
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

            if (textNode.parentNode) {
                textNode.parentNode.replaceChild(fragment, textNode);
            }
        });
    }

    start() {
        this.prepare();

        if (!this.spokenText || !this.wordSpans.length) {
            this.setStatus('No readable content found.');
            return;
        }

        this.runId++;
        const currentRunId = this.runId;

        this.currentSegmentIndex = 0;
        this.currentWordIndex = -1;
        this.boundarySupportedForCurrentRun = false;
        this.state = 'playing';
        this.target.classList.add('tts-reading-active');
        this.clearHighlight();
        this.clearTimers();
        this.updateUi();

        window.speechSynthesis.cancel();

        window.setTimeout(() => {
            if (this.runId !== currentRunId) return;
            this.speakSegment(0, currentRunId);
        }, 30);
    }

    speakSegment(segmentIndex, runId) {
        if (this.runId !== runId) return;
        if (segmentIndex >= this.segments.length) {
            this.finish(runId);
            return;
        }

        this.currentSegmentIndex = segmentIndex;

        const segment = this.segments[segmentIndex];
        const utterance = new SpeechSynthesisUtterance(segment.text);

        utterance.lang = this.voice?.lang || document.documentElement.lang || 'en-US';
        utterance.rate = parseFloat(this.speedSelect?.value || '1');
        utterance.pitch = 1;
        utterance.volume = 1;

        if (this.voice) {
            utterance.voice = this.voice;
        }

        utterance.onstart = () => {
            if (this.runId !== runId) return;

            this.utterance = utterance;
            this.state = 'playing';
            this.target.classList.add('tts-reading-active');
            this.setStatus(this.voice ? `Reading with ${this.voice.name}...` : 'Reading...');
            this.updateUi();

            this.startBoundaryWatchdog(segment, utterance.rate);
        };

        utterance.onboundary = (event) => {
            if (this.runId !== runId) return;
            if (typeof event.charIndex !== 'number') return;

            if (event.name && event.name !== 'word') return;

            this.boundarySupportedForCurrentRun = true;
            this.stopFallbackHighlighter();

            const globalCharIndex = segment.globalStart + event.charIndex;
            const wordIndex = findWordIndexByCharIndex(this.wordOffsets, globalCharIndex);

            if (wordIndex >= 0) {
                this.highlightWord(wordIndex);
            }
        };

        utterance.onpause = () => {
            if (this.runId !== runId) return;
            this.state = 'paused';
            this.setStatus('Paused.');
            this.updateUi();

            if (this.fallbackTimer) {
                clearInterval(this.fallbackTimer);
                this.fallbackTimer = null;
            }
        };

        utterance.onresume = () => {
            if (this.runId !== runId) return;
            this.state = 'playing';
            this.setStatus(this.voice ? `Reading with ${this.voice.name}...` : 'Reading...');
            this.updateUi();

            if (!this.boundarySupportedForCurrentRun) {
                this.startFallbackHighlighter(segment, utterance.rate);
            }
        };

        utterance.onend = () => {
            if (this.runId !== runId) return;
            if (this.state === 'idle') return;

            this.clearTimers();

            const nextSegmentIndex = segmentIndex + 1;

            if (nextSegmentIndex >= this.segments.length) {
                this.finish(runId);
                return;
            }

            this.speakSegment(nextSegmentIndex, runId);
        };

        utterance.onerror = () => {
            if (this.runId !== runId) return;

            this.clearTimers();
            this.state = 'idle';
            this.target.classList.remove('tts-reading-active');
            this.clearHighlight(false);

            this.setStatus('Unable to continue read aloud.');
            this.updateUi();
        };

        window.speechSynthesis.speak(utterance);
    }

    startBoundaryWatchdog(segment, rate) {
        clearTimeout(this.boundaryWatchdog);

        this.boundaryWatchdog = setTimeout(() => {
            if (this.state !== 'playing') return;
            if (this.boundarySupportedForCurrentRun) return;

            this.startFallbackHighlighter(segment, rate);
            this.setStatus('Reading... highlight fallback active');
        }, 700);
    }

    startFallbackHighlighter(segment, rate) {
        this.stopFallbackHighlighter();

        const segmentText = segment.text || '';
        const segmentOffsets = getWordOffsets(segmentText);

        if (!segmentOffsets.length) return;

        const globalStartWordIndex = findWordIndexByCharIndex(this.wordOffsets, segment.globalStart);
        if (globalStartWordIndex < 0) return;

        this.fallbackWordStartIndex = globalStartWordIndex;
        this.fallbackWordEndIndex = globalStartWordIndex + segmentOffsets.length - 1;
        this.fallbackStartTime = performance.now();

        const adjustedWordsPerSecond = Math.max(1.5, this.fallbackWordsPerSecond * (rate || 1));

        this.fallbackTimer = setInterval(() => {
            if (this.state !== 'playing') return;

            const elapsedSeconds = (performance.now() - this.fallbackStartTime) / 1000;
            const progressedWords = Math.floor(elapsedSeconds * adjustedWordsPerSecond);
            const nextWordIndex = Math.min(
                this.fallbackWordStartIndex + progressedWords,
                this.fallbackWordEndIndex
            );

            this.highlightWord(nextWordIndex);
        }, 40);
    }

    stopFallbackHighlighter() {
        if (this.fallbackTimer) {
            clearInterval(this.fallbackTimer);
            this.fallbackTimer = null;
        }
    }

    clearTimers() {
        if (this.boundaryWatchdog) {
            clearTimeout(this.boundaryWatchdog);
            this.boundaryWatchdog = null;
        }

        this.stopFallbackHighlighter();
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
        this.runId++;
        this.clearTimers();
        window.speechSynthesis.cancel();
        this.state = 'idle';
        this.target.classList.remove('tts-reading-active');
        this.clearHighlight(false);

        if (updateStatus) {
            this.setStatus('Stopped.');
        }

        this.updateUi();
    }

    finish(runId) {
        if (this.runId !== runId) return;

        this.clearTimers();
        this.state = 'ended';
        this.target.classList.remove('tts-reading-active');
        this.clearHighlight(false);
        this.setStatus('Finished reading.');
        this.updateUi();
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
        if (!current) return;

        current.classList.add('tts-word-current');
        this.target.classList.add('tts-block-current');

        if (!isVisibleInViewport(current, 80)) {
            current.scrollIntoView({
                behavior: 'auto',
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