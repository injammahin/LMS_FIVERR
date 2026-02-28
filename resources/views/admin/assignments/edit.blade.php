@extends('layouts.admin')

@section('title', 'Edit Assignment')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
            submissionType: '{{ old('submission_type', $assignment->submission_type) }}',
            gradingType: '{{ old('grading_type', $assignment->grading_type) }}',
            allowLate: {{ old('allow_late', $assignment->allow_late) ? 'true' : 'false' }}
         }">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Assignment</h1>
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
            <form id="assignmentForm" method="POST"
                action="{{ route('admin.courses.assignments.update', [$course->id, $assignment->id]) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $assignment->title) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Description
                        (optional)</label>
                    <input type="hidden" name="description" id="descriptionInput"
                        value="{{ old('description', $assignment->description) }}">
                    <div id="quillEditor"
                        class="border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 min-h-[180px]">
                    </div>
                </div>

                {{-- Current Attachment --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Current Attachment</label>
                    @if($assignment->attachment)
                        <div class="flex items-center gap-4">
                            <a class="text-blue-600 underline" target="_blank"
                                href="{{ asset('storage/' . $assignment->attachment) }}">Open attachment</a>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-white/70">
                                <input type="checkbox" name="remove_attachment" value="1" class="rounded">
                                Remove
                            </label>
                        </div>
                    @else
                        <p class="text-sm text-gray-400">No attachment.</p>
                    @endif
                </div>

                {{-- Upload New Attachment --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Upload New Attachment
                        (optional)</label>
                    <input type="file" name="attachment"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                </div>

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

                    <div x-show="gradingType === 'points'">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Total Marks</label>
                        <input type="number" name="total_marks" min="1"
                            value="{{ old('total_marks', $assignment->total_marks) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Max Attempts
                            (optional)</label>
                        <input type="number" name="max_attempts" min="1"
                            value="{{ old('max_attempts', $assignment->max_attempts) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Due At
                            (optional)</label>
                        <input type="datetime-local" name="due_at"
                            value="{{ old('due_at', optional($assignment->due_at)->format('Y-m-d\TH:i')) }}"
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
                        <input type="datetime-local" name="late_until"
                            value="{{ old('late_until', optional($assignment->late_until)->format('Y-m-d\TH:i')) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="draft" {{ old('status', $assignment->status) === 'draft' ? 'selected' : '' }}>Draft
                        </option>
                        <option value="published" {{ old('status', $assignment->status) === 'published' ? 'selected' : '' }}>
                            Published</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.courses.assignments.index', $course->id) }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Save Changes
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

        const initialHtml = @json(old('description', $assignment->description ?? ''));
        if (initialHtml) quill.root.innerHTML = initialHtml;

        document.getElementById('assignmentForm').addEventListener('submit', function () {
            document.getElementById('descriptionInput').value = quill.root.innerHTML;
        });
    </script>
@endsection