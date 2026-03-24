@extends('layouts.student')

@section('title', $assignment->title)

@section('content')
    @php
        $isSubmitted = !empty($submission);
        $maxAttempts = (int) ($assignment->max_attempts ?? 0);
        $attemptText = $maxAttempts > 0 ? "{$usedAttempts}/{$maxAttempts}" : "{$usedAttempts}/∞";

        $canSubmit = true;
        if ($maxAttempts > 0 && $usedAttempts >= $maxAttempts) {
            $canSubmit = false;
        }

        $clickDefineEnabled = !empty($showClickDefine);
    @endphp

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <p class="text-sm text-gray-500">Assignment</p>

                        @if($isSubmitted)
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 border border-green-200">
                                Submitted
                            </span>
                        @else
                            <span
                                class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                Not submitted
                            </span>
                        @endif
                    </div>

                    <h1 class="text-lg font-semibold text-gray-900">{{ $assignment->title }}</h1>

                    <p class="text-sm text-gray-600">
                        Course: <span class="font-medium">{{ $course->title }}</span>
                        • Attempts: <span class="font-semibold">{{ $attemptText }}</span>
                    </p>

                    @if(!empty($assignment->due_at))
                        <p class="text-xs text-gray-500">
                            Due: <span class="font-semibold">{{ $assignment->due_at->format('d M Y, h:i A') }}</span>
                            @if($assignment->allow_late)
                                • Late allowed
                                @if(!empty($assignment->late_until))
                                    until <span class="font-semibold">{{ $assignment->late_until->format('d M Y, h:i A') }}</span>
                                @endif
                            @endif
                        </p>
                    @endif
                </div>

                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>

            @if($clickDefineEnabled)
                <x-student.click-define-toolbar title="Click to Define" />
            @endif

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-5 text-green-900">
                    <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i> {{ session('error') }}
                </div>
            @endif

            {{-- Assignment description: safe for dictionary --}}
            @if($assignment->description)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="prose max-w-none" @if($clickDefineEnabled) data-define-area @endif>
                        {!! $assignment->description !!}
                    </div>
                </div>
            @endif

            {{-- Attachment --}}
            @if($assignment->attachment)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <p class="text-sm text-gray-500 mb-2">Attachment</p>

                    <div data-define-skip>
                        <a href="{{ asset('storage/' . $assignment->attachment) }}" target="_blank"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                            <i class="fa-solid fa-download"></i> Download attachment
                        </a>
                    </div>
                </div>
            @endif

            {{-- Previous submission display: safe on submitted text only --}}
            @if($isSubmitted)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Your submission</h3>
                        <div class="text-xs text-gray-500">
                            Submitted at:
                            <span class="font-semibold">{{ optional($submission->submitted_at)->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>

                    @if(!empty($submission->submission_text))
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                            <div class="text-xs text-gray-500 mb-2">Submitted Text</div>

                            <div class="prose max-w-none" @if($clickDefineEnabled) data-define-area @endif>
                                {!! nl2br(e($submission->submission_text)) !!}
                            </div>
                        </div>
                    @endif

                    @if(!empty($submission->submission_file))
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500">Submitted File</div>
                                <div class="font-semibold text-gray-900">File uploaded</div>
                            </div>

                            <div data-define-skip>
                                <a href="{{ asset('storage/' . $submission->submission_file) }}" target="_blank"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700">
                                    <i class="fa-solid fa-file-arrow-down"></i> View / Download
                                </a>
                            </div>
                        </div>
                    @endif

                    @if(($submission->status ?? '') === 'graded')
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <div class="font-semibold text-amber-900">Graded</div>

                            @if($assignment->grading_type === 'points')
                                <div class="text-sm text-amber-800 mt-1">
                                    Marks:
                                    <span class="font-semibold">{{ $submission->marks_awarded }}</span>
                                    / {{ $assignment->total_marks }}
                                </div>
                            @else
                                <div class="text-sm text-amber-800 mt-1">
                                    Result:
                                    <span class="font-semibold">{{ $submission->is_passed ? 'Passed' : 'Failed' }}</span>
                                </div>
                            @endif

                            @if(!empty($submission->feedback))
                                <div class="text-sm text-amber-800 mt-2">
                                    Feedback: {{ $submission->feedback }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif

            {{-- Submission form: NO dictionary here --}}
            @if($canSubmit)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4">
                    <h3 class="font-semibold text-gray-900">
                        {{ $isSubmitted ? 'Submit again (new attempt)' : 'Submit your work' }}
                    </h3>

                    <form method="POST" action="{{ route('student.assignments.submit', [$course->id, $assignment->id]) }}"
                        enctype="multipart/form-data" class="space-y-4" data-define-skip>
                        @csrf

                        @if(in_array($assignment->submission_type, ['text', 'text_file']))
                            <div>
                                <label class="text-sm font-medium text-gray-700">Submission Text</label>
                                <textarea name="submission_text" rows="5"
                                    class="mt-1 w-full rounded-2xl border border-gray-200 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Write your answer...">{{ old('submission_text') }}</textarea>

                                @error('submission_text')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        @if(in_array($assignment->submission_type, ['file', 'text_file']))
                            <div>
                                <label class="text-sm font-medium text-gray-700">Upload File</label>
                                <input type="file" name="submission_file" class="mt-1 block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4
                                                    file:rounded-xl file:border-0 file:text-sm file:font-semibold
                                                    file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition" />

                                @error('submission_file')
                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">
                            <i class="fa-solid fa-upload"></i>
                            {{ $isSubmitted ? 'Submit Again' : 'Submit Assignment' }}
                        </button>
                    </form>
                </div>
            @else
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 text-gray-700">
                    <div class="font-semibold">Submission closed</div>
                    <div class="text-sm text-gray-600 mt-1">
                        You have used all attempts for this assignment.
                    </div>
                </div>
            @endif

        </div>
    </div>
@endsection