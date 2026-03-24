@if(!empty($note))
    <div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50">
        <button @click="open = true"
            class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 text-white shadow-lg hover:bg-indigo-700">
            <i class="fa-solid fa-book-open"></i> Notebook
        </button>

        <div x-show="open" x-transition
            class="fixed inset-y-0 right-0 w-full max-w-2xl bg-white dark:bg-slate-900 notebook-drawer border-l border-gray-200 dark:border-white/10"
            style="display:none;">
            <div class="h-full flex flex-col">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-white/10">
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white">Lesson Notebook</h3>
                        <p class="text-sm text-gray-500 dark:text-white/50">{{ $course->title }}</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('student.notebook.index', ['note' => $note->id, 'course_id' => $note->course_id]) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5">
                            Full Notebook
                        </a>

                        <button @click="open = false"
                            class="inline-flex items-center justify-center w-10 h-10 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-5 space-y-4" data-note-editor
                    data-autosave-url="{{ route('student.notebook.autosave', $note->id) }}"
                    data-subject-id="{{ $note->subject_id }}" data-course-id="{{ $note->course_id }}">

                    <input type="text" value="{{ $note->title }}" data-note-title
                        class="w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none text-lg font-semibold"
                        placeholder="Note title">

                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/70">
                        <input type="checkbox" data-note-pin {{ $note->is_pinned ? 'checked' : '' }}>
                        Pin this note
                    </label>

                    <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-3 bg-gray-50 dark:bg-slate-950">
                        <div class="flex flex-wrap gap-2" data-note-toolbar>
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                            </span>
                            <span class="ql-formats">
                                <select class="ql-header">
                                    <option selected></option>
                                    <option value="1"></option>
                                    <option value="2"></option>
                                </select>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link"></button>
                            </span>
                        </div>
                    </div>

                    <div class="note-editor-shell quick-note-shell">
                        <div data-note-editor-body></div>
                    </div>

                    <input type="hidden" data-note-body-html value="{{ $note->body_html }}">
                    <textarea class="hidden" data-note-body-text>{{ $note->body_text }}</textarea>

                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-white/50" data-note-status>
                            {{ $note->last_saved_at ? 'Last saved at ' . $note->last_saved_at->format('h:i:s A') : 'Ready to save' }}
                        </div>

                        <button type="button" data-note-save-now
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                            <i class="fa-solid fa-floppy-disk"></i> Save Now
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif