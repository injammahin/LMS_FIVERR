@extends('layouts.staff')
@section('title', 'Course Activity')
@section('page_title', 'Course Activity')

@section('content')
    <div class="space-y-6">

        <div class="rounded-2xl border border-gray-200 bg-white p-5">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-500">Course</div>
                    <div class="text-lg font-extrabold text-gray-900">{{ $course->title }}</div>
                    <div class="text-xs text-gray-500 mt-1">Staff can view submissions (no grading)</div>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('staff.courses.show', $course->id) }}"
                        class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm font-semibold">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Back
                    </a>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="mt-4 flex gap-2">
                <a href="{{ route('staff.courses.activity', $course->id) }}?tab=quizzes"
                    class="px-4 py-2 rounded-xl text-sm font-semibold border {{ $tab === 'quizzes' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                    Quizzes
                </a>
                <a href="{{ route('staff.courses.activity', $course->id) }}?tab=assignments"
                    class="px-4 py-2 rounded-xl text-sm font-semibold border {{ $tab === 'assignments' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white border-gray-200 hover:bg-gray-50' }}">
                    Assignments
                </a>
            </div>

            {{-- Filters --}}
            <form class="mt-4 flex flex-col sm:flex-row gap-2" method="GET">
                <input type="hidden" name="tab" value="{{ $tab }}" />

                @if($tab === 'quizzes')
                    <select name="quiz_id" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All quizzes</option>
                        @foreach($quizzes as $q)
                            <option value="{{ $q->id }}" {{ (string) $quizId === (string) $q->id ? 'selected' : '' }}>
                                {{ $q->title }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <select name="assignment_id" class="rounded-xl border border-gray-200 px-3 py-2 text-sm">
                        <option value="">All assignments</option>
                        @foreach($assignments as $a)
                            <option value="{{ $a->id }}" {{ (string) $assignmentId === (string) $a->id ? 'selected' : '' }}>
                                {{ $a->title }}
                            </option>
                        @endforeach
                    </select>
                @endif

                <button class="rounded-xl bg-gray-900 text-white px-4 py-2 text-sm font-semibold hover:bg-gray-800">
                    Apply
                </button>
            </form>
        </div>

        {{-- TABLE --}}
        <div class="rounded-2xl border border-gray-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        @if($tab === 'quizzes')
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Student</th>
                                <th class="px-6 py-3 text-left font-semibold">Quiz</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-left font-semibold">Score</th>
                                <th class="px-6 py-3 text-left font-semibold">Submitted</th>
                                <th class="px-6 py-3 text-right font-semibold">Action</th>
                            </tr>
                        @else
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">Student</th>
                                <th class="px-6 py-3 text-left font-semibold">Assignment</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-left font-semibold">Marks</th>
                                <th class="px-6 py-3 text-left font-semibold">Updated</th>
                                <th class="px-6 py-3 text-right font-semibold">Action</th>
                            </tr>
                        @endif
                    </thead>

                    <tbody class="divide-y divide-gray-200">
                        @if($tab === 'quizzes')
                            @forelse($quizAttempts as $a)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $a->user?->name }}</td>
                                    <td class="px-6 py-4">{{ $a->quiz?->title }}</td>
                                    <td class="px-6 py-4">{{ $a->status }}</td>
                                    <td class="px-6 py-4">
                                        {{ (int) ($a->score ?? 0) }} / {{ (int) ($a->total ?? 0) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ optional($a->submitted_at)->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('staff.quiz.attempts.show', $a->id) }}"
                                            class="px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">No quiz attempts found.</td>
                                </tr>
                            @endforelse
                        @else
                            @forelse($assignmentSubs as $s)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-900">{{ $s->user?->name }}</td>
                                    <td class="px-6 py-4">{{ $s->assignment?->title }}</td>
                                    <td class="px-6 py-4">{{ $s->status }}</td>
                                    <td class="px-6 py-4">{{ $s->marks_awarded ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ optional($s->updated_at)->diffForHumans() ?? '—' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('staff.assignments.submissions.show', [$s->assignment_id, $s->id]) }}"
                                            class="px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">No assignment submissions found.
                                    </td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200">
                @if($tab === 'quizzes')
                    {{ $quizAttempts?->links() }}
                @else
                    {{ $assignmentSubs?->links() }}
                @endif
            </div>
        </div>

    </div>
@endsection