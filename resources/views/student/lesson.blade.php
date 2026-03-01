@extends('layouts.student')

@section('title', $lesson->title)

@section('content')
@php
    $isDone = !empty($progress?->completed_at);
    $isViewed = !empty($progress?->viewed_at);

    // ✅ Back URL: prefer explicit query param (?back=...), otherwise fallback
    $backUrl = request('back') ?: url()->previous();
@endphp

<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <p class="text-sm text-gray-500">Lesson</p>

                    {{-- Status Badge --}}
                    @if($isDone)
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                            Done
                        </span>
                    @elseif($isViewed)
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                            Viewed
                        </span>
                    @else
                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            Not started
                        </span>
                    @endif
                </div>

                <h1 class="text-lg md:text-lg font-bold text-gray-900">{{ $lesson->title }}</h1>

                <p class="text-sm text-gray-600">
                    Course: <span class="font-medium">{{ $course->title }}</span>
                </p>
            </div>

            <a href="{{ $backUrl }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Description --}}
        @if($lesson->description)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 prose max-w-none">
                {!! $lesson->description !!}
            </div>
        @endif

        {{-- Content Blocks --}}
        @php $blocks = $lesson->content_blocks ?? []; @endphp

        @if(count($blocks))
            <div class="space-y-4">
                @foreach($blocks as $block)
                    @if(($block['type'] ?? '') === 'text')
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-2">Text</div>
                            <div class="prose max-w-none">
                                {!! nl2br(e($block['text'] ?? '')) !!}
                            </div>
                        </div>

                    @elseif(($block['type'] ?? '') === 'video')
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-2">Video</div>
                            <a href="{{ $block['video_url'] ?? '#' }}" target="_blank"
                               class="text-blue-600 underline break-all">
                                {{ $block['video_url'] ?? '' }}
                            </a>
                        </div>

                    @elseif(($block['type'] ?? '') === 'file')
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                            <div class="text-sm text-gray-500 mb-2">File</div>
                            <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                               href="{{ asset('storage/' . ($block['path'] ?? '')) }}" target="_blank">
                                <i class="fa-solid fa-download"></i> Download file
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

        {{-- ✅ End of lesson actions --}}
        <div class="pt-2">
            @if(!$isDone)
                <form method="POST" action="{{ route('student.lessons.done', [$course->id, $lesson->id]) }}">
                    @csrf
                    {{-- keep return/back so after submit you come back properly --}}
                    <input type="hidden" name="back" value="{{ $backUrl }}">

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <div class="font-semibold text-gray-900">Finished this lesson?</div>
                            <div class="text-sm text-gray-500">Mark it as done to update your progress.</div>
                        </div>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-green-600 text-white hover:bg-green-700">
                            ✅ Mark as Done
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                    <div class="font-semibold text-gray-900">✅ Lesson completed</div>
                    <div class="text-sm text-gray-500 mt-1">You’ve already marked this lesson as done.</div>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection