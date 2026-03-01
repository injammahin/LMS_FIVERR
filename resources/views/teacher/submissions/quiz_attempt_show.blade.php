@extends('layouts.teacher')
@section('title', 'Review Quiz Attempt')

@section('content')
@php
    $total = (int)($attempt->total_marks ?? $attempt->total ?? 0);
    $score = (int)($attempt->score ?? 0);
    $pct = $total > 0 ? (int)round(($score / $total) * 100) : 0;

    $passMark = (int)($attempt->quiz?->pass_mark ?? 0);
    $passed = $total > 0 ? ($pct >= $passMark) : null;

    $isGraded = (($attempt->status ?? '') === 'graded');

    $pill = function($type){
        return match($type){
            'graded' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'submitted' => 'bg-purple-50 text-purple-800 border-purple-200',
            'manual' => 'bg-amber-50 text-amber-800 border-amber-200',
            'auto' => 'bg-blue-50 text-blue-800 border-blue-200',
            'ok' => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'bad' => 'bg-red-50 text-red-800 border-red-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    };

    // ✅ normalize quill image src="/storage/..." to full URL so it always loads
    $renderQuestion = function($html){
        $html = $html ?? '';
        $base = url('/'); // http://127.0.0.1:8000

        // handle src="/storage/..." and src='/storage/...'
        $html = str_replace('src="/storage/', 'src="'.$base.'/storage/', $html);
        $html = str_replace("src='/storage/", "src='".$base."/storage/", $html);

        return $html;
    };
@endphp

@once
<style>
    /* ✅ Make Quill HTML look nice + stop huge spacing */
    .q-html { line-height: 1.55; color:#111827; }
    .q-html h1,.q-html h2,.q-html h3,.q-html h4 { font-weight: 700; margin: 0 0 8px; }
    .q-html p { margin: 6px 0; }
    .q-html ul { list-style: disc; padding-left: 20px; margin: 6px 0; }
    .q-html ol { list-style: decimal; padding-left: 20px; margin: 6px 0; }
    .q-html img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 10px 0;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
    }
    .q-html iframe { max-width: 100%; border-radius: 16px; border: 1px solid #e5e7eb; }
</style>
@endonce

<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="h-12 bg-gradient-to-r from-indigo-700 to-purple-700"></div>

        <div class="p-5 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500">Quiz Attempt</div>
                <h1 class="text-base font-semibold text-gray-900 mt-1">{{ $attempt->quiz?->title ?? 'Quiz' }}</h1>
                <div class="text-sm text-gray-600 mt-1">
                    Course: <span class="font-semibold">{{ $attempt->quiz?->course?->title ?? '—' }}</span>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('teacher.submissions.index', ['type'=>'quiz']) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>

                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $isGraded ? $pill('graded') : $pill('submitted') }}">
                    {{ $isGraded ? 'Graded' : 'Submitted' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Top cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Student --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Student</div>
                    <div class="text-sm font-semibold text-gray-900 mt-1">{{ $attempt->user?->name ?? '—' }}</div>
                    <div class="text-xs text-gray-500 mt-1">
                        {{ $attempt->user?->username ?? $attempt->user?->email ?? '—' }}
                    </div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 grid place-items-center">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
            </div>

            <div class="mt-4 text-xs text-gray-600 space-y-1">
                <div>Started: <span class="font-semibold text-gray-900">{{ optional($attempt->started_at)->format('d M Y, h:i A') ?? '—' }}</span></div>
                <div>Submitted: <span class="font-semibold text-gray-900">{{ optional($attempt->submitted_at)->format('d M Y, h:i A') ?? '—' }}</span></div>
            </div>
        </div>

        {{-- Score --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Score</div>
                    <div class="text-sm font-semibold text-gray-900 mt-1">
                        {{ $score }} <span class="text-gray-400">/ {{ $total }}</span>
                    </div>

                    <div class="mt-2">
                        @if($isGraded && $total > 0)
                            <span class="inline-flex text-xs px-2 py-1 rounded-full border {{ $passed ? $pill('ok') : $pill('bad') }}">
                                {{ $passed ? 'Passed' : 'Failed' }} ({{ $pct }}%)
                            </span>
                        @else
                            <span class="inline-flex text-xs px-2 py-1 rounded-full border {{ $pill('submitted') }}">
                                Not graded yet
                            </span>
                        @endif
                    </div>
                </div>

                <div class="w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 grid place-items-center">
                    <i class="fa-solid fa-chart-pie"></i>
                </div>
            </div>

            <div class="mt-4">
                <div class="h-2.5 rounded-full bg-gray-100 overflow-hidden border border-gray-200">
                    <div class="h-2.5 rounded-full" style="width: {{ $pct }}%; background:#10b981;"></div>
                </div>
                <div class="mt-2 text-xs text-gray-500">{{ $pct }}% scored • Pass mark {{ $passMark }}%</div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm p-5">
            <div class="text-xs text-gray-500">Actions</div>
            <div class="text-sm font-semibold text-gray-900 mt-1">Grade & Save</div>
            <div class="text-xs text-gray-500 mt-1">Award marks per question and save.</div>

            <div class="mt-3 text-xs text-gray-600">
                After saving, the student will get a notification.
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-900 text-sm font-semibold">
            {{ session('success') }}
        </div>
    @endif

    {{-- Grade form --}}
    <form method="POST" action="{{ route('teacher.quiz.attempts.grade', $attempt->id) }}" class="space-y-5">
        @csrf

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Answers</div>
                    <div class="text-xs text-gray-500 mt-1">Review each answer and award marks</div>
                </div>

                <button class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold">
                    <i class="fa-solid fa-floppy-disk"></i> Save Grade
                </button>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($attempt->answers as $ans)
                    @php
                        $q = $ans->question;
                        $type = $q->type ?? 'text';
                        $marks = (int)($q->marks ?? 0);
                        $isManual = in_array($type, ['text','file'], true);

                        $payload = (array)($ans->answer_json ?? []);
                        $studentText = $payload['text'] ?? null;
                        $studentTF = array_key_exists('value', $payload) ? ($payload['value'] ? 'True' : 'False') : null;

                        $studentOptionId = (int)($payload['option_id'] ?? 0);
                        $studentOptionIds = array_map('intval', (array)($payload['option_ids'] ?? []));

                        // ✅ show label, not raw id
                        $optLabel = null;
                        if ($type === 'single_choice' && $studentOptionId) {
                            $opt = $q->options?->firstWhere('id', $studentOptionId);
                            $optLabel = $opt?->option_text ?? $opt?->label ?? null;
                        }
                        $selectedSingle = $optLabel ?: ($studentOptionId ? "Option #{$studentOptionId}" : '—');

                        $multiLabels = [];
                        if ($type === 'multiple_choice' && count($studentOptionIds)) {
                            $multiLabels = $q->options?->whereIn('id', $studentOptionIds)->map(function($o){
                                return $o->option_text ?? $o->label ?? '—';
                            })->values()->all() ?? [];
                        }

                        $currentAward = old("awards.{$ans->id}", (int)($ans->awarded_marks ?? 0));
                        $qImg = $q->question_image ?? null;
                    @endphp

                    <div class="p-6 space-y-4">

                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-xs text-gray-500 uppercase tracking-wide">
                                    Question • {{ strtoupper(str_replace('_',' ', $type)) }}
                                </div>

                                {{-- ✅ RENDER HTML from Quill (fix your issue) --}}
                                <div class="q-html mt-2">
                                    {!! $renderQuestion($q->question ?? '') !!}
                                </div>

                                {{-- Optional DB image --}}
                                @if($qImg)
                                    <div class="mt-3">
                                        <img src="{{ asset('storage/'.$qImg) }}"
                                             class="max-h-72 rounded-2xl border border-gray-200 shadow-sm"
                                             alt="Question image">
                                    </div>
                                @endif
                            </div>

                            <div class="shrink-0 text-right">
                                <div class="text-xs text-gray-500">Marks</div>
                                <div class="text-sm font-semibold text-gray-900">{{ $marks }}</div>
                                <div class="mt-2">
                                    <span class="text-xs px-2 py-1 rounded-full border {{ $isManual ? $pill('manual') : $pill('auto') }}">
                                        {{ $isManual ? 'Manual' : 'Auto' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Student Answer --}}
                        <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 space-y-2">
                            <div class="text-sm font-semibold text-gray-900">Student Answer</div>

                            @if($type === 'true_false')
                                <div class="text-sm text-gray-800">Answer: <span class="font-semibold">{{ $studentTF ?? '—' }}</span></div>

                            @elseif($type === 'single_choice')
                                <div class="text-sm text-gray-800">
                                    Selected: <span class="font-semibold">{{ $selectedSingle }}</span>
                                </div>

                            @elseif($type === 'multiple_choice')
                                @if(count($multiLabels))
                                    <ul class="list-disc ml-5 text-sm text-gray-800 space-y-1">
                                        @foreach($multiLabels as $t)
                                            <li>{{ $t }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="text-sm text-gray-600">—</div>
                                @endif

                            @elseif($type === 'text')
                                <div class="text-sm text-gray-800 whitespace-pre-line">{{ $studentText ?: '—' }}</div>

                            @elseif($type === 'file')
                                @if(!empty($ans->file_path))
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ asset('storage/'.$ans->file_path) }}" target="_blank"
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700 text-sm">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
                                        <a href="{{ asset('storage/'.$ans->file_path) }}" target="_blank"
                                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-sm">
                                            <i class="fa-solid fa-eye"></i> Preview
                                        </a>
                                    </div>
                                @else
                                    <div class="text-sm text-gray-600">No file uploaded.</div>
                                @endif
                            @else
                                <div class="text-sm text-gray-600">—</div>
                            @endif

                            {{-- Auto correctness --}}
                            @if(!$isManual)
                                <div class="pt-3 border-t border-gray-200 flex flex-wrap items-center gap-2">
                                    <span class="text-xs px-2 py-1 rounded-full border {{ $ans->is_correct ? $pill('ok') : $pill('bad') }}">
                                        {{ $ans->is_correct ? 'Correct' : 'Incorrect' }}
                                    </span>

                                    <span class="text-xs px-2 py-1 rounded-full border bg-white text-gray-800 border-gray-200">
                                        Current: <span class="font-semibold">{{ (int)($ans->awarded_marks ?? 0) }}</span> / {{ $marks }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Award --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <div class="md:col-span-2">
                                <label class="text-sm font-semibold text-gray-900">Award marks (max {{ $marks }})</label>
                                <input type="number"
                                       name="awards[{{ $ans->id }}]"
                                       value="{{ $currentAward }}"
                                       min="0" max="{{ $marks }}"
                                       class="mt-2 w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                @error("awards.$ans->id")
                                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="text-xs text-gray-500">
                                @if($isManual) Manual review required. @else You can override if needed. @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="p-10 text-center text-gray-500 text-sm">No answers found.</div>
                @endforelse
            </div>
        </div>

        <div class="flex justify-end">
            <button class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold">
                <i class="fa-solid fa-floppy-disk"></i> Save Grade
            </button>
        </div>
    </form>

</div>
@endsection