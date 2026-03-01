@extends('layouts.student')
@section('title', 'Grades')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Grades</h1>
                <p class="text-sm text-gray-500 mt-1">Quiz results and assignment grading updates.</p>
            </div>

            <form method="GET" class="flex items-center gap-2">
                <select name="type" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm">
                    <option value="all" {{ $filter==='all' ? 'selected':'' }}>All</option>
                    <option value="quiz" {{ $filter==='quiz' ? 'selected':'' }}>Quizzes</option>
                    <option value="assignment" {{ $filter==='assignment' ? 'selected':'' }}>Assignments</option>
                </select>
                <button class="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm hover:bg-gray-800">
                    Filter
                </button>
            </form>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">Graded Quizzes</div>
                <div class="text-2xl font-semibold text-gray-900 mt-2">{{ $gradedQuizCount }}</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">Graded Assignments</div>
                <div class="text-2xl font-semibold text-gray-900 mt-2">{{ $gradedAssignmentCount }}</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">Avg Quiz Score</div>
                <div class="text-2xl font-semibold text-gray-900 mt-2">{{ $avgQuizPercent }}%</div>
                <div class="text-xs text-gray-500 mt-1">Based on your submitted attempts</div>
            </div>
        </div>

        {{-- QUIZZES --}}
        @if($filter === 'all' || $filter === 'quiz')
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Quiz Results</div>
                        <div class="text-xs text-gray-500 mt-1">Submitted / Reviewed / Graded attempts</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Quiz</th>
                                <th class="px-6 py-3 text-left font-semibold">Course</th>
                                <th class="px-6 py-3 text-left font-semibold w-[120px]">Score</th>
                                <th class="px-6 py-3 text-left font-semibold w-[120px]">Status</th>
                                <th class="px-6 py-3 text-right font-semibold w-[140px]">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($quizAttempts ?? [] as $a)
                                @php
                                    $total = (int)($a->total ?? 0);
                                    $score = (int)($a->score ?? 0);
                                    $pct = $total > 0 ? (int)round(($score / $total) * 100) : 0;

                                    $st = (string)($a->status ?? 'submitted');
                                    $badge = 'bg-gray-100 text-gray-700 border-gray-200';
                                    if ($st === 'graded') $badge = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                                    elseif ($st === 'reviewed') $badge = 'bg-blue-50 text-blue-800 border-blue-200';
                                    elseif ($st === 'submitted') $badge = 'bg-purple-50 text-purple-800 border-purple-200';
                                @endphp
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $a->quiz?->title ?? 'Quiz' }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ optional($a->submitted_at)->diffForHumans() ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $a->quiz?->course?->title ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">
                                            {{ $score }} <span class="text-gray-400">/ {{ $total }}</span>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $pct }}%</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                                            {{ ucfirst($st) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('student.quiz.attempt.result', $a->id) }}"
                                           class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                                            View Result
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        No quiz attempts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $quizAttempts?->links() }}
                </div>
            </div>
        @endif

        {{-- ASSIGNMENTS --}}
        @if($filter === 'all' || $filter === 'assignment')
            <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Assignment Grades</div>
                        <div class="text-xs text-gray-500 mt-1">Submitted / Graded submissions</div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Assignment</th>
                                <th class="px-6 py-3 text-left font-semibold">Course</th>
                                <th class="px-6 py-3 text-left font-semibold w-[140px]">Status</th>
                                <th class="px-6 py-3 text-left font-semibold w-[160px]">Marks</th>
                                <th class="px-6 py-3 text-right font-semibold w-[120px]">Open</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse($assignmentSubs ?? [] as $s)
                                @php
                                    $st = (string)($s->status ?? 'submitted');
                                    $badge = $st === 'graded'
                                        ? 'bg-emerald-50 text-emerald-800 border-emerald-200'
                                        : 'bg-amber-50 text-amber-800 border-amber-200';

                                    $marks = $s->marks_awarded;
                                    $isPassed = $s->is_passed;
                                @endphp
                                <tr class="hover:bg-gray-50/60">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $s->assignment?->title ?? 'Assignment' }}</div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ optional($s->created_at)->diffForHumans() ?? '-' }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        {{ $s->assignment?->course?->title ?? '—' }}
                                    </td>

                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold border {{ $badge }}">
                                            {{ ucfirst($st) }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 text-gray-700">
                                        @if(!is_null($marks))
                                            <span class="font-semibold text-gray-900">{{ (int)$marks }}</span>
                                            <span class="text-gray-400">points</span>
                                        @elseif(!is_null($isPassed))
                                            <span class="font-semibold {{ $isPassed ? 'text-emerald-700' : 'text-red-700' }}">
                                                {{ $isPassed ? 'Passed' : 'Failed' }}
                                            </span>
                                        @else
                                            <span class="text-gray-500">—</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-right">
                                        @if($s->assignment && $s->assignment->course)
                                            <a href="{{ route('student.assignments.show', [$s->assignment->course_id, $s->assignment_id]) }}"
                                               class="inline-flex items-center justify-center px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                                                View
                                            </a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        No assignment submissions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $assignmentSubs?->links() }}
                </div>
            </div>
        @endif

    </div>
</div>
@endsection