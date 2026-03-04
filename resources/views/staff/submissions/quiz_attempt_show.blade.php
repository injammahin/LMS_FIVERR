@extends('layouts.staff')
@section('title', 'Quiz Attempt')
@section('page_title', 'Quiz Attempt')

@section('content')
    @php
        use Illuminate\Support\Carbon;
        use Illuminate\Support\Facades\Route;

        $quiz = $attempt->quiz;
        $course = $quiz?->course;

        $total = (int) ($attempt->total_marks ?? $attempt->total ?? 0);
        $score = (int) ($attempt->score ?? 0);
        $pct = $total > 0 ? (int) round(($score / $total) * 100) : 0;

        $passMark = (int) ($quiz?->pass_mark ?? 0);
        $passed = ($total > 0 && $passMark > 0) ? ($pct >= $passMark) : null;

        $status = (string) ($attempt->status ?? 'submitted');
        $isGraded = $status === 'graded';

        // ✅ Back URLs (staff)
        $backToSubmissions = Route::has('staff.submissions.index')
            ? route('staff.submissions.index', ['type' => 'quiz'])
            : url()->previous();

        $backToCourse = ($course && Route::has('staff.courses.show'))
            ? route('staff.courses.show', $course->id)
            : null;

        // ✅ Activity link (optional)
        $activityUrl = ($course && Route::has('staff.courses.activity'))
            ? route('staff.courses.activity', $course->id) . '?tab=quizzes&quiz_id=' . (int) ($quiz?->id ?? 0)
            : null;

        // ✅ PDF route name compatibility:
        // - if you moved pdf routes inside staff group => staff.quiz.attempts.pdf
        // - if not => quiz.attempts.pdf
        $pdfRouteName =
            Route::has('staff.quiz.attempts.pdf') ? 'staff.quiz.attempts.pdf' :
            (Route::has('quiz.attempts.pdf') ? 'quiz.attempts.pdf' : null);

        $pdfUrl = $pdfRouteName ? route($pdfRouteName, $attempt->id) : null;

        $pill = function ($type) {
            return match ($type) {
                'graded' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'reviewed' => 'bg-amber-50 text-amber-800 border-amber-200',
                'submitted' => 'bg-purple-50 text-purple-800 border-purple-200',
                'progress' => 'bg-blue-50 text-blue-800 border-blue-200',
                'ok' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
                'bad' => 'bg-red-50 text-red-800 border-red-200',
                default => 'bg-gray-50 text-gray-700 border-gray-200',
            };
        };

        $statusLabel = match ($status) {
            'graded' => 'Graded',
            'reviewed' => 'Reviewed',
            'in_progress' => 'In progress',
            default => 'Submitted',
        };

        $statusTone = match ($status) {
            'graded' => 'graded',
            'reviewed' => 'reviewed',
            'in_progress' => 'progress',
            default => 'submitted',
        };

        // ✅ normalize quill image src="/storage/..." to full URL so it always loads
        $renderQuestion = function ($html) {
            $html = $html ?? '';
            $base = url('/');

            $html = str_replace('src="/storage/', 'src="' . $base . '/storage/', $html);
            $html = str_replace("src='/storage/", "src='" . $base . "/storage/", $html);

            return $html;
        };

        // For mini chart (per-question marks) – build arrays
        $chartLabels = [];
        $chartValues = [];

    @endphp

    @once
        <style>
            /* Quill HTML styling */
            .q-html {
                line-height: 1.6;
                color: #111827
            }

            .q-html h1,
            .q-html h2,
            .q-html h3,
            .q-html h4 {
                font-weight: 800;
                margin: 0 0 10px
            }

            .q-html p {
                margin: 8px 0
            }

            .q-html ul {
                list-style: disc;
                padding-left: 22px;
                margin: 8px 0
            }

            .q-html ol {
                list-style: decimal;
                padding-left: 22px;
                margin: 8px 0
            }

            .q-html img {
                max-width: 100%;
                height: auto;
                display: block;
                margin: 12px 0;
                border-radius: 16px;
                border: 1px solid #e5e7eb
            }

            .q-html iframe {
                max-width: 100%;
                border-radius: 16px;
                border: 1px solid #e5e7eb
            }

            /* Premium cards */
            .card {
                border: 1px solid #e5e7eb;
                background: #fff;
                border-radius: 18px;
                box-shadow: 0 12px 28px rgba(0, 0, 0, .06)
            }

            .cardHead {
                padding: 16px 18px;
                border-bottom: 1px solid #e5e7eb;
                background: linear-gradient(to bottom, #fafafa, #fff)
            }

            .cardBody {
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

            .muted {
                color: #6b7280
            }

            .mono {
                font-variant-numeric: tabular-nums
            }

            /* details */
            details.qa {
                border: 1px solid #e5e7eb;
                border-radius: 16px;
                overflow: hidden;
                background: #fff
            }

            details.qa summary {
                cursor: pointer;
                list-style: none
            }

            details.qa summary::-webkit-details-marker {
                display: none
            }

            .qaTop {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 12px;
                padding: 14px 16px;
                background: linear-gradient(to bottom, #fafafa, #fff);
                border-bottom: 1px solid #e5e7eb
            }

            .qaBody {
                padding: 14px 16px
            }

            .badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 6px 10px;
                border-radius: 999px;
                border: 1px solid;
                font-size: 11px;
                font-weight: 900
            }
        </style>
    @endonce

    <div class="space-y-6">

        {{-- HERO --}}
        <div class="card overflow-hidden">
            <div class="h-14 bg-gradient-to-r from-indigo-700 via-purple-700 to-sky-700 relative">
                <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_50%)]">
                </div>
                <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]">
                </div>
            </div>

            <div class="cardBody mt-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                    <div class="min-w-0">
                        <div class="chip {{ $pill($statusTone) }}">
                            <i class="fa-solid fa-bolt text-[12px]"></i>
                            {{ $statusLabel }}
                        </div>

                        <h1 class="mt-3 text-lg sm:text-xl font-extrabold text-gray-900 truncate">
                            {{ $quiz?->title ?? 'Quiz Attempt' }}
                        </h1>

                        <div class="mt-1 text-sm text-gray-600">
                            Course: <span class="font-semibold text-gray-900">{{ $course?->title ?? '—' }}</span>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="chip {{ $pill('submitted') }}">
                                <i class="fa-solid fa-user-graduate text-[12px]"></i>
                                {{ $attempt->user?->name ?? '—' }}
                            </span>

                            <span class="chip {{ $pill('progress') }}">
                                <i class="fa-solid fa-clock text-[12px]"></i>
                                Submitted: {{ optional($attempt->submitted_at)->format('d M Y, h:i A') ?? '—' }}
                            </span>

                            @if($isGraded)
                                <span
                                    class="chip {{ $passed === true ? $pill('ok') : ($passed === false ? $pill('bad') : $pill('graded')) }}">
                                    <i class="fa-solid fa-circle-check text-[12px]"></i>
                                    Score: {{ $pct }}%
                                    @if(!is_null($passed) && $passMark > 0)
                                        • {{ $passed ? 'Passed' : 'Failed' }}
                                    @endif
                                </span>
                            @endif

                            <span class="chip {{ $pill('auto') }}">
                                <i class="fa-solid fa-eye text-[12px]"></i>
                                Staff view-only
                            </span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ $backToSubmissions }}" class="btn">
                            <i class="fa-solid fa-arrow-left"></i> Back to Submissions
                        </a>

                        @if($backToCourse)
                            <a href="{{ $backToCourse }}" class="btn">
                                <i class="fa-solid fa-book-open"></i> Course
                            </a>
                        @endif

                        @if($activityUrl)
                            <a href="{{ $activityUrl }}" class="btn btnPrimary">
                                <i class="fa-solid fa-chart-simple"></i> Activity
                            </a>
                        @endif

                        @if($pdfUrl)
                            <a href="{{ $pdfUrl }}" class="btn">
                                <i class="fa-solid fa-file-pdf"></i> Download PDF
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Score bar --}}
                <div class="mt-5 grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="card !shadow-none !border-gray-200">
                        <div class="cardBody">
                            <div class="text-xs muted">Score</div>
                            <div class="mt-1 text-xl font-extrabold text-gray-900 mono">
                                {{ $score }} <span class="text-gray-400">/ {{ $total }}</span>
                            </div>

                            <div class="mt-3 h-2.5 rounded-full bg-gray-100 overflow-hidden border border-gray-200">
                                <div class="h-2.5 rounded-full" style="width: {{ $pct }}%; background:#10b981;"></div>
                            </div>

                            <div class="mt-2 text-xs muted">
                                {{ $pct }}% scored
                                @if($passMark > 0)
                                    • Pass mark {{ $passMark }}%
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card !shadow-none !border-gray-200">
                        <div class="cardBody">
                            <div class="text-xs muted">Attempt Info</div>
                            <div class="mt-2 text-sm text-gray-800 space-y-1">
                                <div>Started: <span
                                        class="font-semibold">{{ optional($attempt->started_at)->format('d M Y, h:i A') ?? '—' }}</span>
                                </div>
                                <div>Submitted: <span
                                        class="font-semibold">{{ optional($attempt->submitted_at)->format('d M Y, h:i A') ?? '—' }}</span>
                                </div>
                                <div>Status: <span class="font-semibold">{{ $statusLabel }}</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="card !shadow-none !border-gray-200">
                        <div class="cardBody">
                            <div class="text-xs muted">Notes</div>
                            <div class="mt-2 text-sm text-gray-800">
                                Staff can <span class="font-semibold">view</span> attempts and submissions,
                                but cannot <span class="font-semibold">grade</span>.
                            </div>
                            <div class="mt-2 text-xs muted">
                                Use “Activity” to see all quiz attempts for this course.
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Answers --}}
        <div class="card overflow-hidden">
            <div class="cardHead flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-extrabold text-gray-900">Answers</div>
                    <div class="text-xs muted mt-1">View responses, correctness, and marks (read-only).</div>
                </div>

                <span class="badge {{ $pill($statusTone) }}">
                    <i class="fa-solid fa-layer-group text-[12px]"></i>
                    {{ $attempt->answers?->count() ?? 0 }} questions
                </span>
            </div>

            <div class="cardBody space-y-3">
                @forelse($attempt->answers as $i => $ans)
                    @php
                        $q = $ans->question;
                        $type = $q->type ?? 'text';
                        $marks = (int) ($q->marks ?? 0);

                        $isManual = in_array($type, ['text', 'file'], true);

                        $payload = (array) ($ans->answer_json ?? []);
                        $studentText = $payload['text'] ?? null;
                        $studentTF = array_key_exists('value', $payload) ? ($payload['value'] ? 'True' : 'False') : null;

                        $studentOptionId = (int) ($payload['option_id'] ?? 0);
                        $studentOptionIds = array_map('intval', (array) ($payload['option_ids'] ?? []));

                        // Student labels
                        $selectedSingle = '—';
                        if ($type === 'single_choice' && $studentOptionId) {
                            $opt = $q->options?->firstWhere('id', $studentOptionId);
                            $selectedSingle = $opt?->option_text ?? $opt?->label ?? ("Option #{$studentOptionId}");
                        }

                        $multiLabels = [];
                        if ($type === 'multiple_choice' && count($studentOptionIds)) {
                            $multiLabels = $q->options?->whereIn('id', $studentOptionIds)
                                ->map(fn($o) => $o->option_text ?? $o->label ?? '—')
                                ->values()->all() ?? [];
                        }

                        // Correct answer labels (best effort)
                        $correctSingle = null;
                        $correctMulti = [];
                        if (in_array($type, ['single_choice', 'multiple_choice'], true)) {
                            $correctOptions = $q->options?->where('is_correct', true) ?? collect();
                            $correctSingle = $correctOptions->first()?->option_text ?? $correctOptions->first()?->label ?? null;
                            $correctMulti = $correctOptions->map(fn($o) => $o->option_text ?? $o->label ?? '—')->values()->all();
                        }

                        $awarded = (int) ($ans->awarded_marks ?? 0);
                        $isCorrect = (bool) ($ans->is_correct ?? false);

                        // Collect chart data (only if marks exist)
                        $chartLabels[] = 'Q' . ($i + 1);
                        $chartValues[] = $marks > 0 ? (int) round(($awarded / max(1, $marks)) * 100) : 0;

                        $qImg = $q->question_image ?? null;
                    @endphp

                    <details class="qa" open>
                        <summary class="qaTop">
                            <div class="min-w-0">
                                <div class="text-xs muted uppercase tracking-wide">
                                    Question {{ $i + 1 }} • {{ strtoupper(str_replace('_', ' ', $type)) }}
                                </div>
                                <div class="mt-1 text-sm font-extrabold text-gray-900 truncate">
                                    {{ $q->title ?? ($q->question_plain ?? 'Question') ?? 'Question' }}
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-xs muted">Marks</div>
                                <div class="text-sm font-extrabold text-gray-900 mono">{{ $awarded }} <span
                                        class="text-gray-400">/ {{ $marks }}</span></div>

                                <div class="mt-2 flex justify-end gap-2">
                                    <span class="badge {{ $isManual ? $pill('manual') : $pill('auto') }}">
                                        {{ $isManual ? 'Manual' : 'Auto' }}
                                    </span>

                                    @if(!$isManual)
                                        <span class="badge {{ $isCorrect ? $pill('ok') : $pill('bad') }}">
                                            {{ $isCorrect ? 'Correct' : 'Incorrect' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </summary>

                        <div class="qaBody space-y-4">
                            {{-- Question HTML --}}
                            <div class="q-html">
                                {!! $renderQuestion($q->question ?? '') !!}
                            </div>

                            @if($qImg)
                                <div>
                                    <img src="{{ asset('storage/' . $qImg) }}" class="max-h-80 rounded-2xl border border-gray-200"
                                        alt="Question image">
                                </div>
                            @endif

                            {{-- Student Answer --}}
                            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
                                <div class="text-sm font-extrabold text-gray-900">Student Answer</div>
                                <div class="mt-2 text-sm text-gray-800">
                                    @if($type === 'true_false')
                                        Answer: <span class="font-semibold">{{ $studentTF ?? '—' }}</span>

                                    @elseif($type === 'single_choice')
                                        Selected: <span class="font-semibold">{{ $selectedSingle }}</span>
                                        @if($correctSingle)
                                            <div class="mt-2 text-xs muted">Correct: <span
                                                    class="font-semibold text-gray-900">{{ $correctSingle }}</span></div>
                                        @endif

                                    @elseif($type === 'multiple_choice')
                                        @if(count($multiLabels))
                                            <ul class="list-disc ml-5 space-y-1">
                                                @foreach($multiLabels as $t)
                                                    <li>{{ $t }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            —
                                        @endif

                                        @if(count($correctMulti))
                                            <div class="mt-3 text-xs muted">Correct:</div>
                                            <ul class="list-disc ml-5 text-xs text-gray-800 space-y-1">
                                                @foreach($correctMulti as $t)
                                                    <li>{{ $t }}</li>
                                                @endforeach
                                            </ul>
                                        @endif

                                    @elseif($type === 'text')
                                        <div class="whitespace-pre-line">{{ $studentText ?: '—' }}</div>

                                    @elseif($type === 'file')
                                        @if(!empty($ans->file_path))
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ asset('storage/' . $ans->file_path) }}" target="_blank"
                                                    class="btn btnPrimary">
                                                    <i class="fa-solid fa-download"></i> Download
                                                </a>
                                                <a href="{{ asset('storage/' . $ans->file_path) }}" target="_blank" class="btn">
                                                    <i class="fa-solid fa-eye"></i> Preview
                                                </a>
                                            </div>
                                        @else
                                            No file uploaded.
                                        @endif
                                    @else
                                        —
                                    @endif
                                </div>

                                {{-- Manual note --}}
                                @if($isManual)
                                    <div class="mt-3 text-xs muted border-t border-gray-200 pt-3">
                                        Manual question — correctness may be decided by teacher. Staff can only view.
                                    </div>
                                @endif
                            </div>

                        </div>
                    </details>

                @empty
                    <div class="p-10 text-center text-gray-500 text-sm">No answers found.</div>
                @endforelse
            </div>
        </div>

        {{-- Mini chart (only if there are answers) --}}
        @if(count($chartLabels))
            <div class="card">
                <div class="cardHead flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm font-extrabold text-gray-900">Per-Question Marks</div>
                        <div class="text-xs muted mt-1">Awarded % per question (read-only)</div>
                    </div>
                    <span class="badge {{ $pill('submitted') }}">
                        <i class="fa-solid fa-chart-column text-[12px]"></i> Summary
                    </span>
                </div>
                <div class="cardBody">
                    <div class="h-[260px]">
                        <canvas id="qMarksChart"></canvas>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        (function () {
            const el = document.getElementById('qMarksChart');
            if (!el) return;

            const labels = @json($chartLabels);
            const values = @json($chartValues);

            new Chart(el, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Awarded %',
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
@endsection