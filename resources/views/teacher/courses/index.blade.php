@extends('layouts.teacher')
@section('title', 'My Courses')

@section('teacher_content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">My Courses</h1>
                <p class="text-sm text-gray-500 mt-1">Only courses assigned to you.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($courses as $course)
                <a href="{{ route('teacher.courses.show', $course->id) }}"
                    class="bg-white border border-gray-200 rounded-2xl shadow-sm hover:shadow-md transition p-5">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-xs text-gray-500">Course</div>
                            <div class="text-lg font-semibold text-gray-900">{{ $course->title }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                Division: <span class="font-semibold">{{ optional($course->subject->division)->name }}</span>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                            View
                        </span>
                    </div>
                </a>
            @empty
                <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center text-gray-500">
                    No courses assigned yet.
                </div>
            @endforelse
        </div>

    </div>
@endsection