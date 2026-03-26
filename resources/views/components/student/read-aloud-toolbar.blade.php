@props([
    'target' => '#lessonReadAloudArea',
    'title' => 'Read Aloud',
])
<div
    class="tts-toolbar rounded-3xl border border-sky-200 bg-white shadow-sm p-4 md:p-5"
    data-tts-root
    data-tts-target="{{ $target }}"
>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="tts-toolbar-icon">
                <i class="fa-solid fa-volume-high"></i>
            </div>

            <div>
                <h3 class="text-md font-bold text-slate-900">{{ $title }}</h3>
                <p class="text-sm text-slate-600">
                    Tap play to hear the lesson out loud and follow the highlighted words.
                </p>
                <p class="text-xs text-slate-500 mt-1" data-tts-status>
                    Ready to read.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" class="tts-btn tts-btn-primary" data-tts-action="play">
                <i class="fa-solid fa-play"></i>
                <span>Play</span>
            </button>

            <button type="button" class="tts-btn" data-tts-action="pause">
                <i class="fa-solid fa-pause"></i>
                <span>Pause</span>
            </button>

            <button type="button" class="tts-btn" data-tts-action="replay">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Replay</span>
            </button>

            <button type="button" class="tts-btn" data-tts-action="stop">
                <i class="fa-solid fa-stop"></i>
                <span>Stop</span>
            </button>

            <label class="tts-speed-wrap">
                <span class="text-xs font-semibold text-slate-600">Speed</span>
                <select class="tts-speed-select" data-tts-speed>
                    <option value="0.8" selected>Slow</option>
                    <option value="1" >Normal</option>
                    <option value="1.2">Fast</option>
                </select>
            </label>
        </div>
    </div>
</div>