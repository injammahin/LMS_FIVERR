@extends('layouts.admin')

@section('title', 'Graded Assignments')

@section('content')
    @php
        $k = $kpis ?? [];
        $charts = $charts ?? [];

        $rangeDays = $rangeDays ?? 30;
        $type = $type ?? 'all';
        $search = $search ?? '';
    @endphp

    <div class="space-y-6">

        {{-- HERO --}}
        <div
            class="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
            <div class="h-16 bg-gradient-to-r from-amber-700 via-fuchsia-700 to-indigo-700"></div>

            <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full blur-3xl opacity-25"
                style="background: radial-gradient(circle at center, rgba(255,255,255,.35), transparent 60%);"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-28 w-96 h-96 rounded-full blur-3xl opacity-20"
                style="background: radial-gradient(circle at center, rgba(245,158,11,.22), transparent 60%);"></div>

            <div class="p-5 sm:p-6 lg:p-7">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border
                                    bg-amber-50 text-amber-800 border-amber-100
                                    dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20">
                            <i class="fa-solid fa-circle-check text-[12px]"></i>
                            Graded Assignments
                        </div>

                        <h1 class="mt-2 text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            Grading Performance & Analytics
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-white/60 mt-1 max-w-2xl">
                            Track graded submissions, pass/fail, average score, grading speed, and course performance.
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
                                Type: {{ strtoupper($type) }}
                            </span>
                        </div>
                    </div>

                    {{-- FILTER BAR --}}
                    <form method="GET" class="w-full lg:w-auto">
                        <div
                            class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white/80 dark:bg-slate-950/60 backdrop-blur p-3 shadow-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2">
                                <select name="range"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">
                                    <option value="7" {{ (int) $rangeDays === 7 ? 'selected' : '' }}>Last 7 days</option>
                                    <option value="30" {{ (int) $rangeDays === 30 ? 'selected' : '' }}>Last 30 days</option>
                                    <option value="90" {{ (int) $rangeDays === 90 ? 'selected' : '' }}>Last 90 days</option>
                                </select>

                                <select name="type"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">
                                    <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All types</option>
                                    <option value="points" {{ $type === 'points' ? 'selected' : '' }}>Points</option>
                                    <option value="pass_fail" {{ $type === 'pass_fail' ? 'selected' : '' }}>Pass/Fail</option>
                                </select>

                                <select name="division_id"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">
                                    <option value="">All divisions</option>
                                    @foreach($divisions as $d)
                                        <option value="{{ $d->id }}" {{ (string) ($divisionId ?? '') === (string) $d->id ? 'selected' : '' }}>
                                            {{ $d->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="subject_id"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">
                                    <option value="">All subjects</option>
                                    @foreach($subjects as $s)
                                        <option value="{{ $s->id }}" {{ (string) ($subjectId ?? '') === (string) $s->id ? 'selected' : '' }}>
                                            {{ $s->division?->name }} → {{ $s->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <select name="course_id"
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">
                                    <option value="">All courses</option>
                                    @foreach($coursesList as $c)
                                        <option value="{{ $c->id }}" {{ (string) ($courseId ?? '') === (string) $c->id ? 'selected' : '' }}>
                                            {{ $c->title }}
                                        </option>
                                    @endforeach
                                </select>

                                <div class="flex gap-2">
                                    <input name="search" value="{{ $search }}" placeholder="Search student / assignment..."
                                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">

                                    <select name="per_page"
                                        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm dark:text-white">
                                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                        <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                    </select>

                                    <button
                                        class="rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm px-4 py-2 shadow-sm inline-flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-magnifying-glass text-[12px]"></i> Apply
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- KPI GRID --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:border-indigo-500/20">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Graded</div>
                        <div class="kpiValue">{{ (int) ($k['totalGraded'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Submissions graded</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Students</div>
                        <div class="kpiValue">{{ (int) ($k['uniqueStudents'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Unique students</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-500/10 dark:text-sky-200 dark:border-sky-500/20">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Assignments</div>
                        <div class="kpiValue">{{ (int) ($k['uniqueAssignments'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Unique assignments</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-amber-50 text-amber-800 border-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20">
                        <i class="fa-solid fa-stopwatch"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Avg Time</div>
                        <div class="kpiValue">{{ (int) ($k['avgTurnaroundHrs'] ?? 0) }}h</div>
                    </div>
                </div>
                <div class="kpiHint">Submit → graded</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-500/10 dark:text-purple-200 dark:border-purple-500/20">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Pass Rate</div>
                        <div class="kpiValue">{{ (int) ($k['passRate'] ?? 0) }}%</div>
                    </div>
                </div>
                <div class="kpiHint">Pass/Fail only</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-fuchsia-50 text-fuchsia-700 border-fuchsia-100 dark:bg-fuchsia-500/10 dark:text-fuchsia-200 dark:border-fuchsia-500/20">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Avg Score</div>
                        <div class="kpiValue">{{ (int) ($k['avgPercent'] ?? 0) }}%</div>
                    </div>
                </div>
                <div class="kpiHint">Points only</div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="panelCard xl:col-span-2">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Daily Grading Trend</div>
                        <div class="panelSub">Last 14 days graded submissions</div>
                    </div>
                    <span
                        class="panelPill bg-indigo-50 border border-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-200">
                        <i class="fa-solid fa-wave-square text-[12px]"></i> Trend
                    </span>
                </div>
                <div class="panelBody h-[290px]">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Pass vs Fail</div>
                        <div class="panelSub">Only pass/fail graded</div>
                    </div>
                    <span
                        class="panelPill bg-emerald-50 border border-emerald-100 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">
                        <i class="fa-solid fa-shield-halved text-[12px]"></i> Outcome
                    </span>
                </div>
                <div class="panelBody h-[290px]">
                    <canvas id="chartPassFail"></canvas>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Grade Distribution</div>
                        <div class="panelSub">Points assignments only</div>
                    </div>
                    <span
                        class="panelPill bg-amber-50 border border-amber-100 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200">
                        <i class="fa-solid fa-chart-column text-[12px]"></i> Distribution
                    </span>
                </div>
                <div class="panelBody h-[300px]">
                    <canvas id="chartDist"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Top Courses by Graded Volume</div>
                        <div class="panelSub">Top 10 in this filter scope</div>
                    </div>
                    <span
                        class="panelPill bg-purple-50 border border-purple-100 text-purple-800 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-200">
                        <i class="fa-solid fa-layer-group text-[12px]"></i> Courses
                    </span>
                </div>
                <div class="panelBody h-[300px]">
                    <canvas id="chartTopCourses"></canvas>
                </div>
            </div>
        </div>

        {{-- MOBILE CARDS --}}
        <div class="md:hidden space-y-3">
            @forelse($submissions as $sub)
                @php
                    $a = $sub->assignment;
                    $c = $a?->course;
                    $st = $sub->user;

                    $typeTxt = ($a?->grading_type ?? 'points') === 'pass_fail' ? 'Pass/Fail' : 'Points';
                    $statusBadge = $sub->is_passed === null ? '—' : ($sub->is_passed ? 'Pass' : 'Fail');
                @endphp

                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-white truncate">{{ $a?->title ?? '—' }}</div>
                            <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                                Course: {{ $c?->title ?? '—' }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                                Student: <span class="font-semibold">{{ $st?->name ?? '—' }}</span>
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="px-2 py-1 rounded-full text-[11px] border bg-gray-50 border-gray-200 text-gray-700 dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                    {{ $typeTxt }}
                                </span>

                                @if(($a?->grading_type ?? 'points') === 'points')
                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200">
                                        Marks: {{ (int) ($sub->marks_awarded ?? 0) }} / {{ (int) ($a?->total_marks ?? 0) }}
                                    </span>
                                @else
                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">
                                        {{ $statusBadge }}
                                    </span>
                                @endif
                            </div>

                            <div class="mt-3 text-xs text-gray-500 dark:text-white/60">
                                Graded: {{ optional($sub->graded_at ?? $sub->updated_at)->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div
                    class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 p-8 text-center text-gray-500 dark:text-white/60">
                    No graded submissions found.
                </div>
            @endforelse

            <div class="pt-2">
                {{ $submissions->links() }}
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block panelCard overflow-hidden">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Graded Submissions</div>
                    <div class="panelSub">Detailed list for your filters</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">Student</th>
                            <th class="px-6 py-3 text-left font-semibold">Assignment</th>
                            <th class="px-6 py-3 text-left font-semibold">Course</th>
                            <th class="px-6 py-3 text-left font-semibold">Type</th>
                            <th class="px-6 py-3 text-left font-semibold">Result</th>
                            <th class="px-6 py-3 text-left font-semibold">Graded</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($submissions as $sub)
                            @php
                                $a = $sub->assignment;
                                $c = $a?->course;
                                $st = $sub->user;

                                $isPF = ($a?->grading_type ?? 'points') === 'pass_fail';
                                $typeTxt = $isPF ? 'Pass/Fail' : 'Points';

                                $result = $isPF
                                    ? ($sub->is_passed === null ? '—' : ($sub->is_passed ? 'Pass' : 'Fail'))
                                    : ((int) ($sub->marks_awarded ?? 0) . ' / ' . (int) ($a?->total_marks ?? 0));
                            @endphp

                            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $st?->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                        {{ $st?->email ?? $st?->username ?? '—' }}</div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $a?->title ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60 mt-1">ID: {{ $sub->id }}</div>
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    {{ $c?->title ?? '—' }}
                                </td>

                                <td class="px-6 py-4">
                                    <span
                                        class="px-2 py-1 rounded-full text-[11px] border bg-white border-gray-200 text-gray-700 dark:bg-white/5 dark:border-white/10 dark:text-white/70">
                                        {{ $typeTxt }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($isPF)
                                                        <span
                                                            class="px-2 py-1 rounded-full text-[11px] border
                                                                    {{ $sub->is_passed ? 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200'
                                        : 'bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200' }}">
                                                            {{ $result }}
                                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-[11px] border bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200">
                                            {{ $result }}
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    {{ optional($sub->graded_at ?? $sub->updated_at)->diffForHumans() }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                    No graded submissions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $submissions->links() }}
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
                overflow: hidden
            }

            .dark .kpiCard {
                border-color: rgba(255, 255, 255, .10);
                background: rgb(15 23 42);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .25)
            }

            .kpiCard:before {
                content: "";
                position: absolute;
                inset: -1px;
                background: radial-gradient(420px circle at 20% 20%, rgba(245, 158, 11, .10), transparent 45%),
                    radial-gradient(420px circle at 80% 80%, rgba(99, 102, 241, .08), transparent 50%);
                pointer-events: none;
            }

            .kpiTop {
                position: relative;
                display: flex;
                align-items: center;
                gap: 12px
            }

            .kpiIcon {
                width: 40px;
                height: 40px;
                border-radius: 14px;
                display: grid;
                place-items: center;
                border: 1px solid;
                flex: 0 0 auto
            }

            .kpiMeta {
                position: relative;
                min-width: 0
            }

            .kpiLabel {
                font-size: 12px;
                font-weight: 800;
                color: #6b7280
            }

            .dark .kpiLabel {
                color: rgba(255, 255, 255, .55)
            }

            .kpiValue {
                margin-top: 6px;
                font-size: 18px;
                font-weight: 900;
                color: #111827;
                line-height: 1
            }

            .dark .kpiValue {
                color: rgba(255, 255, 255, .95)
            }

            .kpiHint {
                position: relative;
                margin-top: 10px;
                font-size: 12px;
                color: #9ca3af
            }

            .dark .kpiHint {
                color: rgba(255, 255, 255, .45)
            }

            .panelCard {
                border-radius: 16px;
                border: 1px solid rgba(229, 231, 235, 1);
                background: #fff;
                box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
                overflow: hidden
            }

            .dark .panelCard {
                border-color: rgba(255, 255, 255, .10);
                background: rgb(15 23 42);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .25)
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
                background: linear-gradient(to bottom, rgba(255, 255, 255, .06), rgb(15 23 42))
            }

            .panelTitle {
                font-size: 14px;
                font-weight: 900;
                color: #111827
            }

            .dark .panelTitle {
                color: rgba(255, 255, 255, .95)
            }

            .panelSub {
                margin-top: 4px;
                font-size: 12px;
                color: #6b7280
            }

            .dark .panelSub {
                color: rgba(255, 255, 255, .55)
            }

            .panelPill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 11px;
                padding: 6px 10px;
                border-radius: 999px;
                font-weight: 900;
                white-space: nowrap
            }

            .panelBody {
                padding: 14px 16px
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
            let charts = [];

            const isDark = () => document.documentElement.classList.contains('dark');
            const gridColor = () => isDark() ? 'rgba(255,255,255,.10)' : 'rgba(17,24,39,.08)';
            const tickColor = () => isDark() ? 'rgba(255,255,255,.75)' : 'rgba(55,65,81,1)';

            function destroyCharts() { charts.forEach(c => c?.destroy()); charts = []; }

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
                };
            }

            function render() {
                destroyCharts();

                const trendLabels = @json($charts['trendLabels'] ?? []);
                const trendCounts = @json($charts['trendCounts'] ?? []);

                const passLabels = @json($charts['passLabels'] ?? []);
                const passValues = @json($charts['passValues'] ?? []);

                const distLabels = @json($charts['distLabels'] ?? []);
                const distValues = @json($charts['distValues'] ?? []);

                const topCourseLabels = @json($charts['topCourseLabels'] ?? []);
                const topCourseCounts = @json($charts['topCourseCounts'] ?? []);

                const cTrend = document.getElementById('chartTrend');
                if (cTrend) charts.push(new Chart(cTrend, {
                    type: 'line',
                    data: { labels: trendLabels, datasets: [{ label: 'Graded', data: trendCounts, tension: 0.35, borderWidth: 2, pointRadius: 2 }] },
                    options: baseOptions({ plugins: { legend: { display: false } } })
                }));

                const cPF = document.getElementById('chartPassFail');
                if (cPF) charts.push(new Chart(cPF, {
                    type: 'doughnut',
                    data: { labels: passLabels, datasets: [{ data: passValues, borderWidth: 1 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: tickColor() } } } }
                }));

                const cDist = document.getElementById('chartDist');
                if (cDist) charts.push(new Chart(cDist, {
                    type: 'bar',
                    data: { labels: distLabels, datasets: [{ label: 'Count', data: distValues, borderWidth: 1 }] },
                    options: baseOptions({ plugins: { legend: { display: false } } })
                }));

                const cTop = document.getElementById('chartTopCourses');
                if (cTop) charts.push(new Chart(cTop, {
                    type: 'bar',
                    data: { labels: topCourseLabels, datasets: [{ label: 'Graded', data: topCourseCounts, borderWidth: 1 }] },
                    options: baseOptions({ plugins: { legend: { display: false } } })
                }));
            }

            render();

            // ✅ Re-render charts only if theme changes (no reload loop)
            let last = isDark();
            const observer = new MutationObserver(() => {
                const now = isDark();
                if (now !== last) { last = now; render(); }
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        })();
    </script>
@endsection