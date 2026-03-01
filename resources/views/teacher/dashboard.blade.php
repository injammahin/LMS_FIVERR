@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('content')
@php
    $donut = function ($percent, $color) {
        $p = max(0, min(100, (int)$percent));
        return "background: conic-gradient({$color} {$p}%, #e5e7eb 0%);";
    };

    $mini = fn($n) => number_format((int)$n);
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500">Welcome back, {{ auth()->user()->name }} 👋</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('teacher.submissions.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm shadow-sm">
                <i class="fa-solid fa-check-to-slot"></i>
                Review Submissions
            </a>
            <a href="{{ route('teacher.courses.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                <i class="fa-solid fa-book"></i>
                My Courses
            </a>
        </div>
    </div>

    {{-- KPI Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="kpiCard">
            <div class="kpiTop">
                <div class="kpiIcon bg-blue-50 text-blue-700 border-blue-100">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
                <div class="kpiLabel">My Courses</div>
            </div>
            <div class="kpiValue">{{ $mini($courses->count()) }}</div>
            <div class="kpiHint">Courses assigned to you</div>
        </div>

        <div class="kpiCard">
            <div class="kpiTop">
                <div class="kpiIcon bg-emerald-50 text-emerald-700 border-emerald-100">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="kpiLabel">Students</div>
            </div>
            <div class="kpiValue">{{ $mini($studentsCount) }}</div>
            <div class="kpiHint">Across your divisions</div>
        </div>

        <div class="kpiCard">
            <div class="kpiTop">
                <div class="kpiIcon bg-amber-50 text-amber-800 border-amber-100">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="kpiLabel">Pending Grading</div>
            </div>
            <div class="kpiValue">{{ $mini($pendingGrading) }}</div>
            <div class="kpiHint">Assignments + quiz attempts</div>
        </div>

        <div class="kpiCard">
            <div class="kpiTop">
                <div class="kpiIcon bg-purple-50 text-purple-700 border-purple-100">
                    <i class="fa-solid fa-bell"></i>
                </div>
                <div class="kpiLabel">Unread</div>
            </div>
            <div class="kpiValue">{{ $mini($unread ?? 0) }}</div>
            <div class="kpiHint">Notifications</div>
        </div>
    </div>

    {{-- Overview + Donuts --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

        {{-- Totals / Avg --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm space-y-4 xl:col-span-2">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Teaching Overview</div>
                    <div class="text-xs text-gray-500 mt-1">Totals across courses you teach</div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Avg Student Progress</div>
                        <div class="text-sm font-semibold text-gray-900">{{ (int)$avgOverallPercent }}%</div>
                    </div>
                    <div class="donut border border-gray-200" style="{{ $donut($avgOverallPercent, '#10b981') }}">
                        <span class="text-[12px] font-extrabold">{{ (int)$avgOverallPercent }}%</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="statPill">
                    <div class="statTitle"><i class="fa-solid fa-book-open text-blue-600 mr-1"></i> Lessons</div>
                    <div class="statValue">{{ $mini($lessonsTotal) }}</div>
                </div>

                <div class="statPill">
                    <div class="statTitle"><i class="fa-solid fa-circle-question text-purple-600 mr-1"></i> Quizzes</div>
                    <div class="statValue">{{ $mini($quizzesTotal) }}</div>
                </div>

                <div class="statPill">
                    <div class="statTitle"><i class="fa-solid fa-file-pen text-amber-600 mr-1"></i> Assignments</div>
                    <div class="statValue">{{ $mini($assignmentsTotal) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500">Quizzes Submitted</div>
                        <div class="text-sm font-semibold text-gray-900 mt-1">{{ $mini($quizSubmittedCount) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Across {{ $mini($quizzesTotal) }} quizzes</div>
                    </div>
                    <div class="donut border border-gray-200" style="{{ $donut($quizPercent, '#a855f7') }}">
                        <span class="text-[12px] font-extrabold">{{ (int)$quizPercent }}%</span>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4 flex items-center justify-between">
                    <div>
                        <div class="text-xs text-gray-500">Assignments Submitted</div>
                        <div class="text-sm font-semibold text-gray-900 mt-1">{{ $mini($assignmentSubmittedCount) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Across {{ $mini($assignmentsTotal) }} assignments</div>
                    </div>
                    <div class="donut border border-gray-200" style="{{ $donut($assignPercent, '#f59e0b') }}">
                        <span class="text-[12px] font-extrabold">{{ (int)$assignPercent }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick actions --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm font-semibold text-gray-900">Quick Actions</div>
            <div class="text-xs text-gray-500 mt-1">Jump to your most used pages</div>

            <div class="mt-4 space-y-2">
                <a href="{{ route('teacher.submissions.index', ['type' => 'quiz']) }}"
                   class="qaLink">
                    <span class="qaLeft">
                        <span class="qaDot bg-purple-50 text-purple-700 border-purple-100"><i class="fa-solid fa-bolt"></i></span>
                        Quiz Attempts
                    </span>
                    <i class="fa-solid fa-chevron-right text-gray-400"></i>
                </a>

                <a href="{{ route('teacher.submissions.index', ['type' => 'assignment']) }}"
                   class="qaLink">
                    <span class="qaLeft">
                        <span class="qaDot bg-amber-50 text-amber-800 border-amber-100"><i class="fa-solid fa-file-pen"></i></span>
                        Assignment Submissions
                    </span>
                    <i class="fa-solid fa-chevron-right text-gray-400"></i>
                </a>

                <a href="{{ route('teacher.notifications.index') }}"
                   class="qaLink">
                    <span class="qaLeft">
                        <span class="qaDot bg-blue-50 text-blue-700 border-blue-100"><i class="fa-solid fa-bell"></i></span>
                        Notifications
                    </span>
                    <i class="fa-solid fa-chevron-right text-gray-400"></i>
                </a>

                <a href="{{ route('teacher.courses.index') }}"
                   class="qaLink">
                    <span class="qaLeft">
                        <span class="qaDot bg-emerald-50 text-emerald-700 border-emerald-100"><i class="fa-solid fa-book"></i></span>
                        My Courses
                    </span>
                    <i class="fa-solid fa-chevron-right text-gray-400"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Course Comparison --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="p-5 flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-gray-900">Course Progress (Average)</div>
                <div class="text-xs text-gray-500 mt-1">Completion across students in each course division</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Course</th>
                        <th class="px-6 py-3 text-left font-semibold w-[90px]">Students</th>
                        <th class="px-6 py-3 text-left font-semibold w-[170px]">Lessons</th>
                        <th class="px-6 py-3 text-left font-semibold w-[170px]">Quizzes</th>
                        <th class="px-6 py-3 text-left font-semibold w-[170px]">Assignments</th>
                        <th class="px-6 py-3 text-left font-semibold w-[140px]">Overall</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($courseInsights as $row)
                        @php $c = $row['course']; @endphp
                        <tr class="hover:bg-gray-50/60">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $c->title }}</div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ optional($c->subject)->name }} • {{ optional(optional($c->subject)->division)->name }}
                                </div>
                            </td>

                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ (int)$row['students'] }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                    <span>{{ (int)$row['lessons_total'] }} lessons</span>
                                    <span class="font-semibold text-gray-800">{{ (int)$row['lesson_percent'] }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                    <div class="h-2 rounded-full" style="width: {{ (int)$row['lesson_percent'] }}%; background:#2563eb;"></div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                    <span>{{ (int)$row['quizzes_total'] }} quizzes</span>
                                    <span class="font-semibold text-gray-800">{{ (int)$row['quiz_percent'] }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                    <div class="h-2 rounded-full" style="width: {{ (int)$row['quiz_percent'] }}%; background:#a855f7;"></div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                    <span>{{ (int)$row['assignments_total'] }} assignments</span>
                                    <span class="font-semibold text-gray-800">{{ (int)$row['assignment_percent'] }}%</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                    <div class="h-2 rounded-full" style="width: {{ (int)$row['assignment_percent'] }}%; background:#f59e0b;"></div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="donut border border-gray-200" style="{{ $donut($row['overall_percent'], '#10b981') }}">
                                        <span class="text-[12px] font-extrabold">{{ (int)$row['overall_percent'] }}%</span>
                                    </div>

                                    <a href="{{ route('teacher.courses.show', $c->id) }}"
                                       class="text-xs px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:bg-gray-50">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-500">No courses assigned.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent + Pending --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="panelCard">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Recent Quiz Submissions</div>
                    <div class="panelSub">Latest submitted attempts</div>
                </div>
                <a href="{{ route('teacher.submissions.index', ['type' => 'quiz']) }}" class="panelLink">View all</a>
            </div>

            <div class="space-y-2">
                @forelse($recentQuizAttempts as $a)
                    <a href="{{ route('teacher.quiz.attempts.show', $a->id) }}"
                       class="rowCard">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $a->user?->name }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $a->quiz?->title }} • {{ $a->quiz?->course?->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">Submitted: {{ optional($a->submitted_at)->diffForHumans() }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300"></i>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">No quiz submissions yet.</div>
                @endforelse
            </div>
        </div>

        <div class="panelCard">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Recent Assignment Submissions</div>
                    <div class="panelSub">Latest uploaded assignments</div>
                </div>
                <a href="{{ route('teacher.submissions.index', ['type' => 'assignment']) }}" class="panelLink">View all</a>
            </div>

            <div class="space-y-2">
                @forelse($recentAssignmentSubs as $s)
                    <a href="{{ route('teacher.assignments.submissions.show', [$s->assignment_id, $s->id]) }}"
                       class="rowCard">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $s->user?->name }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $s->assignment?->title }} • {{ $s->assignment?->course?->title }}</div>
                            <div class="text-xs text-gray-500 mt-1">Submitted: {{ optional($s->created_at)->diffForHumans() }}</div>
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300"></i>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">No assignment submissions yet.</div>
                @endforelse
            </div>
        </div>

        <div class="panelCard">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Pending Quiz Grading</div>
                    <div class="panelSub">Needs your review</div>
                </div>
                <a href="{{ route('teacher.submissions.index', ['type' => 'quiz']) }}" class="panelLink">Open</a>
            </div>

            <div class="space-y-2">
                @forelse($pendingQuizAttempts as $a)
                    <a href="{{ route('teacher.quiz.attempts.show', $a->id) }}"
                       class="rowCard border-amber-200 bg-amber-50">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $a->user?->name }}</div>
                            <div class="text-xs text-amber-800 mt-1">{{ $a->quiz?->title }} • {{ $a->quiz?->course?->title }}</div>
                            <div class="text-xs text-amber-800 mt-1">Submitted: {{ optional($a->submitted_at)->diffForHumans() }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full border border-amber-200 text-amber-800 bg-white">
                            Pending
                        </span>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">No pending quiz grading.</div>
                @endforelse
            </div>
        </div>

        <div class="panelCard">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Pending Assignment Grading</div>
                    <div class="panelSub">Needs your review</div>
                </div>
                <a href="{{ route('teacher.submissions.index', ['type' => 'assignment']) }}" class="panelLink">Open</a>
            </div>

            <div class="space-y-2">
                @forelse($pendingAssignmentSubs as $s)
                    <a href="{{ route('teacher.assignments.submissions.show', [$s->assignment_id, $s->id]) }}"
                       class="rowCard border-amber-200 bg-amber-50">
                        <div>
                            <div class="text-sm font-semibold text-gray-900">{{ $s->user?->name }}</div>
                            <div class="text-xs text-amber-800 mt-1">{{ $s->assignment?->title }} • {{ $s->assignment?->course?->title }}</div>
                            <div class="text-xs text-amber-800 mt-1">Submitted: {{ optional($s->created_at)->diffForHumans() }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full border border-amber-200 text-amber-800 bg-white">
                            Pending
                        </span>
                    </a>
                @empty
                    <div class="text-sm text-gray-500">No pending assignment grading.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>

@once
<style>
    /* keep text standard - no huge fonts */
    .donut{width:68px;height:68px;border-radius:9999px;display:grid;place-items:center;position:relative}
    .donut::before{content:"";position:absolute;inset:10px;background:#fff;border-radius:9999px}
    .donut>span{position:relative;color:#111827}

    .kpiCard{border:1px solid #e5e7eb;background:#fff;border-radius:16px;padding:18px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    .kpiTop{display:flex;align-items:center;gap:10px}
    .kpiIcon{width:40px;height:40px;border-radius:14px;display:grid;place-items:center;border:1px solid}
    .kpiLabel{font-size:12px;color:#6b7280;font-weight:600}
    .kpiValue{font-size:22px;font-weight:800;color:#111827;margin-top:10px;line-height:1}
    .kpiHint{font-size:12px;color:#9ca3af;margin-top:8px}

    .statPill{border:1px solid #e5e7eb;border-radius:16px;padding:14px;background:#fff}
    .statTitle{font-size:12px;color:#6b7280;font-weight:600}
    .statValue{font-size:16px;font-weight:800;color:#111827;margin-top:6px}

    .qaLink{display:flex;align-items:center;justify-content:space-between;border:1px solid #e5e7eb;border-radius:14px;padding:12px 14px;background:#fff}
    .qaLink:hover{background:#f9fafb}
    .qaLeft{display:inline-flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#111827}
    .qaDot{width:34px;height:34px;border-radius:12px;display:grid;place-items:center;border:1px solid}

    .panelCard{border:1px solid #e5e7eb;background:#fff;border-radius:16px;padding:18px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    .panelHead{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px}
    .panelTitle{font-size:13px;font-weight:800;color:#111827}
    .panelSub{font-size:12px;color:#6b7280;margin-top:4px}
    .panelLink{font-size:12px;color:#2563eb;text-decoration:underline}

    .rowCard{display:flex;align-items:center;justify-content:space-between;gap:12px;border:1px solid #e5e7eb;border-radius:14px;padding:12px 14px;background:#fff}
    .rowCard:hover{background:#f9fafb}
</style>
@endonce
@endsection