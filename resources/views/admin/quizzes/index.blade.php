@extends('layouts.admin')

@section('title', 'Quizzes')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-lg font-black tracking-tight text-gray-900 dark:text-white">Quizzes</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Course: <span class="font-medium">{{ $course->title }}</span>
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- ✅ Back to Courses --}}
                <a href="{{ route('admin.courses.index') }}"
                   class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                    Back to Courses
                </a>

                {{-- Add Quiz --}}
                <a href="{{ route('admin.courses.quizzes.create', $course->id) }}"
                   class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                    + Add Quiz
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Title</th>
                            <th class="px-6 py-4 text-left font-semibold w-[140px]">Status</th>
                            <th class="px-6 py-4 text-left font-semibold w-[120px]">Time</th>
                            <th class="px-6 py-4 text-right font-semibold w-[260px]">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($quizzes as $quiz)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                                {{-- Title --}}
                                <td class="px-6 py-5">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $quiz->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60 mt-1">
                                        Pass: {{ $quiz->pass_mark ?? '-' }}% • Attempts: {{ $quiz->max_attempts ?? '∞' }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-5">
                                    @if($quiz->status === 'published')
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                                            Published
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                {{-- Time --}}
                                <td class="px-6 py-5 text-gray-700 dark:text-white/80">
                                    {{ $quiz->time_limit_minutes ? $quiz->time_limit_minutes . ' min' : '-' }}
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-5">
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
                                        <div class="text-base font-semibold">No quizzes found</div>
                                        <div class="text-sm mt-1">Click “Add Quiz” to create your first quiz.</div>
                                    </div>
                                </td>
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