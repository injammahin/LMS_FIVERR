@extends('layouts.admin')
@section('title', 'Add Quiz')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Quiz</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">Course: {{ $course->title }}</p>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form id="quizForm" method="POST" action="{{ route('admin.courses.quizzes.store', $course->id) }}"
                class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Title</label>
                    <input name="title" value="{{ old('title') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                </div>

                {{-- Description with Quill --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Description
                        (optional)</label>
                    <input type="hidden" name="description" id="descriptionInput" value="{{ old('description') }}">
                    <div id="quillEditor"
                        class="border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 min-h-[160px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Time Limit
                            (min)</label>
                        <input type="number" name="time_limit_minutes" value="{{ old('time_limit_minutes') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Pass Mark (%)</label>
                        <input type="number" name="pass_mark" value="{{ old('pass_mark') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Max Attempts</label>
                        <input type="number" name="max_attempts" value="{{ old('max_attempts') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/80">
                        <input type="checkbox" name="shuffle_questions" value="1" class="rounded">
                        Shuffle Questions
                    </label>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/80">
                        <input type="checkbox" name="shuffle_options" value="1" class="rounded">
                        Shuffle Options
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.courses.quizzes.index', $course->id) }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>
                    <button class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">Create
                        Quiz</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script>
        const quill = new Quill('#quillEditor', { theme: 'snow' });
        const oldHtml = @json(old('description', ''));
        if (oldHtml) quill.root.innerHTML = oldHtml;

        document.getElementById('quizForm').addEventListener('submit', () => {
            document.getElementById('descriptionInput').value = quill.root.innerHTML;
        });
    </script>
@endsection