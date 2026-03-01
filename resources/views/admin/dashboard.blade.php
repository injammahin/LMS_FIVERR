@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('teacher_content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }} 👋</p>
        </div>

        <div class="flex gap-2">
            <a href="#"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                <i class="fa-regular fa-calendar"></i> Today
            </a>
            <a href="#"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                <i class="fa-solid fa-plus"></i> Create
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">My Courses</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">6</div>
            <div class="text-xs text-gray-500 mt-2">Assigned to you</div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Students</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">128</div>
            <div class="text-xs text-gray-500 mt-2">Enrolled in your courses</div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Pending Grading</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">14</div>
            <div class="text-xs text-amber-700 mt-2">Needs review</div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">Announcements</div>
            <div class="text-3xl font-bold text-gray-900 mt-2">3</div>
            <div class="text-xs text-gray-500 mt-2">Published</div>
        </div>
    </div>

    {{-- Two columns --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Quick actions --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                <span class="text-xs text-gray-500">Shortcuts</span>
            </div>

            <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 grid place-items-center border border-blue-100">
                            <i class="fa-solid fa-file-circle-plus"></i>
                        </span>
                        <div>
                            <div class="font-medium text-gray-900">Create Assignment</div>
                            <div class="text-sm text-gray-500">Add for a course</div>
                        </div>
                    </div>
                </a>

                <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 grid place-items-center border border-emerald-100">
                            <i class="fa-solid fa-video"></i>
                        </span>
                        <div>
                            <div class="font-medium text-gray-900">Upload Lesson</div>
                            <div class="text-sm text-gray-500">Video / notes</div>
                        </div>
                    </div>
                </a>

                <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-amber-50 text-amber-700 grid place-items-center border border-amber-100">
                            <i class="fa-solid fa-check-double"></i>
                        </span>
                        <div>
                            <div class="font-medium text-gray-900">Grade Submissions</div>
                            <div class="text-sm text-gray-500">Pending work</div>
                        </div>
                    </div>
                </a>

                <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 grid place-items-center border border-purple-100">
                            <i class="fa-solid fa-bullhorn"></i>
                        </span>
                        <div>
                            <div class="font-medium text-gray-900">Send Notice</div>
                            <div class="text-sm text-gray-500">Announcement</div>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        {{-- Recent submissions --}}
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Recent Submissions</h2>
                <a href="#" class="text-sm font-medium text-blue-700 hover:text-blue-800">View all</a>
            </div>

            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                    <div>
                        <p class="font-medium text-gray-900">Math Homework #2</p>
                        <p class="text-sm text-gray-500">Submitted by: Rahim</p>
                    </div>
                    <span class="text-xs rounded-full bg-amber-50 px-2 py-1 font-medium text-amber-700 border border-amber-100">
                        Needs Review
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                    <div>
                        <p class="font-medium text-gray-900">Science Quiz</p>
                        <p class="text-sm text-gray-500">Submitted by: Ayesha</p>
                    </div>
                    <span class="text-xs rounded-full bg-emerald-50 px-2 py-1 font-medium text-emerald-700 border border-emerald-100">
                        Graded
                    </span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection