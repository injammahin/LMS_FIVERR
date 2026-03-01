@extends('layouts.admin')
@section('title', 'Quiz Builder')

@section('content')
@php
    use Illuminate\Support\Str;

    $typeMeta = [
        'text' => ['Text Answer', 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/80'],
        'file' => ['File Upload', 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200'],
        'single_choice' => ['Single Choice', 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200'],
        'multiple_choice' => ['Multiple Choice', 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-200'],
        'true_false' => ['True/False', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200'],
    ];
@endphp

<div class="space-y-6">

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Quiz Builder</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Quiz: <span class="font-medium">{{ $quiz->title }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            {{-- ✅ Back to Quizzes --}}
            <a href="{{ route('admin.courses.quizzes.index', $quiz->course_id) }}"
               class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back to Quizzes
            </a>

            {{-- Add Question --}}
            <a href="{{ route('admin.quizzes.questions.create', $quiz->id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                + Add Question
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold w-[90px]">Pos</th>
                        <th class="px-6 py-4 text-left font-semibold">Question</th>
                        <th class="px-6 py-4 text-left font-semibold w-[170px]">Type</th>
                        <th class="px-6 py-4 text-left font-semibold w-[120px]">Marks</th>
                        <th class="px-6 py-4 text-right font-semibold w-[260px]">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($questions as $q)
                        @php
                            // ✅ Clean Quill HTML -> plain text preview
                            $plain = Str::of($q->question ?? '')
                                ->replace('&nbsp;', ' ')
                                ->stripTags()
                                ->squish()
                                ->toString();

                            $preview = Str::limit($plain, 120);

                            $hasInlineImage = Str::contains($q->question ?? '', '<img');
                            $needsOptions = method_exists($q, 'needsOptions') ? $q->needsOptions() : in_array($q->type, ['single_choice','multiple_choice','true_false']);
                            $optCount = $q->options?->count() ?? 0;

                            [$typeLabel, $typeClass] = $typeMeta[$q->type] ?? [Str::headline($q->type), 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80'];
                        @endphp

                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                            {{-- Pos --}}
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white font-bold">
                                    {{ $q->position }}
                                </div>
                            </td>

                            {{-- Question --}}
                            <td class="px-6 py-5">
                                <div class="flex items-start gap-4">
                                    @if($q->question_image)
                                        <img src="{{ asset('storage/'.$q->question_image) }}"
                                             class="w-12 h-12 rounded-lg border border-gray-200 dark:border-white/10 object-cover"
                                             alt="Question image">
                                    @else
                                        <div class="w-12 h-12 rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 text-xs">
                                            Q
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-900 dark:text-white truncate">
                                            {{ $preview ?: '—' }}
                                        </div>

                                        <div class="mt-2 flex flex-wrap items-center gap-2">
                                            @if($hasInlineImage)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                                                    Inline image
                                                </span>
                                            @endif

                                            @if($q->question_image)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/80">
                                                    Uploaded image
                                                </span>
                                            @endif

                                            @if($needsOptions)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                                    {{ $optCount ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200' }}">
                                                    Options: {{ $optCount }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $typeClass }}">
                                    {{ $typeLabel }}
                                </span>
                            </td>

                            {{-- Marks --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-white/10 dark:text-white">
                                    {{ $q->marks }} marks
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    @if($needsOptions)
                                        <a href="{{ route('admin.questions.options.index', $q->id) }}"
                                           class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Options
                                        </a>
                                    @endif

                                    <a href="{{ route('admin.quizzes.questions.edit', [$quiz->id, $q->id]) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                        Edit
                                    </a>

                                    <form method="POST"
                                          action="{{ route('admin.quizzes.questions.destroy', [$quiz->id, $q->id]) }}"
                                          onsubmit="return confirm('Delete this question?');">
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
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="text-gray-400">
                                    <div class="text-base font-semibold">No questions yet</div>
                                    <div class="text-sm mt-1">Click “Add Question” to create your first one.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-white/10">
            {{ $questions->links() }}
        </div>
    </div>

</div>
@endsection