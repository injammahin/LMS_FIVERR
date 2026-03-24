@extends('layouts.student')
@section('title', 'Take Quiz')

@section('content')
@php
    use Illuminate\Support\Str;

    $clickDefineEnabled = !empty($showClickDefine ?? false);
@endphp

@once
<style>
  /* Make Quill/HTML questions look perfect */
  .quizQuestionBody img{
    max-width: 100% !important;
    height: auto !important;
    display: block;
    border-radius: 16px;
    border: 1px solid #e5e7eb;
  }
  .quizQuestionBody p{ margin: .5rem 0; }
  .quizQuestionBody h1,.quizQuestionBody h2,.quizQuestionBody h3{ margin: .25rem 0 .5rem; }
  .quizQuestionBody ul{ padding-left: 1.25rem; margin: .5rem 0; }
  .quizQuestionBody ol{ padding-left: 1.25rem; margin: .5rem 0; }
  .quizQuestionBody blockquote{
    border-left: 4px solid #e5e7eb;
    padding-left: 12px;
    color: #374151;
    margin: .75rem 0;
  }
</style>
@endonce

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

    @if($clickDefineEnabled)
      <x-student.click-define-toolbar title="Click to Define" />
    @endif

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

          $qText = $q->question ?? '';
          $looksHtml = Str::contains($qText, ['<p', '<h', '<div', '<span', '<img', '<br', '</']);
        @endphp

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-5">

          {{-- Top row --}}
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500 font-medium">Question {{ $index + 1 }}</div>
            <div class="text-sm font-semibold text-gray-900">{{ (int)$q->marks }} marks</div>
          </div>

          {{-- Question text only: safe for click-to-define --}}
          <div class="rounded-2xl border border-gray-100 bg-gray-50/70 p-4">
            <div
              class="quizQuestionBody prose max-w-none text-gray-900"
              @if($clickDefineEnabled) data-define-area @endif
            >
              @if($looksHtml)
                {!! $qText !!}
              @else
                {!! nl2br(e($qText)) !!}
              @endif
            </div>
          </div>

          {{-- Optional separate image field --}}
          @if(!empty($q->question_image))
            <div data-define-skip>
              <img src="{{ asset('storage/'.$q->question_image) }}"
                   class="max-h-72 rounded-2xl border border-gray-200 shadow-sm" />
            </div>
          @endif

          {{-- TRUE / FALSE --}}
          @if($q->type === 'true_false')
            @php $current = $existingAnswer['value'] ?? null; @endphp

            <div class="grid sm:grid-cols-2 gap-3" data-define-skip>
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
            <div class="grid gap-3" data-define-skip>
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

                  @if(!empty($opt->option_image))
                    <img src="{{ asset('storage/'.$opt->option_image) }}"
                         class="w-12 h-12 rounded-xl border border-gray-200 object-cover">
                  @endif

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

            <div class="grid gap-3" data-define-skip>
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

                  @if(!empty($opt->option_image))
                    <img src="{{ asset('storage/'.$opt->option_image) }}"
                         class="w-12 h-12 rounded-xl border border-gray-200 object-cover">
                  @endif

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
            <div data-define-skip>
              <input
                type="text"
                name="answers[{{ $q->id }}]"
                value="{{ $val }}"
                placeholder="Type your answer..."
                class="w-full rounded-2xl border border-gray-200 px-4 py-3 bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none"
              >
            </div>
          @endif

          {{-- FILE --}}
          @if($q->type === 'file')
            <div class="rounded-2xl border border-gray-200 p-4 bg-gray-50" data-define-skip>
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
      <div class="flex justify-end" data-define-skip>
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