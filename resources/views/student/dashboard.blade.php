@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Student Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }} 👋</p>
            </div>

            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
                Role: {{ auth()->user()->role }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Enrolled Courses</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">4</p>
                <p class="text-xs text-gray-500 mt-2">Demo number</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Pending Assignments</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">2</p>
                <p class="text-xs text-gray-500 mt-2">Demo number</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Overall Progress</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">67%</p>
                <p class="text-xs text-gray-500 mt-2">Demo number</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Continue Learning</h2>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                        <div>
                            <p class="font-medium text-gray-900">Math • Grade 5</p>
                            <p class="text-sm text-gray-500">Lesson 3: Fractions</p>
                        </div>
                        <a href="#" class="text-sm font-medium text-teal-600 hover:text-teal-700">Resume</a>
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                        <div>
                            <p class="font-medium text-gray-900">Science • Grade 4</p>
                            <p class="text-sm text-gray-500">Lesson 2: Plants</p>
                        </div>
                        <a href="#" class="text-sm font-medium text-teal-600 hover:text-teal-700">Resume</a>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Upcoming</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Assignment Due</p>
                        <p class="text-sm text-gray-500">English Essay • Tomorrow</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Quiz</p>
                        <p class="text-sm text-gray-500">Math Quiz • Friday</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection