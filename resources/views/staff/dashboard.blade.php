@extends('layouts.staff')

@section('title', 'Staff Dashboard')@section('page_title', 'Dashboard')

@section('content')
    @php
        $mini = fn($n) => number_format((int) $n);
    @endphp

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div class="space-y-1">
                <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500">Welcome back, {{ auth()->user()->name }} 👋</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.submissions.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm shadow-sm">
                    <i class="fa-solid fa-inbox"></i>
                    View Submissions
                </a>

                <a href="{{ route('staff.courses.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                    <i class="fa-solid fa-book"></i>
                    My Courses
                </a>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-gray-500">Assigned Courses</div>
                <div class="text-2xl font-extrabold text-gray-900 mt-2">{{ $mini($courses->count()) }}</div>
                <div class="text-xs text-gray-500 mt-2">Courses assigned by admin</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-gray-500">Students</div>
                <div class="text-2xl font-extrabold text-gray-900 mt-2">{{ $mini($studentsCount) }}</div>
                <div class="text-xs text-gray-500 mt-2">Across your course divisions</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-gray-500">Pending (Monitor)</div>
                <div class="text-2xl font-extrabold text-gray-900 mt-2">{{ $mini($pendingGrading) }}</div>
                <div class="text-xs text-gray-500 mt-2">Submitted quizzes + assignments</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-xs text-gray-500">Totals</div>
                <div class="text-sm text-gray-700 mt-2">Lessons: <b>{{ $mini($lessonsTotal) }}</b></div>
                <div class="text-sm text-gray-700">Quizzes: <b>{{ $mini($quizzesTotal) }}</b></div>
                <div class="text-sm text-gray-700">Assignments: <b>{{ $mini($assignmentsTotal) }}</b></div>
            </div>
        </div>

        {{-- Assigned courses list --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200">
                <div class="text-sm font-semibold text-gray-900">My Courses</div>
                <div class="text-xs text-gray-500 mt-1">You can view course content and submissions (no grading).</div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">Course</th>
                            <th class="px-6 py-3 text-left font-semibold">Division</th>
                            <th class="px-6 py-3 text-left font-semibold">Subject</th>
                            <th class="px-6 py-3 text-right font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @forelse($courses as $c)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-semibold text-gray-900">{{ $c->title }}</td>
                                <td class="px-6 py-4">{{ $c->subject?->division?->name ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $c->subject?->name ?? '—' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('staff.courses.show', $c->id) }}"
                                       class="text-xs px-3 py-1.5 rounded-full border border-gray-200 bg-white hover:bg-gray-50">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                    No courses assigned yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Recent Quiz Submissions</div>
                        <div class="text-xs text-gray-500 mt-1">Latest submitted attempts</div>
                    </div>
                    <a href="{{ route('staff.submissions.index', ['type' => 'quiz']) }}" class="text-xs text-blue-600 underline">View all</a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse($recentQuizAttempts as $a)
                        <a href="{{ route('staff.quiz.attempts.show', $a->id) }}"
                           class="flex items-center justify-between border border-gray-200 rounded-xl p-3 hover:bg-gray-50">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $a->user?->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $a->quiz?->title }} • {{ $a->quiz?->course?->title }}</div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-gray-300"></i>
                        </a>
                    @empty
                        <div class="text-sm text-gray-500">No quiz submissions yet.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Recent Assignment Submissions</div>
                        <div class="text-xs text-gray-500 mt-1">Latest uploaded assignments</div>
                    </div>
                    <a href="{{ route('staff.submissions.index', ['type' => 'assignment']) }}" class="text-xs text-blue-600 underline">View all</a>
                </div>

                <div class="mt-3 space-y-2">
                    @forelse($recentAssignmentSubs as $s)
                        <a href="{{ route('staff.assignments.submissions.show', [$s->assignment_id, $s->id]) }}"
                           class="flex items-center justify-between border border-gray-200 rounded-xl p-3 hover:bg-gray-50">
                            <div>
                                <div class="text-sm font-semibold text-gray-900">{{ $s->user?->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">{{ $s->assignment?->title }} • {{ $s->assignment?->course?->title }}</div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-gray-300"></i>
                        </a>
                    @empty
                        <div class="text-sm text-gray-500">No assignment submissions yet.</div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
@endsection