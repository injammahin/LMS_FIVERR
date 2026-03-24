const WORD_REGEX = /[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu;

function cleanTerm(text = '') {
    return text
        .replace(/\s+/g, ' ')
        .replace(/[“”"()[\]{}]/g, '')
        .trim();
}

function positionPopup(popup, rect) {
    const gap = 12;
    const popupWidth = Math.min(360, window.innerWidth - 24);
    let left = rect.left + (rect.width / 2) - (popupWidth / 2);
    left = Math.max(12, Math.min(left, window.innerWidth - popupWidth - 12));

    let top = rect.bottom + gap;

    const estimatedHeight = popup.offsetHeight || 220;
    if (top + estimatedHeight > window.innerHeight - 12) {
        top = rect.top - estimatedHeight - gap;
    }

    top = Math.max(12, top);

    popup.style.left = `${left}px`;
    popup.style.top = `${top}px`;
}

class ClickDefineController {
    constructor() {
        this.cache = new Map();
        this.popup = this.createPopup();
        this.activeWordEl = null;

        this.initAreas();
        this.bindEvents();
    }

    initAreas() {
        const areas = document.querySelectorAll('[data-define-area]');
        areas.forEach((area) => {
            if (area.dataset.definePrepared === 'true') return;
            this.wrapWords(area);
            area.dataset.definePrepared = 'true';
        });
    }

    bindEvents() {
        document.addEventListener('click', async (event) => {
            const wordEl = event.target.closest('.define-word');

            if (wordEl) {
                event.preventDefault();
                const term = cleanTerm(wordEl.dataset.term || wordEl.textContent || '');
                if (!term) return;

                this.setActiveWord(wordEl);
                const rect = wordEl.getBoundingClientRect();
                await this.showDefinition(term, rect);
                return;
            }

            const clickedInsidePopup = event.target.closest('#dictionaryDefinePopup');
            if (!clickedInsidePopup) {
                this.hidePopup();
            }
        });

        document.addEventListener('mouseup', async () => {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0 || selection.isCollapsed) return;

            const range = selection.getRangeAt(0);
            const selectedText = cleanTerm(selection.toString());

            if (!selectedText) return;
            if (selectedText.length > 40) return;

            const anchorNode = selection.anchorNode?.parentElement;
            if (!anchorNode || !anchorNode.closest('[data-define-area]')) return;

            const rect = range.getBoundingClientRect();
            await this.showDefinition(selectedText, rect);
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.hidePopup();
            }
        });

        window.addEventListener('scroll', () => this.hidePopup(), true);
        window.addEventListener('resize', () => this.hidePopup());
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
                    if (parent.closest('[data-define-skip]')) return NodeFilter.FILTER_REJECT;
                    if (skipTags.includes(parent.tagName)) return NodeFilter.FILTER_REJECT;
                    if (parent.classList.contains('define-word')) return NodeFilter.FILTER_REJECT;

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

            let lastIndex = 0;
            WORD_REGEX.lastIndex = 0;

            let match;
            while ((match = WORD_REGEX.exec(text)) !== null) {
                if (match.index > lastIndex) {
                    fragment.appendChild(document.createTextNode(text.slice(lastIndex, match.index)));
                }

                const span = document.createElement('span');
                span.className = 'define-word';
                span.dataset.term = match[0];
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

    createPopup() {
        const popup = document.createElement('div');
        popup.id = 'dictionaryDefinePopup';
        popup.className = 'define-popup';
        popup.style.display = 'none';
        document.body.appendChild(popup);
        return popup;
    }

    setActiveWord(el) {
        if (this.activeWordEl) {
            this.activeWordEl.classList.remove('define-word-active');
        }

        this.activeWordEl = el;
        this.activeWordEl?.classList.add('define-word-active');
    }

    clearActiveWord() {
        if (this.activeWordEl) {
            this.activeWordEl.classList.remove('define-word-active');
        }
        this.activeWordEl = null;
    }

    async showDefinition(term, rect) {
        this.popup.innerHTML = `
            <div class="define-loading">Looking up <strong>${this.escapeHtml(term)}</strong>...</div>
        `;
        this.popup.style.display = 'block';
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
            const audio = new Audio(src.startsWith('//') ? `https:${src}` : src);
            audio.play().catch(() => { });
        });
    }

    hidePopup() {
        this.popup.style.display = 'none';
        this.clearActiveWord();

        const selection = window.getSelection();
        if (selection && selection.rangeCount > 0) {
            selection.removeAllRanges();
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
    if (document.documentElement.dataset.clickDefineInitialized === 'true') return;
    document.documentElement.dataset.clickDefineInitialized = 'true';
    new ClickDefineController();
}

document.addEventListener('DOMContentLoaded', initClickDefine);

export default initClickDefine;