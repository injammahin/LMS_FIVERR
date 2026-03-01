{{-- resources/views/admin/question_options/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Options')

@section('content')
@php
    use Illuminate\Support\Str;

    // Clean question preview (Quill HTML -> plain text)
    $questionPlain = Str::of($question->question ?? '')
        ->replace('&nbsp;', ' ')
        ->stripTags()
        ->squish()
        ->toString();

    $questionPreview = Str::limit($questionPlain, 120);

    $typeMeta = [
        'text' => ['Text Answer', 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/80'],
        'file' => ['File Upload', 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200'],
        'single_choice' => ['Single Choice', 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200'],
        'multiple_choice' => ['Multiple Choice', 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-200'],
        'true_false' => ['True/False', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200'],
    ];

    [$typeLabel, $typeClass] = $typeMeta[$question->type] ?? [Str::headline($question->type), 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80'];

    $needsOptions = method_exists($question, 'needsOptions')
        ? $question->needsOptions()
        : in_array($question->type, ['single_choice','multiple_choice','true_false'], true);

    // quick counts
    $correctCount = $options->where('is_correct', true)->count();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-2">
            <div class="flex items-center gap-2">
                <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Options</h1>
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $typeClass }}">
                    {{ $typeLabel }}
                </span>
            </div>

            <div class="text-sm text-gray-500 dark:text-white/60 space-y-1">
                <div>
                    Quiz: <span class="font-medium text-gray-800 dark:text-white/80">{{ $question->quiz?->title }}</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="shrink-0">Question:</span>
                    <span class="font-medium text-gray-800 dark:text-white/80">
                        {{ $questionPreview ?: '—' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80">
                        Options: {{ $options->count() }}
                    </span>

                    @if($needsOptions)
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                            {{ $correctCount ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200' }}">
                            Correct: {{ $correctCount }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.quizzes.questions.index', $question->quiz_id) }}"
               class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back
            </a>

            <a href="{{ route('admin.questions.options.create', $question->id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                + Add Option
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(!$needsOptions)
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-800 px-4 py-3 text-sm">
            This question type does not use options.
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold w-[90px]">Pos</th>
                        <th class="px-6 py-4 text-left font-semibold">Option</th>
                        <th class="px-6 py-4 text-left font-semibold w-[130px]">Correct</th>
                        <th class="px-6 py-4 text-right font-semibold w-[200px]">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($options as $opt)
                        @php
                            $optText = Str::of($opt->option_text ?? '')
                                ->replace('&nbsp;', ' ')
                                ->stripTags()
                                ->squish()
                                ->toString();
                            $optPreview = Str::limit($optText, 150);
                        @endphp

                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                            {{-- Pos --}}
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white font-bold">
                                    {{ $opt->position }}
                                </div>
                            </td>

                            {{-- Option --}}
                            <td class="px-6 py-5">
                                <div class="flex items-start gap-4">
                                    @if($opt->option_image)
                                        <img src="{{ asset('storage/' . $opt->option_image) }}"
                                             alt="Option image"
                                             class="w-14 h-14 rounded-xl border border-gray-200 dark:border-white/10 object-cover">
                                    @else
                                        <div class="w-14 h-14 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 text-xs">
                                            —
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $optPreview ?: '—' }}
                                        </div>
                                        @if($opt->option_image)
                                            <div class="mt-2">
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/80">
                                                    Has image
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Correct --}}
                            <td class="px-6 py-5">
                                @if($opt->is_correct)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                                        ✅ Yes
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                        No
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.questions.options.edit', [$question->id, $opt->id]) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.questions.options.destroy', [$question->id, $opt->id]) }}"
                                          onsubmit="return confirm('Delete this option?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="text-gray-400">
                                    <div class="text-base font-semibold">No options found</div>
                                    <div class="text-sm mt-1">Click “Add Option” to create the first option.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Helpful notes --}}
    @if(in_array($question->type, ['single_choice','true_false'], true))
        <div class="rounded-lg border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3 text-sm">
            For <b>single_choice / true_false</b> you should mark <b>exactly one</b> option as correct.
        </div>
    @elseif($question->type === 'multiple_choice')
        <div class="rounded-lg border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3 text-sm">
            For <b>multiple_choice</b> you can mark <b>multiple</b> options as correct.
        </div>
    @endif

</div>
@endsection