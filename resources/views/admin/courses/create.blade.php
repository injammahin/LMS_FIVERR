@extends('layouts.admin')

@section('title', 'Create Course')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
            Create New Course
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Add a new course to the LMS
        </p>
    </div>

    {{-- Form Card --}}
    <div class="bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-sm">

        <form class="space-y-6">

            {{-- Course Title --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Course Title
                </label>
                <input type="text"
                       class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                       placeholder="Enter course title">
            </div>

            {{-- Grade --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Grade
                </label>
                <select class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                    <option>Select Grade</option>
                    <option>Grade 1</option>
                    <option>Grade 2</option>
                    <option>Grade 3</option>
                </select>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Description
                </label>
                <textarea rows="4"
                          class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800"
                          placeholder="Course description..."></textarea>
            </div>

            {{-- Thumbnail --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Course Thumbnail
                </label>
                <input type="file"
                       class="w-full text-sm">
            </div>

            {{-- Status --}}
            <div>
                <label class="block text-sm font-medium mb-2">
                    Status
                </label>
                <select class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                    <option>Draft</option>
                    <option>Published</option>
                </select>
            </div>

            {{-- Buttons --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ url('/admin/courses') }}"
                   class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100">
                    Cancel
                </a>

                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition">
                    Save Course
                </button>
            </div>

        </form>

    </div>

</div>

@endsection