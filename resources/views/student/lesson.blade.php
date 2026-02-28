@extends('layouts.student')

@section('title', $lesson->title)

@section('content')
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Lesson</p>
                    <h1 class="text-lg font-semibold text-gray-900">{{ $lesson->title }}</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Course: <span class="font-medium">{{ $course->title }}</span>
                    </p>
                </div>

                <a href="{{ url()->previous() }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>

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
                                <a href="{{ $block['video_url'] ?? '#' }}" target="_blank" class="text-blue-600 underline break-all">
                                    {{ $block['video_url'] ?? '' }}
                                </a>
                            </div>
                        @elseif(($block['type'] ?? '') === 'file')
                            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                                <div class="text-sm text-gray-500 mb-2">File</div>
                                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                                    href="{{ asset('storage/' . $block['path']) }}" target="_blank">
                                    <i class="fa-solid fa-download"></i> Download file
                                </a>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>
@endsection