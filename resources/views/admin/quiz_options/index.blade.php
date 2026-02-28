@extends('layouts.admin')

@section('title', 'Options')

@section('content')
<div class="space-y-6">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Options</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Quiz: <span class="font-medium">{{ $question->quiz?->title }}</span><br>
                Question: <span class="font-medium">{{ \Illuminate\Support\Str::limit($question->question, 120) }}</span><br>
                Type: <span class="font-medium">{{ $question->type }}</span>
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.quizzes.questions.index', $question->quiz_id) }}"
               class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back to Questions
            </a>

            <a href="{{ route('admin.questions.options.create', $question->id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Option
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if(!$question->needsOptions())
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 text-yellow-800 px-4 py-3 text-sm">
            This question type does not use options.
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">Pos</th>
                        <th class="px-6 py-3 text-left font-medium">Option</th>
                        <th class="px-6 py-3 text-left font-medium">Correct</th>
                        <th class="px-6 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($options as $opt)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="px-6 py-4">
                                {{ $opt->position }}
                            </td>

                            <td class="px-6 py-4">
                                @if($opt->option_image)
                                    <img src="{{ asset('storage/' . $opt->option_image) }}"
                                         class="h-12 w-16 object-cover rounded-md border border-gray-200 dark:border-white/10 mb-2"
                                         alt="Option image">
                                @endif

                                <div class="text-gray-800 dark:text-white">
                                    {{ \Illuminate\Support\Str::limit($opt->option_text ?? '-', 150) }}
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                @if($opt->is_correct)
                                    <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">
                                        Yes
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                        No
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4">
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
                            <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                No options found.
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
            For <b>single_choice/true_false</b> you should mark <b>exactly one</b> option as correct.
        </div>
    @elseif($question->type === 'multiple_choice')
        <div class="rounded-lg border border-blue-200 bg-blue-50 text-blue-800 px-4 py-3 text-sm">
            For <b>multiple_choice</b> you can mark <b>multiple</b> options as correct.
        </div>
    @endif

</div>
@endsection