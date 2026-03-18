@extends('layouts.student')

@section('title', $lesson->title)

@section('content')
    @php
        $isDone = !empty($progress?->completed_at);
        $isViewed = !empty($progress?->viewed_at);
        $backUrl = request('back') ?: url()->previous();
        $goldStars = (int) (auth()->user()->gold_stars ?? 0);
    @endphp


    <div
        class="min-h-screen bg-gradient-to-b from-sky-50 via-white to-amber-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
        <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

            <div class="flex items-start justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex items-center gap-3 flex-wrap">
                        <p class="text-sm text-gray-500 dark:text-white/60">Lesson</p>

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

                        @if(!empty($isElementaryRewardDivision))
                            <span
                                class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                ⭐ Gold Star Lesson
                            </span>
                        @endif
                    </div>

                    <h1 class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>

                    <p class="text-sm text-gray-600 dark:text-white/70">
                        Course: <span class="font-medium">{{ $course->title }}</span>
                    </p>
                </div>

                <a href="{{ $backUrl }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-white/5 shadow-sm text-gray-800 dark:text-white/80">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            </div>

            @if(!empty($isElementaryRewardDivision))
                <div
                    class="relative overflow-hidden rounded-3xl border border-amber-200 bg-gradient-to-r from-yellow-50 via-amber-50 to-orange-50 shadow-sm">
                    <div class="absolute top-4 right-6 text-4xl opacity-20 animate-float-soft">⭐</div>
                    <div class="absolute bottom-3 left-8 text-2xl opacity-20 animate-float-soft" style="animation-delay:.5s;">✨
                    </div>

                    <div class="relative p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold tracking-[0.2em] text-amber-600 uppercase">Elementary Reward</p>
                            <h3 class="text-sm font-bold text-gray-900 mt-1">Complete this lesson and earn a gold star</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                Every completed elementary lesson gives the student 1 gold star.
                            </p>
                        </div>

                        <div
                            class="flex items-center gap-3 rounded-2xl bg-white/80 backdrop-blur px-4 py-3 border border-amber-100 shadow-sm">
                            <div
                                class="w-12 h-12 rounded-2xl bg-gradient-to-br from-yellow-300 to-amber-500 flex items-center justify-center shadow-inner">
                                <i class="fa-solid fa-star text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500">Your Stars</div>
                                <div class="text-md font-extrabold text-gray-900">{{ $goldStars }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-green-50 border border-green-200 text-green-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($lesson->description)
                <div
                    class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6 prose max-w-none dark:prose-invert">
                    {!! $lesson->description !!}
                </div>
            @endif

            @php $blocks = $lesson->content_blocks ?? []; @endphp

            @if(count($blocks))
                <div class="space-y-4">
                    @foreach($blocks as $block)

                        @if(($block['type'] ?? '') === 'text')
                            <div
                                class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
                                <div class="text-sm text-gray-500 dark:text-white/60 mb-2">Text</div>
                                <div class="prose max-w-none dark:prose-invert">
                                    {!! nl2br(e($block['text'] ?? '')) !!}
                                </div>
                            </div>

                        @elseif(($block['type'] ?? '') === 'video')
                            <div
                                class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
                                <div class="text-sm text-gray-500 dark:text-white/60 mb-2">Video</div>
                                <a href="{{ $block['video_url'] ?? '#' }}" target="_blank" class="text-blue-600 underline break-all">
                                    {{ $block['video_url'] ?? '' }}
                                </a>
                            </div>

                        @elseif(($block['type'] ?? '') === 'file')
                            <div
                                class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
                                <div class="text-sm text-gray-500 dark:text-white/60 mb-2">File</div>
                                <a class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700"
                                    href="{{ asset('storage/' . ($block['path'] ?? '')) }}" target="_blank">
                                    <i class="fa-solid fa-download"></i> Download file
                                </a>
                            </div>

                        @elseif(($block['type'] ?? '') === 'h5p')
                            @php
                                $h5pRaw = trim($block['embed'] ?? $block['h5p_embed'] ?? $block['h5p_url'] ?? '');
                                $h5pSrc = null;

                                if ($h5pRaw) {
                                    // If full iframe code was pasted, extract only src=""
                                    if (preg_match('/src=["\']([^"\']+)["\']/', $h5pRaw, $matches)) {
                                        $h5pSrc = $matches[1];
                                    } else {
                                        // Otherwise assume raw value is already a URL
                                        $h5pSrc = $h5pRaw;
                                    }
                                }
                            @endphp

                            <div
                                class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
                                <div class="text-sm text-gray-500 dark:text-white/60 mb-3">Interactive Activity</div>

                                @if($h5pSrc)
                                    <div
                                        class="rounded-2xl overflow-hidden border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 h5p-wrapper">
                                        <iframe src="{{ $h5pSrc }}" class="w-full h-[700px] block" frameborder="0" allowfullscreen
                                            loading="lazy"
                                            allow="autoplay *; geolocation *; microphone *; camera *; midi *; encrypted-media *">
                                        </iframe>
                                    </div>
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
                            class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-white">Finished this lesson?</div>
                                <div class="text-sm text-gray-500 dark:text-white/60">
                                    Mark it as done to update your progress
                                    @if(!empty($isElementaryRewardDivision))
                                        and earn your gold star.
                                    @endif
                                </div>
                            </div>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 shadow-md hover:shadow-lg transition-all duration-300">
                                ✅ Mark as Done
                            </button>
                        </div>
                    </form>
                @else
                    <div
                        class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-200 dark:border-white/10 shadow-sm p-6">
                        <div class="font-semibold text-gray-900 dark:text-white">✅ Lesson completed</div>
                        <div class="text-sm text-gray-500 dark:text-white/60 mt-1">You’ve already marked this lesson as done.
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    @if(session('reward_animation'))
        <div id="rewardPopup" class="fixed inset-0 z-[9999] flex items-center justify-center px-4">
            <div class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"></div>

            <div class="reward-card relative w-full max-w-md rounded-[32px] overflow-hidden shadow-2xl">
                <div class="bg-gradient-to-br from-yellow-300 via-amber-300 to-orange-400 p-8 text-center relative">
                    <div class="absolute top-4 left-6 text-white/60 text-2xl animate-float-soft">✨</div>
                    <div class="absolute top-8 right-8 text-white/50 text-3xl animate-float-soft" style="animation-delay:.4s;">⭐
                    </div>
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 text-white/40 text-2xl animate-float-soft"
                        style="animation-delay:.9s;">✨</div>

                    <div
                        class="relative w-24 h-24 mx-auto rounded-full bg-white/35 backdrop-blur flex items-center justify-center shadow-lg border border-white/40">
                        <i class="fa-solid fa-star text-white text-5xl animate-star-bounce"></i>
                    </div>

                    <h3 class="mt-5 text-3xl font-extrabold text-white drop-shadow">
                        Awesome!
                    </h3>

                    <p class="mt-2 text-white/95 text-lg font-semibold">
                        You earned {{ session('reward_stars', 1) }} gold star!
                    </p>
                </div>

                <div class="bg-white dark:bg-slate-900 p-7 text-center">
                    <p class="text-gray-600 dark:text-white/70 text-sm">
                        Great job finishing your lesson.
                    </p>

                    <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-100 px-5 py-4">
                        <div class="text-xs uppercase tracking-[0.2em] text-amber-600 font-semibold">
                            Total Collection
                        </div>
                        <div class="mt-1 text-3xl font-extrabold text-gray-900">
                            {{ session('reward_total_stars', $goldStars) }}
                        </div>
                        <div class="text-sm text-gray-500">
                            Gold {{ session('reward_total_stars', $goldStars) == 1 ? 'Star' : 'Stars' }}
                        </div>
                    </div>

                    <button id="closeRewardPopup"
                        class="mt-5 inline-flex items-center justify-center px-6 py-3 rounded-2xl bg-slate-900 text-white hover:bg-slate-800 transition">
                        Keep Learning 🚀
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        .h5p-wrapper iframe {
            width: 100% !important;
            height: 700px !important;
            min-height: 700px;
            display: block;
        }

        .h5p-wrapper {
            width: 100%;
        }

        @keyframes rewardPop {
            0% {
                opacity: 0;
                transform: translateY(24px) scale(.88);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes starBounce {

            0%,
            100% {
                transform: scale(1) rotate(0deg);
            }

            25% {
                transform: scale(1.12) rotate(-8deg);
            }

            50% {
                transform: scale(1.18) rotate(8deg);
            }

            75% {
                transform: scale(1.08) rotate(-4deg);
            }
        }

        @keyframes floatSoft {

            0%,
            100% {
                transform: translateY(0);
                opacity: .95;
            }

            50% {
                transform: translateY(-8px);
                opacity: 1;
            }
        }

        .reward-card {
            animation: rewardPop .45s ease-out both;
        }

        .animate-star-bounce {
            animation: starBounce 1.4s ease-in-out infinite;
        }

        .animate-float-soft {
            animation: floatSoft 3s ease-in-out infinite;
        }
    </style>
@endsection

@section('scripts')
    @if(session('reward_animation'))
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const popup = document.getElementById('rewardPopup');
                const closeBtn = document.getElementById('closeRewardPopup');

                const end = Date.now() + 2200;
                const colors = ['#facc15', '#f59e0b', '#fde68a', '#ffffff', '#93c5fd'];

                confetti({
                    particleCount: 120,
                    spread: 90,
                    startVelocity: 35,
                    origin: { y: 0.6 },
                    colors: colors
                });

                (function frame() {
                    confetti({
                        particleCount: 4,
                        angle: 60,
                        spread: 60,
                        origin: { x: 0, y: 0.7 },
                        colors: colors
                    });

                    confetti({
                        particleCount: 4,
                        angle: 120,
                        spread: 60,
                        origin: { x: 1, y: 0.7 },
                        colors: colors
                    });

                    if (Date.now() < end) {
                        requestAnimationFrame(frame);
                    }
                })();

                closeBtn?.addEventListener('click', function () {
                    popup?.remove();
                });

                popup?.addEventListener('click', function (e) {
                    if (e.target === popup) {
                        popup.remove();
                    }
                });
            });
        </script>
    @endif
@endsection