import Quill from 'quill';
import 'quill/dist/quill.snow.css';

function debounce(fn, delay = 1200) {
    let timer = null;

    return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => fn(...args), delay);
    };
}

function initNotebookEditors() {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    document.querySelectorAll('[data-note-editor]').forEach((root) => {
        if (root.dataset.editorInitialized === 'true') return;
        root.dataset.editorInitialized = 'true';

        const toolbar = root.querySelector('[data-note-toolbar]');
        const editorEl = root.querySelector('[data-note-editor-body]');
        const titleInput = root.querySelector('[data-note-title]');
        const pinInput = root.querySelector('[data-note-pin]');
        const hiddenHtml = root.querySelector('[data-note-body-html]');
        const hiddenText = root.querySelector('[data-note-body-text]');
        const statusEl = root.querySelector('[data-note-status]');
        const saveNowBtn = root.querySelector('[data-note-save-now]');

        const autosaveUrl = root.dataset.autosaveUrl;
        const subjectId = root.dataset.subjectId || '';
        const courseId = root.dataset.courseId || '';

        if (!editorEl || !autosaveUrl) return;

        const quill = new Quill(editorEl, {
            theme: 'snow',
            modules: {
                toolbar,
            },
            placeholder: 'Start writing your notes...',
        });

        quill.root.innerHTML = hiddenHtml?.value || '';

        function syncInputs() {
            if (hiddenHtml) hiddenHtml.value = quill.root.innerHTML;
            if (hiddenText) hiddenText.value = quill.getText().trim();
        }

        async function saveNote() {
            syncInputs();

            if (statusEl) {
                statusEl.textContent = 'Saving...';
            }

            try {
                const response = await fetch(autosaveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        title: titleInput?.value || 'Untitled Note',
                        body_html: hiddenHtml?.value || '',
                        body_text: hiddenText?.value || '',
                        subject_id: subjectId || null,
                        course_id: courseId || null,
                        is_pinned: pinInput?.checked ? 1 : 0,
                    }),
                });

                if (!response.ok) {
                    throw new Error('Autosave failed');
                }

                const data = await response.json();

                if (statusEl) {
                    statusEl.textContent = `Saved at ${data.saved_at}`;
                }
            } catch (error) {
                if (statusEl) {
                    statusEl.textContent = 'Autosave failed. Please try again.';
                }
            }
        }

        const debouncedSave = debounce(saveNote, 1200);

        quill.on('text-change', () => {
            syncInputs();
            debouncedSave();
        });

        titleInput?.addEventListener('input', debouncedSave);
        pinInput?.addEventListener('change', debouncedSave);

        saveNowBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            saveNote();
        });
    });
}

document.addEventListener('DOMContentLoaded', initNotebookEditors);
document.addEventListener('livewire:navigated', initNotebookEditors);

export default initNotebookEditors;