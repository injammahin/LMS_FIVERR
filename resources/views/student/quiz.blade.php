@extends('layouts.student')

@section('title', $quiz->title)

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

            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <a href="{{ route('student.quiz.start', $quiz->id) }}"
                    class="mt-4 inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-purple-600 text-white hover:bg-purple-700">
                    <i class="fa-solid fa-play"></i> Start Quiz
                </a>
            </div>

        </div>
    </div>
@endsection