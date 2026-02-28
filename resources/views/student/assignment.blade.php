@extends('layouts.student')

@section('title', $assignment->title)

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Assignment</p>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $assignment->title }}</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Course: <span class="font-medium">{{ $course->title }}</span>
                    </p>
                </div>

                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>

            @if($assignment->description)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 prose max-w-none">
                    {!! $assignment->description !!}
                </div>
            @endif

            {{-- Attachment --}}
            @if($assignment->attachment)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <p class="text-sm text-gray-500 mb-2">Attachment</p>
                    <a href="{{ asset('storage/' . $assignment->attachment) }}" target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                        <i class="fa-solid fa-download"></i> Download attachment
                    </a>
                </div>
            @endif

            {{-- Submission placeholder --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                <h3 class="font-semibold text-gray-900">Submit your work</h3>
                <p class="text-sm text-gray-600 mt-1">
                    Next step: connect submission table + file upload based on submission type.
                </p>

                <div class="mt-4 flex gap-2">
                    <button class="px-5 py-2 rounded-xl bg-amber-600 text-white hover:bg-amber-700">
                        <i class="fa-solid fa-upload mr-2"></i> Upload Submission
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection