@extends('layouts.admin')
@section('title', 'Quiz Builder')

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Quiz Builder</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Quiz: <span class="font-medium">{{ $quiz->title }}</span>
                </p>
            </div>

            <a href="{{ route('admin.quizzes.questions.create', $quiz->id) }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Question
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Pos</th>
                            <th class="px-6 py-3 text-left font-medium">Question</th>
                            <th class="px-6 py-3 text-left font-medium">Type</th>
                            <th class="px-6 py-3 text-left font-medium">Marks</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($questions as $q)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4">{{ $q->position }}</td>

                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ \Illuminate\Support\Str::limit($q->question, 90) }}</div>
                                    @if($q->question_image)
                                        <div class="text-xs text-gray-500 dark:text-white/60">Has image</div>
                                    @endif
                                    @if($q->needsOptions())
                                        <div class="text-xs text-gray-500 dark:text-white/60">Options: {{ $q->options->count() }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">{{ $q->type }}</td>
                                <td class="px-6 py-4">{{ $q->marks }}</td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if($q->needsOptions())
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
                                            <button
                                                class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">No questions yet.</td>
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