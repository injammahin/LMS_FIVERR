@extends('layouts.admin')

@section('title', 'Add Courses')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6" x-data="{
                            titles: @js(old('titles', [''])),
                            add(){ this.titles.push(''); },
                            remove(i){ if(this.titles.length>1) this.titles.splice(i,1); }
                         }">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Courses</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">Create multiple courses under one subject.</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.courses.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Subject select --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Subject</label>
                    <select name="subject_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="">Select Subject</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ (string) old('subject_id', $selectedSubjectId) === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->division?->name }} → {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('subject_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Courses titles --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Course Titles</label>
                        <button type="button" @click="add()"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                            <span class="text-sm leading-none">+</span> Add more
                        </button>
                    </div>

                    <template x-for="(val, i) in titles" :key="i">
                        <div class="flex gap-2">
                            <input type="text" :name="`titles[${i}]`" x-model="titles[i]"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                placeholder="Unit 1,2,3...">

                            <button type="button" @click="remove(i)"
                                class="w-10 h-10 rounded-lg border border-gray-300 dark:border-white/10 hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600"
                                title="Remove">×</button>
                        </div>
                    </template>

                    @foreach($errors->get('titles.*') as $msgs)
                        <p class="text-red-500 text-sm">{{ $msgs[0] }}</p>
                    @endforeach
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="draft" {{ old('status', 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                    @error('status') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description (optional; applies to all in bulk create) --}}
                {{-- <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Description
                        (optional)</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">{{ old('description') }}</textarea>
                </div> --}}

                {{-- Thumbnail (optional; same for all in bulk create) --}}
                {{-- <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Thumbnail
                        (optional)</label>
                    <input type="file" name="thumbnail"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                    @error('thumbnail') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div> --}}

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.courses.index') }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Save Courses
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection