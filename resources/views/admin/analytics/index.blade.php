@extends('layouts.admin')

@section('title', 'Admin Analytics')

@section('content')
    @php
        $donutStyle = function (int $percent, string $color) {
            $p = max(0, min(100, $percent));
            return "background: conic-gradient({$color} {$p}%, rgba(148,163,184,.20) 0%);";
        };

        $chip = function (string $tone) {
            return match ($tone) {
                'blue' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-500/10 dark:text-blue-200 dark:border-blue-500/20',
                'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20',
                'amber' => 'bg-amber-50 text-amber-700 border-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20',
                'purple' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-500/10 dark:text-purple-200 dark:border-purple-500/20',
                'red' => 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/10 dark:text-red-200 dark:border-red-500/20',
                default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-white/5 dark:text-white/70 dark:border-white/10',
            };
        };

        // KPI donuts
        $activePct = $totalStudents > 0 ? (int) round(($activeStudents / $totalStudents) * 100) : 0;
        $suspendStuPct = $totalStudents > 0 ? (int) round(($suspendedStudents / $totalStudents) * 100) : 0;
        $suspendTeachPct = $totalTeachers > 0 ? (int) round(($suspendedTeachers / $totalTeachers) * 100) : 0;

        $completionPct = (int) ($avgOverallCompletion ?? 0);
        $gradePct = (int) ($avgOverallGrade ?? 0);

        // Top division snapshot
        $topDivision = collect($divisionRows)->sortByDesc('overall_percent')->first();
        $topDivName = $topDivision['division']?->name ?? '—';
        $topDivPct = (int) ($topDivision['overall_percent'] ?? 0);
    @endphp

    <div class="space-y-6">

        {{-- Premium Header --}}
        <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="h-14 bg-gradient-to-r from-indigo-700 via-blue-700 to-sky-700"></div>

            <div class="p-5 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border {{ $chip('blue') }}">
                        <i class="fa-solid fa-chart-pie text-[12px]"></i>
                        Analytics Center
                    </div>

                    <h1 class="mt-2 text-md font-semibold text-gray-900 dark:text-white">Admin Analytics</h1>
                    <p class="text-sm text-gray-500 dark:text-white/60 mt-1">
                        Deep insights across Students, Teachers, Courses, Subjects, Divisions, and Activity.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                    <form method="GET" class="flex flex-wrap items-center gap-2">
                        <div class="text-xs text-gray-500 dark:text-white/60 mr-1">Range</div>
                        <select name="range"
                            class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white">
                            <option value="7"  {{ $rangeDays == 7 ? 'selected' : '' }}>Last 7 days</option>
                            <option value="30" {{ $rangeDays == 30 ? 'selected' : '' }}>Last 30 days</option>
                            <option value="90" {{ $rangeDays == 90 ? 'selected' : '' }}>Last 90 days</option>
                        </select>
                        <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm shadow-sm">
                            Apply
                        </button>
                    </form>

                    <div class="hidden sm:flex gap-2">
                        <span class="px-3 py-2 rounded-xl border text-sm {{ $chip('gray') }}">
                            <i class="fa-solid fa-circle-info mr-1 text-[12px]"></i>
                            Charts update automatically
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">

            {{-- Students --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('blue') }}">
                        <i class="fa-solid fa-users text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('blue') }}">Students</span>
                </div>

                <div class="kpiValue">{{ $totalStudents }}</div>
                <div class="kpiSub">Total enrolled (role=student)</div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Suspended</div>
                    <div class="kpiMiniValue">{{ $suspendedStudents }}</div>
                </div>
            </div>

            {{-- Active Students --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('emerald') }}">
                        <i class="fa-solid fa-bolt text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('emerald') }}">Active</span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="kpiValue">{{ $activeStudents }}</div>
                        <div class="kpiSub">Active in last {{ $rangeDays }} days</div>
                    </div>

                    <div class="donut border border-gray-200 dark:border-white/10"
                        style="{{ $donutStyle($activePct, '#10b981') }}">
                        <span class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $activePct }}%</span>
                    </div>
                </div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Range</div>
                    <div class="kpiMiniValue">{{ $rangeDays }}d</div>
                </div>
            </div>

            {{-- Teachers --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('purple') }}">
                        <i class="fa-solid fa-chalkboard-user text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('purple') }}">Teachers</span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="kpiValue">{{ $totalTeachers }}</div>
                        <div class="kpiSub">Total teaching accounts</div>
                    </div>

                    <div class="donut border border-gray-200 dark:border-white/10"
                        style="{{ $donutStyle($suspendTeachPct, '#a855f7') }}">
                        <span class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $suspendTeachPct }}%</span>
                    </div>
                </div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Suspended</div>
                    <div class="kpiMiniValue">{{ $suspendedTeachers }}</div>
                </div>
            </div>

            {{-- Content --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('amber') }}">
                        <i class="fa-solid fa-layer-group text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('amber') }}">Content</span>
                </div>

                <div class="kpiValue">{{ $totalCourses }}</div>
                <div class="kpiSub">Courses</div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Subjects / Divisions</div>
                    <div class="kpiMiniValue">{{ $totalSubjects }} / {{ $totalDivisions }}</div>
                </div>
            </div>

            {{-- Completion --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('emerald') }}">
                        <i class="fa-solid fa-chart-line text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('emerald') }}">Completion</span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="kpiValue">{{ $avgOverallCompletion }}%</div>
                        <div class="kpiSub">Avg completion across courses</div>
                    </div>

                    <div class="donut border border-gray-200 dark:border-white/10"
                        style="{{ $donutStyle($completionPct, '#10b981') }}">
                        <span class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $completionPct }}%</span>
                    </div>
                </div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Top Division</div>
                    <div class="kpiMiniValue">{{ $topDivPct }}%</div>
                </div>
            </div>

            {{-- Grade + Activity --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('blue') }}">
                        <i class="fa-solid fa-graduation-cap text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('blue') }}">Performance</span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="kpiValue">{{ $avgOverallGrade }}%</div>
                        <div class="kpiSub">Avg quiz grade</div>
                    </div>

                    <div class="donut border border-gray-200 dark:border-white/10"
                        style="{{ $donutStyle($gradePct, '#3b82f6') }}">
                        <span class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $gradePct }}%</span>
                    </div>
                </div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Activity</div>
                    <div class="kpiMiniValue">{{ $rangeAssignmentSubmissions + $rangeQuizAttempts }}</div>
                </div>
            </div>

        </div>

        {{-- Charts Row 1 --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            <div class="panelCard xl:col-span-2">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Active Students Trend</div>
                        <div class="panelSub">Last 14 days — distinct active students per day</div>
                    </div>
                    <span class="panelPill {{ $chip('emerald') }}">
                        <i class="fa-solid fa-wave-square text-[12px]"></i> Activity
                    </span>
                </div>
                <div class="panelBody h-[300px]">
                    <canvas id="activeTrendChart"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Division Progress</div>
                        <div class="panelSub">Completion % + student distribution</div>
                    </div>
                    <span class="panelPill {{ $chip('blue') }}">
                        <i class="fa-solid fa-sitemap text-[12px]"></i> Divisions
                    </span>
                </div>
                <div class="panelBody h-[300px]">
                    <canvas id="divisionProgressChart"></canvas>
                </div>
            </div>

        </div>

        {{-- Charts Row 2 --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Course Completion</div>
                        <div class="panelSub">Top 12 courses (by student count)</div>
                    </div>
                    <span class="panelPill {{ $chip('emerald') }}">
                        <i class="fa-solid fa-bars-progress text-[12px]"></i> Completion
                    </span>
                </div>
                <div class="panelBody h-[340px]">
                    <canvas id="courseCompletionChart"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Course Average Grades</div>
                        <div class="panelSub">Top 12 courses (avg quiz %)</div>
                    </div>
                    <span class="panelPill {{ $chip('purple') }}">
                        <i class="fa-solid fa-graduation-cap text-[12px]"></i> Grades
                    </span>
                </div>
                <div class="panelBody h-[340px]">
                    <canvas id="courseGradesChart"></canvas>
                </div>
            </div>

        </div>

        {{-- Charts Row 3 --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Assignment Submissions Trend</div>
                        <div class="panelSub">Last 14 days — submissions count</div>
                    </div>
                    <span class="panelPill {{ $chip('amber') }}">
                        <i class="fa-solid fa-file-arrow-up text-[12px]"></i> Assignments
                    </span>
                </div>
                <div class="panelBody h-[280px]">
                    <canvas id="assignmentsTrendChart"></canvas>
                </div>
            </div>

            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Quiz Attempts Trend</div>
                        <div class="panelSub">Last 14 days — attempts count</div>
                    </div>
                    <span class="panelPill {{ $chip('blue') }}">
                        <i class="fa-solid fa-clipboard-question text-[12px]"></i> Quizzes
                    </span>
                </div>
                <div class="panelBody h-[280px]">
                    <canvas id="quizzesTrendChart"></canvas>
                </div>
            </div>

        </div>

        {{-- Tables Row --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Division Overview --}}
            <div class="panelCard overflow-hidden">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Divisions Overview</div>
                        <div class="panelSub">Students, subjects, courses, completion</div>
                    </div>
                    <span class="panelPill {{ $chip('blue') }}">
                        <i class="fa-solid fa-building-columns text-[12px]"></i> Overview
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Division</th>
                                <th class="px-5 py-3 text-left font-semibold">Students</th>
                                <th class="px-5 py-3 text-left font-semibold">Subjects</th>
                                <th class="px-5 py-3 text-left font-semibold">Courses</th>
                                <th class="px-5 py-3 text-left font-semibold w-[180px]">Completion</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach($divisionRows as $row)
                                @php $p = (int) $row['overall_percent']; @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                    <td class="px-5 py-4 font-semibold text-gray-900 dark:text-white">
                                        {{ $row['division']->name }}
                                    </td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $row['students'] }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $row['subjects'] }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $row['courses'] }}</td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-full max-w-[140px] rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden border border-gray-200 dark:border-white/10">
                                                <div class="h-2 rounded-full" style="width: {{ $p }}%; background:#10b981;"></div>
                                            </div>
                                            <div class="text-xs font-semibold text-gray-700 dark:text-white/80 w-[46px]">
                                                {{ $p }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

            {{-- Teachers Snapshot --}}
            <div class="panelCard overflow-hidden">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Teachers Snapshot</div>
                        <div class="panelSub">First 12 teachers (load + status)</div>
                    </div>
                    <span class="panelPill {{ $chip('purple') }}">
                        <i class="fa-solid fa-chalkboard-user text-[12px]"></i> Teachers
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Teacher</th>
                                <th class="px-5 py-3 text-left font-semibold">Login</th>
                                <th class="px-5 py-3 text-left font-semibold">Courses</th>
                                <th class="px-5 py-3 text-left font-semibold">Status</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @foreach($teachersTable as $t)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                    <td class="px-5 py-4 font-semibold text-gray-900 dark:text-white">{{ $t->name }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $t->email ?? $t->username ?? '—' }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">
                                        {{ isset($t->courses_teaching_count) ? $t->courses_teaching_count : '—' }}
                                    </td>
                                    <td class="px-5 py-4">
                                        @if(isset($t->is_active))
                                            @if($t->is_active)
                                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip('emerald') }}">Active</span>
                                            @else
                                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip('red') }}">Suspended</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

        {{-- Course Analytics Table --}}
        <div class="panelCard overflow-hidden">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Course Analytics</div>
                    <div class="panelSub">Completion + content totals + average grade</div>
                </div>
                <span class="panelPill {{ $chip('emerald') }}">
                    <i class="fa-solid fa-chart-simple text-[12px]"></i> Courses
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold">Course</th>
                            <th class="px-5 py-3 text-left font-semibold">Division</th>
                            <th class="px-5 py-3 text-left font-semibold">Students</th>
                            <th class="px-5 py-3 text-left font-semibold">L/Q/A</th>
                            <th class="px-5 py-3 text-left font-semibold w-[190px]">Completion</th>
                            <th class="px-5 py-3 text-left font-semibold">Avg Grade</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach($courseInsights as $row)
                            @php
                                $p = (int) $row['overall_percent'];
                                $g = (int) ($row['avg_grade'] ?? 0);
                                $gradeTone = $g >= 70 ? 'emerald' : ($g >= 40 ? 'amber' : 'red');
                            @endphp

                            <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-xl border grid place-items-center {{ $chip('blue') }}">
                                            <i class="fa-solid fa-book text-[13px]"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 dark:text-white truncate">
                                                {{ $row['course']->title }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-white/60 truncate">
                                                {{ optional($row['course']->subject)->name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $row['division'] }}</td>
                                <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $row['students'] }}</td>

                                <td class="px-5 py-4 text-gray-700 dark:text-white/80">
                                    {{ $row['lessons_total'] }}/{{ $row['quizzes_total'] }}/{{ $row['assignments_total'] }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-full max-w-[150px] rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden border border-gray-200 dark:border-white/10">
                                            <div class="h-2 rounded-full" style="width: {{ $p }}%; background:#10b981;"></div>
                                        </div>
                                        <div class="text-xs font-semibold text-gray-700 dark:text-white/80 w-[46px]">
                                            {{ $p }}%
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    @if($g > 0)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip($gradeTone) }}">
                                            {{ $g }}%
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip('gray') }}">
                                            —
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            <div class="panelCard overflow-hidden">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Recent Assignment Submissions</div>
                        <div class="panelSub">Last 10 submissions</div>
                    </div>
                    <span class="panelPill {{ $chip('amber') }}">
                        <i class="fa-solid fa-file-arrow-up text-[12px]"></i> Submissions
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Student</th>
                                <th class="px-5 py-3 text-left font-semibold">Course</th>
                                <th class="px-5 py-3 text-left font-semibold">Assignment</th>
                                <th class="px-5 py-3 text-left font-semibold">Time</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @forelse($recentAssignmentSubmissions as $r)
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                    <td class="px-5 py-4 text-gray-900 dark:text-white">{{ $r->student_name }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $r->course_title }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $r->assignment_title }}</td>
                                    <td class="px-5 py-4 text-gray-500 dark:text-white/60">
                                        {{ \Carbon\Carbon::parse($r->created_at)->format('d M, h:i A') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                        No data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="panelCard overflow-hidden">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Recent Quiz Attempts</div>
                        <div class="panelSub">Last 10 attempts</div>
                    </div>
                    <span class="panelPill {{ $chip('blue') }}">
                        <i class="fa-solid fa-clipboard-question text-[12px]"></i> Attempts
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Student</th>
                                <th class="px-5 py-3 text-left font-semibold">Course</th>
                                <th class="px-5 py-3 text-left font-semibold">Quiz</th>
                                <th class="px-5 py-3 text-left font-semibold">Score</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @forelse($recentQuizAttempts as $r)
                                @php
                                    $pct = ($r->total ?? 0) > 0 ? (int) round(($r->score / $r->total) * 100) : null;
                                    $tone = $pct === null ? 'gray' : ($pct >= 70 ? 'emerald' : ($pct >= 40 ? 'amber' : 'red'));
                                @endphp
                                <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                    <td class="px-5 py-4 text-gray-900 dark:text-white">{{ $r->student_name }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $r->course_title }}</td>
                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">{{ $r->quiz_title }}</td>
                                    <td class="px-5 py-4">
                                        @if($pct !== null)
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip($tone) }}">
                                                {{ $pct }}%
                                            </span>
                                        @else
                                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip('gray') }}">
                                                —
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                        No data
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

        </div>

    </div>

    @once
        <style>
            /* Premium small components like your dashboard */
            .kpiCard {
                border-radius: 16px;
                border: 1px solid rgba(229, 231, 235, 1);
                background: rgba(255, 255, 255, 1);
                padding: 16px;
                box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
                position: relative;
                overflow: hidden;
            }
            .dark .kpiCard {
                border-color: rgba(255, 255, 255, .10);
                background: rgba(15, 23, 42, 1);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
            }
            .kpiCard:before {
                content: "";
                position: absolute;
                inset: -1px;
                background: radial-gradient(420px circle at 30% 20%, rgba(59, 130, 246, .10), transparent 40%),
                            radial-gradient(420px circle at 80% 90%, rgba(16, 185, 129, .08), transparent 45%);
                pointer-events: none;
            }
            .kpiTop { position: relative; display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; gap:10px; }
            .kpiIcon { width:40px; height:40px; border-radius:14px; display:grid; place-items:center; border:1px solid; flex:0 0 auto; }
            .kpiPill { position:relative; display:inline-flex; align-items:center; gap:6px; font-size:11px; padding:6px 10px; border-radius:999px; border:1px solid; font-weight:700; letter-spacing:.2px; white-space:nowrap; }
            .kpiValue { position:relative; font-size:18px; font-weight:700; color:#111827; line-height:1.2; }
            .dark .kpiValue { color: rgba(255, 255, 255, .95); }
            .kpiSub { position:relative; margin-top:6px; font-size:12px; color: rgba(107,114,128,1); }
            .dark .kpiSub { color: rgba(255,255,255,.55); }
            .kpiFoot { position:relative; margin-top:12px; padding-top:12px; border-top:1px dashed rgba(229,231,235,1); display:flex; align-items:center; justify-content:space-between; font-size:12px; }
            .dark .kpiFoot { border-top-color: rgba(255,255,255,.10); }
            .kpiMiniLabel { color: rgba(107,114,128,1); }
            .dark .kpiMiniLabel { color: rgba(255,255,255,.55); }
            .kpiMiniValue { font-weight:700; color:#111827; }
            .dark .kpiMiniValue { color: rgba(255,255,255,.90); }

            .panelCard {
                border-radius: 16px;
                border: 1px solid rgba(229, 231, 235, 1);
                background: rgba(255, 255, 255, 1);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
                overflow: hidden;
            }
            .dark .panelCard {
                border-color: rgba(255, 255, 255, .10);
                background: rgba(15, 23, 42, 1);
                box-shadow: 0 10px 24px rgba(0, 0, 0, .25);
            }
            .panelHead {
                padding: 14px 16px;
                border-bottom: 1px solid rgba(229, 231, 235, 1);
                display:flex; align-items:flex-start; justify-content:space-between; gap:12px;
                background: linear-gradient(to bottom, rgba(249,250,251,1), rgba(255,255,255,1));
            }
            .dark .panelHead {
                border-bottom-color: rgba(255,255,255,.10);
                background: linear-gradient(to bottom, rgba(255,255,255,.05), rgba(15,23,42,1));
            }
            .panelTitle { font-size:14px; font-weight:700; color:#111827; }
            .dark .panelTitle { color: rgba(255,255,255,.95); }
            .panelSub { margin-top:4px; font-size:12px; color: rgba(107,114,128,1); }
            .dark .panelSub { color: rgba(255,255,255,.55); }
            .panelPill { display:inline-flex; align-items:center; gap:6px; font-size:11px; padding:6px 10px; border-radius:999px; border:1px solid; font-weight:700; white-space:nowrap; }
            .panelBody { padding: 14px 16px; }

            .donut {
                width: 62px; height: 62px; border-radius: 9999px;
                display:grid; place-items:center;
                position: relative;
            }
            .donut::before {
                content:"";
                position:absolute; inset: 9px;
                border-radius:9999px;
                background: rgba(255,255,255,.95);
                box-shadow: inset 0 0 0 1px rgba(229,231,235,1);
            }
            .dark .donut::before {
                background: rgba(15,23,42,.95);
                box-shadow: inset 0 0 0 1px rgba(255,255,255,.10);
            }
            .donut > span { position: relative; }

            table th, table td { white-space: nowrap; }
        </style>
    @endonce
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // Data from controller
        const activeLabels = @json($chartActiveLabels);
        const activeValues = @json($chartActiveValues);

        const divLabels = @json($chartDivLabels);
        const divProgress = @json($chartDivProgress);
        const divStudents = @json($chartDivStudents);

        const courseLabels = @json($chartCourseLabels);
        const courseCompletion = @json($chartCourseCompletion);
        const courseGrades = @json($chartCourseGrades);

        const asgLabels = @json($chartAssignmentLabels);
        const asgValues = @json($chartAssignmentValues);

        const quizLabels = @json($chartQuizLabels);
        const quizValues = @json($chartQuizValues);

        function chartTheme() {
            const dark = document.documentElement.classList.contains('dark');
            return {
                tick: dark ? 'rgba(255,255,255,.65)' : 'rgba(55,65,81,.75)',
                grid: dark ? 'rgba(255,255,255,.08)' : 'rgba(17,24,39,.08)'
            };
        }

        const charts = [];

        function destroyCharts() {
            while (charts.length) {
                try { charts.pop().destroy(); } catch (e) {}
            }
        }

        function buildCharts() {
            const theme = chartTheme();

            // Active trend
            charts.push(new Chart(document.getElementById('activeTrendChart'), {
                type: 'line',
                data: {
                    labels: activeLabels,
                    datasets: [{
                        label: 'Active students',
                        data: activeValues,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: theme.grid }, ticks: { color: theme.tick } },
                        y: { beginAtZero: true, grid: { color: theme.grid }, ticks: { color: theme.tick } }
                    }
                }
            }));

            // Division progress
            charts.push(new Chart(document.getElementById('divisionProgressChart'), {
                type: 'bar',
                data: {
                    labels: divLabels,
                    datasets: [
                        { label: 'Completion %', data: divProgress, borderWidth: 1 },
                        { label: 'Students', data: divStudents, borderWidth: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom', labels: { color: theme.tick } } },
                    scales: {
                        x: { grid: { color: theme.grid }, ticks: { color: theme.tick } },
                        y: { beginAtZero: true, grid: { color: theme.grid }, ticks: { color: theme.tick } }
                    }
                }
            }));

            // Course completion
            charts.push(new Chart(document.getElementById('courseCompletionChart'), {
                type: 'bar',
                data: {
                    labels: courseLabels,
                    datasets: [{ label: 'Completion %', data: courseCompletion, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: theme.grid }, ticks: { color: theme.tick } },
                        y: { beginAtZero: true, max: 100, grid: { color: theme.grid }, ticks: { color: theme.tick } }
                    }
                }
            }));

            // Course grades
            charts.push(new Chart(document.getElementById('courseGradesChart'), {
                type: 'bar',
                data: {
                    labels: courseLabels,
                    datasets: [{ label: 'Avg Grade %', data: courseGrades, borderWidth: 1 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: theme.grid }, ticks: { color: theme.tick } },
                        y: { beginAtZero: true, max: 100, grid: { color: theme.grid }, ticks: { color: theme.tick } }
                    }
                }
            }));

            // Assignment trend
            charts.push(new Chart(document.getElementById('assignmentsTrendChart'), {
                type: 'line',
                data: {
                    labels: asgLabels,
                    datasets: [{ label: 'Submissions', data: asgValues, tension: 0.35, borderWidth: 2, pointRadius: 2 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: theme.grid }, ticks: { color: theme.tick } },
                        y: { beginAtZero: true, grid: { color: theme.grid }, ticks: { color: theme.tick } }
                    }
                }
            }));

            // Quiz trend
            charts.push(new Chart(document.getElementById('quizzesTrendChart'), {
                type: 'line',
                data: {
                    labels: quizLabels,
                    datasets: [{ label: 'Attempts', data: quizValues, tension: 0.35, borderWidth: 2, pointRadius: 2 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { color: theme.grid }, ticks: { color: theme.tick } },
                        y: { beginAtZero: true, grid: { color: theme.grid }, ticks: { color: theme.tick } }
                    }
                }
            }));
        }

        // First render
        destroyCharts();
        buildCharts();

        // If your app toggles dark mode after load, re-render charts automatically
        const observer = new MutationObserver(() => {
            destroyCharts();
            buildCharts();
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    </script>
@endsection