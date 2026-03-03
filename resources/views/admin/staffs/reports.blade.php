@extends('layouts.admin')

@section('title', 'Staff Reports')

@section('content')
    @php
        $rangeDays = $rangeDays ?? 30;
        $status = $status ?? 'all';
        $search = $search ?? '';
        $k = $kpis ?? [];
        $m = $map ?? [];
        $charts = $charts ?? [];
    @endphp

    <div class="space-y-6">

        {{-- HERO --}}
        <div
            class="relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
            <div class="h-16 bg-gradient-to-r from-cyan-700 via-indigo-700 to-fuchsia-700"></div>

            <div class="pointer-events-none absolute -top-24 -left-24 w-80 h-80 rounded-full blur-3xl opacity-25"
                style="background: radial-gradient(circle at center, rgba(255,255,255,.35), transparent 60%);"></div>
            <div class="pointer-events-none absolute -bottom-28 -right-28 w-96 h-96 rounded-full blur-3xl opacity-20"
                style="background: radial-gradient(circle at center, rgba(99,102,241,.30), transparent 60%);"></div>

            <div class="p-5 sm:p-6 lg:p-7">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
                    <div class="min-w-0">
                        <div class="inline-flex items-center gap-2 text-xs px-2.5 py-1 rounded-full border
                                    bg-cyan-50 text-cyan-700 border-cyan-100
                                    dark:bg-cyan-500/10 dark:text-cyan-200 dark:border-cyan-500/20">
                            <i class="fa-solid fa-user-gear text-[12px]"></i>
                            Staff Report Center
                        </div>

                        <h1 class="mt-2 text-xl sm:text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                            Staff Analytics
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-white/60 mt-1 max-w-2xl">
                            Staff course load and the activity happening inside their assigned courses (read-only support
                            role).
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
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
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

                                <input name="search" value="{{ $search }}" placeholder="Search staff..."
                                    class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white placeholder:text-gray-400 dark:placeholder:text-white/40">

                                <div class="flex gap-2">
                                    <select name="per_page"
                                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm text-gray-900 dark:text-white">
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
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">
            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-indigo-50 text-indigo-700 border-indigo-100 dark:bg-indigo-500/10 dark:text-indigo-200 dark:border-indigo-500/20">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Staff</div>
                        <div class="kpiValue">{{ (int) ($k['totalStaff'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">All staff accounts</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Active</div>
                        <div class="kpiValue">{{ (int) ($k['activeStaff'] ?? 0) }}</div>
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
                        <div class="kpiValue">{{ (int) ($k['suspendedStaff'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Blocked accounts</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-sky-50 text-sky-700 border-sky-100 dark:bg-sky-500/10 dark:text-sky-200 dark:border-sky-500/20">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Assigned</div>
                        <div class="kpiValue">{{ (int) ($k['assignedCoursesOnPage'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">Courses (this page)</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-500/10 dark:text-purple-200 dark:border-purple-500/20">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Quiz Attempts</div>
                        <div class="kpiValue">{{ (int) ($k['rangeQuizAttemptsOnPage'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">{{ (int) $rangeDays }} day scope</div>
            </div>

            <div class="kpiCard">
                <div class="kpiTop">
                    <div
                        class="kpiIcon bg-amber-50 text-amber-800 border-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20">
                        <i class="fa-solid fa-file-pen"></i>
                    </div>
                    <div class="kpiMeta">
                        <div class="kpiLabel">Assignments</div>
                        <div class="kpiValue">{{ (int) ($k['rangeAssignmentSubsOnPage'] ?? 0) }}</div>
                    </div>
                </div>
                <div class="kpiHint">{{ (int) $rangeDays }} day scope</div>
            </div>
        </div>

        {{-- Charts --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4">
            <div class="panelCard xl:col-span-2">
                <div class="panelHead">
                    <div>
                        <div class="panelTitle">Top Staff by Course Load</div>
                        <div class="panelSub">How many courses each staff supports</div>
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
                    <div>
                        <div class="panelTitle">Staff Status</div>
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

        <div class="panelCard">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Top Staff by Course Activity</div>
                    <div class="panelSub">Quiz attempts + assignment submissions in their courses (range)</div>
                </div>
                <span
                    class="panelPill bg-amber-50 border border-amber-100 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200">
                    <i class="fa-solid fa-wave-square text-[12px]"></i> Activity
                </span>
            </div>
            <div class="panelBody h-[320px]">
                <canvas id="chartActivity"></canvas>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="panelCard overflow-hidden">
            <div class="panelHead">
                <div>
                    <div class="panelTitle">Staff Detailed Report</div>
                    <div class="panelSub">Course load + divisions + activity in assigned courses</div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">Staff</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Courses</th>
                            <th class="px-6 py-3 text-left font-semibold">Divisions</th>
                            <th class="px-6 py-3 text-left font-semibold">L / Q / A</th>
                            <th class="px-6 py-3 text-left font-semibold">Students (Scope)</th>
                            <th class="px-6 py-3 text-left font-semibold">Range Activity</th>
                            <th class="px-6 py-3 text-left font-semibold">Last Activity</th>
                            <th class="px-6 py-3 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($staffs as $s)
                                        @php
                                            $id = $s->id;

                                            $courses = (int) ($m['courses'][$id] ?? 0);
                                            $divs = $m['divisions'][$id] ?? '—';

                                            $less = (int) ($m['lessons'][$id] ?? 0);
                                            $quiz = (int) ($m['quizzes'][$id] ?? 0);
                                            $asg = (int) ($m['assignments'][$id] ?? 0);

                                            $scopeStudents = (int) ($m['students_scope'][$id] ?? 0);

                                            $qa = (int) ($m['range_quiz_attempts'][$id] ?? 0);
                                            $aa = (int) ($m['range_assignment_subs'][$id] ?? 0);
                                            $activity = $qa + $aa;

                                            $last = $m['last_activity'][$id] ?? null;
                                            $lastTxt = $last ? \Carbon\Carbon::parse($last)->diffForHumans() : '—';
                                        @endphp

                                        <tr class="hover:bg-gray-50/60 dark:hover:bg-white/5">
                                            <td class="px-6 py-4">
                                                <div class="font-semibold text-gray-900 dark:text-white">{{ $s->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                                    {{ $s->email ?? $s->username ?? '—' }}
                                                </div>
                                            </td>

                                            <td class="px-6 py-4">
                                                @if($s->is_active)
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[11px] border bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200">Active</span>
                                                @else
                                                    <span
                                                        class="px-2 py-1 rounded-full text-[11px] border bg-red-50 border-red-200 text-red-800 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-200">Suspended</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $courses }}</td>
                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">{{ $divs }}</td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                                {{ $less }} / {{ $quiz }} / {{ $asg }}
                                            </td>

                                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">{{ $scopeStudents }}</td>

                                            <td class="px-6 py-4">
                                                <div class="text-xs text-gray-500 dark:text-white/60">Quiz: {{ $qa }} • Asg: {{ $aa }}</div>
                                                <div class="font-semibold text-gray-900 dark:text-white mt-1">{{ $activity }}</div>
                                            </td>

                                            <td class="px-6 py-4 text-gray-700 dark:text-white/80">{{ $lastTxt }}</td>

                                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                                <a href="{{ route('admin.staffs.courses.edit', $s->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs">
                                                    Assign
                                                </a>

                                                <a href="{{ route('admin.staffs.edit', $s->id) }}"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-xs">
                                                    Edit
                                                </a>

                                                <form action="{{ route('admin.staffs.toggle-status', $s->id) }}" method="POST"
                                                    class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button
                                                        class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs border
                                                            {{ $s->is_active
                            ? 'bg-amber-50 border-amber-200 text-amber-800 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-200'
                            : 'bg-emerald-50 border-emerald-200 text-emerald-800 dark:bg-emerald-500/10 dark:border-emerald-500/20 dark:text-emerald-200' }}">
                                                        {{ $s->is_active ? 'Suspend' : 'Activate' }}
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-gray-500 dark:text-white/60">
                                    No staff found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $staffs->links() }}
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
                background: radial-gradient(420px circle at 20% 20%, rgba(99, 102, 241, .10), transparent 45%),
                    radial-gradient(420px circle at 80% 80%, rgba(236, 72, 153, .08), transparent 50%);
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

            function destroyCharts() { charts.forEach(c => c?.destroy()); charts = []; }

            function render() {
                destroyCharts();

                const courseLabels = @json($charts['coursesLabels'] ?? []);
                const courseValues = @json($charts['coursesValues'] ?? []);
                const actLabels = @json($charts['activityLabels'] ?? []);
                const actValues = @json($charts['activityValues'] ?? []);
                const active = @json((int) ($charts['statusActive'] ?? 0));
                const suspended = @json((int) ($charts['statusSuspended'] ?? 0));

                const c1 = document.getElementById('chartCourses');
                if (c1) charts.push(new Chart(c1, {
                    type: 'bar',
                    data: { labels: courseLabels, datasets: [{ label: 'Courses', data: courseValues, borderWidth: 1 }] },
                    options: baseOptions({ plugins: { legend: { display: false } } })
                }));

                const c2 = document.getElementById('chartStatus');
                if (c2) charts.push(new Chart(c2, {
                    type: 'doughnut',
                    data: { labels: ['Active', 'Suspended'], datasets: [{ data: [active, suspended], borderWidth: 1 }] },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { color: tickColor() } } } }
                }));

                const c3 = document.getElementById('chartActivity');
                if (c3) charts.push(new Chart(c3, {
                    type: 'bar',
                    data: { labels: actLabels, datasets: [{ label: 'Activity', data: actValues, borderWidth: 1 }] },
                    options: baseOptions({ plugins: { legend: { display: false } } })
                }));
            }

            render();

            // re-render only when theme actually changes (no page reload loop)
            let last = isDark();
            const observer = new MutationObserver(() => {
                const now = isDark();
                if (now !== last) { last = now; render(); }
            });
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        })();
    </script>
@endsection