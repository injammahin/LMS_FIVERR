@extends('layouts.admin')

@section('title', 'Add Question')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{ type: '{{ old('type', 'text') }}' }">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Question</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Quiz: <span class="font-medium">{{ $quiz->title }}</span>
                </p>
            </div>

            <a href="{{ route('admin.quizzes.questions.index', $quiz->id) }}"
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
            <form method="POST" action="{{ route('admin.quizzes.questions.store', $quiz->id) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Question Type</label>
                    <select name="type" x-model="type"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="text">Text Answer</option>
                        <option value="file">File Upload Answer</option>
                        <option value="single_choice">Single Choice (Radio)</option>
                        <option value="multiple_choice">Multiple Choice (Checkbox)</option>
                        <option value="true_false">True / False</option>
                    </select>
                </div>

                {{-- Question --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Question</label>
                    <textarea name="question" rows="4"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        placeholder="Write the question...">{{ old('question') }}</textarea>
                </div>

                {{-- Question Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Question Image
                        (optional)</label>
                    <input type="file" name="question_image"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                </div>

                {{-- Marks + Position --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Marks</label>
                        <input type="number" name="marks" min="1" value="{{ old('marks', 1) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Position</label>
                        <input type="number" name="position" min="1" value="{{ old('position', 1) }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    </div>

                    <div class="flex items-center gap-2 pt-6">
                        <input type="checkbox" name="is_required" value="1" class="rounded" {{ old('is_required', 1) ? 'checked' : '' }}>
                        <label class="text-sm text-gray-700 dark:text-white/80">Required</label>
                    </div>
                </div>

                {{-- Explanation --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Explanation
                        (optional)</label>
                    <textarea name="explanation" rows="3"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        placeholder="Shown after submit (optional)">{{ old('explanation') }}</textarea>
                </div>

                {{-- Note --}}
                <div class="text-sm text-gray-500 dark:text-white/60">
                    <p>
                        For <b>single_choice</b>, <b>multiple_choice</b>, and <b>true_false</b>, after creating the
                        question,
                        go to <b>Options</b> and set correct answers.
                    </p>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.quizzes.questions.index', $quiz->id) }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Question
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection