@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Teacher Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Hello, {{ auth()->user()->name }} 👋</p>
            </div>

            <span
                class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-emerald-700">
                Role: {{ auth()->user()->role }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">My Courses</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">6</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">128</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Pending Grading</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">14</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Announcements</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">3</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900">Create Assignment</p>
                        <p class="text-sm text-gray-500">Demo link</p>
                    </a>
                    <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900">Upload Lesson</p>
                        <p class="text-sm text-gray-500">Demo link</p>
                    </a>
                    <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900">Grade Submissions</p>
                        <p class="text-sm text-gray-500">Demo link</p>
                    </a>
                    <a href="#" class="rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900">Send Notice</p>
                        <p class="text-sm text-gray-500">Demo link</p>
                    </a>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Recent Submissions</h2>
                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                        <div>
                            <p class="font-medium text-gray-900">Math Homework #2</p>
                            <p class="text-sm text-gray-500">Submitted by: Rahim</p>
                        </div>
                        <span class="text-xs rounded-full bg-yellow-50 px-2 py-1 font-medium text-yellow-700">Needs
                            Review</span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl border border-gray-100 p-4">
                        <div>
                            <p class="font-medium text-gray-900">Science Quiz</p>
                            <p class="text-sm text-gray-500">Submitted by: Ayesha</p>
                        </div>
                        <span class="text-xs rounded-full bg-green-50 px-2 py-1 font-medium text-green-700">Graded</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection