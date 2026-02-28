@extends('layouts.student')
@section('title', 'Take Quiz')

@section('content')
<div class="min-h-screen bg-gray-50">
  <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
      <div>
        <p class="text-sm text-gray-500">Taking Quiz</p>
        <h1 class="text-lg font-semibold text-gray-900">{{ $quiz->title }}</h1>
        <p class="text-sm text-gray-600 mt-1">
          Course: <span class="font-semibold">{{ $course->title }}</span>
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">

        {{-- TIMER BOX --}}
        @if(!empty($timeLimitMinutes) && $timeLimitMinutes > 0 && !empty($endsAt))
          <div id="timerBox"
               class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white px-4 py-3 shadow-sm">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 grid place-items-center border border-purple-100">
              <i class="fa-solid fa-clock"></i>
            </div>
            <div class="leading-tight">
              <div class="text-[11px] uppercase tracking-wider text-gray-500">Time Left</div>
              <div class="text-md font-extrabold text-gray-900 tabular-nums" id="timeLeftText">--:--</div>
              <div class="text-xs text-gray-500">
                Limit: <span class="font-semibold">{{ $timeLimitMinutes }}</span> min
              </div>
            </div>
          </div>
        @endif

        <a href="{{ url()->previous() }}"
          class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition">
          <i class="fa-solid fa-arrow-left"></i> Back
        </a>
      </div>
    </div>

    {{-- Warning / Danger bars --}}
    @if(!empty($timeLimitMinutes) && $timeLimitMinutes > 0 && !empty($endsAt))
      <div id="timeWarning"
           class="hidden rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900">
        <div class="flex items-start gap-3">
          <div class="mt-0.5"><i class="fa-solid fa-triangle-exclamation"></i></div>
          <div>
            <div class="font-semibold">Hurry up — time is running out</div>
            <div class="text-sm text-amber-800">
              You have less than <span class="font-semibold">20%</span> time remaining.
            </div>
          </div>
        </div>
      </div>

      <div id="timeDanger"
           class="hidden rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-red-900">
        <div class="flex items-start gap-3">
          <div class="mt-0.5"><i class="fa-solid fa-circle-exclamation"></i></div>
          <div>
            <div class="font-semibold">Final warning</div>
            <div class="text-sm text-red-800">
              Less than <span class="font-semibold">10%</span> time left. It will auto-submit soon.
            </div>
          </div>
        </div>
      </div>
    @endif

    {{-- Form --}}
    <form id="quizForm"
          method="POST"
          action="{{ route('student.quiz.attempt.submit', $attempt->id) }}"
          enctype="multipart/form-data"
          class="space-y-5">
      @csrf

      @foreach($questions as $index => $q)
        @php
          $existing = $answers[$q->id] ?? null;
          $existingAnswer = $existing?->answer ?? [];
        @endphp

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">

          {{-- Top row --}}
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500 font-medium">Question {{ $index + 1 }}</div>
            <div class="text-sm font-semibold text-gray-900">{{ (int)$q->marks }} marks</div>
          </div>

          {{-- Question text --}}
          <div class="text-gray-900 font-semibold leading-relaxed">
            {!! nl2br(e($q->question)) !!}
          </div>

          {{-- Optional image --}}
          @if(!empty($q->question_image))
            <img src="{{ asset('storage/'.$q->question_image) }}"
                 class="max-h-72 rounded-2xl border border-gray-200 shadow-sm" />
          @endif

          {{-- Answer types --}}
          {{-- TRUE / FALSE --}}
          @if($q->type === 'true_false')
            @php
              $current = $existingAnswer['value'] ?? null;
            @endphp
            <div class="grid sm:grid-cols-2 gap-3">
              <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 cursor-pointer transition">
                <input type="radio" class="h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                       name="answers[{{ $q->id }}]" value="true"
                       {{ $current === true ? 'checked' : '' }}>
                <span class="font-medium text-gray-900">True</span>
              </label>

              <label class="flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 hover:bg-gray-50 cursor-pointer transition">
                <input type="radio" class="h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                       name="answers[{{ $q->id }}]" value="false"
                       {{ $current === false ? 'checked' : '' }}>
                <span class="font-medium text-gray-900">False</span>
              </label>
            </div>
          @endif

          {{-- SINGLE CHOICE --}}
          @if($q->type === 'single_choice')
            <div class="grid gap-3">
              @foreach($q->options as $opt)
                @php
                  $label = $opt->text ?? $opt->option_text ?? $opt->title ?? $opt->value ?? $opt->name ?? '';
                  $checked = ((int)($existingAnswer['option_id'] ?? 0) === (int)$opt->id);
                @endphp

                <label class="group flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 hover:bg-blue-50/50 hover:border-blue-200 cursor-pointer transition">
                  <input
                    type="radio"
                    name="answers[{{ $q->id }}]"
                    value="{{ $opt->id }}"
                    class="h-5 w-5 text-blue-600 border-gray-300 focus:ring-blue-500"
                    {{ $checked ? 'checked' : '' }}
                  >
                  <span class="text-gray-900 font-medium">
                    {{ $label }}
                  </span>
                </label>
              @endforeach
            </div>
          @endif

          {{-- MULTIPLE CHOICE --}}
          @if($q->type === 'multiple_choice')
            @php
              $selected = (array)($existingAnswer['option_ids'] ?? []);
              $selected = array_map('intval', $selected);
            @endphp

            <div class="grid gap-3">
              @foreach($q->options as $opt)
                @php
                  $label = $opt->text ?? $opt->option_text ?? $opt->title ?? $opt->value ?? $opt->name ?? '';
                  $checked = in_array((int)$opt->id, $selected, true);
                @endphp

                <label class="group flex items-center gap-3 rounded-2xl border border-gray-200 bg-white p-4 hover:bg-emerald-50/40 hover:border-emerald-200 cursor-pointer transition">
                  <input
                    type="checkbox"
                    name="answers[{{ $q->id }}][]"
                    value="{{ $opt->id }}"
                    class="h-5 w-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500"
                    {{ $checked ? 'checked' : '' }}
                  >
                  <span class="text-gray-900 font-medium">
                    {{ $label }}
                  </span>
                </label>
              @endforeach
            </div>
          @endif

          {{-- TEXT --}}
          @if($q->type === 'text')
            @php $val = $existingAnswer['text'] ?? ''; @endphp

            {{-- short input style (perfect for 1+1) --}}
            <input
              type="text"
              name="answers[{{ $q->id }}]"
              value="{{ $val }}"
              placeholder="Type your answer..."
              class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
            >
          @endif

          {{-- FILE --}}
          @if($q->type === 'file')
            <div class="rounded-2xl border border-gray-200 p-4 bg-gray-50">
              <input type="file" name="answers[{{ $q->id }}]"
                     class="block w-full text-sm text-gray-700 file:mr-4 file:py-2 file:px-4
                            file:rounded-xl file:border-0 file:text-sm file:font-semibold
                            file:bg-blue-600 file:text-white hover:file:bg-blue-700 transition" />

              @if(!empty($existing?->file_path))
                <p class="text-sm text-gray-600 mt-3">
                  Uploaded:
                  <a class="text-blue-700 font-semibold underline" target="_blank"
                     href="{{ asset('storage/'.$existing->file_path) }}">
                    View file
                  </a>
                </p>
              @endif
            </div>
          @endif

          @error("answers.$q->id")
            <p class="text-sm text-red-600 font-medium">{{ $message }}</p>
          @enderror

        </div>
      @endforeach

      {{-- Submit --}}
      <div class="flex justify-end">
        <button id="submitBtn"
                class="px-6 py-3 rounded-xl bg-purple-600 text-white hover:bg-purple-700 font-semibold transition">
          <i class="fa-solid fa-paper-plane mr-2"></i> Submit Quiz
        </button>
      </div>

    </form>
  </div>
