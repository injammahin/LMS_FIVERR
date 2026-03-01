@extends('layouts.teacher') {{-- if not available yet, change to layouts.app --}}

@section('title', 'Review Quiz Attempt')

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            {{-- Header --}}
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Quiz Attempt</p>
                    <h1 class="text-xl font-semibold text-gray-900">
                        {{ $attempt->quiz->title ?? 'Quiz' }}
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Course: <span class="font-semibold">{{ $attempt->quiz->course->title ?? '—' }}</span>
                    </p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('teacher.submissions.index') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                        <i class="fa-solid fa-arrow-left"></i> Back to Submissions
                    </a>

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border
              {{ ($attempt->status ?? '') === 'graded'
        ? 'bg-green-50 text-green-700 border-green-200'
        : 'bg-purple-50 text-purple-700 border-purple-200' }}">
                        {{ ($attempt->status ?? '') === 'graded' ? 'Graded' : 'Submitted' }}
                    </span>
                </div>
            </div>

            {{-- Top cards --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Student --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Student</div>
                            <div class="text-lg font-bold text-gray-900">{{ $attempt->user->name ?? '—' }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                Username: <span
                                    class="font-semibold text-gray-800">{{ $attempt->user->username ?? '—' }}</span>
                            </div>
                        </div>
                        <div
                            class="w-12 h-12 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 grid place-items-center">
                            <i class="fa-solid fa-user-graduate"></i>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-600 space-y-1">
                        <div>
                            Started:
                            <span class="font-semibold text-gray-900">
                                {{ optional($attempt->started_at)->format('d M Y, h:i A') ?? '—' }}
                            </span>
                        </div>
                        <div>
                            Submitted:
                            <span class="font-semibold text-gray-900">
                                {{ optional($attempt->submitted_at)->format('d M Y, h:i A') ?? '—' }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Result summary --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Score</div>
                            <div class="text-lg font-bold text-gray-900">
                                {{ (int) ($attempt->score ?? 0) }}
                                <span class="text-gray-500">/
                                    {{ (int) ($attempt->total ?? $attempt->total_marks ?? 0) }}</span>
                            </div>
                            @php
                                $total = (int) ($attempt->total ?? $attempt->total_marks ?? 0);
                                $score = (int) ($attempt->score ?? 0);
                                $pct = $total > 0 ? round(($score / $total) * 100) : 0;
                                $passMark = (int) ($attempt->quiz->pass_mark ?? 0);
                                $passed = $total > 0 ? ($pct >= $passMark) : null;
                            @endphp

                            <div class="mt-2">
                                @if(($attempt->status ?? '') === 'graded')
                                                <span
                                                    class="inline-flex text-xs px-2 py-1 rounded-full border
                                      {{ $passed ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                                    {{ $passed ? 'Passed' : 'Failed' }} ({{ $pct }}%)
                                                </span>
                                @else
                                    <span
                                        class="inline-flex text-xs px-2 py-1 rounded-full border bg-gray-50 text-gray-700 border-gray-200">
                                        Not graded yet
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div
                            class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 grid place-items-center">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="h-3 rounded-full bg-gray-100 overflow-hidden border border-gray-200">
                            <div class="h-3 rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                        </div>
                        <div class="mt-2 text-xs text-gray-500">{{ $pct }}% scored • Pass mark {{ $passMark }}%</div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm text-gray-500">Actions</div>
                            <div class="text-lg font-bold text-gray-900">Grade & Save</div>
                            <div class="text-sm text-gray-600 mt-1">
                                Grade manual questions (text/file).
                            </div>
                        </div>
                        <div
                            class="w-12 h-12 rounded-2xl bg-purple-50 border border-purple-100 text-purple-700 grid place-items-center">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                    </div>

                    <div class="mt-4 text-sm text-gray-600">
                        Objective questions may already have marks.
                        Manual marks will be added by you below.
                    </div>
                </div>
            </div>

            {{-- Success --}}
            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-4 text-green-900">
                    <div class="font-semibold">{{ session('success') }}</div>
                </div>
            @endif

            {{-- Grade form --}}
            <form method="POST" action="{{ route('teacher.quiz.attempts.grade', $attempt->id) }}" class="space-y-5">
                @csrf

                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-gray-900">Answers</h2>

                        <button
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
                            <i class="fa-solid fa-floppy-disk"></i> Save Grade
                        </button>
                    </div>

                    <div class="divide-y divide-gray-100">

                        @forelse($attempt->answers as $ans)
                            @php
                                $q = $ans->question;
                                $type = $q->type ?? 'text';
                                $marks = (int) ($q->marks ?? 0);

                                // determine manual types
                                $isManual = in_array($type, ['text', 'file'], true);

                                // display student's payload
                                $payload = (array) ($ans->answer_json ?? []);
                                $studentText = $payload['text'] ?? null;
                                $studentTF = array_key_exists('value', $payload) ? ($payload['value'] ? 'True' : 'False') : null;
                                $studentOptionId = $payload['option_id'] ?? null;
                                $studentOptionIds = (array) ($payload['option_ids'] ?? []);

                                // awarded
                                $currentAward = old("awards.{$ans->id}", $ans->awarded_marks);
                            @endphp

                            <div class="p-6 space-y-4">

                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wide">
                                            Question • {{ strtoupper(str_replace('_', ' ', $type)) }}
                                        </div>
                                        <div class="text-gray-900 font-semibold mt-1">
                                            {!! nl2br(e($q->question ?? '—')) !!}
                                        </div>
                                    </div>

                                    <div class="shrink-0 text-right">
                                        <div class="text-xs text-gray-500">Marks</div>
                                        <div class="text-sm font-bold text-gray-900">{{ $marks }}</div>

                                        <div class="mt-2">
                                            @if($isManual)
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full border bg-amber-50 text-amber-800 border-amber-200">
                                                    Manual
                                                </span>
                                            @else
                                                <span
                                                    class="text-xs px-2 py-1 rounded-full border bg-blue-50 text-blue-700 border-blue-200">
                                                    Auto
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Student answer display --}}
                                <div class="rounded-2xl border border-gray-200 bg-gray-50 p-5 space-y-3">
                                    <div class="text-sm font-semibold text-gray-900">Student Answer</div>

                                    @if($type === 'true_false')
                                        <div class="text-sm text-gray-800">
                                            Answer: <span class="font-semibold">{{ $studentTF ?? '—' }}</span>
                                        </div>

                                    @elseif($type === 'single_choice')
                                        <div class="text-sm text-gray-800">
                                            Selected option id: <span class="font-semibold">{{ $studentOptionId ?? '—' }}</span>
                                            <div class="text-xs text-gray-500 mt-1">Tip: you can show option label by loading
                                                options in controller (optional).</div>
                                        </div>

                                    @elseif($type === 'multiple_choice')
                                        <div class="text-sm text-gray-800">
                                            Selected option ids:
                                            <span
                                                class="font-semibold">{{ count($studentOptionIds) ? implode(', ', $studentOptionIds) : '—' }}</span>
                                        </div>

                                    @elseif($type === 'text')
                                        <div class="text-sm text-gray-800 whitespace-pre-line">
                                            {{ $studentText ?: '—' }}
                                        </div>

                                    @elseif($type === 'file')
                                        @if(!empty($ans->file_path))
                                            <div class="flex flex-wrap items-center gap-2">
                                                <a href="{{ asset('storage/' . $ans->file_path) }}" target="_blank"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                                                    <i class="fa-solid fa-download"></i> Download File
                                                </a>

                                                <a href="{{ asset('storage/' . $ans->file_path) }}" target="_blank"
                                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50">
                                                    <i class="fa-solid fa-eye"></i> Preview
                                                </a>

                                                <span class="text-xs text-gray-500 break-all">{{ $ans->file_path }}</span>
                                            </div>

                                            @php
                                                $f = strtolower($ans->file_path);
                                                $isPdf = str_ends_with($f, '.pdf');
                                                $isImg = str_ends_with($f, '.png') || str_ends_with($f, '.jpg') || str_ends_with($f, '.jpeg') || str_ends_with($f, '.webp');
                                            @endphp

                                            @if($isImg)
                                                <div class="mt-4">
                                                    <img src="{{ asset('storage/' . $ans->file_path) }}"
                                                        class="max-h-96 rounded-2xl border border-gray-200 shadow-sm" />
                                                </div>
                                            @elseif($isPdf)
                                                <div class="mt-4 rounded-2xl overflow-hidden border border-gray-200">
                                                    <iframe src="{{ asset('storage/' . $ans->file_path) }}" class="w-full h-[520px]"></iframe>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-sm text-gray-600">No file uploaded.</div>
                                        @endif
                                    @else
                                        <div class="text-sm text-gray-600">—</div>
                                    @endif

                                    {{-- Auto correctness --}}
                                    @if(!$isManual)
                                                <div class="pt-3 border-t border-gray-200 flex flex-wrap items-center gap-2">
                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full border
                                          {{ $ans->is_correct ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                                        {{ $ans->is_correct ? 'Correct' : 'Incorrect' }}
                                                    </span>

                                                    <span
                                                        class="text-xs px-2 py-1 rounded-full border bg-white text-gray-800 border-gray-200">
                                                        Awarded: <span class="font-semibold">{{ (int) ($ans->awarded_marks ?? 0) }}</span> /
                                                        {{ $marks }}
                                                    </span>
                                                </div>
                                    @endif
                                </div>

                                {{-- Manual grading input (still allow adjusting all answers if you want) --}}
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                    <div class="md:col-span-2">
                                        <label class="text-sm font-semibold text-gray-900">
                                            Award marks (max {{ $marks }})
                                        </label>
                                        <input type="number" name="awards[{{ $ans->id }}]" value="{{ $currentAward }}" min="0"
                                            max="{{ $marks }}"
                                            class="mt-2 w-full rounded-2xl border border-gray-200 px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                            placeholder="0 - {{ $marks }}">
                                        @error("awards.$ans->id")
                                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="text-sm text-gray-500">
                                        @if($isManual)
                                            This question needs manual review.
                                        @else
                                            You can override marks if needed.
                                        @endif
                                    </div>
                                </div>

                            </div>
                        @empty
                            <div class="p-10 text-center text-gray-500">No answers found.</div>
                        @endforelse

                    </div>
                </div>

                {{-- bottom save --}}
                <div class="flex justify-end">
                    <button
                        class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 font-semibold">
                        <i class="fa-solid fa-floppy-disk"></i> Save Grade
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection