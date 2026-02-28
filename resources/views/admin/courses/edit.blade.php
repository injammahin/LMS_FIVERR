@extends('layouts.admin')

@section('title', 'Edit Course')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Course</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">Update course info.</p>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                Please fix the errors below.
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.courses.update', $course->id) }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Division + Subject --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Division (display only) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division</label>
                        <input type="text" value="{{ $course->subject?->division?->name }}" readonly
                            class="w-full px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-lg bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <p class="text-xs text-gray-400 mt-1">Division is derived from Subject.</p>
                    </div>

                    {{-- Subject --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Subject</label>
                        <select name="subject_id"
                            class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}" {{ (string) old('subject_id', $course->subject_id) === (string) $s->id ? 'selected' : '' }}>
                                    {{ $s->division?->name }} → {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('subject_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Course Title</label>
                    <input type="text" name="title" value="{{ old('title', $course->title) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="draft" {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>Draft
                        </option>
                        <option value="published" {{ old('status', $course->status) === 'published' ? 'selected' : '' }}>
                            Published</option>
                    </select>
                    @error('status') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Description
                        (optional)</label>
                    <textarea name="description" rows="4"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('description', $course->description) }}</textarea>
                    @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Current Thumbnail --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Current Thumbnail</label>

                    @if($course->thumbnail)
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                class="h-16 w-24 object-cover rounded-lg border border-gray-200 dark:border-white/10"
                                alt="{{ $course->title }}">

                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-white/70">
                                <input type="checkbox" name="remove_thumbnail" value="1"
                                    class="rounded border-gray-300 dark:border-white/20">
                                Remove thumbnail
                            </label>
                        </div>
                    @else
                        <p class="text-sm text-gray-400">No thumbnail uploaded.</p>
                    @endif
                </div>

                {{-- New Thumbnail --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Upload New Thumbnail
                        (optional)</label>
                    <input type="file" name="thumbnail"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                    @error('thumbnail') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.courses.index', ['subject_id' => $course->subject_id]) }}"
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
    
@endsection