@extends('layouts.admin')

@section('title', 'Edit Option')

@section('content')
@php
    use Illuminate\Support\Str;

    $questionHtml = $question->question ?? '';
    $questionPlain = Str::of($questionHtml)
        ->replace('&nbsp;', ' ')
        ->stripTags()
        ->squish()
        ->toString();

    $questionPreview = Str::limit($questionPlain, 140);
@endphp

<div class="max-w-3xl mx-auto space-y-6" x-data="{ showPreview: false }">

    <div class="flex items-start justify-between gap-4">
        <div class="space-y-2">
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Edit Option</h1>

            <div class="text-sm text-gray-500 dark:text-white/60 space-y-1">
                <div>
                    Quiz: <span class="font-medium text-gray-800 dark:text-white/80">{{ $question->quiz?->title }}</span>
                </div>

                <div class="flex items-start gap-2">
                    <span class="shrink-0">Question:</span>
                    <span class="font-medium text-gray-800 dark:text-white/80">
                        {{ $questionPreview ?: '—' }}
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80">
                        Type: {{ Str::headline($question->type) }}
                    </span>

                    <button type="button"
                        @click="showPreview = !showPreview"
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold border border-gray-300 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-gray-700 dark:text-white/80">
                        <span x-text="showPreview ? 'Hide Preview' : 'Preview'"></span>
                    </button>
                </div>

                {{-- Rendered HTML preview (perfect view) --}}
                <div x-show="showPreview" x-cloak
                     class="questionPreview mt-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-4">
                    {!! $questionHtml !!}
                </div>
            </div>
        </div>

        <a href="{{ route('admin.questions.options.index', $question->id) }}"
           class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 p-6 shadow-sm">
        <form method="POST"
              action="{{ route('admin.questions.options.update', [$question->id, $option->id]) }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                    Option Text (optional)
                </label>
                <textarea name="option_text" rows="3"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                >{{ old('option_text', $option->option_text) }}</textarea>
                @error('option_text') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Current Image --}}
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Current Image</label>

                @if($option->option_image)
                    <div class="flex items-center gap-4">
                        <img src="{{ asset('storage/'.$option->option_image) }}"
                             class="h-16 w-24 object-cover rounded-lg border border-gray-200 dark:border-white/10"
                             alt="Option image">

                        <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-white/70">
                            <input type="checkbox" name="remove_image" value="1" class="rounded">
                            Remove image
                        </label>
                    </div>
                @else
                    <p class="text-sm text-gray-400">No image.</p>
                @endif
            </div>

            {{-- Upload new image --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                    Upload New Image (optional)
                </label>
                <input type="file" name="option_image" accept="image/png,image/jpeg,image/webp"
                       class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                @error('option_image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Position</label>
                    <input type="number" name="position" min="1"
                           value="{{ old('position', $option->position) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('position') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/80 pt-6">
                    <input type="checkbox" name="is_correct" value="1" class="rounded"
                           {{ old('is_correct', $option->is_correct) ? 'checked' : '' }}>
                    Mark as Correct
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.questions.options.index', $question->id) }}"
                   class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                    Cancel
                </a>

                <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .questionPreview img { max-width: 100%; height: auto; display: block; }
    .questionPreview * { max-width: 100%; }
</style>
@endsection