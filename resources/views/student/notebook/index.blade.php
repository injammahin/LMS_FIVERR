@extends('layouts.student')

@section('title', 'My Notebook')

@section('content')
    <div class="min-h-screen bg-[radial-gradient(circle_at_top,rgba(59,130,246,0.08),transparent_24%),linear-gradient(to_bottom,#f8fbff,#f8fafc)] dark:bg-[radial-gradient(circle_at_top,rgba(59,130,246,0.12),transparent_18%),linear-gradient(to_bottom,#020617,#0f172a)]">
        <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            {{-- Hero --}}
            <div class="relative overflow-hidden rounded-[34px] border border-white/50 dark:border-white/10 bg-gradient-to-r from-indigo-600 via-blue-700 to-cyan-600 text-white shadow-[0_25px_80px_rgba(37,99,235,0.25)]">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.18),transparent_20%),radial-gradient(circle_at_bottom_left,rgba(255,255,255,0.12),transparent_24%)]"></div>
                <div class="absolute -top-14 right-10 h-44 w-44 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -bottom-20 left-8 h-48 w-48 rounded-full bg-cyan-300/10 blur-3xl"></div>

                <div class="relative px-6 py-7 md:px-8 md:py-8">
                    <div class="flex flex-col xl:flex-row xl:items-end xl:justify-between gap-6">

                        <div class="space-y-4 max-w-3xl">
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 backdrop-blur px-3 py-1.5 text-xs md:text-sm">
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                <span class="text-sm">High School Notebook</span>
                            </div>

                            <div>
                                <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight">
                                    My Personal Notes
                                </h1>
                                <p class="mt-2 text-sm md:text-base text-white/85 max-w-2xl leading-7">
                                    Create, organize, autosave, and export your study notes by subject or course.
                                    Keep everything in one clean learning workspace.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-3">
                                <div class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur">
                                    <i class="fa-solid fa-floppy-disk text-cyan-200"></i>
                                    <span class="text-sm text-sm">Auto-save enabled</span>
                                </div>

                                <div class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur">
                                    <i class="fa-solid fa-layer-group text-cyan-200"></i>
                                    <span class="text-sm text-sm">Organize by subject & course</span>
                                </div>

                                <div class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 backdrop-blur">
                                    <i class="fa-solid fa-file-export text-cyan-200"></i>
                                    <span class="text-sm text-sm">Export anytime</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('student.dashboard') }}"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-white text-blue-700 font-semibold shadow-lg hover:bg-blue-50 transition">
                                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                            </a>

                            <a href="#create-note"
                                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl border border-white/20 bg-white/10 text-white font-semibold backdrop-blur hover:bg-white/15 transition">
                                <i class="fa-solid fa-plus"></i> Create Note
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-green-800 shadow-sm">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
            @endif

            <div class="grid lg:grid-cols-12 gap-6">

                {{-- Sidebar --}}
                <aside class="lg:col-span-4 xl:col-span-4 space-y-5">

                    <div id="create-note"
                        class="rounded-[30px] border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-[0_18px_40px_rgba(15,23,42,0.06)] p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-300">Quick Create</p>
                                <h3 class="mt-1 text-md font-bold text-gray-900 dark:text-white">Create New Note</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-white/50">
                                    Start a new notebook and organize it from the beginning.
                                </p>
                            </div>

                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white grid place-items-center shadow-lg">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('student.notebook.store') }}" class="space-y-4">
                            @csrf

                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Title</label>
                                <input type="text" name="title" value="{{ old('title') }}"
                                    class="mt-1.5 w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Untitled Note">
                            </div>

                            <div class="grid sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Subject</label>
                                    <select name="subject_id"
                                        class="mt-1.5 w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        <option value="">General</option>
                                        @foreach($subjects as $subject)
                                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Course</label>
                                    <select name="course_id"
                                        class="mt-1.5 w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                        <option value="">General</option>
                                        @foreach($courses as $course)
                                            <option value="{{ $course->id }}">{{ $course->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <label class="inline-flex items-center gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                <input type="checkbox" name="is_pinned" value="1" class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                <span class="text-sm">Pin this note to the top</span>
                            </label>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl bg-gradient-to-r from-blue-600 via-blue-700 to-indigo-700 text-white font-semibold shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300">
                                <i class="fa-solid fa-plus"></i> Create Note
                            </button>
                        </form>
                    </div>

                    <div class="rounded-[30px] border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-[0_18px_40px_rgba(15,23,42,0.06)] p-5 md:p-6">
                        <div class="flex items-start justify-between gap-4 mb-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-violet-600 dark:text-violet-300">Smart Filter</p>
                                <h3 class="mt-1 text-md font-bold text-gray-900 dark:text-white">Find Your Notes</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-white/50">
                                    Narrow notes by subject or course.
                                </p>
                            </div>

                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-600 to-purple-600 text-white grid place-items-center shadow-lg">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                        </div>

                        <form method="GET" action="{{ route('student.notebook.index') }}" class="space-y-4">
                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Subject</label>
                                <select name="subject_id"
                                    class="mt-1.5 w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    <option value="">All Subjects</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}" {{ (int) $subjectId === (int) $subject->id ? 'selected' : '' }}>
                                            {{ $subject->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Course</label>
                                <select name="course_id"
                                    class="mt-1.5 w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-violet-500 focus:outline-none">
                                    <option value="">All Courses</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}" {{ (int) $courseId === (int) $course->id ? 'selected' : '' }}>
                                            {{ $course->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-2xl border border-violet-200 dark:border-white/10 bg-violet-50 dark:bg-slate-950 text-violet-700 dark:text-white hover:bg-violet-100 dark:hover:bg-white/5 font-semibold transition">
                                <i class="fa-solid fa-sliders"></i> Apply Filters
                            </button>
                        </form>
                    </div>

                    <div class="rounded-[30px] border border-gray-200/80 dark:border-white/10 bg-white/90 dark:bg-slate-900/90 backdrop-blur shadow-[0_18px_40px_rgba(15,23,42,0.06)] p-5 md:p-6">
                        <div class="flex items-center justify-between gap-4 mb-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-300">Notebook List</p>
                                <h3 class="mt-1 text-md font-bold text-gray-900 dark:text-white">My Notes</h3>
                            </div>

                            <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-white/5 px-3 py-1.5 text-xs font-semibold text-gray-600 dark:text-white/60">
                                <i class="fa-solid fa-book"></i>
                                {{ $notes->count() }}
                            </div>
                        </div>

                        <div class="space-y-3 max-h-[620px] overflow-y-auto pr-1 custom-soft-scroll">
                            @forelse($notes as $note)
                                                        <a href="{{ route('student.notebook.index', ['note' => $note->id, 'subject_id' => $subjectId, 'course_id' => $courseId]) }}"
                                                            class="group block rounded-[24px] border p-4 transition-all duration-300 hover:-translate-y-0.5
                                                            {{ $activeNote && $activeNote->id === $note->id
                                ? 'border-blue-300 bg-gradient-to-r from-blue-50 to-indigo-50 shadow-md dark:bg-blue-900/20'
                                : 'border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 hover:shadow-md' }}">

                                                            <div class="flex items-start justify-between gap-3">
                                                                <div class="min-w-0">
                                                                    <div class="font-bold text-gray-900 dark:text-white truncate">
                                                                        {{ $note->title }}
                                                                    </div>
                                                                    <div class="text-xs text-gray-500 dark:text-white/50 mt-1">
                                                                        Updated {{ optional($note->updated_at)->diffForHumans() }}
                                                                    </div>
                                                                </div>

                                                                @if($note->is_pinned)
                                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 text-amber-700 px-2.5 py-1 text-[10px] font-bold shadow-sm">
                                                                        <i class="fa-solid fa-thumbtack"></i> Pinned
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            @if($note->excerpt)
                                                                <div class="text-sm text-gray-600 dark:text-white/60 mt-3 line-clamp-2 leading-6">
                                                                    {{ $note->excerpt }}
                                                                </div>
                                                            @endif

                                                            <div class="flex flex-wrap gap-2 mt-3">
                                                                @if($note->subject)
                                                                    <span class="px-2.5 py-1 rounded-full text-[11px] bg-purple-100 text-purple-700 text-sm">
                                                                        {{ $note->subject->name }}
                                                                    </span>
                                                                @endif

                                                                @if($note->course)
                                                                    <span class="px-2.5 py-1 rounded-full text-[11px] bg-blue-100 text-blue-700 text-sm">
                                                                        {{ $note->course->title }}
                                                                    </span>
                                                                @endif
                                                            </div>

                                                            <div class="mt-3 flex items-center gap-2 text-xs font-semibold text-blue-700 dark:text-blue-300 opacity-0 group-hover:opacity-100 transition">
                                                                <span>Open note</span>
                                                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                                            </div>
                                                        </a>
                            @empty
                                <div class="rounded-[24px] border border-dashed border-gray-300 dark:border-white/10 p-8 text-center bg-gray-50/70 dark:bg-white/5">
                                    <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white grid place-items-center shadow-lg mb-4">
                                        <i class="fa-solid fa-book-open"></i>
                                    </div>
                                    <div class="text-base font-bold text-gray-900 dark:text-white">No notes found yet</div>
                                    <div class="text-sm text-gray-500 dark:text-white/50 mt-2">
                                        Create your first notebook and start building your study space.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </aside>

                {{-- Main Editor --}}
                <section class="lg:col-span-8 xl:col-span-8">
                    @if($activeNote)
                        <div class="rounded-[34px] border border-gray-200/80 dark:border-white/10 bg-white/95 dark:bg-slate-900/95 backdrop-blur shadow-[0_24px_70px_rgba(15,23,42,0.08)] overflow-hidden">

                            <div class="relative overflow-hidden border-b border-gray-200 dark:border-white/10 bg-gradient-to-r from-slate-50 via-white to-blue-50 dark:from-slate-900 dark:via-slate-900 dark:to-slate-800 px-6 py-5 md:px-7">
                                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.10),transparent_24%)]"></div>

                                <div class="relative flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2 mb-2">
                                            <span class="inline-flex items-center gap-2 rounded-full bg-blue-100 text-blue-700 px-3 py-1 text-xs font-bold uppercase tracking-[0.16em]">
                                                <i class="fa-solid fa-pen-nib"></i> Notebook Editor
                                            </span>

                                            @if($activeNote->subject)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-purple-100 text-purple-700 px-3 py-1 text-xs font-semibold">
                                                    {{ $activeNote->subject->name }}
                                                </span>
                                            @endif

                                            @if($activeNote->course)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-cyan-100 text-cyan-700 px-3 py-1 text-xs font-semibold">
                                                    {{ $activeNote->course->title }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="text-md md:text-md font-extrabold text-gray-900 dark:text-white">
                                            {{ $activeNote->title }}
                                        </h3>

                                        <p class="text-sm text-gray-500 dark:text-white/50 mt-2">
                                            Your note autosaves while you type, so your work stays protected.
                                        </p>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('student.notebook.export', [$activeNote->id, 'html']) }}"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white/90 dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                                            <i class="fa-solid fa-download"></i> Export HTML
                                        </a>

                                        <a href="{{ route('student.notebook.export', [$activeNote->id, 'txt']) }}"
                                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white/90 dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                                            <i class="fa-solid fa-file-lines"></i> Export TXT
                                        </a>

                                        <form method="POST" action="{{ route('student.notebook.destroy', $activeNote->id) }}"
                                            onsubmit="return confirm('Delete this note?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-red-600 text-white hover:bg-red-700 text-sm shadow-sm">
                                                <i class="fa-solid fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 md:p-7 space-y-6">

                                {{-- Editor --}}
                                <div class="rounded-[28px] border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm p-5">
                                    <div class="space-y-5"
                                        data-note-editor
                                        data-autosave-url="{{ route('student.notebook.autosave', $activeNote->id) }}"
                                        data-subject-id="{{ $activeNote->subject_id }}"
                                        data-course-id="{{ $activeNote->course_id }}">

                                        <div class="grid xl:grid-cols-[1fr_auto] gap-3">
                                            <input type="text" value="{{ $activeNote->title }}" data-note-title
                                                class="w-full rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-4 py-3.5 text-md font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                                placeholder="Note title">

                                            <label class="inline-flex items-center justify-center gap-2 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3.5 text-sm text-amber-800 text-sm">
                                                <input type="checkbox" data-note-pin {{ $activeNote->is_pinned ? 'checked' : '' }} class="rounded border-amber-300 text-amber-600 focus:ring-amber-500">
                                                Pin this note
                                            </label>
                                        </div>

                                        <div class="rounded-2xl border border-gray-200 dark:border-white/10 p-3 bg-gray-50 dark:bg-slate-950 shadow-inner">
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
                                                    <button class="ql-blockquote"></button>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="note-editor-shell">
                                            <div data-note-editor-body></div>
                                        </div>

                                        <input type="hidden" data-note-body-html value="{{ $activeNote->body_html }}">
                                        <textarea class="hidden" data-note-body-text>{{ $activeNote->body_text }}</textarea>

                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <div class="inline-flex items-center gap-2 rounded-full bg-gray-100 dark:bg-white/5 px-3.5 py-2 text-sm text-gray-600 dark:text-white/60" data-note-status>
                                                <i class="fa-solid fa-cloud-check text-emerald-500"></i>
                                                {{ $activeNote->last_saved_at ? 'Last saved at ' . $activeNote->last_saved_at->format('h:i:s A') : 'Ready to save' }}
                                            </div>

                                            <button type="button" data-note-save-now
                                                class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-700 text-white hover:shadow-lg font-semibold transition">
                                                <i class="fa-solid fa-floppy-disk"></i> Save Now
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Attachments --}}
                                <div class="rounded-[28px] border border-gray-200 dark:border-white/10 bg-gradient-to-br from-slate-50 to-white dark:from-slate-950 dark:to-slate-900 p-5 md:p-6 shadow-sm space-y-5">
                                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600 dark:text-emerald-300">Resource Shelf</p>
                                            <h4 class="mt-1 text-md font-bold text-gray-900 dark:text-white">Attachments</h4>
                                            <p class="mt-1 text-sm text-gray-500 dark:text-white/50">
                                                Add files, images, documents, or supporting materials to this note.
                                            </p>
                                        </div>

                                        <span class="inline-flex items-center gap-2 rounded-full bg-emerald-100 text-emerald-700 px-3 py-1.5 text-xs font-bold">
                                            <i class="fa-solid fa-paperclip"></i>
                                            {{ $activeNote->attachments->count() }} file{{ $activeNote->attachments->count() === 1 ? '' : 's' }}
                                        </span>
                                    </div>

                                    <form method="POST" action="{{ route('student.notebook.attachments.store', $activeNote->id) }}"
                                        enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-3">
                                        @csrf

                                        <input type="file" name="attachment"
                                            class="block w-full text-sm text-gray-700 dark:text-white file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">

                                        <button type="submit"
                                            class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold shadow-sm">
                                            <i class="fa-solid fa-paperclip"></i> Upload
                                        </button>
                                    </form>

                                    <div class="space-y-3">
                                        @forelse($activeNote->attachments as $attachment)
                                            <div class="group flex flex-col md:flex-row md:items-center md:justify-between gap-4 rounded-[22px] border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 px-4 py-4 shadow-sm hover:shadow-md transition">
                                                <div class="min-w-0 flex items-center gap-4">
                                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-500 text-white grid place-items-center shadow">
                                                        <i class="fa-solid fa-file"></i>
                                                    </div>

                                                    <div class="min-w-0">
                                                        <div class="font-semibold text-gray-900 dark:text-white truncate">
                                                            {{ $attachment->original_name }}
                                                        </div>
                                                        <div class="text-xs text-gray-500 dark:text-white/50 mt-1">
                                                            {{ $attachment->mime_type }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap gap-2">
                                                    <a href="{{ asset('storage/' . $attachment->file_path) }}" target="_blank"
                                                        class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                                                        <i class="fa-solid fa-eye"></i> View
                                                    </a>

                                                    <form method="POST"
                                                        action="{{ route('student.notebook.attachments.destroy', $attachment->id) }}"
                                                        onsubmit="return confirm('Delete this attachment?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                            class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl bg-red-600 text-white hover:bg-red-700 text-sm">
                                                            <i class="fa-solid fa-trash"></i> Remove
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="rounded-[22px] border border-dashed border-gray-300 dark:border-white/10 p-8 text-center bg-white/70 dark:bg-white/5">
                                                <div class="w-16 h-16 mx-auto rounded-2xl bg-gradient-to-br from-emerald-500 to-cyan-500 text-white grid place-items-center shadow-lg mb-4">
                                                    <i class="fa-solid fa-paperclip"></i>
                                                </div>
                                                <div class="text-base font-bold text-gray-900 dark:text-white">No attachments yet</div>
                                                <div class="text-sm text-gray-500 dark:text-white/50 mt-2">
                                                    Upload helpful files or images for this note.
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                            </div>
                        </div>
                    @else
                        <div class="rounded-[34px] border border-dashed border-gray-300 dark:border-white/10 bg-white/80 dark:bg-slate-900/90 backdrop-blur p-12 text-center shadow-sm">
                            <div class="w-20 h-20 mx-auto rounded-[28px] bg-gradient-to-br from-blue-600 to-indigo-700 text-white grid place-items-center shadow-xl mb-5">
                                <i class="fa-solid fa-book-open text-2xl"></i>
                            </div>

                            <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white">No note selected</h3>
                            <p class="text-sm text-gray-500 dark:text-white/50 mt-3 max-w-md mx-auto leading-7">
                                Pick a note from the left side or create a new one to start building your study notebook.
                            </p>

                            <a href="#create-note"
                                class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-blue-600 text-white hover:bg-blue-700 font-semibold shadow-lg transition">
                                <i class="fa-solid fa-plus"></i> Create Your First Note
                            </a>
                        </div>
                    @endif
                </section>

            </div>
        </div>
    </div>

    <style>
        .custom-soft-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .custom-soft-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-soft-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.4);
            border-radius: 999px;
        }

        .custom-soft-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.55);
        }
    </style>
@endsection