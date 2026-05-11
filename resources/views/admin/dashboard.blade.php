@extends('layouts.admin')

@section('title', 'Admin Dashboard')

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

        $activePct = $totalStudents > 0 ? (int) round(($activeStudents / $totalStudents) * 100) : 0;
        $riskPct = $totalStudents > 0 ? (int) round(($atRiskCount / $totalStudents) * 100) : 0;
        $divAvg = (int) round(collect($divisionRows)->avg('overall_percent') ?? 0);

        $courseInsightsTotal = collect($courseInsights)->count();
        $atRiskTotal = collect($atRiskRows)->count();
    @endphp

    <div class="space-y-6">

        {{-- Premium header --}}
        <div
            class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="h-14 bg-gradient-to-r from-indigo-700 via-blue-700 to-sky-700"></div>

            <div class="p-5 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border {{ $chip('blue') }}">
                        <i class="fa-solid fa-shield-halved text-[12px]"></i>
                        Admin Overview
                    </div>

                    <h1 class="mt-2 text-lg font-semibold text-gray-900 dark:text-white">Dashboard</h1>
                    <p class="text-sm text-gray-500 dark:text-white/60 mt-1">
                        Real-time view of enrollment, activity, completion, and performance signals.
                    </p>
                </div>

                <form method="GET" class="flex flex-wrap items-center gap-2">
                    <div class="text-xs text-gray-500 dark:text-white/60 mr-1">Active range</div>

                    <select name="range"
                        class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white">
                        <option value="7" {{ $rangeDays == 7 ? 'selected' : '' }}>Last 7 days</option>
                        <option value="30" {{ $rangeDays == 30 ? 'selected' : '' }}>Last 30 days</option>
                        <option value="90" {{ $rangeDays == 90 ? 'selected' : '' }}>Last 90 days</option>
                    </select>

                    <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm shadow-sm">
                        Apply
                    </button>
                </form>
            </div>
        </div>

        {{-- KPI grid --}}
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
                    <div class="kpiMiniLabel">Divisions</div>
                    <div class="kpiMiniValue">{{ $totalDivisions }}</div>
                </div>
            </div>

            {{-- Active --}}
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
                        <div class="kpiSub">Active in selected range</div>
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

            {{-- At risk --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('red') }}">
                        <i class="fa-solid fa-triangle-exclamation text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('red') }}">At Risk</span>
                </div>

                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="kpiValue">{{ $atRiskCount }}</div>
                        <div class="kpiSub">Low progress / low grades / inactive</div>
                    </div>
                    <div class="donut border border-gray-200 dark:border-white/10"
                        style="{{ $donutStyle($riskPct, '#ef4444') }}">
                        <span class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $riskPct }}%</span>
                    </div>
                </div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Monitor</div>
                    <div class="kpiMiniValue">{{ $atRiskCount }}</div>
                </div>
            </div>

            {{-- Courses --}}
            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('purple') }}">
                        <i class="fa-solid fa-book-open text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('purple') }}">Courses</span>
                </div>

                <div class="kpiValue">{{ $totalCourses }}</div>
                <div class="kpiSub">Total courses in system</div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Teachers</div>
                    <div class="kpiMiniValue">{{ $totalTeachers }}</div>
                </div>
            </div>

            {{-- Avg completion --}}
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
                        <div class="kpiSub">Average completion across courses</div>
                    </div>

                    <div class="donut border border-gray-200 dark:border-white/10"
                        style="{{ $donutStyle((int) $avgOverallCompletion, '#10b981') }}">
                        <span
                            class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $avgOverallCompletion }}%</span>
                    </div>
                </div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Division avg</div>
                    <div class="kpiMiniValue">{{ $divAvg }}%</div>
                </div>
            </div>

            {{-- Spotlight --}}
            @php
                $topDivision = collect($divisionRows)->sortByDesc('overall_percent')->first();
                $topDivName = $topDivision['division']?->name ?? '—';
                $topDivPct = (int) ($topDivision['overall_percent'] ?? 0);
            @endphp

            <div class="kpiCard">
                <div class="kpiTop">
                    <div class="kpiIcon {{ $chip('amber') }}">
                        <i class="fa-solid fa-award text-[14px]"></i>
                    </div>
                    <span class="kpiPill {{ $chip('amber') }}">Top Division</span>
                </div>

                <div class="kpiValue truncate">{{ $topDivName }}</div>
                <div class="kpiSub">Highest overall completion</div>

                <div class="kpiFoot">
                    <div class="kpiMiniLabel">Completion</div>
                    <div class="kpiMiniValue">{{ $topDivPct }}%</div>
                </div>
            </div>
        </div>

        {{-- Charts row --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

            {{-- Active trend --}}
            <div class="panelCard xl:col-span-2">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Active Students Trend</div>
                        <div class="panelSub">Last 14 days (distinct active students per day)</div>
                    </div>
                    <span class="panelPill {{ $chip('emerald') }}">
                        <i class="fa-solid fa-wave-square text-[12px]"></i> Activity
                    </span>
                </div>
                <div class="panelBody h-[280px]">
                    <canvas id="activeTrendChart"></canvas>
                </div>
            </div>

            {{-- Division progress --}}
            <div class="panelCard">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Division Progress</div>
                        <div class="panelSub">Completion % and students by division</div>
                    </div>
                    <span class="panelPill {{ $chip('blue') }}">
                        <i class="fa-solid fa-sitemap text-[12px]"></i> Divisions
                    </span>
                </div>
                <div class="panelBody h-[280px]">
                    <canvas id="divisionProgressChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Course grades --}}
        <div class="panelCard">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Average Grades by Course</div>
                    <div class="panelSub">Average quiz score % (from attempts where total > 0)</div>
                </div>
                <span class="panelPill {{ $chip('purple') }}">
                    <i class="fa-solid fa-graduation-cap text-[12px]"></i> Grades
                </span>
            </div>
            <div class="panelBody h-[320px]">
                <canvas id="courseGradesChart"></canvas>
            </div>
        </div>

        {{-- Tables --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Course analytics --}}
            <div class="panelCard overflow-hidden">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Course Analytics</div>
                        <div class="panelSub">Completion + average grade per course</div>
                    </div>
                    <span class="panelPill {{ $chip('emerald') }}">
                        <i class="fa-solid fa-chart-simple text-[12px]"></i> Insights
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Course</th>
                                <th class="px-5 py-3 text-left font-semibold w-[92px]">Students</th>
                                <th class="px-5 py-3 text-left font-semibold w-[140px]">Completion</th>
                                <th class="px-5 py-3 text-left font-semibold w-[92px]">Avg</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @forelse($courseInsights as $row)
                                @php
                                    $c = $row['course'];
                                    $p = (int) $row['overall_percent'];
                                    $g = (int) $row['avg_grade'];

                                    $gradeTone = $g >= 70 ? 'emerald' : ($g >= 40 ? 'amber' : 'red');
                                @endphp

                                <tr class="course-insight-row {{ $loop->iteration > 5 ? 'hidden' : '' }} hover:bg-gray-50/60 dark:hover:bg-white/5">
                                    <td class="px-5 py-4">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl border grid place-items-center {{ $chip('blue') }}">
                                                <i class="fa-solid fa-book text-[13px]"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $c->title }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-white/60 truncate">
                                                    {{ optional($c->subject)->name ?? '—' }} •
                                                    {{ optional(optional($c->subject)->division)->name ?? '—' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 font-semibold text-gray-900 dark:text-white">
                                        {{ (int) $row['students'] }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-2 w-full max-w-[140px] rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden border border-gray-200 dark:border-white/10">
                                                <div class="h-2 rounded-full"
                                                    style="width: {{ $p }}%; background:#10b981;">
                                                </div>
                                            </div>
                                            <div class="text-xs font-semibold text-gray-700 dark:text-white/80 w-[42px]">
                                                {{ $p }}%
                                            </div>
                                        </div>
                                        <div class="mt-2 text-[11px] text-gray-500 dark:text-white/60">
                                            L: {{ (int) $row['lessons_total'] }} •
                                            Q: {{ (int) $row['quizzes_total'] }} •
                                            A: {{ (int) $row['assignments_total'] }}
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $chip($gradeTone) }}">
                                            {{ $g }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                        No courses found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Course Analytics Pagination --}}
                @if($courseInsightsTotal > 5)
                    <div class="tablePager">
                        <div id="courseInsightsInfo" class="tablePagerInfo"></div>

                        <div class="tablePagerActions">
                            <button type="button" id="courseInsightsPrev" class="tablePagerBtn">
                                <i class="fa-solid fa-chevron-left text-[11px]"></i>
                                Prev
                            </button>

                            <div id="courseInsightsPages" class="tablePagerPages"></div>

                            <button type="button" id="courseInsightsNext" class="tablePagerBtn">
                                Next
                                <i class="fa-solid fa-chevron-right text-[11px]"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            {{-- At risk --}}
            <div class="panelCard overflow-hidden">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Students at Risk</div>
                        <div class="panelSub">Low progress, low grades, or inactive</div>
                    </div>
                    <span class="panelPill {{ $chip('red') }}">
                        <i class="fa-solid fa-shield-heart text-[12px]"></i> Attention
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold">Student</th>
                                <th class="px-5 py-3 text-left font-semibold">Division</th>
                                <th class="px-5 py-3 text-left font-semibold w-[130px]">Progress</th>
                                <th class="px-5 py-3 text-left font-semibold w-[92px]">Avg</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @forelse($atRiskRows as $r)
                                @php
                                    $st = $r['student'];
                                    $p = (int) $r['progress'];
                                    $g = (int) $r['avg_quiz'];
                                    $inactive = (bool) $r['inactive'];

                                    $avgTone = $g >= 70 ? 'emerald' : ($g >= 40 ? 'amber' : 'red');
                                @endphp

                                <tr class="at-risk-row {{ $loop->iteration > 5 ? 'hidden' : '' }} hover:bg-gray-50/60 dark:hover:bg-white/5">
                                    <td class="px-5 py-4">
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl border grid place-items-center {{ $chip($inactive ? 'red' : 'amber') }}">
                                                <i
                                                    class="fa-solid {{ $inactive ? 'fa-user-slash' : 'fa-user' }} text-[13px]"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $st->name }}
                                                </div>
                                                <div class="text-xs text-gray-500 dark:text-white/60 truncate">
                                                    {{ $st->username ?? $st->email ?? '—' }}
                                                    @if($inactive)
                                                        • <span class="font-semibold text-red-600 dark:text-red-300">inactive</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4 text-gray-700 dark:text-white/80">
                                        {{ $r['division'] }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="h-2 w-full max-w-[130px] rounded-full bg-gray-100 dark:bg-white/10 overflow-hidden border border-gray-200 dark:border-white/10">
                                                <div class="h-2 rounded-full"
                                                    style="width: {{ $p }}%; background:#ef4444;">
                                                </div>
                                            </div>
                                            <div class="text-xs font-semibold text-gray-700 dark:text-white/80 w-[42px]">
                                                {{ $p }}%
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <span
                                            class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold border {{ $g ? $chip($avgTone) : $chip('gray') }}">
                                            {{ $g ? $g . '%' : '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                        No at-risk students detected yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- At Risk Pagination --}}
                @if($atRiskTotal > 5)
                    <div class="tablePager">
                        <div id="atRiskInfo" class="tablePagerInfo"></div>

                        <div class="tablePagerActions">
                            <button type="button" id="atRiskPrev" class="tablePagerBtn">
                                <i class="fa-solid fa-chevron-left text-[11px]"></i>
                                Prev
                            </button>

                            <div id="atRiskPages" class="tablePagerPages"></div>

                            <button type="button" id="atRiskNext" class="tablePagerBtn">
                                Next
                                <i class="fa-solid fa-chevron-right text-[11px]"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @once
        <style>
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

            .kpiTop {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 10px;
                gap: 10px;
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

            .kpiPill {
                position: relative;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                font-size: 11px;
                padding: 6px 10px;
                border-radius: 999px;
                border: 1px solid;
                font-weight: 700;
                letter-spacing: .2px;
                white-space: nowrap;
            }

            .kpiValue {
                position: relative;
                font-size: 18px;
                font-weight: 700;
                color: #111827;
                line-height: 1.2;
            }

            .dark .kpiValue {
                color: rgba(255, 255, 255, .95);
            }

            .kpiSub {
                position: relative;
                margin-top: 6px;
                font-size: 12px;
                color: rgba(107, 114, 128, 1);
            }

            .dark .kpiSub {
                color: rgba(255, 255, 255, .55);
            }

            .kpiFoot {
                position: relative;
                margin-top: 12px;
                padding-top: 12px;
                border-top: 1px dashed rgba(229, 231, 235, 1);
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 12px;
            }

            .dark .kpiFoot {
                border-top-color: rgba(255, 255, 255, .10);
            }

            .kpiMiniLabel {
                color: rgba(107, 114, 128, 1);
            }

            .dark .kpiMiniLabel {
                color: rgba(255, 255, 255, .55);
            }

            .kpiMiniValue {
                font-weight: 700;
                color: #111827;
            }

            .dark .kpiMiniValue {
                color: rgba(255, 255, 255, .90);
            }

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
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                background: linear-gradient(to bottom, rgba(249, 250, 251, 1), rgba(255, 255, 255, 1));
            }

            .dark .panelHead {
                border-bottom-color: rgba(255, 255, 255, .10);
                background: linear-gradient(to bottom, rgba(255, 255, 255, .05), rgba(15, 23, 42, 1));
            }

            .panelTitle {
                font-size: 14px;
                font-weight: 700;
                color: #111827;
            }

            .dark .panelTitle {
                color: rgba(255, 255, 255, .95);
            }

            .panelSub {
                margin-top: 4px;
                font-size: 12px;
                color: rgba(107, 114, 128, 1);
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
                border: 1px solid;
                font-weight: 700;
                white-space: nowrap;
            }

            .panelBody {
                padding: 14px 16px;
            }

            .donut {
                width: 62px;
                height: 62px;
                border-radius: 9999px;
                display: grid;
                place-items: center;
                position: relative;
            }

            .donut::before {
                content: "";
                position: absolute;
                inset: 9px;
                border-radius: 9999px;
                background: rgba(255, 255, 255, .95);
                box-shadow: inset 0 0 0 1px rgba(229, 231, 235, 1);
            }

            .dark .donut::before {
                background: rgba(15, 23, 42, .95);
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10);
            }

            .donut>span {
                position: relative;
            }

            table th,
            table td {
                white-space: nowrap;
            }

            .tablePager {
                border-top: 1px solid rgba(229, 231, 235, 1);
                padding: 12px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
                flex-wrap: wrap;
                background: rgba(249, 250, 251, .75);
            }

            .dark .tablePager {
                border-top-color: rgba(255, 255, 255, .10);
                background: rgba(255, 255, 255, .03);
            }

            .tablePagerInfo {
                font-size: 12px;
                color: rgba(107, 114, 128, 1);
            }

            .dark .tablePagerInfo {
                color: rgba(255, 255, 255, .55);
            }

            .tablePagerActions {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }

            .tablePagerPages {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }

            .tablePagerBtn,
            .tablePagerPageBtn {
                min-height: 32px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                border-radius: 10px;
                border: 1px solid rgba(229, 231, 235, 1);
                background: rgba(255, 255, 255, 1);
                color: rgba(55, 65, 81, 1);
                font-size: 12px;
                font-weight: 700;
                padding: 7px 10px;
                transition: .2s ease;
            }

            .tablePagerPageBtn {
                width: 32px;
                padding: 0;
            }

            .dark .tablePagerBtn,
            .dark .tablePagerPageBtn {
                border-color: rgba(255, 255, 255, .10);
                background: rgba(15, 23, 42, 1);
                color: rgba(255, 255, 255, .75);
            }

            .tablePagerBtn:hover,
            .tablePagerPageBtn:hover {
                background: rgba(243, 244, 246, 1);
            }

            .dark .tablePagerBtn:hover,
            .dark .tablePagerPageBtn:hover {
                background: rgba(255, 255, 255, .06);
            }

            .tablePagerBtn:disabled {
                opacity: .45;
                cursor: not-allowed;
            }

            .tablePagerPageBtn.is-active {
                border-color: rgba(37, 99, 235, .35);
                background: rgba(37, 99, 235, .10);
                color: rgba(29, 78, 216, 1);
            }

            .dark .tablePagerPageBtn.is-active {
                border-color: rgba(96, 165, 250, .35);
                background: rgba(59, 130, 246, .16);
                color: rgba(191, 219, 254, 1);
            }
        </style>
    @endonce
@endsection

@section('scripts')
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        const activeLabels = @json($chartActiveLabels);
        const activeValues = @json($chartActiveValues);

        const divLabels = @json($chartDivLabels);
        const divProgress = @json($chartDivProgress);
        const divStudents = @json($chartDivStudents);

        const courseLabels = @json($chartCourseLabels);
        const courseGrades = @json($chartCourseGrades);

        new Chart(document.getElementById('activeTrendChart'), {
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
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(document.getElementById('divisionProgressChart'), {
            type: 'bar',
            data: {
                labels: divLabels,
                datasets: [
                    { label: 'Progress %', data: divProgress, borderWidth: 1 },
                    { label: 'Students', data: divStudents, borderWidth: 1 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(document.getElementById('courseGradesChart'), {
            type: 'bar',
            data: {
                labels: courseLabels,
                datasets: [{
                    label: 'Avg grade %',
                    data: courseGrades,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100 }
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            setupTablePagination({
                rowSelector: '.course-insight-row',
                perPage: 5,
                infoId: 'courseInsightsInfo',
                prevId: 'courseInsightsPrev',
                nextId: 'courseInsightsNext',
                pagesId: 'courseInsightsPages',
                itemLabel: 'courses'
            });

            setupTablePagination({
                rowSelector: '.at-risk-row',
                perPage: 5,
                infoId: 'atRiskInfo',
                prevId: 'atRiskPrev',
                nextId: 'atRiskNext',
                pagesId: 'atRiskPages',
                itemLabel: 'students'
            });
        });

        function setupTablePagination(config) {
            const rows = Array.from(document.querySelectorAll(config.rowSelector));
            const info = document.getElementById(config.infoId);
            const prev = document.getElementById(config.prevId);
            const next = document.getElementById(config.nextId);
            const pages = document.getElementById(config.pagesId);

            if (!rows.length || !info || !prev || !next || !pages) {
                return;
            }

            const perPage = config.perPage || 5;
            const totalRows = rows.length;
            const totalPages = Math.ceil(totalRows / perPage);
            let currentPage = 1;

            function renderPageButtons() {
                pages.innerHTML = '';

                for (let i = 1; i <= totalPages; i++) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = i;
                    btn.className = 'tablePagerPageBtn';

                    if (i === currentPage) {
                        btn.classList.add('is-active');
                    }

                    btn.addEventListener('click', function () {
                        currentPage = i;
                        render();
                    });

                    pages.appendChild(btn);
                }
            }

            function render() {
                const start = (currentPage - 1) * perPage;
                const end = start + perPage;

                rows.forEach((row, index) => {
                    const shouldShow = index >= start && index < end;
                    row.classList.toggle('hidden', !shouldShow);
                });

                const showingFrom = totalRows === 0 ? 0 : start + 1;
                const showingTo = Math.min(end, totalRows);

                info.textContent = `Showing ${showingFrom}-${showingTo} of ${totalRows} ${config.itemLabel}`;

                prev.disabled = currentPage === 1;
                next.disabled = currentPage === totalPages;

                renderPageButtons();
            }

            prev.addEventListener('click', function () {
                if (currentPage > 1) {
                    currentPage--;
                    render();
                }
            });

            next.addEventListener('click', function () {
                if (currentPage < totalPages) {
                    currentPage++;
                    render();
                }
            });

            render();
        }
    </script>
@endsection