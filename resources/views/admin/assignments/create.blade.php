@extends('layouts.admin')

@section('title', 'Add Assignment')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
            submissionType: '{{ old('submission_type', 'file') }}',
            gradingType: '{{ old('grading_type', 'points') }}',
            allowLate: {{ old('allow_late', 0) ? 'true' : 'false' }}
         }">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Assignment</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Course: <span class="font-medium">{{ $course->title }}</span>
                </p>
            </div>

            <a href="{{ route('admin.courses.assignments.index', $course->id) }}"
                class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back
            </a>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form id="assignmentForm" method="POST" action="{{ route('admin.courses.assignments.store', $course->id) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                </div>

                {{-- Description (Quill) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Description
                        (optional)</label>
                    <input type="hidden" name="description" id="descriptionInput" value="{{ old('description') }}">
                    <div id="quillEditor"
                        class="border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 min-h-[180px]">
                    </div>
                </div>

                {{-- Teacher Attachment --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Attachment
                        (optional)</label>
                    <input type="file" name="attachment"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">PDF/DOCX/ZIP/Images up to 50MB</p>
                </div>

                {{-- Submission + Grading Settings --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Submission
                            Type</label>
                        <select name="submission_type" x-model="submissionType"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                            <option value="text">Text Only</option>
                            <option value="file">File Only</option>
                            <option value="text_file">Text + File</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Grading Type</label>
                        <select name="grading_type" x-model="gradingType"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                            <option value="points">Points</option>
                            <option value="pass_fail">Pass / Fail</option>
                        </select>
                    </div>

                    {{-- Total Marks (only if points) --}}
                    <div x-show="gradingType === 'points'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Total Marks</label>
                        <input type="number" name="total_marks" min="1" value="{{ old('total_marks', 10) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Max Attempts
                            (optional)</label>
                        <input type="number" name="max_attempts" min="1" value="{{ old('max_attempts') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <p class="text-xs text-gray-400 mt-1">Leave empty for unlimited</p>
                    </div>
                </div>

                {{-- Due / Late --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Due At
                            (optional)</label>
                        <input type="datetime-local" name="due_at" value="{{ old('due_at') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>

                    <div class="pt-6">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/80">
                            <input type="checkbox" name="allow_late" value="1" x-model="allowLate" class="rounded">
                            Allow Late Submissions
                        </label>
                    </div>

                    <div x-show="allowLate" class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Late Until
                            (optional)</label>
                        <input type="datetime-local" name="late_until" value="{{ old('late_until') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.courses.assignments.index', $course->id) }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Assignment
                    </button>
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

        document.getElementById('assignmentForm').addEventListener('submit', function () {
            document.getElementById('descriptionInput').value = quill.root.innerHTML;
        });
    </script>
@endsection