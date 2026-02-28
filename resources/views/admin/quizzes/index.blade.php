@extends('layouts.admin')

@section('title', 'Quizzes')

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Quizzes</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Course: <span class="font-medium">{{ $course->title }}</span>
                </p>
            </div>

            <a href="{{ route('admin.courses.quizzes.create', $course->id) }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Quiz
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
                            <th class="px-6 py-3 text-left font-medium">Title</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-left font-medium">Time</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($quizzes as $quiz)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $quiz->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">
                                        Pass: {{ $quiz->pass_mark ?? '-' }}% • Attempts: {{ $quiz->max_attempts ?? '∞' }}
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    @if($quiz->status === 'published')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">
                                            Published
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-600 dark:text-white/70">
                                    {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.quizzes.questions.index', $quiz->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Builder
                                        </a>

                                        <a href="{{ route('admin.courses.quizzes.edit', [$course->id, $quiz->id]) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Edit
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.courses.quizzes.destroy', [$course->id, $quiz->id]) }}"
                                            onsubmit="return confirm('Delete this quiz?');">
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
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">No quizzes found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $quizzes->links() }}
            </div>
        </div>
    </div>
@endsection