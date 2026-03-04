@extends('layouts.staff')
@section('title', 'Student Progress')
@section('page_title', 'Student Progress')

@section('content')
    @php
        use Illuminate\Support\Carbon;

        $subjectName = optional($course->subject)->name ?? '—';
        $divisionName = optional(optional($course->subject)->division)->name ?? '—';

        $lessonTotal = $course->lessons->count();
        $quizTotal = $course->quizzes->count();
        $assTotal = $course->assignments->count();

        $lessonDone = $course->lessons->filter(fn($l) => !empty(($lessonProgress[$l->id] ?? null)?->completed_at))->count();
        $lessonViewed = $course->lessons->filter(fn($l) => !empty(($lessonProgress[$l->id] ?? null)?->viewed_at))->count();

        $quizSubmitted = $course->quizzes->filter(function ($q) use ($quizAttempts) {
            $rows = $quizAttempts[$q->id] ?? collect();
            return $rows->whereNotNull('submitted_at')->count() > 0;
        })->count();

        $assSubmitted = $course->assignments->filter(function ($a) use ($assignmentSubs) {
            $rows = $assignmentSubs[$a->id] ?? collect();
            return $rows->count() > 0;
        })->count();

        $totalItems = $lessonTotal + $quizTotal + $assTotal;
        $doneItems = $lessonDone + $quizSubmitted + $assSubmitted;

        $overallPercent = $totalItems > 0 ? (int) round(($doneItems / $totalItems) * 100) : 0;

        $lessonPct = $lessonTotal > 0 ? (int) round(($lessonDone / $lessonTotal) * 100) : 0;
        $quizPct = $quizTotal > 0 ? (int) round(($quizSubmitted / $quizTotal) * 100) : 0;
        $assPct = $assTotal > 0 ? (int) round(($assSubmitted / $assTotal) * 100) : 0;

        // Last activity (best-effort from what we have)
        $lastLesson = collect($lessonProgress)->pluck('updated_at')->filter()->max();
        $lastQuiz = $quizAttempts->flatten()->pluck('submitted_at')->filter()->max();
        $lastAss = $assignmentSubs->flatten()->pluck('created_at')->filter()->max();
        $lastActiveRaw = collect([$lastLesson, $lastQuiz, $lastAss])->filter()->max();
        $lastActive = $lastActiveRaw ? Carbon::parse($lastActiveRaw) : null;

        $donutStyle = function (int $percent, string $color) {
            $p = max(0, min(100, $percent));
            return "background: conic-gradient({$color} {$p}%, rgba(148,163,184,.22) 0%);";
        };

        $chip = function (string $tone) {
            return match ($tone) {
                'blue' => 'bg-blue-50 text-blue-700 border-blue-100 dark:bg-blue-500/10 dark:text-blue-200 dark:border-blue-500/20',
                'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-200 dark:border-emerald-500/20',
                'amber' => 'bg-amber-50 text-amber-800 border-amber-100 dark:bg-amber-500/10 dark:text-amber-200 dark:border-amber-500/20',
                'purple' => 'bg-purple-50 text-purple-700 border-purple-100 dark:bg-purple-500/10 dark:text-purple-200 dark:border-purple-500/20',
                'gray' => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-white/5 dark:text-white/70 dark:border-white/10',
                default => 'bg-gray-50 text-gray-700 border-gray-200 dark:bg-white/5 dark:text-white/70 dark:border-white/10',
            };
        };
    @endphp

    <div class="space-y-6" x-data="{ open: { lessons:true, quizzes:true, assignments:true } }">

        {{-- HERO --}}
        <div
            class="rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm overflow-hidden">
            <div class="h-20 bg-gradient-to-r from-indigo-700 via-blue-700 to-sky-700 relative">
                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_45%)]">
                </div>
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]">
                </div>
            </div>

            <div class="p-5 md:p-6 mt-8">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex items-start gap-4 min-w-0">
                        <div
                            class="w-14 h-14 rounded-2xl bg-white/95 dark:bg-slate-950 border border-white/50 dark:border-white/10 shadow grid place-items-center">
                            <span class="text-lg font-extrabold text-indigo-700 dark:text-indigo-300">
                                {{ strtoupper(substr($student->name ?? 'S', 0, 1)) }}
                            </span>
                        </div>

                        <div class="min-w-0">
                            <div class="text-xs text-gray-500 dark:text-white/60">Student progress</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $student->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                                {{ $student->username ?? $student->email ?? '-' }}
                                @if($lastActive)
                                    • Last active: {{ $lastActive->diffForHumans() }}
                                @endif
                            </div>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs {{ $chip('gray') }}">
                                    <i class="fa-solid fa-book-open text-[11px]"></i>
                                    {{ $course->title }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs {{ $chip('blue') }}">
                                    <i class="fa-solid fa-tag text-[11px]"></i>
                                    {{ $subjectName }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs {{ $chip('emerald') }}">
                                    <i class="fa-solid fa-sitemap text-[11px]"></i>
                                    {{ $divisionName }}
                                </span>
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full border text-xs {{ $chip('amber') }}">
                                    <i class="fa-solid fa-eye text-[11px]"></i>
                                    View-only staff
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('staff.courses.show', $course->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                            <i class="fa-solid fa-arrow-left"></i> Back to Course
                        </a>

                        <a href="{{ route('staff.courses.activity', $course->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm shadow-sm">
                            <i class="fa-solid fa-chart-simple"></i> Course Activity
                        </a>
                    </div>
                </div>

                {{-- KPI ROW --}}
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="kpiCard">
                        <div class="kpiTop">
                            <div class="kpiIcon {{ $chip('blue') }}"><i class="fa-solid fa-book"></i></div>
                            <div class="kpiLabel">Lessons</div>
                        </div>
                        <div class="kpiValue">{{ $lessonDone }}/{{ $lessonTotal }}</div>
                        <div class="kpiSub">Viewed: {{ $lessonViewed }}</div>
                    </div>

                    <div class="kpiCard">
                        <div class="kpiTop">
                            <div class="kpiIcon {{ $chip('purple') }}"><i class="fa-solid fa-bolt"></i></div>
                            <div class="kpiLabel">Quizzes</div>
                        </div>
                        <div class="kpiValue">{{ $quizSubmitted }}/{{ $quizTotal }}</div>
                        <div class="kpiSub">Submitted quizzes</div>
                    </div>

                    <div class="kpiCard">
                        <div class="kpiTop">
                            <div class="kpiIcon {{ $chip('amber') }}"><i class="fa-solid fa-file-pen"></i></div>
                            <div class="kpiLabel">Assignments</div>
                        </div>
                        <div class="kpiValue">{{ $assSubmitted }}/{{ $assTotal }}</div>
                        <div class="kpiSub">Submitted assignments</div>
                    </div>

                    <div class="kpiCard">
                        <div class="kpiTop">
                            <div class="kpiIcon {{ $chip('emerald') }}"><i class="fa-solid fa-chart-line"></i></div>
                            <div class="kpiLabel">Overall</div>
                        </div>

                        <div class="flex items-center justify-between gap-3 mt-2">
                            <div class="min-w-0">
                                <div class="kpiValue">{{ $overallPercent }}%</div>
                                <div class="kpiSub">{{ $doneItems }} / {{ $totalItems }} items</div>
                            </div>
                            <div class="donut border border-gray-200 dark:border-white/10"
                                style="{{ $donutStyle($overallPercent, '#10b981') }}">
                                <span
                                    class="text-[11px] font-extrabold text-gray-900 dark:text-white">{{ $overallPercent }}%</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- GRAPH --}}
                <div class="mt-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">Completion Graph</div>
                            <div class="text-xs text-gray-500 dark:text-white/60 mt-1">Lessons vs Quizzes vs Assignments
                            </div>
                        </div>
                        <span class="text-xs px-3 py-1 rounded-full border {{ $chip('gray') }}">
                            {{ $overallPercent }}% overall
                        </span>
                    </div>

                    <div class="h-[220px] mt-3">
                        <canvas id="progressBar"></canvas>
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION: LESSONS --}}
        <div class="sectionCard">
            <button type="button" class="sectionHead" @click="open.lessons = !open.lessons">
                <div>
                    <div class="sectionTitle">
                        <span class="iconDot {{ $chip('blue') }}"><i class="fa-solid fa-book"></i></span>
                        Lessons
                    </div>
                    <div class="sectionSub">Done / Viewed / Not started</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="pill {{ $chip('gray') }}">{{ $lessonDone }}/{{ $lessonTotal }}</span>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition"
                        :class="open.lessons ? 'rotate-180' : ''"></i>
                </div>
            </button>

            <div x-show="open.lessons" x-collapse class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse($course->lessons as $lesson)
                    @php
                        $p = $lessonProgress[$lesson->id] ?? null;
                        $done = !empty($p?->completed_at);
                        $viewed = !empty($p?->viewed_at);
                    @endphp

                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-xs text-gray-500 dark:text-white/60">Position {{ $lesson->position ?? '-' }}</div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $lesson->title }}</div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            @if($done)
                                <span class="pill {{ $chip('emerald') }}">Done</span>
                            @elseif($viewed)
                                <span class="pill {{ $chip('blue') }}">Viewed</span>
                            @else
                                <span class="pill {{ $chip('gray') }}">Not started</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-white/60 text-sm">No lessons in this course.</div>
                @endforelse
            </div>
        </div>

        {{-- SECTION: QUIZZES --}}
        <div class="sectionCard">
            <button type="button" class="sectionHead" @click="open.quizzes = !open.quizzes">
                <div>
                    <div class="sectionTitle">
                        <span class="iconDot {{ $chip('purple') }}"><i class="fa-solid fa-bolt"></i></span>
                        Quizzes
                    </div>
                    <div class="sectionSub">Attempts, submitted, reviewed, graded</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="pill {{ $chip('gray') }}">{{ $quizSubmitted }}/{{ $quizTotal }}</span>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition"
                        :class="open.quizzes ? 'rotate-180' : ''"></i>
                </div>
            </button>

            <div x-show="open.quizzes" x-collapse class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse($course->quizzes as $quiz)
                    @php
                        $rows = $quizAttempts[$quiz->id] ?? collect();

                        $submittedCount = $rows->whereNotNull('submitted_at')->count();
                        $max = (int) ($quiz->max_attempts ?? 0);
                        $attemptText = $max > 0 ? "{$submittedCount}/{$max}" : "{$submittedCount}/∞";

                        $latest = $rows->first();
                        $latestStatus = $latest?->status ?? null;

                        // Better status
                        $badgeText = 'Not started';
                        $badgeTone = 'gray';

                        if ($rows->where('status', 'in_progress')->count() > 0) {
                            $badgeText = 'In progress';
                            $badgeTone = 'blue';
                        }
                        if ($submittedCount > 0) {
                            $badgeText = 'Submitted';
                            $badgeTone = 'purple';
                        }
                        if ($rows->where('status', 'reviewed')->count() > 0) {
                            $badgeText = 'Reviewed';
                            $badgeTone = 'amber';
                        }
                        if ($rows->where('status', 'graded')->count() > 0) {
                            $badgeText = 'Graded';
                            $badgeTone = 'emerald';
                        }

                        $lastSubmitted = $rows->whereNotNull('submitted_at')->first();
                        $scoreText = null;
                        if ($lastSubmitted && (int) ($lastSubmitted->total ?? 0) > 0) {
                            $pct = (int) round(((int) $lastSubmitted->score / (int) $lastSubmitted->total) * 100);
                            $scoreText = "{$pct}%";
                        }

                        $openUrl = route('staff.courses.activity', $course->id) . '?tab=quizzes&quiz_id=' . $quiz->id;
                    @endphp

                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $quiz->title }}</div>
                            <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                Attempts: <span class="font-semibold text-gray-900 dark:text-white">{{ $attemptText }}</span>
                                @if($scoreText)
                                    • Latest score: <span
                                        class="font-semibold text-gray-900 dark:text-white">{{ $scoreText }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="pill {{ $chip($badgeTone) }}">{{ $badgeText }}</span>

                            <a href="{{ $openUrl }}"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5">
                                View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-white/60 text-sm">No quizzes in this course.</div>
                @endforelse
            </div>
        </div>

        {{-- SECTION: ASSIGNMENTS --}}
        <div class="sectionCard">
            <button type="button" class="sectionHead" @click="open.assignments = !open.assignments">
                <div>
                    <div class="sectionTitle">
                        <span class="iconDot {{ $chip('amber') }}"><i class="fa-solid fa-file-pen"></i></span>
                        Assignments
                    </div>
                    <div class="sectionSub">Submissions and grading status</div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="pill {{ $chip('gray') }}">{{ $assSubmitted }}/{{ $assTotal }}</span>
                    <i class="fa-solid fa-chevron-down text-gray-400 transition"
                        :class="open.assignments ? 'rotate-180' : ''"></i>
                </div>
            </button>

            <div x-show="open.assignments" x-collapse class="divide-y divide-gray-100 dark:divide-white/10">
                @forelse($course->assignments as $assignment)
                    @php
                        $rows = $assignmentSubs[$assignment->id] ?? collect();
                        $used = $rows->count();

                        $last = $rows->first();
                        $status = $last?->status ?? 'not_submitted';

                        $badgeText = 'Not submitted';
                        $badgeTone = 'gray';

                        if ($status === 'submitted') {
                            $badgeText = 'Submitted';
                            $badgeTone = 'blue';
                        }
                        if ($status === 'graded') {
                            $badgeText = 'Graded';
                            $badgeTone = 'emerald';
                        }

                        $marksText = null;
                        if ($last && !is_null($last->marks_awarded)) {
                            $marksText = (string) $last->marks_awarded;
                            if (!empty($last->assignment?->total_marks)) {
                                $marksText .= '/' . (int) $last->assignment->total_marks;
                            }
                        }

                        $openUrl = route('staff.courses.activity', $course->id) . '?tab=assignments&assignment_id=' . $assignment->id;
                    @endphp

                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $assignment->title }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                Submissions: <span class="font-semibold text-gray-900 dark:text-white">{{ $used }}</span>
                                @if($marksText)
                                    • Marks: <span class="font-semibold text-gray-900 dark:text-white">{{ $marksText }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <span class="pill {{ $chip($badgeTone) }}">{{ $badgeText }}</span>

                            <a href="{{ $openUrl }}"
                                class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5">
                                View
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500 dark:text-white/60 text-sm">No assignments in this course.</div>
                @endforelse
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        (function () {
            const ctx = document.getElementById('progressBar');
            if (!ctx) return;

            const labels = ['Lessons', 'Quizzes', 'Assignments'];
            const values = [@json($lessonPct), @json($quizPct), @json($assPct)];

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Completion %',
                        data: values,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, max: 100 },
                        x: { grid: { display: false } }
                    }
                }
            });
        })();
    </script>

    <style>
        /* Premium components */
        .kpiCard {
            border-radius: 16px;
            border: 1px solid rgba(229, 231, 235, 1);
            background: #fff;
            padding: 16px;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
            overflow: hidden
        }

        .dark .kpiCard {
            border-color: rgba(255, 255, 255, .10);
            background: rgb(15 23 42);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .25)
        }

        .kpiTop {
            display: flex;
            align-items: center;
            gap: 10px
        }

        .kpiIcon {
            width: 40px;
            height: 40px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            border: 1px solid
        }

        .kpiLabel {
            font-size: 12px;
            color: rgba(107, 114, 128, 1);
            font-weight: 700
        }

        .dark .kpiLabel {
            color: rgba(255, 255, 255, .60)
        }

        .kpiValue {
            font-size: 18px;
            font-weight: 900;
            color: #111827;
            margin-top: 10px;
            line-height: 1
        }

        .dark .kpiValue {
            color: rgba(255, 255, 255, .92)
        }

        .kpiSub {
            font-size: 12px;
            color: rgba(107, 114, 128, 1);
            margin-top: 8px
        }

        .dark .kpiSub {
            color: rgba(255, 255, 255, .55)
        }

        .donut {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            position: relative
        }

        .donut::before {
            content: "";
            position: absolute;
            inset: 9px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .95);
            box-shadow: inset 0 0 0 1px rgba(229, 231, 235, 1)
        }

        .dark .donut::before {
            background: rgba(15, 23, 42, .95);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .10)
        }

        .donut>span {
            position: relative
        }

        .sectionCard {
            border-radius: 18px;
            border: 1px solid rgba(229, 231, 235, 1);
            background: #fff;
            box-shadow: 0 10px 24px rgba(0, 0, 0, .05);
            overflow: hidden
        }

        .dark .sectionCard {
            border-color: rgba(255, 255, 255, .10);
            background: rgb(15 23 42);
            box-shadow: 0 10px 24px rgba(0, 0, 0, .25)
        }

        .sectionHead {
            width: 100%;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            padding: 16px;
            border-bottom: 1px solid rgba(229, 231, 235, 1);
            background: linear-gradient(to bottom, rgba(249, 250, 251, 1), rgba(255, 255, 255, 1))
        }

        .dark .sectionHead {
            border-bottom-color: rgba(255, 255, 255, .10);
            background: linear-gradient(to bottom, rgba(255, 255, 255, .05), rgb(15 23 42))
        }

        .sectionTitle {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 900;
            color: #111827
        }

        .dark .sectionTitle {
            color: rgba(255, 255, 255, .95)
        }

        .sectionSub {
            font-size: 12px;
            color: rgba(107, 114, 128, 1);
            margin-top: 4px
        }

        .dark .sectionSub {
            color: rgba(255, 255, 255, .55)
        }

        .iconDot {
            width: 34px;
            height: 34px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            border: 1px solid
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid;
            font-weight: 800;
            white-space: nowrap
        }
    </style>
@endsection