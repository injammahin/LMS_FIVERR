@extends('layouts.student')

@section('title', $quiz->title)
@if(session('error'))
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-red-900 flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="mt-0.5 text-red-700">
                <i class="fa-solid fa-circle-exclamation"></i>
            </div>
            <div>
                <div class="font-semibold">Attempt limit reached</div>
                <div class="text-sm text-red-800 mt-1">
                    {{ session('error') }}
                </div>
            </div>
        </div>

        <a href="{{ route('student.subjects.show', [$course->subject->division_id, $course->subject_id]) }}"
            class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-red-200 hover:bg-red-100 transition">
            <i class="fa-solid fa-arrow-left"></i> Back to Course
        </a>
    </div>
@endif
@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Quiz</p>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $quiz->title }}</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Course: <span class="font-medium">{{ $course->title }}</span>
                        • Questions: <span class="font-medium">{{ $quiz->questions_count }}</span>
                    </p>
                </div>

                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>

            @if($quiz->description)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 prose max-w-none">
                    {!! $quiz->description !!}
                </div>
            @endif
            {{-- ✅ Replace your old Start Quiz box with this --}}
            @php
                $max = (int) ($quiz->max_attempts ?? 0);
                $attemptText = $max > 0 ? "{$usedAttempts}/{$max}" : "{$usedAttempts}/∞";
            @endphp

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex items-center justify-between">
                <div>
                    <div class="text-sm text-gray-500">Attempts</div>
                    <div class="text-lg font-bold text-gray-900">{{ $attemptText }}</div>

                    @if($inProgress)
                        <div
                            class="mt-2 inline-flex text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700 border border-blue-200">
                            In progress
                        </div>
                    @elseif($usedAttempts > 0)
                        <div
                            class="mt-2 inline-flex text-xs px-2 py-1 rounded-full bg-purple-100 text-purple-700 border border-purple-200">
                            Submitted
                        </div>
                    @else
                        <div
                            class="mt-2 inline-flex text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                            Not started
                        </div>
                    @endif
                </div>

                <a href="{{ route('student.quiz.start', $quiz->id) }}"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-purple-600 text-white hover:bg-purple-700">
                    <i class="fa-solid fa-play"></i>
                    {{ $inProgress ? 'Resume Quiz' : 'Start Quiz' }}
                </a>
            </div>

        </div>
    </div>
@endsection