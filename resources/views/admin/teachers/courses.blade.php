@extends('layouts.admin')

@section('title', 'Assign Courses')

@section('content')
<div class="space-y-6"
     x-data="{
        selected: @js(old('course_ids', $assigned)),
        toggleAll(on, ids){
            if(on){
                ids.forEach(id => { if(!this.selected.includes(id)) this.selected.push(id) })
            }else{
                this.selected = this.selected.filter(id => !ids.includes(id))
            }
        }
     }">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Assign Courses</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Teacher: <span class="font-medium">{{ $teacher->name }}</span>
                <span class="text-xs text-gray-400">({{ $teacher->email ?? $teacher->username }})</span>
            </p>
        </div>

        <a href="{{ route('admin.teachers.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
            Back to Teachers
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-4">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-3 md:items-end">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division</label>
                <select name="division_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $d)
                        <option value="{{ $d->id }}" {{ (string)$divisionId === (string)$d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Subject</label>
                <select name="subject_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" {{ (string)$subjectId === (string)$s->id ? 'selected' : '' }}>
                            {{ $s->division?->name }} → {{ $s->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Search</label>
                <input name="search" value="{{ $search }}"
                       placeholder="Course title..."
                       class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                    Filter
                </button>
                <a href="{{ route('admin.teachers.courses.edit', $teacher->id) }}"
                   class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Assign Form --}}
    <form method="POST" action="{{ route('admin.teachers.courses.update', $teacher->id) }}">
        @csrf

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">

            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-white/10">
                <div class="text-sm text-gray-600 dark:text-white/70">
                    Selected: <span class="font-semibold" x-text="selected.length"></span>
                </div>

                @php
                    $pageCourseIds = $courses->pluck('id')->toArray();
                @endphp

                <div class="flex gap-2">
                    <button type="button"
                            @click="toggleAll(true, @js($pageCourseIds))"
                            class="px-3 py-1.5 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Select page
                    </button>
                    <button type="button"
                            @click="toggleAll(false, @js($pageCourseIds))"
                            class="px-3 py-1.5 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Unselect page
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Assign</th>
                            <th class="px-6 py-3 text-left font-medium">Course</th>
                            <th class="px-6 py-3 text-left font-medium">Division / Subject</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($courses as $course)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    <input type="checkbox"
                                           :value="{{ $course->id }}"
                                           x-model="selected"
                                           class="rounded">
                                </td>

                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-800 dark:text-white">{{ $course->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">{{ $course->slug }}</div>
                                </td>

                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    <div>{{ $course->subject?->division?->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-white/60">{{ $course->subject?->name }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-6 py-10 text-center text-gray-400">
                                    No courses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10 flex items-center justify-between">
                <div>
                    {{ $courses->links() }}
                </div>

                {{-- hidden inputs for selected --}}
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="course_ids[]" :value="id">
                </template>

                <button class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Save Assigned Courses
                </button>
            </div>
        </div>
    </form>

</div>
@endsection