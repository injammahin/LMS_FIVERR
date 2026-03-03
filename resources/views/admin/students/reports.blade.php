{{-- resources/views/admin/students/reports.blade.php --}}
@extends('layouts.admin')

@section('title', 'Student Reports')

@section('content')
    @php
        /**
         * EXPECTED DATA from controller:
         * $students (Paginator<User>)  // role=student, filtered by status/search/per_page
         * $rangeDays (int)             // 7/30/90
         * $status (string)             // all|active|suspended
         * $search (string)
         * $kpis (array)                // totals
         * $map (array)                 // per-student metrics keyed by student_id
         * $charts (array)              // chart arrays
         */

        $rangeDays = $rangeDays ?? 30;
        $status = $status ?? 'all';
        $search = $search ?? '';

        $k = $kpis ?? [
            'totalStudents' => 0,
            'activeStudents' => 0,
            'suspendedStudents' => 0,
            'avgOverallProgressAll' => 0,
            'rangeQuizAttempts' => 0,
            'rangeAssignmentSubmissions' => 0,
            'activeInRange' => 0,
        ];

        $m = $map ?? [
            // examples:
            // 'division' => [studentId => 'Division name'],
            // 'courses' => [studentId => 0],
            // 'lessons_total' => [studentId => 0],
            // 'lessons_done' => [studentId => 0],
            // 'quizzes_total' => [studentId => 0],
            // 'quizzes_submitted' => [studentId => 0],
            // 'assignments_total' => [studentId => 0],
            // 'assignments_submitted' => [studentId => 0],
            // 'overall_percent' => [studentId => 0],
            // 'avg_quiz_grade' => [studentId => 0],
            // 'last_activity' => [studentId => null],
        ];

        $charts = $charts ?? [
            // chart 1: top students by progress
            'topLabels' => [],
            'topProgress' => [],
            // chart 2: activity trend (last 14 days) optional
            'trendLabels' => [],
            'trendQuiz' => [],
            'trendAssignments' => [],
            // chart 3: division distribution
            'divLabels' => [],
            'divCounts' => [],
        ];
    @endphp

    <div class="space-y-6">

        {{-- HERO --}}
        <div
            class="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
            <div class="h-16 bg-gradient-to-r from-emerald-700 via-teal-700 to-sky-700"></div>

            {{-- Decorative glows --}}
            <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full blur-3xl opacity-25"
                style="background: radial-gradient(circle at center, rgba(255,255,255,.35), transparent 60%);"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-28 w-96 h-96 rounded-full blur-3xl opacity-20"
                style="background: radial-gradient(circle at center, rgba(16,185,129,.28), transparent 60%);"></div>

            <div class="p-5 sm:p-6 lg:p-7">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border
                                        bg-emerald-50 text-emerald-700 border-emerald-100
                                        dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20">
                            <i class="fa-solid fa-user-graduate text-[12px]"></i>
                            Student Report Center
                        </div>

                        <h1 class="mt-2 text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            Students Analytics
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-white/60 mt-1 max-w-2xl">
                            Enrollment, engagement, completion progress, quiz performance, and recent activity.
                        </p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border
                                             bg-white/80 border-gray-200 text-gray-700
                                             dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                <i class="fa-solid fa-calendar-days text-[12px]"></i>
                                Range: {{ (int) $rangeDays }} days
                            </span>

                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border
                                             bg-white/80 border-gray-200 text-gray-700
                                             dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                <i class="fa-solid fa-filter text-[12px]"></i>
                                Status: {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>

                    {{-- FILTER BAR --}}
                    <form method="GET" class="w-full lg:w-auto">
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white/80 dark:bg-slate-950/60 backdrop-blur p-3 shadow-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2">
                                <select name="range"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                    <option value="7" {{ (int) $rangeDays === 7 ? 'selected' : '' }}>Last 7 days</option>
                                    <option value="30" {{ (int) $rangeDays === 30 ? 'selected' : '' }}>Last 30 days</option>
                                    <option value="90" {{ (int) $rangeDays === 90 ? 'selected' : '' }}>Last 90 days</option>
                                </select>

                                <select name="status"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ $status === 'suspended' ? 'selected' : '' }}>Suspended
                                    </option>
                                </select>

                                <input name="search" value="{{ $search }}" placeholder="Search student..."
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/40">

                                <select name="per_page"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white">
                                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                </select>

                                <button
                                    class="rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm px-4 py-2 shadow-sm inline-flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-magnifying-glass text-[12px]"></i>
                                    Apply
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-7 gap-4">

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-500/10 dark:text-sky-200 dark:border-sky-500/20">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Students</div>
                        <div class="kpiValue">{{ (int) ($k['totalStudents'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">All students</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Active</div>
                        <div class="kpiValue">{{ (int) ($k['activeStudents'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Can login</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-200 dark:border-red-500/20">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Suspended</div>
                        <div class="kpiValue">{{ (int) ($k['suspendedStudents'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Blocked accounts</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:border-indigo-500/20">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Avg Progress</div>
                        <div class="kpiValue">{{ (int) ($k['avgOverallProgressAll'] ?? 0) }}%</div>
                    </div>
                </div>
                <div class="kpiHint">Overall completion</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-500/10 dark:text-purple-200 dark:border-purple-500/20">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Quiz Attempts</div>
                        <div class="kpiValue">{{ (int) ($k['rangeQuizAttempts'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">{{ (int) $rangeDays }} day activity</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-amber-50 text-amber-800 border-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Assignments</div>
                        <div class="kpiValue">{{ (int) ($k['rangeAssignmentSubmissions'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">{{ (int) $rangeDays }} day activity</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-teal-50 text-teal-800 border-teal-100 dark:bg-teal-500/10 dark:text-teal-200 dark:border-teal-500/20">
                        <i class="fa-solid fa-fire"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Active (Range)</div>
                        <div class="kpiValue">{{ (int) ($k['activeInRange'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Did something recently</div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            <div class="panelCard xl:col-span-2">
                <div class="panelHead">
                    <div class="min-w-0">
                        <div class="panelTitle">Top Students by Overall Progress</div>
                        <div class="panelSub">Top 10 completion %</div>
                    </div>
                    <span
                        class="panelPill bg-emerald-50 border border-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">
                        <i class="fa-solid fa-chart-column text-[12px]"></i> Progress
                    </span>
                </div>
                <div class="panelBody h-[290px]">
                    <canvas id="chartProgress"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div class="min-w-0">
                        <div class="panelTitle">Division Distribution</div>
                        <div class="panelSub">Students per division</div>
                    </div>
                    <span
                        class="panelPill bg-sky-50 border border-sky-100 text-sky-800 dark:bg-sky-500/10 dark:border-sky-500/20 dark:text-sky-200">
                        <i class="fa-solid fa-sitemap text-[12px]"></i> Divisions
                    </span>
                </div>
                <div class="panelBody h-[290px]">
                    <canvas id="chartDivisions"></canvas>
                </div>
            </div>

        </div>

        <div class="panelCard">
            <div class="panelHead">
                <div class="min-w-0">
                    <div class="panelTitle">Activity Trend (Last 14 Days)</div>
                    <div class="panelSub">Quiz attempts vs assignment submissions</div>
                </div>
                <span
                    class="panelPill bg-purple-50 border border-purple-100 text-purple-800 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-200">
                    <i class="fa-solid fa-wave-square text-[12px]"></i> Trend
                </span>
            </div>
            <div class="panelBody h-[320px]">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="md:hidden space-y-3">
            @forelse($students as $s)
                @php
                    $id = $s->id;
                    $division = $m['division'][$id] ?? '—';

                    $overall = (int) ($m['overall_percent'][$id] ?? 0);
                    $avgGrade = (int) round((float) ($m['avg_quiz_grade'][$id] ?? 0));

                    $lt = (int) ($m['lessons_total'][$id] ?? 0);
                    $ld = (int) ($m['lessons_done'][$id] ?? 0);

                    $qt = (int) ($m['quizzes_total'][$id] ?? 0);
                    $qs = (int) ($m['quizzes_submitted'][$id] ?? 0);

                    $at = (int) ($m['assignments_total'][$id] ?? 0);
                    $asub = (int) ($m['assignments_submitted'][$id] ?? 0);

                    $last = $m['last_activity'][$id] ?? null;
                    $lastTxt = $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—';

                    $tone = $overall >= 70 ? 'emerald' : ($overall >= 40 ? 'amber' : 'red');
                @endphp

                <div
                    class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 dark:text-white truncate">{{ $s->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                                    {{ $s->email ?? $s->username ?? '—' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                                    Division: <span
                                        class="font-semibold text-gray-800 dark:text-white/80">{{ $division }}</span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border
                                                    {{ $tone === 'emerald' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' :
                ($tone === 'amber' ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200' :
                    'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200') }}">
                                        Progress: <span class="font-semibold">{{ $overall }}%</span>
                                    </span>

                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border bg-white border-gray-200 text-gray-700 dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                        Avg Grade: <span class="font-semibold">{{ $avgGrade }}%</span>
                                    </span>

                                    @if($s->is_active)
                                        <span
                                            class="px-2 py-1 rounded-full text-[11px] border bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">
                                            Active
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-[11px] border bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200">
                                            Suspended
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 text-xs text-gray-500 dark:text-white/60">
                                    L: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $ld }}/{{ $lt }}</span>
                                    • Q: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $qs }}/{{ $qt }}</span>
                                    • A: <span
                                        class="font-semibold text-gray-800 dark:text-white/80">{{ $asub }}/{{ $at }}</span>
                                    • Last: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $lastTxt }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('admin.students.edit', $s->id) }}"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs font-semibold">
                                <i class="fa-solid fa-pen mr-2 text-[12px]"></i> Edit
                            </a>

                            <form action="{{ route('admin.students.toggle-status', $s->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold border
                                                {{ $s->is_active ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200'
                : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' }}">
                                    <i
                                        class="fa-solid {{ $s->is_active ? 'fa-user-slash' : 'fa-circle-check' }} mr-2 text-[12px]"></i>
                                    {{ $s->is_active ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 p-8 text-center text-gray-500 dark:text-white/60">
                    No students found.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $students->links() }}
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block panelCard overflow-hidden">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Student Detailed Report</div>
                    <div class="panelSub">Division, progress, activity and average quiz grades</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">Student</th>
                            <th class="px-6 py-3 text-left font-semibold">Division</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Progress</th>
                            <th class="px-6 py-3 text-left font-semibold">Lessons</th>
                            <th class="px-6 py-3 text-left font-semibold">Quizzes</th>
                            <th class="px-6 py-3 text-left font-semibold">Assignments</th>
                            <th class="px-6 py-3 text-left font-semibold">Avg Grade</th>
                            <th class="px-6 py-3 text-left font-semibold">Last Activity</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($students as $s)
                                        @php
                                            $id = $s->id;
                                            $division = $m['division'][$id] ?? '—';

                                            $overall = (int) ($m['overall_percent'][$id] ?? 0);
                                            $avgGrade = (int) round((float) ($m['avg_quiz_grade'][$id] ?? 0));

                                            $lt = (int) ($m['lessons_total'][$id] ?? 0);
                                            $ld = (int) ($m['lessons_done'][$id] ?? 0);

                                            $qt = (int) ($m['quizzes_total'][$id] ?? 0);
                                            $qs = (int) ($m['quizzes_submitted'][$id] ?? 0);

                                            $at = (int) ($m['assignments_total'][$id] ?? 0);
                                            $asub = (int) ($m['assignments_submitted'][$id] ?? 0);

                                            $last = $m['last_activity'][$id] ?? null;
                                            $lastTxt = $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—';

                                            $tone = $overall >= 70 ? 'emerald' : ($overall >= 40 ? 'amber' : 'red');
                                        @endphp

                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $s->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                                    {{ $s->email ?? $s->username ?? '—' }}
                                                </div>
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $division }}
                                            </td>

                                            <td class="px-6 py-4">
                                                @if($s->is_active)
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[11px] border bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">
                                                        Active
                                                    </span>
                                                @else
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[11px] border bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200">
                                                        Suspended
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-2 py-1 rounded-full text-[11px] border
                                                                            {{ $tone === 'emerald' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' :
                            ($tone === 'amber' ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200' :
                                'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200') }}">
                                                    {{ $overall }}%
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $ld }}/{{ $lt }}
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $qs }}/{{ $qt }}
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $asub }}/{{ $at }}
                                            </td>

                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-2 py-1 rounded-full text-[11px] border bg-white border-gray-200 text-gray-700 dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                                    {{ $avgGrade }}%
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $lastTxt }}
                                            </td>

                                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                                <a href="{{ route('admin.students.edit', $s->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs">
                                                    Edit
                                                </a>

                                                <form action="{{ route('admin.students.toggle-status', $s->id) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button
                                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs border
                                                                                {{ $s->is_active ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200'
                            : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' }}">
                                                        {{ $s->is_active ? 'Suspend' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                    No students found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $students->links() }}
            </div>
        </div>

    </div>

    @once
        <style>
            .kpiCard {
                border-radius: 16px;
                border: 1px solid rgba(229, 231, 235, 1);
                background: #fff;
                padding: 16px;
                box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
                position: relative;
                overflow: hidden;
            }

            .dark .kpiCard {
                border-color: rgba(255, 255, 255, .10);
                background: rgb(15 23 42);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
            }

            .kpiCard:before {
                content: "";
                position: absolute;
                inset: -1px;
                background:
                    radial-gradient(420px circle at 20% 20%, rgba(16, 185, 129, .10), transparent 45%),
                    radial-gradient(420px circle at 80% 80%, rgba(56, 189, 248, .08), transparent 50%);
                pointer-events: none;
            }

            .kpiTop {
                position: relative;
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .kpiIcon {
                width: 40px;
                height: 40px;
                border-radius: 14px;
                display: grid;
                place-items: center;
                border: 1px solid;
                flex: 0 0 auto;
            }

            .kpiMeta {
                position: relative;
                min-width: 0;
            }

            .kpiLabel {
                font-size: 12px;
                font-weight: 800;
                color: #6b7280;
            }

            .dark .kpiLabel {
                color: rgba(255, 255, 255, .55);
            }

            .kpiValue {
                margin-top: 6px;
                font-size: 18px;
                font-weight: 900;
                color: #111827;
                line-height: 1;
            }

            .dark .kpiValue {
                color: rgba(255, 255, 255, .95);
            }

            .kpiHint {
                position: relative;
                margin-top: 10px;
                font-size: 12px;
                color: #9ca3af;
            }

            .dark .kpiHint {
                color: rgba(255, 255, 255, .45);
            }

            .panelCard {
                border-radius: 16px;
                border: 1px solid rgba(229, 231, 235, 1);
                background: #fff;
                box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
                overflow: hidden;
            }

            .dark .panelCard {
                border-color: rgba(255, 255, 255, .10);
                background: rgb(15 23 42);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
            }

            .panelHead {
                padding: 14px 16px;
                border-bottom: 1px solid rgba(229, 231, 235, 1);
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                background: linear-gradient(to bottom, rgba(249, 250, 251, 1), rgba(255, 255, 255, 1));
            }

            .dark .panelHead {
                border-bottom-color: rgba(255, 255, 255, .10);
                background: linear-gradient(to bottom, rgba(255, 255, 255, .06), rgb(15 23 42));
            }

            .panelTitle {
                font-size: 14px;
                font-weight: 900;
                color: #111827;
            }

            .dark .panelTitle {
                color: rgba(255, 255, 255, .95);
            }

            .panelSub {
                margin-top: 4px;
                font-size: 12px;
                color: #6b7280;
            }

            .dark .panelSub {
                color: rgba(255, 255, 255, .55);
            }

            .panelPill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 11px;
                padding: 6px 10px;
                border-radius: 999px;
                font-weight: 900;
                white-space: nowrap;
            }

            .panelBody {
                padding: 14px 16px;
            }

            table th,
            table td {
                white-space: nowrap;
            }
        </style>
    @endonce
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const isDark = () => document.documentElement.classList.contains('dark');
            const gridColor = () => isDark() ? 'rgba(255,255,255,.10)' : 'rgba(17,24,39,.08)';
            const tickColor = () => isDark() ? 'rgba(255,255,255,.75)' : 'rgba(55,65,81,1)';

            function baseOptions(extra = {}) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: tickColor() } } },
                    scales: {
                        x: { ticks: { color: tickColor() }, grid: { display: false } },
                        y: { ticks: { color: tickColor() }, grid: { color: gridColor() }, beginAtZero: true }
                    },
                    ...extra
                }
            }

            // Chart: Top progress
            const topLabels = @json($charts['topLabels'] ?? []);
            const topProgress = @json($charts['topProgress'] ?? []);
            const elProgress = document.getElementById('chartProgress');
            if (elProgress) {
                new Chart(elProgress, {
                    type: 'bar',
                    data: { labels: topLabels, datasets: [{ label: 'Progress %', data: topProgress, borderWidth: 1 }] },
                    options: baseOptions({ plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 100 } } })
                });
            }

            // Chart: Divisions
            const divLabels = @json($charts['divLabels'] ?? []);
            const divCounts = @json($charts['divCounts'] ?? []);
            const elDiv = document.getElementById('chartDivisions');
            if (elDiv) {
                new Chart(elDiv, {
                    type: 'doughnut',
                    data: { labels: divLabels, datasets: [{ data: divCounts, borderWidth: 1 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: tickColor() } } } }
                });
            }

            // Chart: Trend
            const tLabels = @json($charts['trendLabels'] ?? []);
            const tQuiz = @json($charts['trendQuiz'] ?? []);
            const tAsg = @json($charts['trendAssignments'] ?? []);
            const elTrend = document.getElementById('chartTrend');
            if (elTrend) {
                new Chart(elTrend, {
                    type: 'line',
                    data: {
                        labels: tLabels,
                        datasets: [
                            { label: 'Quiz Attempts', data: tQuiz, tension: 0.35, borderWidth: 2, pointRadius: 2 },
                            { label: 'Assignments', data: tAsg, tension: 0.35, borderWidth: 2, pointRadius: 2 },
                        ]
                    },
                    options: baseOptions({
                        plugins: { legend: { position: 'bottom' } },
                        scales: { y: { beginAtZero: true } }
                    })
                });
            }

            // Optional: if you toggle dark class dynamically
            // const observer = new MutationObserver(() => location.reload());
            // observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        })();
    </script>
@endsection