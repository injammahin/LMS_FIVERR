@extends('layouts.admin')

@section('title', 'Teacher Reports')

@section('content')
    @php
        $m = $map ?? [];
        $rangeDays = $rangeDays ?? 30;
        $status = $status ?? 'all';
        $search = $search ?? '';
        $k = $kpis ?? [
            'totalTeachers' => 0,
            'activeTeachers' => 0,
            'suspendedTeachers' => 0,
            'assignedCourses' => 0,
            'pendingAssignments' => 0,
            'pendingQuizzes' => 0,
            'avgGradeAll' => 0,
        ];
        $charts = $charts ?? [
            'topLabels' => [],
            'topCourses' => [],
            'topPending' => [],
            'topGrades' => [],
        ];
    @endphp

    <div class="space-y-6">

        {{-- HERO --}}
        <div
            class="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
            <div class="h-16 bg-gradient-to-r from-fuchsia-700 via-indigo-700 to-sky-700"></div>

            {{-- Decorative glows --}}
            <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full blur-3xl opacity-25"
                style="background: radial-gradient(circle at center, rgba(255,255,255,.35), transparent 60%);"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-28 w-96 h-96 rounded-full blur-3xl opacity-20"
                style="background: radial-gradient(circle at center, rgba(56,189,248,.35), transparent 60%);"></div>

            <div class="p-5 sm:p-6 lg:p-7">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border
                                    bg-fuchsia-50 text-fuchsia-700 border-fuchsia-100
                                    dark:bg-fuchsia-500/10 dark:text-fuchsia-200 dark:border-fuchsia-500/20">
                            <i class="fa-solid fa-clipboard-list text-[12px]"></i>
                            Teacher Report Center
                        </div>

                        <h1 class="mt-2 text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            Teachers Analytics
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-white/60 mt-1 max-w-2xl">
                            Course load, content totals, pending reviews, grading volume, and average quiz performance.
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

                                <input name="search" value="{{ $search }}" placeholder="Search teacher..."
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

        {{-- KPI GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-7 gap-4">
            {{-- Teachers --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:border-indigo-500/20">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Teachers</div>
                        <div class="kpiValue">{{ (int) ($k['totalTeachers'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">All teachers</div>
            </div>

            {{-- Active --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Active</div>
                        <div class="kpiValue">{{ (int) ($k['activeTeachers'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Can login</div>
            </div>

            {{-- Suspended --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-200 dark:border-red-500/20">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Suspended</div>
                        <div class="kpiValue">{{ (int) ($k['suspendedTeachers'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Blocked accounts</div>
            </div>

            {{-- Assigned --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-500/10 dark:text-blue-200 dark:border-blue-500/20">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Assigned</div>
                        <div class="kpiValue">{{ (int) ($k['assignedCourses'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Courses (current page)</div>
            </div>

            {{-- Pending Total --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-amber-50 text-amber-800 border-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20">
                        <i class="fa-solid fa-inbox"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Pending</div>
                        <div class="kpiValue">{{ (int) ($k['pendingAssignments'] ?? 0) + (int) ($k['pendingQuizzes'] ?? 0) }}
                        </div>
                    </div>
                </div>
                <div class="kpiHint">Assignments + quizzes</div>
            </div>

            {{-- Pending Assignments --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-500/10 dark:text-purple-200 dark:border-purple-500/20">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Pending Assign</div>
                        <div class="kpiValue">{{ (int) ($k['pendingAssignments'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Needs grading</div>
            </div>

            {{-- Avg Grade --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-500/10 dark:text-sky-200 dark:border-sky-500/20">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Avg Grade</div>
                        <div class="kpiValue">{{ (int) ($k['avgGradeAll'] ?? 0) }}%</div>
                    </div>
                </div>
                <div class="kpiHint">Range average</div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="panelCard xl:col-span-2">
                <div class="panelHead">
                    <div class="min-w-0">
                        <div class="panelTitle">Top Teachers by Course Load</div>
                        <div class="panelSub">Top 10 based on courses assigned</div>
                    </div>
                    <span
                        class="panelPill bg-indigo-50 border border-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-200">
                        <i class="fa-solid fa-layer-group text-[12px]"></i> Load
                    </span>
                </div>
                <div class="panelBody h-[290px]">
                    <canvas id="chartCourses"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div class="min-w-0">
                        <div class="panelTitle">Teacher Status Split</div>
                        <div class="panelSub">Active vs suspended</div>
                    </div>
                    <span
                        class="panelPill bg-emerald-50 border border-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">
                        <i class="fa-solid fa-shield-halved text-[12px]"></i> Status
                    </span>
                </div>
                <div class="panelBody h-[290px]">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="panelCard">
                <div class="panelHead">
                    <div class="min-w-0">
                        <div class="panelTitle">Pending Reviews</div>
                        <div class="panelSub">Assignments + quizzes (Top 10)</div>
                    </div>
                    <span
                        class="panelPill bg-amber-50 border border-amber-100 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200">
                        <i class="fa-solid fa-inbox text-[12px]"></i> Pending
                    </span>
                </div>
                <div class="panelBody h-[300px]">
                    <canvas id="chartPending"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div class="min-w-0">
                        <div class="panelTitle">Average Quiz Grade</div>
                        <div class="panelSub">From submitted attempts in selected range (Top 10)</div>
                    </div>
                    <span
                        class="panelPill bg-sky-50 border border-sky-100 text-sky-800 dark:bg-sky-500/10 dark:border-sky-500/20 dark:text-sky-200">
                        <i class="fa-solid fa-graduation-cap text-[12px]"></i> Grade
                    </span>
                </div>
                <div class="panelBody h-[300px]">
                    <canvas id="chartGrades"></canvas>
                </div>
            </div>
        </div>

        {{-- MOBILE CARDS (better than wide table on small screens) --}}
        <div class="md:hidden space-y-3">
            @forelse($teachers as $t)
                @php
                    $id = $t->id;

                    $courses = (int) ($m['courses'][$id] ?? 0);
                    $lessons = (int) ($m['lessons'][$id] ?? 0);
                    $quizzes = (int) ($m['quizzes'][$id] ?? 0);
                    $assigns = (int) ($m['assignments'][$id] ?? 0);

                    $pA = (int) ($m['pending_assignments'][$id] ?? 0);
                    $pQ = (int) ($m['pending_quizzes'][$id] ?? 0);
                    $pending = $pA + $pQ;

                    $gA = (int) ($m['graded_assignments'][$id] ?? 0);
                    $gQ = (int) ($m['graded_quizzes'][$id] ?? 0);

                    $avg = (int) round((float) ($m['avg_grade'][$id] ?? 0));

                    $last = $m['last_activity'][$id] ?? null;
                    $lastTxt = $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—';

                    $avgTone = $avg >= 70 ? 'emerald' : ($avg >= 40 ? 'amber' : 'red');
                @endphp

                <div
                    class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-gray-900 dark:text-white truncate">{{ $t->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                                    {{ $t->email ?? $t->username ?? '—' }}
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if($t->is_active)
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

                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border bg-white border-gray-200 text-gray-700 dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                        Courses: <span class="font-semibold">{{ $courses }}</span>
                                    </span>

                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border bg-white border-gray-200 text-gray-700 dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                        Pending: <span class="font-semibold">{{ $pending }}</span>
                                    </span>

                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border
                                            {{ $avgTone === 'emerald' ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' :
                ($avgTone === 'amber' ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200' :
                    'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200') }}">
                                        Avg: <span class="font-semibold">{{ $avg }}%</span>
                                    </span>
                                </div>

                                <div class="mt-3 text-xs text-gray-500 dark:text-white/60">
                                    L/Q/A: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $lessons }} /
                                        {{ $quizzes }} / {{ $assigns }}</span>
                                    • Last: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $lastTxt }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <a href="{{ route('admin.teachers.courses.edit', $t->id) }}"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs font-semibold">
                                <i class="fa-solid fa-layer-group mr-2 text-[12px]"></i> Assign
                            </a>

                            <a href="{{ route('admin.teachers.edit', $t->id) }}"
                                class="inline-flex items-center justify-center px-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs font-semibold">
                                <i class="fa-solid fa-pen mr-2 text-[12px]"></i> Edit
                            </a>

                            <form action="{{ route('admin.teachers.toggle-status', $t->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button
                                    class="inline-flex items-center justify-center px-3 py-2 rounded-xl text-xs font-semibold border
                                        {{ $t->is_active ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200'
                : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' }}">
                                    <i
                                        class="fa-solid {{ $t->is_active ? 'fa-user-slash' : 'fa-circle-check' }} mr-2 text-[12px]"></i>
                                    {{ $t->is_active ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 p-8 text-center text-gray-500 dark:text-white/60">
                    No teachers found.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $teachers->links() }}
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block panelCard overflow-hidden">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Teacher Detailed Report</div>
                    <div class="panelSub">Per-teacher load, pending grading, and performance</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">Teacher</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Courses</th>
                            <th class="px-6 py-3 text-left font-semibold">L / Q / A</th>
                            <th class="px-6 py-3 text-left font-semibold">Pending</th>
                            <th class="px-6 py-3 text-left font-semibold">Graded</th>
                            <th class="px-6 py-3 text-left font-semibold">Avg Grade</th>
                            <th class="px-6 py-3 text-left font-semibold">Last Activity</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($teachers as $t)
                                        @php
                                            $id = $t->id;

                                            $courses = (int) ($m['courses'][$id] ?? 0);
                                            $lessons = (int) ($m['lessons'][$id] ?? 0);
                                            $quizzes = (int) ($m['quizzes'][$id] ?? 0);
                                            $assigns = (int) ($m['assignments'][$id] ?? 0);

                                            $pA = (int) ($m['pending_assignments'][$id] ?? 0);
                                            $pQ = (int) ($m['pending_quizzes'][$id] ?? 0);
                                            $pending = $pA + $pQ;

                                            $gA = (int) ($m['graded_assignments'][$id] ?? 0);
                                            $gQ = (int) ($m['graded_quizzes'][$id] ?? 0);

                                            $avg = (int) round((float) ($m['avg_grade'][$id] ?? 0));

                                            $last = $m['last_activity'][$id] ?? null;
                                            $lastTxt = $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—';
                                        @endphp

                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $t->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                                    {{ $t->email ?? $t->username ?? '—' }}
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                @if($t->is_active)
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

                                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $courses }}</td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $lessons }} / {{ $quizzes }} / {{ $assigns }}
                                            </td>

                                            <td class="px-6 py-4">
                                                <div class="text-xs text-gray-500 dark:text-white/60">A: {{ $pA }} • Q: {{ $pQ }}</div>
                                                <div class="font-semibold text-gray-900 dark:text-white mt-1">{{ $pending }}</div>
                                            </td>

                                            <td class="px-6 py-4">
                                                <div class="text-xs text-gray-500 dark:text-white/60">A: {{ $gA }} • Q: {{ $gQ }}</div>
                                                <div class="font-semibold text-gray-900 dark:text-white mt-1">{{ $gA + $gQ }}</div>
                                            </td>

                                            <td class="px-6 py-4">
                                                <span
                                                    class="px-2 py-1 rounded-full text-[11px] border
                                                        {{ $avg >= 70 ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200'
                            : ($avg >= 40 ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200'
                                : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200') }}">
                                                    {{ $avg }}%
                                                </span>
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">{{ $lastTxt }}</td>

                                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                                <a href="{{ route('admin.teachers.courses.edit', $t->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs">
                                                    Assign
                                                </a>

                                                <a href="{{ route('admin.teachers.edit', $t->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs">
                                                    Edit
                                                </a>

                                                <form action="{{ route('admin.teachers.toggle-status', $t->id) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button
                                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs border
                                                            {{ $t->is_active ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200'
                            : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' }}">
                                                        {{ $t->is_active ? 'Suspend' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                    No teachers found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $teachers->links() }}
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
                    radial-gradient(420px circle at 20% 20%, rgba(99, 102, 241, .10), transparent 45%),
                    radial-gradient(420px circle at 80% 80%, rgba(236, 72, 153, .08), transparent 50%);
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

            /* avoid “tight” table on small widths */
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
            const labels = @json($charts['topLabels'] ?? []);
            const topCourses = @json($charts['topCourses'] ?? []);
            const topPending = @json($charts['topPending'] ?? []);
            const topGrades = @json($charts['topGrades'] ?? []);

            const activeCount = @json((int) ($k['activeTeachers'] ?? 0));
            const suspendedCount = @json((int) ($k['suspendedTeachers'] ?? 0));

            const isDark = () => document.documentElement.classList.contains('dark');
            const gridColor = () => isDark() ? 'rgba(255,255,255,.10)' : 'rgba(17,24,39,.08)';
            const tickColor = () => isDark() ? 'rgba(255,255,255,.75)' : 'rgba(55,65,81,1)';

            function baseOptions(extra = {}) {
                return {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: tickColor() } }
                    },
                    scales: {
                        x: { ticks: { color: tickColor() }, grid: { color: gridColor(), display: false } },
                        y: { ticks: { color: tickColor() }, grid: { color: gridColor() }, beginAtZero: true }
                    },
                    ...extra
                }
            }

            // Courses
            new Chart(document.getElementById('chartCourses'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Courses', data: topCourses, borderWidth: 1 }] },
                options: baseOptions({ plugins: { legend: { display: false } } })
            });

            // Status doughnut
            new Chart(document.getElementById('chartStatus'), {
                type: 'doughnut',
                data: {
                    labels: ['Active', 'Suspended'],
                    datasets: [{ data: [activeCount, suspendedCount], borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { color: tickColor() } }
                    }
                }
            });

            // Pending
            new Chart(document.getElementById('chartPending'), {
                type: 'bar',
                data: { labels, datasets: [{ label: 'Pending', data: topPending, borderWidth: 1 }] },
                options: baseOptions({ plugins: { legend: { display: false } } })
            });

            // Grades
            new Chart(document.getElementById('chartGrades'), {
                type: 'line',
                data: { labels, datasets: [{ label: 'Avg Grade %', data: topGrades, tension: 0.35, borderWidth: 2, pointRadius: 2 }] },
                options: baseOptions({
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: tickColor() }, grid: { display: false } },
                        y: { ticks: { color: tickColor() }, grid: { color: gridColor() }, beginAtZero: true, max: 100 }
                    }
                })
            });
        })();
    </script>
@endsection