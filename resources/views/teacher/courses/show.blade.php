@extends('layouts.teacher')
@section('title', 'Course Details')

@section('content')
@php
    $subjectName  = optional($course->subject)->name ?? '-';
    $divisionName = optional(optional($course->subject)->division)->name ?? '-';
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="h-16 bg-gradient-to-r from-blue-700 to-indigo-700 relative">
            <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_45%)]"></div>
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]"></div>
        </div>

        <div class="p-5 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <div class="text-xs text-gray-500">Course</div>
                <h1 class="text-lg font-semibold text-gray-900 mt-1">{{ $course->title }}</h1>

                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="px-3 py-1 rounded-full text-xs border bg-gray-50 border-gray-200 text-gray-700">
                        Subject: <span class="font-semibold text-gray-900">{{ $subjectName }}</span>
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs border bg-emerald-50 border-emerald-100 text-emerald-800">
                        Division: <span class="font-semibold">{{ $divisionName }}</span>
                    </span>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('teacher.courses.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <div class="statCard">
            <div class="statLabel">Lessons</div>
            <div class="statValue">{{ $totals['lessons'] }}</div>
        </div>
        <div class="statCard">
            <div class="statLabel">Quizzes</div>
            <div class="statValue">{{ $totals['quizzes'] }}</div>
        </div>
        <div class="statCard">
            <div class="statLabel">Assignments</div>
            <div class="statValue">{{ $totals['assignments'] }}</div>
        </div>
        <div class="statCard">
            <div class="statLabel">Students</div>
            <div class="statValue">{{ $students->total() }}</div>
        </div>
    </div>

    {{-- Students Table --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-200 flex items-start justify-between gap-3">
            <div>
                <div class="text-sm font-semibold text-gray-900">Students</div>
                <div class="text-xs text-gray-500 mt-1">Overview progress for this course</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold">Student</th>
                        <th class="px-6 py-3 text-left font-semibold w-[190px]">Lessons</th>
                        <th class="px-6 py-3 text-left font-semibold w-[190px]">Quizzes</th>
                        <th class="px-6 py-3 text-left font-semibold w-[190px]">Assignments</th>
                        <th class="px-6 py-3 text-left font-semibold w-[150px]">Overall</th>
                        <th class="px-6 py-3 text-right font-semibold w-[150px]">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">
                @forelse($students as $row)
                    @php
                        $st = $row['student'];

                        $lessonDone = $row['lesson_done'];
                        $lessonViewed = $row['lesson_viewed'];

                        $quizSubmitted = $row['quiz_submitted'];
                        $quizAttempts = $row['quiz_attempts'];

                        $assSubmitted = $row['ass_submitted'];
                        $assAttempts = $row['ass_attempts'];

                        $overall = $row['overall_percent'];

                        $lessonPct = $totals['lessons'] > 0 ? (int)round(($lessonDone / $totals['lessons']) * 100) : 0;
                        $quizPct = $totals['quizzes'] > 0 ? (int)round(($quizSubmitted / $totals['quizzes']) * 100) : 0;
                        $assPct = $totals['assignments'] > 0 ? (int)round(($assSubmitted / $totals['assignments']) * 100) : 0;
                    @endphp

                    <tr class="hover:bg-gray-50/60">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $st->name }}</div>
                            <div class="text-xs text-gray-500 mt-1">{{ $st->username ?? $st->email ?? '-' }}</div>
                            <div class="text-xs text-gray-400 mt-1">Joined: {{ optional($st->created_at)->format('d M Y') }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                <span>Done {{ $lessonDone }}/{{ $totals['lessons'] }}</span>
                                <span class="font-semibold text-gray-800">{{ $lessonPct }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                <div class="h-2 rounded-full" style="width: {{ $lessonPct }}%; background:#2563eb;"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">Viewed: {{ $lessonViewed }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                <span>Submitted {{ $quizSubmitted }}/{{ $totals['quizzes'] }}</span>
                                <span class="font-semibold text-gray-800">{{ $quizPct }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                <div class="h-2 rounded-full" style="width: {{ $quizPct }}%; background:#a855f7;"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">Attempts: {{ $quizAttempts }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-2">
                                <span>Submitted {{ $assSubmitted }}/{{ $totals['assignments'] }}</span>
                                <span class="font-semibold text-gray-800">{{ $assPct }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-gray-100 border border-gray-200 overflow-hidden">
                                <div class="h-2 rounded-full" style="width: {{ $assPct }}%; background:#f59e0b;"></div>
                            </div>
                            <div class="text-xs text-gray-500 mt-2">Submissions: {{ $assAttempts }}</div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="text-xs text-gray-500 mb-2">Overall</div>
                            <div class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border border-emerald-200 bg-emerald-50 text-emerald-800">
                                {{ $overall }}%
                            </div>
                        </td>

                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('teacher.courses.students.show', [$course->id, $st->id]) }}"
                               class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold">
                                View Progress <i class="fa-solid fa-chevron-right text-[10px] text-gray-400"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            No students found for this division.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200">
            {{ $students->links() }}
        </div>
    </div>

</div>

@once
<style>
    .statCard{border:1px solid #e5e7eb;background:#fff;border-radius:16px;padding:18px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
    .statLabel{font-size:12px;color:#6b7280;font-weight:600}
    .statValue{font-size:18px;font-weight:800;color:#111827;margin-top:8px;line-height:1}
</style>
@endonce
@endsection