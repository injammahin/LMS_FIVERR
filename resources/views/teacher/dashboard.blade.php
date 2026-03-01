@extends('layouts.teacher')

@section('title', 'Teacher Dashboard')

@section('teacher_content')
    @php
        $donut = function ($percent, $color) {
            return "background: conic-gradient({$color} {$percent}%, #e5e7eb 0%);";
        };
    @endphp

    <div class="space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }} 👋</p>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">My Courses</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $courses->count() }}</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">Students</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $studentsCount }}</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">Pending Grading</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $pendingGrading }}</div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500">Unread Notifications</div>
                <div class="text-3xl font-bold text-gray-900 mt-2">{{ $unread }}</div>
            </div>
        </div>

        {{-- Donuts --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Quizzes Submitted</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ $quizSubmittedCount }}</div>
                    <div class="text-xs text-gray-500">Across {{ $quizzesTotal }} quizzes</div>
                </div>
                <div class="donut border border-gray-200" style="{{ $donut($quizPercent, '#a855f7') }}">
                    <span class="text-[12px] font-extrabold">{{ $quizPercent }}%</span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Assignments Submitted</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ $assignmentSubmittedCount }}</div>
                    <div class="text-xs text-gray-500">Across {{ $assignmentsTotal }} assignments</div>
                </div>
                <div class="donut border border-gray-200" style="{{ $donut($assignPercent, '#f59e0b') }}">
                    <span class="text-[12px] font-extrabold">{{ $assignPercent }}%</span>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="text-sm text-gray-500 mb-3">Quick Actions</div>
                <div class="grid grid-cols-1 gap-2">
                    <a href="{{ route('teacher.submissions.index') }}"
                        class="rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 flex items-center justify-between">
                        <span class="font-medium text-gray-900">Review Submissions</span>
                        <i class="fa-solid fa-chevron-right text-gray-400"></i>
                    </a>
                    <a href="{{ route('teacher.courses.index') }}"
                        class="rounded-xl border border-gray-200 px-4 py-3 hover:bg-gray-50 flex items-center justify-between">
                        <span class="font-medium text-gray-900">My Courses</span>
                        <i class="fa-solid fa-chevron-right text-gray-400"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    @once
        <style>
            .donut {
                width: 72px;
                height: 72px;
                border-radius: 9999px;
                display: grid;
                place-items: center;
                position: relative
            }

            .donut::before {
                content: "";
                position: absolute;
                inset: 10px;
                background: #fff;
                border-radius: 9999px
            }

            .donut>span {
                position: relative;
                color: #111827
            }
        </style>
    @endonce
@endsection