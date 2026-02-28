@extends('layouts.admin')

@section('title', 'Courses')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Courses</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">Manage courses under subjects.</p>
            </div>

            <a href="{{ route('admin.courses.create', ['subject_id' => $subjectId]) }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Courses
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 md:items-end">

                {{-- Division --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division</label>
                    <select name="division_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="">All Divisions</option>
                        @foreach($divisions as $d)
                            <option value="{{ $d->id }}" {{ (string) $divisionId === (string) $d->id ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Subject --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Subject</label>
                    <select name="subject_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="">All Subjects</option>
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ (string) $subjectId === (string) $s->id ? 'selected' : '' }}>
                                {{ $s->division?->name }} → {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Reset --}}
                <div class="flex md:justify-end">
                    <a href="{{ route('admin.courses.index') }}"
                        class="inline-flex px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            {{-- <th class="px-6 py-3 text-left font-medium">Thumbnail</th> --}}
                            <th class="px-6 py-3 text-left font-medium">Course</th>
                            <th class="px-6 py-3 text-left font-medium">Division / Subject</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($courses as $course)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">

                                {{-- Thumbnail --}}
                                {{-- <td class="px-6 py-4">
                                    @if($course->thumbnail)
                                    <img src="{{ asset('storage/' . $course->thumbnail) }}"
                                        class="h-10 w-14 object-cover rounded-md border border-gray-200 dark:border-white/10"
                                        alt="{{ $course->title }}">
                                    @else
                                    <div
                                        class="h-10 w-14 rounded-md border border-dashed border-gray-300 dark:border-white/15 grid place-items-center text-gray-400 text-xs">
                                        No image
                                    </div>
                                    @endif
                                </td> --}}

                                {{-- Title --}}
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $course->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">{{ $course->slug }}</div>
                                </td>

                                {{-- Division / Subject --}}
                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    <div class="text-sm">{{ $course->subject?->division?->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">{{ $course->subject?->name }}</div>
                                </td>

                                {{-- Status --}}
                                <td class="px-6 py-4">
                                    @if($course->status === 'published')
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-300">
                                            Published
                                        </span>
                                    @else
                                        <span
                                            class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                            Draft
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.courses.assignments.index', $course->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Assignments
                                        </a>
                                        <a href="{{ route('admin.courses.quizzes.index', $course->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Quizzes
                                        </a>

                                        <a href="{{ route('admin.courses.lessons.index', $course->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Lessons
                                        </a>

                                        <a href="{{ route('admin.courses.edit', $course->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.courses.destroy', $course->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this course?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm">
                                                Delete
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                    No courses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $courses->links() }}
            </div>
        </div>

    </div>
@endsection