</div>
@endsection


@section('scripts')
@if(!empty($timeLimitMinutes) && $timeLimitMinutes > 0)
<script>
(function () {
  let remaining = Number(@json($remainingSeconds));
  const total = Number(@json($timeLimitMinutes * 60));
  const warnThreshold = Math.floor(total * 0.20);
  const dangerThreshold = Math.floor(total * 0.10);

  const timeLeftEl = document.getElementById('timeLeftText');
  const timerBox = document.getElementById('timerBox');
  const warnBar = document.getElementById('timeWarning');
  const dangerBar = document.getElementById('timeDanger');
  const form = document.getElementById('quizForm');
  const submitBtn = document.getElementById('submitBtn');

  function pad(n){ return String(n).padStart(2,'0'); }

  function setTimerStyle(state) {
    if (!timerBox) return;
    timerBox.classList.remove('border-amber-300','bg-amber-50','border-red-300','bg-red-50');
    if (state === 'warn') timerBox.classList.add('border-amber-300','bg-amber-50');
    if (state === 'danger') timerBox.classList.add('border-red-300','bg-red-50');
  }

  function autoSubmit(){
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.classList.add('opacity-70','cursor-not-allowed');
      submitBtn.innerText = 'Submitting...';
    }
    form.submit();
  }

  function tick(){
    if (remaining <= 0) {
      if (timeLeftEl) timeLeftEl.innerText = '00:00';
      dangerBar?.classList.remove('hidden');
      warnBar?.classList.add('hidden');
      setTimerStyle('danger');
      autoSubmit();
      return;
    }

    const mins = Math.floor(remaining / 60);
    const secs = remaining % 60;
    if (timeLeftEl) timeLeftEl.innerText = `${pad(mins)}:${pad(secs)}`;

    if (remaining <= dangerThreshold) {
      dangerBar?.classList.remove('hidden');
      warnBar?.classList.add('hidden');
      setTimerStyle('danger');
    } else if (remaining <= warnThreshold) {
      warnBar?.classList.remove('hidden');
      dangerBar?.classList.add('hidden');
      setTimerStyle('warn');
    } else {
      warnBar?.classList.add('hidden');
      dangerBar?.classList.add('hidden');
      setTimerStyle('normal');
    }

    remaining--;
  }

  tick();
  setInterval(tick, 1000);
})();
</script>
@endif
@endsection