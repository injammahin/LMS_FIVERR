@extends('layouts.admin')

@section('title', 'Assignments')

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Assignments</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Course: <span class="font-medium">{{ $course->title }}</span>
                </p>
            </div>

            <a href="{{ route('admin.courses.assignments.create', $course->id) }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Assignment
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
                            <th class="px-6 py-3 text-left font-medium">Due</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($assignments as $a)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $a->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">
                                        {{ $a->submission_type }} • {{ $a->grading_type }}
                                        @if($a->grading_type === 'points') • {{ $a->total_marks }} marks @endif
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-gray-600 dark:text-white/70">
                                    {{ $a->due_at?->format('d M Y, h:i A') ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($a->status === 'published')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">Published</span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">Draft</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.assignments.submissions.index', $a->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Submissions
                                        </a>

                                        <a href="{{ route('admin.courses.assignments.edit', [$course->id, $a->id]) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Edit
                                        </a>

                                        <form method="POST"
                                            action="{{ route('admin.courses.assignments.destroy', [$course->id, $a->id]) }}"
                                            onsubmit="return confirm('Delete this assignment?');">
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
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">No assignments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
@endsection