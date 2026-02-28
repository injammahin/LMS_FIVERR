@extends('layouts.student')
@section('title', 'Quiz Result')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

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
                            {{ $attempt->score }} <span class="text-gray-400 text-xl">/ {{ $attempt->total }}</span>
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Objective: {{ $objectiveScore }} / {{ $objectiveTotal }}
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
                @endif
            </div>

            <div class="space-y-4">
                @foreach($questions as $i => $q)
                    @php
                        $ans = $answers->get($q->id);
                        $payload = $ans?->answer_json ?? [];

                        $isManual = in_array($q->type, ['text', 'file']);
                        $isObjective = in_array($q->type, ['true_false', 'single_choice', 'multiple_choice']);

                        $answered = $isManual
                            ? (!empty($payload) || !empty($ans?->file_path))
                            : (!empty($payload));

                        $statusLabel = 'Not Answered';
                        $statusClass = 'bg-gray-100 text-gray-700 border-gray-200';

                        if ($isManual && $answered) {
                            $statusLabel = 'Teacher will review';
                            $statusClass = 'bg-amber-50 text-amber-800 border-amber-200';
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
                    @endphp

                    <div class="bg-white rounded-3xl border border-gray-200 shadow-sm p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs text-gray-500">Question {{ $i + 1 }}</p>
                                <div class="text-gray-900 font-semibold mt-1">{!! nl2br(e($q->question)) !!}</div>
                                <p class="text-xs text-gray-500 mt-2">Type: <span class="font-semibold">{{ $q->type }}</span>
                                </p>
                            </div>

                            <div class="text-right">
                                <div class="text-sm font-semibold text-gray-800">{{ $q->marks }} marks</div>
                                <span
                                    class="inline-flex mt-2 px-3 py-1 rounded-full border text-xs font-semibold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                @if($isObjective && $answered)
                                    <div class="text-xs text-gray-500 mt-2">
                                        Awarded: <span class="font-semibold">{{ (int) ($ans?->awarded_marks ?? 0) }}</span>
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
                                    <p class="text-sm font-semibold text-gray-800">{{ ($payload['value'] ?? false) ? 'True' : 'False' }}
                                    </p>

                                @elseif($q->type === 'single_choice')
                                    @php
                                        $optId = (int) ($payload['option_id'] ?? 0);
                                        $opt = $q->options->firstWhere('id', $optId);
                                    @endphp
                                    <p class="text-sm text-gray-800">{{ $opt?->label ?? '—' }}</p>

                                @elseif($q->type === 'multiple_choice')
                                    @php
                                        $ids = array_map('intval', (array) ($payload['option_ids'] ?? []));
                                        $texts = $q->options->whereIn('id', $ids)->map(fn($o) => $o->label)->values();
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
                                    @php $correctOpt = $q->options->firstWhere('is_correct', 1); @endphp
                                    <p class="text-sm text-emerald-900 font-semibold">{{ $correctOpt?->label ?? '—' }}</p>

                                @elseif($q->type === 'multiple_choice')
                                    @php
                                        $correct = $q->options->where('is_correct', 1)->map(fn($o) => $o->label)->values();
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