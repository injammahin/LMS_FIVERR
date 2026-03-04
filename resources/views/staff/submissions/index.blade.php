@extends('layouts.staff')

@section('title', 'Submissions')
@section('page_title', 'Submissions Inbox')

@section('content')
    @php
        $badge = function (string $type) {
            return match ($type) {
                'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
                'graded' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'quiz' => 'bg-purple-50 text-purple-800 border-purple-200',
                'ass' => 'bg-blue-50 text-blue-800 border-blue-200',
                default => 'bg-gray-50 text-gray-700 border-gray-200',
            };
        };

        $statusChip = function (?string $s) use ($badge) {
            $s = (string) $s;
            if ($s === 'graded')
                return ['Graded', $badge('graded')];
            if ($s === 'reviewed')
                return ['Reviewed', 'bg-sky-50 text-sky-800 border-sky-200'];
            if ($s === 'submitted')
                return ['Submitted', $badge('pending')];
            if ($s === 'in_progress')
                return ['In progress', 'bg-blue-50 text-blue-800 border-blue-200'];
            return ['Pending', $badge('pending')];
        };
    @endphp

    @once
        <style>
            .panel {
                border: 1px solid #e5e7eb;
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .06);
                overflow: hidden
            }

            .panelHead {
                padding: 16px 18px;
                border-bottom: 1px solid #e5e7eb;
                background: linear-gradient(to bottom, #fafafa, #fff)
            }

            .panelBody {
                padding: 16px 18px
            }

            .chip {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 7px 12px;
                border-radius: 999px;
                border: 1px solid;
                font-size: 12px;
                font-weight: 800;
                white-space: nowrap
            }

            .btn {
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 10px 14px;
                border-radius: 14px;
                border: 1px solid #e5e7eb;
                background: #fff;
                font-weight: 800;
                font-size: 13px
            }

            .btn:hover {
                background: #f9fafb
            }

            .btnPrimary {
                background: #111827;
                color: #fff;
                border-color: #111827
            }

            .btnPrimary:hover {
                background: #0b1220
            }
        </style>
    @endonce

    <div class="space-y-6">

        {{-- HERO --}}
        <div class="panel">
            <div class="h-14 bg-gradient-to-r from-indigo-700 via-purple-700 to-fuchsia-700 relative">
                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_50%)]">
                </div>
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]">
                </div>
            </div>

            <div class="panelBody mt-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <div class="chip bg-white/90 border-gray-200 text-gray-800">
                            <i class="fa-solid fa-inbox text-[12px]"></i>
                            Submissions Inbox
                        </div>
                        <h1 class="mt-2 text-xl font-extrabold text-gray-900">Assignments & Quiz Attempts</h1>
                        <p class="text-sm text-gray-500 mt-1">View submissions from your assigned courses (staff view-only).
                        </p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="chip {{ $badge('ass') }}">
                                <i class="fa-solid fa-file-pen text-[12px]"></i>
                                Assignments: {{ (int) $countAssignments }}
                                <span class="text-gray-400">•</span>
                                Pending {{ (int) $countAssignmentsPending }}
                                <span class="text-gray-400">•</span>
                                Graded {{ (int) $countAssignmentsGraded }}
                            </span>

                            <span class="chip {{ $badge('quiz') }}">
                                <i class="fa-solid fa-bolt text-[12px]"></i>
                                Quizzes: {{ (int) $countQuizzes }}
                                <span class="text-gray-400">•</span>
                                Pending {{ (int) $countQuizzesPending }}
                                <span class="text-gray-400">•</span>
                                Graded {{ (int) $countQuizzesGraded }}
                            </span>
                        </div>
                    </div>

                    {{-- FILTER BAR --}}
                    <form method="GET" class="w-full lg:w-auto">
                        <div class="rounded-2xl border border-gray-200 bg-white/85 backdrop-blur p-3 shadow-sm">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                                <input name="search" value="{{ $search ?? '' }}"
                                    placeholder="Search student/course/title..."
                                    class="rounded-xl border border-gray-200 px-4 py-2 text-sm bg-white">

                                <select name="type" class="rounded-xl border border-gray-200 px-4 py-2 text-sm bg-white">
                                    <option value="all" {{ ($filter ?? 'all') === 'all' ? 'selected' : '' }}>All</option>
                                    <option value="assignment" {{ ($filter ?? 'all') === 'assignment' ? 'selected' : '' }}>
                                        Assignments</option>
                                    <option value="quiz" {{ ($filter ?? 'all') === 'quiz' ? 'selected' : '' }}>Quizzes
                                    </option>
                                </select>

                                <select name="status" class="rounded-xl border border-gray-200 px-4 py-2 text-sm bg-white">
                                    <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Status
                                    </option>
                                    <option value="pending" {{ ($status ?? 'all') === 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="graded" {{ ($status ?? 'all') === 'graded' ? 'selected' : '' }}>Graded
                                    </option>
                                </select>

                                <button class="btnPrimary rounded-xl px-4 py-2 text-sm font-extrabold">
                                    <i class="fa-solid fa-filter text-[12px]"></i> Apply
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                {{-- Client-side quick filter --}}
                <div class="mt-4">
                    <div class="relative">
                        <input id="subSearch" type="text" placeholder="Quick filter on this page..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-2 pl-10 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-3 top-3"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ASSIGNMENTS --}}
        @if(($filter ?? 'all') === 'all' || ($filter ?? 'all') === 'assignment')
            <div class="panel">
                <div class="panelHead flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-extrabold text-gray-900">Assignment Submissions</div>
                        <div class="text-xs text-gray-500 mt-1">Submitted + graded submissions</div>
                    </div>
                    <span class="chip {{ $badge('ass') }}">
                        <i class="fa-solid fa-file-pen text-[12px]"></i>
                        {{ (int) $countAssignments }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100" id="assignmentList">
                    @forelse($assignmentSubs as $sub)
                        @php
                            $courseTitle = $sub->assignment?->course?->title ?? '—';
                            $divisionName = optional(optional($sub->assignment?->course?->subject)->division)->name ?? '—';
                            $studentName = $sub->user?->name ?? 'Student';
                            $title = $sub->assignment?->title ?? 'Assignment';

                            [$label, $cls] = $statusChip($sub->status ?? null);

                            // ✅ STAFF route (FIX)
                            $viewUrl = route('staff.assignments.submissions.show', [$sub->assignment_id, $sub->id]);
                        @endphp

                        <a href="{{ $viewUrl }}"
                            class="subRow p-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-2xl border grid place-items-center {{ $cls }}">
                                    <i
                                        class="fa-solid {{ ($sub->status ?? '') === 'graded' ? 'fa-circle-check' : 'fa-hourglass-half' }}"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-gray-900 truncate subTitle">{{ $title }}</div>
                                    <div class="text-xs text-gray-500 mt-1 truncate subMeta">
                                        {{ $studentName }} • {{ $courseTitle }} • {{ $divisionName }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Updated: {{ optional($sub->updated_at ?? $sub->created_at)->diffForHumans() }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="chip {{ $cls }}">{{ $label }}</span>
                                <i class="fa-solid fa-chevron-right text-gray-300"></i>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center text-gray-500 text-sm">No assignment submissions found.</div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $assignmentSubs?->links() }}
                </div>
            </div>
        @endif

        {{-- QUIZZES --}}
        @if(($filter ?? 'all') === 'all' || ($filter ?? 'all') === 'quiz')
            <div class="panel">
                <div class="panelHead flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-extrabold text-gray-900">Quiz Attempts</div>
                        <div class="text-xs text-gray-500 mt-1">Submitted/reviewed/graded attempts</div>
                    </div>

                    <span class="chip {{ $badge('quiz') }}">
                        <i class="fa-solid fa-bolt text-[12px]"></i>
                        {{ (int) $countQuizzes }}
                    </span>
                </div>

                <div class="divide-y divide-gray-100" id="quizList">
                    @forelse($quizAttempts as $att)
                        @php
                            $courseTitle = $att->quiz?->course?->title ?? '—';
                            $divisionName = optional(optional($att->quiz?->course?->subject)->division)->name ?? '—';
                            $studentName = $att->user?->name ?? 'Student';
                            $title = $att->quiz?->title ?? 'Quiz';

                            [$label, $cls] = $statusChip($att->status ?? null);

                            $max = (int) ($att->quiz?->max_attempts ?? 0);
                            $used = (int) ($att->attempt_used ?? 0);
                            $attemptText = $max > 0 ? "{$used}/{$max}" : ($used > 0 ? "{$used}/∞" : "—");

                            // ✅ STAFF route (FIX)
                            $viewUrl = route('staff.quiz.attempts.show', $att->id);
                        @endphp

                        <a href="{{ $viewUrl }}"
                            class="subRow p-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-11 h-11 rounded-2xl border border-purple-200 bg-purple-50 text-purple-800 grid place-items-center">
                                    <i class="fa-solid fa-bolt"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="text-sm font-extrabold text-gray-900 truncate subTitle">{{ $title }}</div>
                                    <div class="text-xs text-gray-500 mt-1 truncate subMeta">
                                        {{ $studentName }} • {{ $courseTitle }} • {{ $divisionName }}
                                    </div>
                                    <div class="text-xs text-gray-400 mt-1">
                                        Submitted: {{ optional($att->submitted_at ?? $att->created_at)->diffForHumans() }}
                                        • Attempts: <span class="font-semibold text-gray-600">{{ $attemptText }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="chip {{ $cls }}">{{ $label }}</span>
                                <i class="fa-solid fa-chevron-right text-gray-300"></i>
                            </div>
                        </a>
                    @empty
                        <div class="p-10 text-center text-gray-500 text-sm">No quiz attempts found.</div>
                    @endforelse
                </div>

                <div class="p-4 border-t border-gray-200">
                    {{ $quizAttempts?->links() }}
                </div>
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const input = document.getElementById('subSearch');
            const rows = document.querySelectorAll('.subRow');

            function norm(s) { return (s || '').toLowerCase(); }

            input?.addEventListener('input', function () {
                const q = norm(this.value);
                rows.forEach(r => {
                    const title = norm(r.querySelector('.subTitle')?.innerText);
                    const meta = norm(r.querySelector('.subMeta')?.innerText);
                    r.style.display = (title.includes(q) || meta.includes(q)) ? '' : 'none';
                });
            });
        })();
    </script>
@endsection