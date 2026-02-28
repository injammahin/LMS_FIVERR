@extends('layouts.student')

@section('title', $subject->name)

@section('content')
    <div class="min-h-screen bg-gray-50">

        {{-- Top banner --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-white/80 text-sm">Subject</p>
                        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                            {{ $subject->name }}
                        </h1>
                        <p class="text-white/80 text-sm mt-1">
                            Division: {{ $division->name }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('student.division.show', $division->id) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 transition">
                            <i class="fa-solid fa-arrow-left"></i>
                            Back
                        </a>

                        <button type="button" id="toggleAllBtn"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-blue-800 hover:bg-blue-50 transition font-semibold">
                            <i class="fa-solid fa-layer-group"></i>
                            Expand / Collapse All
                        </button>
                    </div>
                </div>

                {{-- Real stats --}}
                <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Courses</p>
                        <p class="text-md font-semibold">{{ $coursesCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Lessons</p>
                        <p class="text-md font-semibold">{{ $lessonsCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Quizzes</p>
                        <p class="text-md font-semibold">{{ $quizzesCount }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Assignments</p>
                        <p class="text-md font-semibold">{{ $assignmentsCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            {{-- Courses accordion --}}
            <div class="space-y-4" id="courseAccordion">

                @forelse($subject->courses as $course)
                    @php
                        $totalLessons = $course->lessons->count();
                        $totalQuizzes = $course->quizzes->count();
                        $totalAssignments = $course->assignments->count();

                        // Until progress tracking is built
                        $completedLessons = 0;
                        $percent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
                    @endphp

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">

                        {{-- Course header --}}
                        <button type="button"
                            class="course-toggle w-full flex items-center justify-between gap-4 p-5 hover:bg-gray-50 transition"
                            data-target="course-{{ $course->id }}">

                            <div class="flex items-center gap-4">
                                {{-- icon instead of U --}}
                                <span
                                    class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-700 border border-blue-100 grid place-items-center">
                                    <i class="fa-solid fa-book-open-reader"></i>
                                </span>

                                <div class="text-left">
                                    {{-- <div class="text-xs uppercase tracking-wider text-gray-500">
                                        Course
                                    </div> --}}

                                    {{-- smaller title --}}
                                    <div class="text-base md:text-sm mx-2 font-semibold text-gray-900">
                                        {{ $course->title }}
                                    </div>

                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-700">
                                            <i class="fa-solid fa-book mr-1"></i> Lessons: {{ $totalLessons }}
                                        </span>

                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-purple-50 border border-purple-100 text-purple-700">
                                            <i class="fa-solid fa-circle-question mr-1"></i> Quizzes: {{ $totalQuizzes }}
                                        </span>

                                        <span
                                            class="text-xs px-2 py-1 rounded-full bg-amber-50 border border-amber-100 text-amber-800">
                                            <i class="fa-solid fa-file-pen mr-1"></i> Assignments: {{ $totalAssignments }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="hidden sm:block text-right">
                                    <div class="text-xs text-gray-500">Progress</div>
                                    <div class="text-sm font-semibold text-gray-800">
                                        {{ $completedLessons }} / {{ $totalLessons }}
                                    </div>
                                </div>

                                <svg class="chev w-6 h-6 text-gray-500 transition-transform" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>

                        {{-- Progress bar --}}
                        <div class="px-5 pb-5">
                            <div class="h-3 rounded-full bg-gray-100 overflow-hidden border border-gray-200">
                                <div class="h-3 rounded-full bg-emerald-500 transition-all duration-500"
                                    style="width: {{ $percent }}%"></div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $percent }}% complete
                            </div>
                        </div>

                        {{-- Course body --}}
                        <div id="course-{{ $course->id }}" class="course-body hidden border-t border-gray-200">
                            <div class="p-5 space-y-6">

                                {{-- LESSONS --}}
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            <i class="fa-solid fa-book text-blue-600"></i>
                                            Lessons
                                        </h3>
                                        <span class="text-xs text-gray-500">
                                            Total: {{ $totalLessons }}
                                        </span>
                                    </div>

                                    @if($totalLessons === 0)
                                        <div class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-xl p-4">
                                            No lessons added yet.
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            @foreach($course->lessons as $lesson)
                                                <a href="{{ route('student.lessons.show', [$course->id, $lesson->id]) }}"
                                                    class="group flex items-center justify-between gap-4 rounded-xl border border-gray-200 hover:border-blue-200 hover:bg-blue-50/30 transition p-4">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 rounded-xl bg-blue-50 grid place-items-center text-blue-700 border border-blue-100">
                                                            <i class="fa-solid fa-play"></i>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs text-gray-500 uppercase tracking-wide">
                                                                Lesson • Position {{ $lesson->position ?? '-' }}
                                                            </div>
                                                            <div class="font-semibold text-gray-900">
                                                                {{ $lesson->title }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        <span
                                                            class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                                            Not started
                                                        </span>
                                                        <i
                                                            class="fa-solid fa-chevron-right text-gray-400 group-hover:text-blue-700 transition"></i>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- QUIZZES --}}
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            <i class="fa-solid fa-circle-question text-purple-600"></i>
                                            Quizzes
                                        </h3>
                                        <span class="text-xs text-gray-500">
                                            Total: {{ $totalQuizzes }}
                                        </span>
                                    </div>

                                    @if($totalQuizzes === 0)
                                        <div class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-xl p-4">
                                            No quizzes added yet.
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            @foreach($course->quizzes as $quiz)
                                                <a href="{{ route('student.quizzes.show', [$course->id, $quiz->id]) }}"
                                                    class="group flex items-center justify-between gap-4 rounded-xl border border-gray-200 hover:border-purple-200 hover:bg-purple-50/30 transition p-4">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 rounded-xl bg-purple-50 grid place-items-center text-purple-700 border border-purple-100">
                                                            <i class="fa-solid fa-bolt"></i>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs text-gray-500 uppercase tracking-wide">
                                                                Quiz
                                                            </div>
                                                            <div class="font-semibold text-gray-900">
                                                                {{ $quiz->title }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        <span
                                                            class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                                            Not started
                                                        </span>
                                                        <i
                                                            class="fa-solid fa-chevron-right text-gray-400 group-hover:text-purple-700 transition"></i>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- ASSIGNMENTS --}}
                                <div>
                                    <div class="flex items-center justify-between mb-3">
                                        <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                                            <i class="fa-solid fa-file-pen text-amber-600"></i>
                                            Assignments
                                        </h3>
                                        <span class="text-xs text-gray-500">
                                            Total: {{ $totalAssignments }}
                                        </span>
                                    </div>

                                    @if($totalAssignments === 0)
                                        <div class="text-sm text-gray-500 bg-gray-50 border border-gray-200 rounded-xl p-4">
                                            No assignments added yet.
                                        </div>
                                    @else
                                        <div class="space-y-3">
                                            @foreach($course->assignments as $assignment)
                                                <a href="{{ route('student.assignments.show', [$course->id, $assignment->id]) }}"
                                                    class="group flex items-center justify-between gap-4 rounded-xl border border-gray-200 hover:border-amber-200 hover:bg-amber-50/30 transition p-4">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="w-10 h-10 rounded-xl bg-amber-50 grid place-items-center text-amber-700 border border-amber-100">
                                                            <i class="fa-solid fa-upload"></i>
                                                        </div>

                                                        <div>
                                                            <div class="text-xs text-gray-500 uppercase tracking-wide">
                                                                Assignment
                                                            </div>
                                                            <div class="font-semibold text-gray-900">
                                                                {{ $assignment->title }}
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center gap-3">
                                                        <span
                                                            class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                                            Not submitted
                                                        </span>
                                                        <i
                                                            class="fa-solid fa-chevron-right text-gray-400 group-hover:text-amber-700 transition"></i>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>

                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-500">
                        No courses found in this subject yet.
                    </div>
                @endforelse

            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // Toggle single accordion
        document.querySelectorAll('.course-toggle').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-target');
                const body = document.getElementById(id);
                const chev = btn.querySelector('.chev');
                body.classList.toggle('hidden');
                chev.classList.toggle('rotate-180');
            });
        });

        // Expand/Collapse all
        const toggleAllBtn = document.getElementById('toggleAllBtn');
        toggleAllBtn?.addEventListener('click', () => {
            const bodies = document.querySelectorAll('.course-body');
            const anyClosed = Array.from(bodies).some(b => b.classList.contains('hidden'));

            bodies.forEach(b => b.classList.toggle('hidden', !anyClosed));
            document.querySelectorAll('.course-toggle .chev').forEach(c => c.classList.toggle('rotate-180', anyClosed));
        });
    </script>
@endsection