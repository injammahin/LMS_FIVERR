@extends('layouts.teacher')
@section('title', 'Student Progress')

@section('content')
@php
    $subjectName  = optional($course->subject)->name ?? '-';
    $divisionName = optional(optional($course->subject)->division)->name ?? '-';

    $lessonTotal = $course->lessons->count();
    $quizTotal = $course->quizzes->count();
    $assTotal = $course->assignments->count();

    $lessonDone = $course->lessons->filter(fn($l)=>!empty(($lessonProgress[$l->id] ?? null)?->completed_at))->count();
    $lessonViewed = $course->lessons->filter(fn($l)=>!empty(($lessonProgress[$l->id] ?? null)?->viewed_at))->count();

    $quizSubmitted = $course->quizzes->filter(function($q) use ($quizAttempts){
        $rows = $quizAttempts[$q->id] ?? collect();
        return $rows->whereNotNull('submitted_at')->count() > 0;
    })->count();

    $assSubmitted = $course->assignments->filter(function($a) use ($assignmentSubs){
        $rows = $assignmentSubs[$a->id] ?? collect();
        return $rows->count() > 0;
    })->count();

    $totalItems = $lessonTotal + $quizTotal + $assTotal;
    $doneItems = $lessonDone + $quizSubmitted + $assSubmitted;
    $overallPercent = $totalItems > 0 ? (int)round(($doneItems / $totalItems) * 100) : 0;

    $lessonPct = $lessonTotal > 0 ? (int)round(($lessonDone / $lessonTotal) * 100) : 0;
    $quizPct   = $quizTotal > 0 ? (int)round(($quizSubmitted / $quizTotal) * 100) : 0;
    $assPct    = $assTotal > 0 ? (int)round(($assSubmitted / $assTotal) * 100) : 0;
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500">Student Progress</div>
                <div class="text-sm font-semibold text-gray-900 mt-1">{{ $student->name }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ $student->username ?? $student->email ?? '-' }}</div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-xs border bg-gray-50 border-gray-200 text-gray-700">
                        Course: <span class="font-semibold text-gray-900">{{ $course->title }}</span>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs border bg-blue-50 border-blue-100 text-blue-700">
                        Subject: <span class="font-semibold">{{ $subjectName }}</span>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs border bg-emerald-50 border-emerald-100 text-emerald-800">
                        Division: <span class="font-semibold">{{ $divisionName }}</span>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs border bg-emerald-50 border-emerald-200 text-emerald-800">
                        Overall: <span class="font-semibold">{{ $overallPercent }}%</span>
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('teacher.courses.show', $course->id) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                    <i class="fa-solid fa-arrow-left"></i> Back to Course
                </a>
            </div>
        </div>

        {{-- progress bars --}}
        <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                    <span>Lessons</span><span class="font-semibold text-gray-900">{{ $lessonPct }}%</span>
                </div>
                <div class="h-2 rounded-full bg-white border border-gray-200 overflow-hidden">
                    <div class="h-2 rounded-full" style="width: {{ $lessonPct }}%; background:#2563eb;"></div>
                </div>
                <div class="text-xs text-gray-500 mt-2">Done {{ $lessonDone }}/{{ $lessonTotal }} • Viewed {{ $lessonViewed }}</div>
            </div>

            <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                    <span>Quizzes</span><span class="font-semibold text-gray-900">{{ $quizPct }}%</span>
                </div>
                <div class="h-2 rounded-full bg-white border border-gray-200 overflow-hidden">
                    <div class="h-2 rounded-full" style="width: {{ $quizPct }}%; background:#a855f7;"></div>
                </div>
                <div class="text-xs text-gray-500 mt-2">Submitted {{ $quizSubmitted }}/{{ $quizTotal }}</div>
            </div>

            <div class="p-4 rounded-2xl border border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between text-xs text-gray-600 mb-2">
                    <span>Assignments</span><span class="font-semibold text-gray-900">{{ $assPct }}%</span>
                </div>
                <div class="h-2 rounded-full bg-white border border-gray-200 overflow-hidden">
                    <div class="h-2 rounded-full" style="width: {{ $assPct }}%; background:#f59e0b;"></div>
                </div>
                <div class="text-xs text-gray-500 mt-2">Submitted {{ $assSubmitted }}/{{ $assTotal }}</div>
            </div>
        </div>
    </div>

    {{-- LESSONS --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200">
            <div class="text-sm font-semibold text-gray-900">Lessons</div>
            <div class="text-xs text-gray-500 mt-1">Done / Viewed / Not started</div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($course->lessons as $lesson)
                @php
                    $p = $lessonProgress[$lesson->id] ?? null;
                    $done = !empty($p?->completed_at);
                    $viewed = !empty($p?->viewed_at);
                @endphp

                <div class="p-4 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-xs text-gray-500">Position {{ $lesson->position ?? '-' }}</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $lesson->title }}</div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($done)
                            <span class="px-3 py-1 rounded-full text-xs border border-emerald-200 bg-emerald-50 text-emerald-800">Done</span>
                        @elseif($viewed)
                            <span class="px-3 py-1 rounded-full text-xs border border-blue-200 bg-blue-50 text-blue-800">Viewed</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs border border-gray-200 bg-gray-50 text-gray-700">Not started</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">No lessons in this course.</div>
            @endforelse
        </div>
    </div>

    {{-- QUIZZES --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200">
            <div class="text-sm font-semibold text-gray-900">Quizzes</div>
            <div class="text-xs text-gray-500 mt-1">Attempts, submitted, and status</div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($course->quizzes as $quiz)
                @php
                    $rows = $quizAttempts[$quiz->id] ?? collect();

                    $used = $rows->whereNotNull('submitted_at')->count();
                    $max  = (int)($quiz->max_attempts ?? 0);
                    $attemptText = $max > 0 ? "{$used}/{$max}" : "{$used}/∞";

                    $hasInProgress = $rows->where('status','in_progress')->count() > 0;
                    $submitted = $used > 0;

                    if ($hasInProgress) {
                        $badgeText = 'In progress';
                        $badgeClass = 'border-blue-200 bg-blue-50 text-blue-800';
                    } elseif ($submitted) {
                        $badgeText = 'Submitted';
                        $badgeClass = 'border-purple-200 bg-purple-50 text-purple-800';
                    } else {
                        $badgeText = 'Not started';
                        $badgeClass = 'border-gray-200 bg-gray-50 text-gray-700';
                    }

                    $lastSubmitted = $rows->whereNotNull('submitted_at')->first();
                @endphp

                <div class="p-4 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">{{ $quiz->title }}</div>
                        <div class="text-xs text-gray-500 mt-1">Attempts: <span class="font-semibold text-gray-900">{{ $attemptText }}</span></div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs border {{ $badgeClass }}">{{ $badgeText }}</span>

                        @if($lastSubmitted && \Illuminate\Support\Facades\Route::has('teacher.quizzes.attempts.show'))
                            <a href="{{ route('teacher.quizzes.attempts.show', $lastSubmitted->id) }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 bg-white hover:bg-gray-50">
                                Open Attempt
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">No quizzes in this course.</div>
            @endforelse
        </div>
    </div>

    {{-- ASSIGNMENTS --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200">
            <div class="text-sm font-semibold text-gray-900">Assignments</div>
            <div class="text-xs text-gray-500 mt-1">Submissions and status</div>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($course->assignments as $assignment)
                @php
                    $rows = $assignmentSubs[$assignment->id] ?? collect();
                    $used = $rows->count();

                    $last = $rows->first();
                    $status = $last?->status ?? 'not_submitted';

                    if ($status === 'graded') {
                        $badgeText = 'Graded';
                        $badgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-800';
                    } elseif ($status === 'submitted') {
                        $badgeText = 'Submitted';
                        $badgeClass = 'border-blue-200 bg-blue-50 text-blue-800';
                    } else {
                        $badgeText = 'Not submitted';
                        $badgeClass = 'border-gray-200 bg-gray-50 text-gray-700';
                    }
                @endphp

                <div class="p-4 flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">{{ $assignment->title }}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Submissions: <span class="font-semibold text-gray-900">{{ $used }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs border {{ $badgeClass }}">{{ $badgeText }}</span>

                        @if($last && \Illuminate\Support\Facades\Route::has('teacher.assignments.submissions.show'))
                            <a href="{{ route('teacher.assignments.submissions.show', [$assignment->id, $last->id]) }}"
                               class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 bg-white hover:bg-gray-50">
                                Open
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500 text-sm">No assignments in this course.</div>
            @endforelse
        </div>
    </div>

</div>
@endsection