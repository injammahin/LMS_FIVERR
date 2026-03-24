@props([
    'title' => 'Click to Define',
])


         
<div class="define-toolbar rounded-[28px] border border-violet-200 bg-white shadow-[0_16px_40px_rgba(124,58,237,0.08)] p-4 md:p-5">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-start gap-4">
            <div class="define-toolbar-icon">
                <i class="fa-solid fa-book-open-reader"></i>
            </div>

            <div>
                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <h3 class="text-md md:text-md font-extrabold tracking-tight text-slate-900">
                        {{ $title }}
                    </h3>
                </div>

                <p class="text-sm md:text-[15px] leading-6 text-slate-600 max-w-2xl">
                    Click a word or highlight a short phrase to see its meaning instantly.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <span class="define-chip">
                <i class="fa-solid fa-hand-pointer"></i>
                Click a word
            </span>

            <span class="define-chip">
                <i class="fa-solid fa-highlighter"></i>
                Highlight to define
            </span>

            <span class="define-chip">
                <i class="fa-solid fa-volume-high"></i>
                Pronunciation
            </span>
        </div>
    </div>
</div>