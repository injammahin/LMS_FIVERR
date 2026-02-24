@extends('layouts.admin')

@section('title', 'Courses')

@section('content')

<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                Courses
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Manage all LMS courses
            </p>
        </div>

        <a href="{{ url('/admin/courses/create') }}"
           class="px-5 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-700 transition">
            + Create Course
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 p-4 rounded-2xl shadow-sm">
        <div class="grid md:grid-cols-3 gap-4">
            <input type="text"
                   placeholder="Search courses..."
                   class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800">

            <select class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                <option>All Grades</option>
                <option>Grade 1</option>
                <option>Grade 2</option>
            </select>

            <select class="w-full px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-700 dark:bg-slate-800">
                <option>Status</option>
                <option>Published</option>
                <option>Draft</option>
            </select>
        </div>
    </div>

    {{-- Courses Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm overflow-hidden">

        <table class="min-w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800 text-left text-slate-500 dark:text-slate-400">
                <tr>
                    <th class="px-6 py-4">Course</th>
                    <th>Grade</th>
                    <th>Lessons</th>
                    <th>Status</th>
                    <th class="text-right pr-6">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">

                <tr>
                    <td class="px-6 py-4 font-medium">
                        Mathematics Basics
                    </td>
                    <td>Grade 1</td>
                    <td>12</td>
                    <td>
                        <span class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded-full">
                            Published
                        </span>
                    </td>
                    <td class="text-right pr-6 space-x-2">
                        <button class="text-blue-600 hover:underline text-sm">Edit</button>
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </td>
                </tr>

                <tr>
                    <td class="px-6 py-4 font-medium">
                        Science Exploration
                    </td>
                    <td>Grade 2</td>
                    <td>8</td>
                    <td>
                        <span class="px-3 py-1 text-xs bg-yellow-100 text-yellow-700 rounded-full">
                            Draft
                        </span>
                    </td>
                    <td class="text-right pr-6 space-x-2">
                        <button class="text-blue-600 hover:underline text-sm">Edit</button>
                        <button class="text-red-600 hover:underline text-sm">Delete</button>
                    </td>
                </tr>

            </tbody>
        </table>

    </div>

</div>

@endsection