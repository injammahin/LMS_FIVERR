@extends('layouts.student')
@section('title', 'Quiz Result')

@section('content')
    @php
        use Illuminate\Support\Str;

        // ✅ totals (fix 6/0)
        $totalMarks = (int) $questions->sum(fn($q) => (int) ($q->marks ?? 0));

        // awarded marks (best source)
        $earnedMarksFromAnswers = (int) $answers->sum(fn($a) => (int) ($a->awarded_marks ?? 0));

        // fallback display score
        $earnedMarks = (int) ($attempt->score ?? 0);
        if ($earnedMarks <= 0 && $earnedMarksFromAnswers > 0) {
            $earnedMarks = $earnedMarksFromAnswers;
        }

        // objective totals
        $objectiveTypes = ['true_false', 'single_choice', 'multiple_choice'];
        $objectiveTotalCalc = (int) $questions->whereIn('type', $objectiveTypes)->sum(fn($q) => (int) $q->marks);

        $objectiveScoreCalc = (int) $questions->whereIn('type', $objectiveTypes)->sum(function ($q) use ($answers) {
            $a = $answers->get($q->id);
            return (int) ($a?->awarded_marks ?? 0);
        });

        // pending review count (manual questions answered)
        $pendingReviewCalc = (int) $questions->whereIn('type', ['text', 'file'])->filter(function ($q) use ($answers, $attempt) {
            $a = $answers->get($q->id);
            $payload = $a?->answer_json ?? [];
            $answered = !empty($payload) || !empty($a?->file_path);

            if (!$answered)
                return false;

            // pending until attempt is reviewed/graded
            return !in_array($attempt->status, ['reviewed', 'graded'], true);
        })->count();

        // If controller already sends these, keep them; otherwise use calc
        $objectiveTotal = $objectiveTotalCalc;
        $objectiveScore = $objectiveScoreCalc;
        $pendingReview = $pendingReviewCalc;
    @endphp

    @once
        <style>
            /* Render Quill/HTML perfectly on result page */
            .quizQuestionBody img {
                max-width: 100% !important;
                height: auto !important;
                display: block;
                border-radius: 16px;
                border: 1px solid #e5e7eb;
                margin-top: .75rem;
                margin-bottom: .75rem;
            }

            .quizQuestionBody p {
                margin: .5rem 0;
            }

            .quizQuestionBody h1,
            .quizQuestionBody h2,
            .quizQuestionBody h3 {
                margin: .25rem 0 .5rem;
            }

            .quizQuestionBody ul,
            .quizQuestionBody ol {
                padding-left: 1.25rem;
                margin: .5rem 0;
            }

            .quizQuestionBody blockquote {
                border-left: 4px solid #e5e7eb;
                padding-left: 12px;
                color: #374151;
                margin: .75rem 0;
            }
        </style>
    @endonce

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            {{-- Header --}}
            <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Result</p>
                        <h1 class="text-2xl font-extrabold text-gray-900">{{ $quiz->title }}</h1>
                        <p class="text-sm text-gray-600 mt-1">
                            Course: <span class="font-semibold">{{ $course->title }}</span>
                            • Status: <span class="font-semibold capitalize">{{ $attempt->status }}</span>
                        </p>
                    </div>

                    <div class="text-right">
                        <p class="text-sm text-gray-500">Score</p>
                        <p class="text-3xl font-extrabold text-gray-900">
                            {{ $earnedMarks }}
                            <span class="text-gray-400 text-xl">/ {{ $totalMarks }}</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Objective: {{ (int) $objectiveScore }} / {{ (int) $objectiveTotal }}
                            @if($pendingReview > 0)
                                • Pending review: {{ $pendingReview }}
                            @endif
                        </p>
                    </div>
                </div>

                @if($pendingReview > 0)
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm">
                        <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                        Teacher review pending for text/file answers. Score may update after review.
                    </div>
                @else
                    {{-- ✅ Show this only when teacher has graded/reviewed --}}
                    @if(in_array($attempt->status, ['graded', 'reviewed']))
                        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-900 text-sm">
                            <i class="fa-solid fa-circle-check mr-2"></i>
                            Teacher has reviewed your answers.
                        </div>
                    @endif
                @endif
            </div>

            {{-- Questions --}}
            <div class="space-y-4">
                @foreach($questions as $i => $q)
                    @php
                        $ans = $answers->get($q->id);
                        $payload = $ans?->answer_json ?? [];

                        $isManual = in_array($q->type, ['text', 'file'], true);
                        $isObjective = in_array($q->type, $objectiveTypes, true);

                        $answered = $isManual
                            ? (!empty($payload) || !empty($ans?->file_path))
                            : (!empty($payload));

                        $statusLabel = 'Not Answered';
                        $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';

                        if ($isManual && $answered) {
                            if (($attempt->status ?? '') === 'graded') {
                                $statusLabel = 'Teacher reviewed';
                                $statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            } else {
                                $statusLabel = 'Teacher will review';
                                $statusClass = 'bg-amber-50 text-amber-800 border-amber-200';
                            }
                        }

                        if ($isObjective && $answered) {
                            if ($ans?->is_correct) {
                                $statusLabel = 'Correct';
                                $statusClass = 'bg-emerald-50 text-emerald-800 border-emerald-200';
                            } else {
                                $statusLabel = 'Wrong';
                                $statusClass = 'bg-red-50 text-red-800 border-red-200';
                            }
                        }

                        // ✅ Render Quill HTML if it looks like HTML
                        $qText = $q->question ?? '';
                        $looksHtml = Str::contains($qText, ['<p', '<h', '<div', '<span', '<img', '<br', '</']);
                    @endphp

                    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-500">Question {{ $i + 1 }}</p>

                                <div class="quizQuestionBody prose max-w-none text-gray-900 font-semibold mt-1">
                                    @if($looksHtml)
                                        {!! $qText !!}
                                    @else
                                        {!! nl2br(e($qText)) !!}
                                    @endif
                                </div>

                                {{-- optional separate image --}}
                                @if(!empty($q->question_image))
                                    <img src="{{ asset('storage/' . $q->question_image) }}"
                                        class="mt-3 max-h-72 rounded-2xl border border-gray-200 shadow-sm" />
                                @endif

                                <p class="text-xs text-gray-500 mt-2">
                                    Type: <span class="font-semibold">{{ $q->type }}</span>
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                <div class="text-sm font-semibold text-gray-800">{{ (int) $q->marks }} marks</div>
                                <span
                                    class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>

                                @if($answered)
                                    <div class="text-xs text-gray-500 mt-2">
                                        Awarded:
                                        @if(!is_null($ans?->awarded_marks))
                                            <span class="font-semibold">{{ (int) $ans->awarded_marks }}</span>
                                        @else
                                            <span class="font-semibold">Pending</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Your Answer --}}
                        <div class="mt-4 rounded-2xl bg-gray-50 border border-gray-200 p-4">
                            <p class="text-xs text-gray-500 mb-2">Your Answer</p>

                            @if(!$answered)
                                <p class="text-sm text-gray-600">No answer submitted.</p>
                            @else
                                @if($q->type === 'text')
                                    <p class="text-sm text-gray-800 whitespace-pre-line">{{ $payload['text'] ?? '' }}</p>

                                @elseif($q->type === 'file')
                                    @if($ans?->file_path)
                                        <a class="text-blue-600 underline text-sm" target="_blank"
                                            href="{{ asset('storage/' . $ans->file_path) }}">
                                            View uploaded file
                                        </a>
                                    @else
                                        <p class="text-sm text-gray-600">File not uploaded.</p>
                                    @endif

                                @elseif($q->type === 'true_false')
                                    <p class="text-sm font-semibold text-gray-800">
                                        {{ ($payload['value'] ?? false) ? 'True' : 'False' }}
                                    </p>

                                @elseif($q->type === 'single_choice')
                                    @php
                                        $optId = (int) ($payload['option_id'] ?? 0);
                                        $opt = $q->options->firstWhere('id', $optId);
                                        $optLabel = $opt->option_text ?? $opt->text ?? $opt->label ?? '—';
                                    @endphp
                                    <p class="text-sm text-gray-800">{{ $optLabel }}</p>

                                @elseif($q->type === 'multiple_choice')
                                    @php
                                        $ids = array_map('intval', (array) ($payload['option_ids'] ?? []));
                                        $texts = $q->options
                                            ->whereIn('id', $ids)
                                            ->map(fn($o) => $o->option_text ?? $o->text ?? $o->label ?? '')
                                            ->filter()
                                            ->values();
                                    @endphp
                                    @if($texts->count())
                                        <ul class="list-disc ml-5 text-sm text-gray-800 space-y-1">
                                            @foreach($texts as $t)
                                                <li>{{ $t }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-gray-600">—</p>
                                    @endif
                                @endif
                            @endif
                        </div>

                        {{-- Correct Answer --}}
                        @if($isObjective)
                            <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                <p class="text-xs text-emerald-800 font-semibold mb-2">Correct Answer</p>

                                @if($q->type === 'true_false')
                                    @php
                                        $correctOpt = $q->options->firstWhere('is_correct', 1);
                                        $v = strtolower((string) ($correctOpt?->option_text ?? ''));
                                    @endphp
                                    <p class="text-sm text-emerald-900 font-semibold">{{ $v === 'true' ? 'True' : 'False' }}</p>

                                @elseif($q->type === 'single_choice')
                                    @php
                                        $correctOpt = $q->options->firstWhere('is_correct', 1);
                                        $correctLabel = $correctOpt?->option_text ?? $correctOpt?->text ?? $correctOpt?->label ?? '—';
                                    @endphp
                                    <p class="text-sm text-emerald-900 font-semibold">{{ $correctLabel }}</p>

                                @elseif($q->type === 'multiple_choice')
                                    @php
                                        $correct = $q->options->where('is_correct', 1)
                                            ->map(fn($o) => $o->option_text ?? $o->text ?? $o->label ?? '')
                                            ->filter()
                                            ->values();
                                    @endphp
                                    @if($correct->count())
                                        <ul class="list-disc ml-5 text-sm text-emerald-900 space-y-1">
                                            @foreach($correct as $t)
                                                <li>{{ $t }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <p class="text-sm text-emerald-900">—</p>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <a href="{{ route('student.dashboard') }}" class="text-blue-600 underline">Back to dashboard</a>
            </div>

        </div>
    </div>
@endsection