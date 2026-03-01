@extends('layouts.teacher')

@section('title', 'Course Details')

@section('teacher_content')
    <div class="space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm text-gray-500">Course</div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $course->title }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Subject: {{ $course->subject->name ?? '-' }}
                    • Division: {{ $course->subject->division->name ?? '-' }}
                </p>
            </div>

            <a href="{{ route('teacher.courses.index') }}"
                class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left mr-2"></i> Back
            </a>
        </div>

        {{-- stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500">Lessons</div>
                <div class="text-2xl font-bold">{{ $course->lessons->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500">Quizzes</div>
                <div class="text-2xl font-bold">{{ $course->quizzes->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500">Assignments</div>
                <div class="text-2xl font-bold">{{ $course->assignments->count() }}</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500">Students</div>
                <div class="text-2xl font-bold">{{ $students->total() }}</div>
            </div>
        </div>

        {{-- students --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Students</h2>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($students as $st)
                    <div class="p-4 flex items-center justify-between">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $st->name }}</div>
                            <div class="text-sm text-gray-500">{{ $st->username ?? $st->email }}</div>
                        </div>
                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-700">
                            Student
                        </span>
                    </div>
                @empty
                    <div class="p-10 text-center text-gray-500">
                        No students found for this division.
                    </div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $students->links() }}
            </div>
        </div>

    </div>
@endsection