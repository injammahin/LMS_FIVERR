@extends('layouts.admin')

@section('title', 'Submission Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Submission</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Assignment: <span class="font-medium">{{ $assignment->title }}</span><br>
                Student: <span class="font-medium">{{ $submission->user?->name }}</span>
                <span class="text-xs text-gray-400">({{ $submission->user?->email }})</span>
            </p>
        </div>

        <a href="{{ route('admin.assignments.submissions.index', $assignment->id) }}"
           class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
            Back
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Submitted content --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
        <div class="flex justify-between text-sm text-gray-600 dark:text-white/70">
            <span>Status: <b>{{ $submission->status }}</b></span>
            <span>Submitted at: <b>{{ $submission->submitted_at?->format('d M Y, h:i A') ?? '-' }}</b></span>
        </div>

        @if($submission->submission_text)
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">Text Submission</h3>
                <div class="prose max-w-none dark:prose-invert">
                    {!! nl2br(e($submission->submission_text)) !!}
                </div>
            </div>
        @endif

        @if($submission->submission_file)
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white mb-2">File Submission</h3>
                <a class="text-blue-600 underline" target="_blank"
                   href="{{ asset('storage/'.$submission->submission_file) }}">
                    Open file
                </a>
            </div>
        @endif
    </div>

    {{-- Grading --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6 space-y-4">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Grade & Feedback</h3>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.assignments.submissions.grade', [$assignment->id, $submission->id]) }}"
              enctype="multipart/form-data"
              class="space-y-4">
            @csrf

            @if($assignment->grading_type === 'points')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                        Marks Awarded (out of {{ $assignment->total_marks }})
                    </label>
                    <input type="number" name="marks_awarded" min="0"
                           value="{{ old('marks_awarded', $submission->marks_awarded) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                </div>
            @else
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-2">Result</label>
                    <div class="flex gap-6 text-sm">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="is_passed" value="1"
                                   {{ old('is_passed', $submission->is_passed) === true ? 'checked' : '' }}>
                            Pass
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="is_passed" value="0"
                                   {{ old('is_passed', $submission->is_passed) === false ? 'checked' : '' }}>
                            Fail
                        </label>
                    </div>
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Feedback (optional)</label>
                <textarea name="feedback" rows="4"
                          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">{{ old('feedback', $submission->feedback) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Feedback File (optional)</label>
                <input type="file" name="feedback_file"
                       class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                @if($submission->feedback_file)
                    <p class="text-xs text-gray-500 mt-2">
                        Current:
                        <a class="text-blue-600 underline" target="_blank"
                           href="{{ asset('storage/'.$submission->feedback_file) }}">Open feedback file</a>
                    </p>
                @endif
            </div>

            <div class="flex justify-end">
                <button class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Save Grade
                </button>
            </div>
        </form>
    </div>

</div>
@endsection