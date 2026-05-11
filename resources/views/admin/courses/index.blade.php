@extends('layouts.admin')

@section('title', 'Courses')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Courses</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Manage courses under subjects.
                </p>
            </div>

            <a href="{{ route('admin.courses.create', [
                    'division_id' => $divisionId,
                    'subject_id' => $subjectId
                ]) }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Courses
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" id="courseFilterForm" class="grid grid-cols-1 md:grid-cols-3 gap-3 md:items-end">

                {{-- Division --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                        Division <span class="text-red-500">*</span>
                    </label>

                    <select name="division_id"
                        id="divisionFilter"
                        required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        @foreach($divisions as $d)
                            <option value="{{ $d->id }}" {{ (string) $divisionId === (string) $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                        Subject
                    </label>

                    <select name="subject_id"
                        id="subjectFilter"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="">All Subjects</option>

                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ (string) $subjectId === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Reset --}}
                <div class="flex md:justify-end">
                    <a href="{{ route('admin.courses.index') }}"
                        class="inline-flex px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Rule Note --}}
        <div class="rounded-xl border border-blue-100 bg-blue-50 dark:bg-blue-500/10 dark:border-blue-500/20 px-4 py-3 text-sm text-blue-800 dark:text-blue-200">
            <strong>Selected Division Rule:</strong>
            Assignment is available after every 5 courses inside the selected division.
            Quiz is available after every 45 courses inside the selected division.
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Course</th>
                            <th class="px-6 py-3 text-left font-medium">Division / Subject</th>
                            <th class="px-6 py-3 text-left font-medium">Rule Status</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($courses as $course)
                            @php
                                $rule = $courseRuleMap[$course->id] ?? [
                                    'course_number' => null,
                                    'show_assignment' => false,
                                    'show_quiz' => false,
                                ];

                                $divisionCourseNumber = $rule['course_number'];
                                $showAssignmentButton = $rule['show_assignment'];
                                $showQuizButton = $rule['show_quiz'];
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">

                                {{-- Course --}}
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">
                                        {{ $course->title }}
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-white/60">
                                        {{ $course->slug }}
                                    </div>

                                    @if($divisionCourseNumber)
                                        <div class="mt-1 inline-flex items-center gap-1 text-xs text-blue-700 dark:text-blue-300 font-medium">
                                            <i class="fa-solid fa-layer-group"></i>
                                            Division Course #{{ $divisionCourseNumber }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Division / Subject --}}
                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    <div class="text-sm">
                                        {{ $course->subject?->division?->name ?? 'N/A' }}
                                    </div>

                                    <div class="text-xs text-gray-500 dark:text-white/60">
                                        {{ $course->subject?->name ?? 'N/A' }}
                                    </div>
                                </td>

                                {{-- Rule Status --}}
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @if($showAssignmentButton)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20">
                                                <i class="fa-solid fa-file-pen"></i>
                                                Assignment
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-gray-50 text-gray-400 border border-gray-200 dark:bg-white/5 dark:text-white/40 dark:border-white/10">
                                                No Assignment
                                            </span>
                                        @endif

                                        @if($showQuizButton)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-500/10 dark:text-purple-300 dark:border-purple-500/20">
                                                <i class="fa-solid fa-circle-question"></i>
                                                Quiz
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs bg-gray-50 text-gray-400 border border-gray-200 dark:bg-white/5 dark:text-white/40 dark:border-white/10">
                                                No Quiz
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    @if($course->status === 'published')
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">
                                            Published
                                        </span>
                                    @else
                                        <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2 flex-wrap">

                                        @if($showAssignmentButton)
                                            <a href="{{ route('admin.courses.assignments.index', $course->id) }}"
                                                class="px-3 py-1.5 rounded-lg border border-amber-200 text-amber-700 bg-amber-50 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:border-amber-500/20 text-sm">
                                                Assignments
                                            </a>
                                        @else
                                            <span
                                                class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-400 dark:bg-white/5 dark:border-white/10 dark:text-white/30 text-sm cursor-not-allowed"
                                                title="Assignment is available after every 5 courses within the selected division">
                                                No Assignment
                                            </span>
                                        @endif

                                        @if($showQuizButton)
                                            <a href="{{ route('admin.courses.quizzes.index', $course->id) }}"
                                                class="px-3 py-1.5 rounded-lg border border-purple-200 text-purple-700 bg-purple-50 hover:bg-purple-100 dark:bg-purple-500/10 dark:text-purple-300 dark:border-purple-500/20 text-sm">
                                                Quizzes
                                            </a>
                                        @else
                                            <span
                                                class="px-3 py-1.5 rounded-lg border border-gray-200 bg-gray-50 text-gray-400 dark:bg-white/5 dark:border-white/10 dark:text-white/30 text-sm cursor-not-allowed"
                                                title="Quiz is available after every 45 courses within the selected division">
                                                No Quiz
                                            </span>
                                        @endif

                                        <a href="{{ route('admin.courses.lessons.index', $course->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm dark:text-white">
                                            Lessons
                                        </a>

                                        <a href="{{ route('admin.courses.edit', $course->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm dark:text-white">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.courses.destroy', $course->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this course?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                    No courses found for this division.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $courses->links() }}
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('courseFilterForm');
            const divisionFilter = document.getElementById('divisionFilter');
            const subjectFilter = document.getElementById('subjectFilter');

            if (divisionFilter) {
                divisionFilter.addEventListener('change', function () {
                    if (subjectFilter) {
                        subjectFilter.value = '';
                    }

                    form.submit();
                });
            }

            if (subjectFilter) {
                subjectFilter.addEventListener('change', function () {
                    form.submit();
                });
            }
        });
    </script>
@endsection