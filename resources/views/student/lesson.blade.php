@extends('layouts.student')

@section('title', $lesson->title)

@section('content')
    @php
        $isDone = !empty($progress?->completed_at);
        $isViewed = !empty($progress?->viewed_at);
        $backUrl = request('back') ?: url()->previous();
    @endphp

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <p class="text-sm text-gray-500">Lesson</p>

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

            @if(session('success'))
                <div class="p-4 rounded-xl bg-green-50 border border-green-200 text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if($lesson->description)
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 prose max-w-none">
                    {!! $lesson->description !!}
                </div>
            @endif

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
                                    href="{{ asset('storage/' . ($block['path'] ?? '')) }}" target="_blank">
                                    <i class="fa-solid fa-download"></i> Download file
                                </a>
                            </div>

                        @elseif(($block['type'] ?? '') === 'h5p')
                            @php
                                $h5pRaw = trim($block['embed'] ?? $block['h5p_embed'] ?? $block['h5p_url'] ?? '');
                                $isIframeCode = str_contains($h5pRaw, '<iframe');
                            @endphp

                            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6">
                                <div class="text-sm text-gray-500 mb-3">Interactive Activity</div>

                                @if($h5pRaw)
                                    @if($isIframeCode)
                                        <div class="rounded-xl overflow-hidden border border-gray-200 bg-white h5p-wrapper">
                                            {!! $h5pRaw !!}
                                        </div>
                                    @else
                                        <div class="rounded-xl overflow-hidden border border-gray-200 bg-white h5p-wrapper">
                                            <iframe src="{{ $h5pRaw }}" class="w-full min-h-[650px]" frameborder="0"
                                                allowfullscreen="allowfullscreen"
                                                allow="autoplay *; geolocation *; microphone *; camera *; midi *; encrypted-media *">
                                            </iframe>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-sm text-red-500">
                                        No H5P content found.
                                    </div>
                                @endif
                            </div>
                        @endif

                    @endforeach
                </div>
            @endif

            <div class="pt-2">
                @if(!$isDone)
                    <form method="POST" action="{{ route('student.lessons.done', [$course->id, $lesson->id]) }}">
                        @csrf
                        <input type="hidden" name="back" value="{{ $backUrl }}">

                        <div
                            class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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

    <style>
        .h5p-wrapper iframe {
            width: 100% !important;
            min-height: 650px;
            display: block;
        }

        .h5p-wrapper {
            width: 100%;
        }
    </style>
@endsection