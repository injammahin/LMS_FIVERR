{{-- resources/views/admin/ai_assistant/kb/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit AI Training')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-2">
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Edit AI Training</h1>
            <div class="text-sm text-gray-500 dark:text-white/60">
                Update training content. After save, it will sync to AI.
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.ai.kb.index') }}"
               class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back
            </a>

            <button form="kbEditForm"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                Update & Train
            </button>
        </div>
    </div>

    {{-- Errors --}}
    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-800 px-4 py-3 text-sm dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-200">
            <div class="font-semibold mb-1">Please fix the errors below:</div>
            <ul class="list-disc ms-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <form id="kbEditForm" method="POST" action="{{ route('admin.ai.kb.update', $entry) }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Top row --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Scope</label>
                    <select class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2"
                            name="scope" id="scope">
                        <option value="global" {{ old('scope', $entry->scope)==='global'?'selected':'' }}>Global</option>
                        <option value="course" {{ old('scope', $entry->scope)==='course'?'selected':'' }}>Course</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Course</label>
                    <select class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2"
                            name="course_id" id="course_id">
                        <option value="">-- select --</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" {{ (string)old('course_id', $entry->course_id)===(string)$c->id ? 'selected' : '' }}>
                                {{ $c->id }} - {{ $c->title ?? $c->name ?? 'Course' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Type</label>
                    <select class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2"
                            name="type" id="type">
                        <option value="qa" {{ old('type', $entry->type)==='qa'?'selected':'' }}>Q/A</option>
                        <option value="doc" {{ old('type', $entry->type)==='doc'?'selected':'' }}>Document</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Status</label>
                    <label class="flex items-center gap-3 rounded-xl border border-gray-200 dark:border-white/10 px-3 py-2 bg-gray-50 dark:bg-white/5">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $entry->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded border-gray-300">
                        <span class="text-sm font-semibold text-gray-800 dark:text-white/80">Active</span>
                    </label>
                </div>
            </div>

            {{-- Title --}}
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Title</label>
                <input type="text" name="title" value="{{ old('title', $entry->title) }}" required
                       class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2">
            </div>

            {{-- QA Fields --}}
            <div id="qa_fields" class="space-y-4">
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Question (for matching)</label>
                    <textarea name="question" rows="3"
                              class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2">{{ old('question', $entry->question) }}</textarea>
                </div>

                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Answer (exact answer)</label>
                    <textarea name="answer" rows="6"
                              class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2">{{ old('answer', $entry->answer) }}</textarea>
                </div>
            </div>

            {{-- Doc Fields --}}
            <div id="doc_fields" class="space-y-4 hidden">
                <div class="space-y-1">
                    <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Body (article content)</label>
                    <textarea name="body" rows="10"
                              class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2">{{ old('body', $entry->body) }}</textarea>
                </div>
            </div>

            {{-- Keywords --}}
            <div class="space-y-1">
                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Keywords (comma separated)</label>
                <input type="text" name="keywords" value="{{ old('keywords', $entry->keywords) }}"
                       class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white px-3 py-2">
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between gap-2 pt-2">
                <div class="text-xs text-gray-500 dark:text-white/60">
                    Last updated: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $entry->updated_at }}</span>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.ai.kb.index') }}"
                       class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                        Update & Train
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>

<script>
(function () {
    const type = document.getElementById('type');
    const qa = document.getElementById('qa_fields');
    const doc = document.getElementById('doc_fields');

    const scope = document.getElementById('scope');
    const course = document.getElementById('course_id');

    function toggleType() {
        if (type.value === 'qa') {
            qa.classList.remove('hidden');
            doc.classList.add('hidden');
        } else {
            qa.classList.add('hidden');
            doc.classList.remove('hidden');
        }
    }

    function toggleScope() {
        if (scope.value === 'global') {
            course.value = '';
            course.setAttribute('disabled', 'disabled');
            course.classList.add('opacity-60');
        } else {
            course.removeAttribute('disabled');
            course.classList.remove('opacity-60');
        }
    }

    type.addEventListener('change', toggleType);
    scope.addEventListener('change', toggleScope);

    toggleType();
    toggleScope();
})();
</script>
@endsection