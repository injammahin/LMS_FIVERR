@extends('layouts.admin')

@section('title', 'Add Option')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Option</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Quiz: <span class="font-medium">{{ $question->quiz?->title }}</span><br>
                    Question: <span
                        class="font-medium">{{ \Illuminate\Support\Str::limit($question->question, 120) }}</span>
                </p>
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

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.questions.options.store', $question->id) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Option Text
                        (optional)</label>
                    <textarea name="option_text" rows="3"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">{{ old('option_text') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Option Image
                        (optional)</label>
                    <input type="file" name="option_image"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Position</label>
                        <input type="number" name="position" min="1" value="{{ old('position', 1) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>

                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/80 pt-6">
                        <input type="checkbox" name="is_correct" value="1" class="rounded" {{ old('is_correct') ? 'checked' : '' }}>
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
                        Create Option
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection