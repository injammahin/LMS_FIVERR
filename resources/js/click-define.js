const INTERACTIVE_SELECTOR = [
    'a',
    'button',
    'input',
    'textarea',
    'select',
    'option',
    'label',
    'iframe',
    'audio',
    'video',
    'img',
    'svg',
    'canvas',
    'code',
    'pre',
    '[contenteditable="true"]',
    '[data-define-skip]'
].join(',');

const WORD_REGEX = /[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu;

function cleanTerm(text = '') {
    return text
        .replace(/\s+/g, ' ')
        .replace(/[“”"()[\]{}]/g, '')
        .trim();
}

function isInsideArea(node, area) {
    if (!node || !area) return false;
    const el = node.nodeType === 1 ? node : node.parentElement;
    return !!el && area.contains(el);
}

function isInteractiveTarget(target) {
    return !!target.closest(INTERACTIVE_SELECTOR);
}

function getRangeFromPoint(x, y) {
    if (document.caretPositionFromPoint) {
        const pos = document.caretPositionFromPoint(x, y);
        if (!pos || !pos.offsetNode) return null;

        const range = document.createRange();
        range.setStart(pos.offsetNode, pos.offset);
        range.setEnd(pos.offsetNode, pos.offset);
        return range;
    }

    if (document.caretRangeFromPoint) {
        return document.caretRangeFromPoint(x, y);
    }

    return null;
}

function getWordAtPoint(x, y, area) {
    const pointRange = getRangeFromPoint(x, y);
    if (!pointRange) return null;

    const node = pointRange.startContainer;
    const offset = pointRange.startOffset;

    if (!isInsideArea(node, area)) return null;
    if (node.nodeType !== Node.TEXT_NODE) return null;

    const parent = node.parentElement;
    if (!parent || parent.closest(INTERACTIVE_SELECTOR)) return null;

    const text = node.textContent || '';
    if (!text.trim()) return null;

    WORD_REGEX.lastIndex = 0;
    let match;

    while ((match = WORD_REGEX.exec(text)) !== null) {
        const start = match.index;
        const end = start + match[0].length;

        if (offset >= start && offset <= end) {
            const wordRange = document.createRange();
            wordRange.setStart(node, start);
            wordRange.setEnd(node, end);

            const rect = wordRange.getBoundingClientRect();
            const term = cleanTerm(match[0]);

            if (!term) return null;

            return {
                term,
                rect,
                range: wordRange
            };
        }
    }

    return null;
}

function getSelectedTextInArea(area) {
    const selection = window.getSelection();
    if (!selection || selection.rangeCount === 0 || selection.isCollapsed) return null;

    const range = selection.getRangeAt(0);
    const text = cleanTerm(selection.toString());

    if (!text) return null;
    if (text.length > 40) return null;

    const common = range.commonAncestorContainer;
    if (!isInsideArea(common, area)) return null;

    const startEl = range.startContainer.nodeType === 1 ? range.startContainer : range.startContainer.parentElement;
    if (startEl && startEl.closest(INTERACTIVE_SELECTOR)) return null;

    const rect = range.getBoundingClientRect();
    if (!rect || (!rect.width && !rect.height)) return null;

    return {
        term: text,
        rect,
        range
    };
}

function positionPopup(popup, rect) {
    const gap = 12;
    const popupWidth = Math.min(360, window.innerWidth - 24);

    let left = rect.left + (rect.width / 2) - (popupWidth / 2);
    left = Math.max(12, Math.min(left, window.innerWidth - popupWidth - 12));

    popup.style.left = `${left}px`;
    popup.style.display = 'block';

    const measuredHeight = popup.offsetHeight || 220;

    let top = rect.bottom + gap;
    if (top + measuredHeight > window.innerHeight - 12) {
        top = rect.top - measuredHeight - gap;
    }

    top = Math.max(12, top);
    popup.style.top = `${top}px`;
}

class ClickDefineController {
    constructor() {
        this.cache = new Map();
        this.popup = this.createPopup();
        this.currentAudio = null;
        this.activeArea = null;
        this.mouseUpTimer = null;

        this.areas = [...document.querySelectorAll('[data-define-area]')];
        if (!this.areas.length) return;

        this.bindEvents();
    }

    bindEvents() {
        this.areas.forEach((area) => {
            area.addEventListener('click', async (event) => {
                if (isInteractiveTarget(event.target)) return;

                const selection = window.getSelection();
                if (selection && !selection.isCollapsed && cleanTerm(selection.toString())) {
                    return;
                }

                const word = getWordAtPoint(event.clientX, event.clientY, area);
                if (!word) return;

                this.activeArea = area;
                await this.showDefinition(word.term, word.rect);
            });

            area.addEventListener('mouseup', () => {
                clearTimeout(this.mouseUpTimer);

                this.mouseUpTimer = setTimeout(async () => {
                    const selected = getSelectedTextInArea(area);
                    if (!selected) return;

                    this.activeArea = area;
                    await this.showDefinition(selected.term, selected.rect);
                }, 20);
            });
        });

        document.addEventListener('click', (event) => {
            const insidePopup = event.target.closest('#dictionaryDefinePopup');
            const insideArea = event.target.closest('[data-define-area]');

            if (!insidePopup && !insideArea) {
                this.hidePopup();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.hidePopup();
            }
        });

        window.addEventListener('scroll', () => this.hidePopup(), true);
        window.addEventListener('resize', () => this.hidePopup());
    }

    createPopup() {
        const popup = document.createElement('div');
        popup.id = 'dictionaryDefinePopup';
        popup.className = 'define-popup';
        popup.style.display = 'none';
        document.body.appendChild(popup);
        return popup;
    }

    async showDefinition(term, rect) {
        this.popup.innerHTML = `
            <div class="define-loading">Looking up <strong>${this.escapeHtml(term)}</strong>...</div>
        `;
        positionPopup(this.popup, rect);

        try {
            const data = await this.fetchDefinition(term);
            this.renderPopup(data);
            positionPopup(this.popup, rect);
        } catch (error) {
            this.popup.innerHTML = `
                <div class="define-error">
                    <strong>${this.escapeHtml(term)}</strong><br>
                    Sorry, no definition was found.
                </div>
            `;
            positionPopup(this.popup, rect);
        }
    }

    async fetchDefinition(term) {
        const key = term.toLowerCase();

        if (this.cache.has(key)) {
            return this.cache.get(key);
        }

        const response = await fetch(`/student/dictionary/lookup?term=${encodeURIComponent(term)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Definition not found');
        }

        const data = await response.json();
        this.cache.set(key, data);

        return data;
    }

    renderPopup(data) {
        const audioBtn = data.audio
            ? `
                <button type="button" class="define-audio-btn" data-define-audio="${this.escapeHtml(data.audio)}" title="Play pronunciation">
                    <i class="fa-solid fa-volume-high"></i>
                </button>
            `
            : '';

        const phonetic = data.phonetic
            ? `<span class="define-badge"><i class="fa-solid fa-wave-square"></i>${this.escapeHtml(data.phonetic)}</span>`
            : '';

        const partOfSpeech = data.part_of_speech
            ? `<span class="define-badge"><i class="fa-solid fa-tag"></i>${this.escapeHtml(data.part_of_speech)}</span>`
            : '';

        const example = data.example
            ? `<div class="define-example"><strong>Example:</strong> ${this.escapeHtml(data.example)}</div>`
            : '';

        this.popup.innerHTML = `
            <div class="define-popup-header">
                <div>
                    <div class="define-popup-word">${this.escapeHtml(data.word || '')}</div>
                    <div class="define-popup-meta">
                        ${phonetic}
                        ${partOfSpeech}
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    ${audioBtn}
                    <button type="button" class="define-close-btn" data-define-close title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="define-popup-body">
                <div class="define-section-label">Definition</div>
                <div class="define-definition">${this.escapeHtml(data.definition || '')}</div>
                ${example}
            </div>
        `;

        this.popup.querySelector('[data-define-close]')?.addEventListener('click', () => {
            this.hidePopup();
        });

        this.popup.querySelector('[data-define-audio]')?.addEventListener('click', (event) => {
            const src = event.currentTarget.getAttribute('data-define-audio');
            if (!src) return;

            if (this.currentAudio) {
                this.currentAudio.pause();
                this.currentAudio.currentTime = 0;
            }

            const audio = new Audio(src.startsWith('//') ? `https:${src}` : src);
            this.currentAudio = audio;
            audio.play().catch(() => { });
        });
    }

    hidePopup() {
        this.popup.style.display = 'none';

        if (this.currentAudio) {
            this.currentAudio.pause();
            this.currentAudio.currentTime = 0;
            this.currentAudio = null;
        }
    }

    escapeHtml(value = '') {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
}

function initClickDefine() {
    if (window.__clickDefineControllerInitialized) return;
    window.__clickDefineControllerInitialized = true;
    new ClickDefineController();
}

document.addEventListener('DOMContentLoaded', initClickDefine);

export default initClickDefine;