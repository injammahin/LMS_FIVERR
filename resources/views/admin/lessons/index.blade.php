@extends('layouts.admin')

@section('title', 'Lessons')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Lessons</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Course: <span class="font-medium">{{ $course->title }}</span>
            </p>
        </div>

        <div class="flex items-center gap-2">
            {{-- ✅ Back to Courses --}}
            <a href="{{ route('admin.courses.index') }}"
               class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back to Courses
            </a>

            {{-- Add Lesson --}}
            <a href="{{ route('admin.courses.lessons.create', $course->id) }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                + Add Lesson
            </a>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold w-[90px]">Pos</th>
                        <th class="px-6 py-4 text-left font-semibold">Title</th>
                        <th class="px-6 py-4 text-left font-semibold w-[140px]">Video</th>
                        <th class="px-6 py-4 text-right font-semibold w-[220px]">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($lessons as $lesson)
                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                            {{-- Position --}}
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white font-bold">
                                    {{ $lesson->position }}
                                </div>
                            </td>

                            {{-- Title --}}
                            <td class="px-6 py-5">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $lesson->title }}
                                </div>

                                {{-- Optional: show short description indicator --}}
                                @if(!empty($lesson->description))
                                    <div class="mt-1 text-xs text-gray-500 dark:text-white/60">
                                        Has description
                                    </div>
                                @endif
                            </td>

                            {{-- Video --}}
                            <td class="px-6 py-5">
                                @if($lesson->video_url)
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/70">
                                        No
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.courses.lessons.edit', [$course->id, $lesson->id]) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.courses.lessons.destroy', [$course->id, $lesson->id]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this lesson?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10 text-sm">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="text-gray-400">
                                    <div class="text-base font-semibold">No lessons found</div>
                                    <div class="text-sm mt-1">Click “Add Lesson” to create your first lesson.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-200 dark:border-white/10">
            {{ $lessons->links() }}
        </div>
    </div>

</div>
@endsection