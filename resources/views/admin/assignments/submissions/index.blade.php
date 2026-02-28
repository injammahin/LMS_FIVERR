@extends('layouts.admin')

@section('title', 'Assignment Submissions')

@section('content')
    <div class="space-y-6">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Submissions</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Assignment: <span class="font-medium">{{ $assignment->title }}</span>
                    <span class="ml-2 text-xs text-gray-400">
                        ({{ $assignment->grading_type }}{{ $assignment->grading_type === 'points' ? ', ' . $assignment->total_marks . ' marks' : '' }})
                    </span>
                </p>
            </div>

            <a href="{{ route('admin.courses.assignments.index', $assignment->course_id) }}"
                class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back
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
                            <th class="px-6 py-3 text-left font-medium">Student</th>
                            <th class="px-6 py-3 text-left font-medium">Submitted</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-left font-medium">Result</th>
                            <th class="px-6 py-3 text-right font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($submissions as $sub)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $sub->user?->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">{{ $sub->user?->email }}</div>
                                </td>

                                <td class="px-6 py-4 text-gray-600 dark:text-white/70">
                                    {{ $sub->submitted_at?->format('d M Y, h:i A') ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($sub->status === 'graded')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">
                                            Graded
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                            Submitted
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    @if($assignment->grading_type === 'points')
                                        {{ $sub->marks_awarded !== null ? $sub->marks_awarded . ' / ' . $assignment->total_marks : '-' }}
                                    @else
                                        @if($sub->is_passed === true)
                                            <span class="text-green-600 dark:text-green-300 font-medium">Passed</span>
                                        @elseif($sub->is_passed === false)
                                            <span class="text-red-600 dark:text-red-300 font-medium">Failed</span>
                                        @else
                                            -
                                        @endif
                                    @endif
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end">
                                        <a href="{{ route('admin.assignments.submissions.show', [$assignment->id, $sub->id]) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                    No submissions yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $submissions->links() }}
            </div>
        </div>

    </div>
@endsection