@extends('layouts.staff') {{-- use your teacher layout (sidebar+topbar). If you don’t have it yet, use layouts.app --}}

@section('title', 'Review Assignment Submission')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Assignment Submission</p>
                <h1 class="text-xl font-semibold text-gray-900">
                    {{ $assignment->title }}
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    Course: <span class="font-semibold">{{ $assignment->course->title ?? '-' }}</span>
                </p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('teacher.submissions.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left"></i> Back to Submissions
                </a>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border
                    {{ ($submission->status ?? 'submitted') === 'graded'
                        ? 'bg-green-50 text-green-700 border-green-200'
                        : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                    {{ ($submission->status ?? 'submitted') === 'graded' ? 'Graded' : 'Needs Review' }}
                </span>
            </div>
        </div>

        {{-- Top cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Student card --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Student</div>
                        <div class="text-lg font-bold text-gray-900">{{ $submission->user->name ?? '—' }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            Username: <span class="font-semibold text-gray-800">{{ $submission->user->username ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 grid place-items-center">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-600">
                    Submitted at:
                    <span class="font-semibold text-gray-900">
                        {{ optional($submission->submitted_at)->format('d M Y, h:i A') ?? $submission->created_at?->format('d M Y, h:i A') ?? '—' }}
                    </span>
                </div>
            </div>

            {{-- Assignment meta --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Assignment Info</div>
                        <div class="text-lg font-bold text-gray-900">Details</div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-100 text-amber-700 grid place-items-center">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Total Marks</div>
                        <div class="font-bold text-gray-900">{{ $assignment->total_marks ?? '—' }}</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3">
                        <div class="text-xs text-gray-500">Grading</div>
                        <div class="font-bold text-gray-900">
                            {{ ($assignment->grading_type ?? 'points') === 'pass_fail' ? 'Pass / Fail' : 'Points' }}
                        </div>
                    </div>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 col-span-2">
                        <div class="text-xs text-gray-500">Due Date</div>
                        <div class="font-semibold text-gray-900">
                            {{ optional($assignment->due_at)->format('d M Y, h:i A') ?? '—' }}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Current grade --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-sm text-gray-500">Current Result</div>
                        <div class="text-lg font-bold text-gray-900">
                            @if(($submission->status ?? '') === 'graded')
                                Graded
                            @else
                                Not graded yet
                            @endif
                        </div>
                    </div>
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 grid place-items-center">
                        <i class="fa-solid fa-check"></i>
                    </div>
                </div>

                <div class="mt-4 text-sm text-gray-700 space-y-2">
                    @if(($assignment->grading_type ?? 'points') === 'pass_fail')
                        <div>
                            Pass/Fail:
                            <span class="font-semibold text-gray-900">
                                {{ is_null($submission->is_passed) ? '—' : ($submission->is_passed ? 'Passed' : 'Failed') }}
                            </span>
                        </div>
                    @else
                        <div>
                            Marks Awarded:
                            <span class="font-semibold text-gray-900">
                                {{ is_null($submission->marks_awarded) ? '—' : $submission->marks_awarded }}
                            </span>
                            <span class="text-gray-500">/ {{ $assignment->total_marks ?? '—' }}</span>
                        </div>
                    @endif

                    <div>
                        Feedback:
                        <span class="font-semibold text-gray-900">
                            {{ $submission->feedback ? 'Added' : '—' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submitted content --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Student Submission</h2>

                <span class="text-xs px-2 py-1 rounded-full border bg-blue-50 text-blue-700 border-blue-200">
                    {{ strtoupper($assignment->submission_type ?? 'mixed') }}
                </span>
            </div>

            <div class="p-6 space-y-5">
                {{-- Text submission --}}
                @if(!empty($submission->submission_text))
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-sm font-semibold text-gray-900 mb-2">Text Answer</div>
                        <div class="prose max-w-none text-gray-800">
                            {!! nl2br(e($submission->submission_text)) !!}
                        </div>
                    </div>
                @endif

                {{-- File submission --}}
                @if(!empty($submission->submission_file))
                    <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="text-sm font-semibold text-gray-900 mb-3">Submitted File</div>

                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ asset('storage/'.$submission->submission_file) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                                <i class="fa-solid fa-download"></i> Download
                            </a>

                            <a href="{{ asset('storage/'.$submission->submission_file) }}" target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50">
                                <i class="fa-solid fa-eye"></i> Preview
                            </a>

                            <span class="text-xs text-gray-500 break-all">
                                {{ $submission->submission_file }}
                            </span>
                        </div>

                        {{-- Optional inline preview for PDF/images --}}
                        @php
                            $file = strtolower($submission->submission_file);
                            $isPdf = str_ends_with($file, '.pdf');
                            $isImg = str_ends_with($file, '.png') || str_ends_with($file, '.jpg') || str_ends_with($file, '.jpeg') || str_ends_with($file, '.webp');
                        @endphp

                        @if($isImg)
                            <div class="mt-4">
                                <img src="{{ asset('storage/'.$submission->submission_file) }}"
                                     class="max-h-96 rounded-2xl border border-gray-200 shadow-sm" />
                            </div>
                        @elseif($isPdf)
                            <div class="mt-4 rounded-2xl overflow-hidden border border-gray-200">
                                <iframe src="{{ asset('storage/'.$submission->submission_file) }}"
                                        class="w-full h-[520px]"></iframe>
                            </div>
                        @endif
                    </div>
                @endif

                @if(empty($submission->submission_text) && empty($submission->submission_file))
                    <div class="text-gray-500 text-sm">
                        No submission content found.
                    </div>
                @endif
            </div>
        </div>

        {{-- Grading form --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Grade Submission</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Fill in marks / pass-fail and optional feedback, then save.
                </p>
            </div>

            <form class="p-6 space-y-5"
                  method="POST"
                  action="{{ route('teacher.assignments.submissions.grade', [$assignment->id, $submission->id]) }}">
                @csrf

                @if(($assignment->grading_type ?? 'points') === 'pass_fail')
                    <div>
                        <label class="text-sm font-semibold text-gray-900">Result</label>
                        <div class="mt-2 grid sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 rounded-2xl border border-gray-200 p-4 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="is_passed" value="1"
                                       {{ old('is_passed', is_null($submission->is_passed) ? null : ($submission->is_passed ? '1' : '0')) === '1' ? 'checked' : '' }}>
                                <span class="font-medium text-gray-900">Pass</span>
                            </label>

                            <label class="flex items-center gap-3 rounded-2xl border border-gray-200 p-4 hover:bg-gray-50 cursor-pointer">
                                <input type="radio" name="is_passed" value="0"
                                       {{ old('is_passed', is_null($submission->is_passed) ? null : ($submission->is_passed ? '1' : '0')) === '0' ? 'checked' : '' }}>
                                <span class="font-medium text-gray-900">Fail</span>
                            </label>
                        </div>
                        @error('is_passed')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div>
                        <label class="text-sm font-semibold text-gray-900">
                            Marks (out of {{ $assignment->total_marks ?? '—' }})
                        </label>
                        <input type="number"
                               name="marks_awarded"
                               min="0"
                               max="{{ (int)($assignment->total_marks ?? 100000) }}"
                               value="{{ old('marks_awarded', $submission->marks_awarded) }}"
                               class="mt-2 w-full rounded-2xl border border-gray-200 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                               placeholder="Enter marks...">
                        @error('marks_awarded')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <div>
                    <label class="text-sm font-semibold text-gray-900">Feedback (optional)</label>
                    <textarea name="feedback"
                              rows="4"
                              class="mt-2 w-full rounded-2xl border border-gray-200 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                              placeholder="Write feedback for the student...">{{ old('feedback', $submission->feedback) }}</textarea>
                    @error('feedback')
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <a href="{{ route('teacher.submissions.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50">
                        Cancel
                    </a>

                    <button class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
                        <i class="fa-solid fa-floppy-disk"></i> Save Grade
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection