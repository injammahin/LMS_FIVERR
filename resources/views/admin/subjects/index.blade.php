@extends('layouts.admin')

@section('title', 'Subjects')

@section('content')
    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Subjects</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">Subjects are created under divisions.</p>
            </div>

            <a href="{{ route('admin.subjects.create', ['division_id' => $divisionId]) }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Subject
            </a>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <form method="GET" class="flex flex-col md:flex-row gap-3 md:items-end">
                <div class="w-full md:w-80">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Filter by
                        Division</label>
                    <select name="division_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="">All Divisions</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ (string) $divisionId === (string) $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <a href="{{ route('admin.subjects.index') }}"
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
                            <th class="px-6 py-3 text-left font-medium">Division</th>
                            <th class="px-6 py-3 text-left font-medium">Subject Name</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($subjects as $subject)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4 text-gray-700 dark:text-white/80">
                                    {{ $subject->division?->name }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                                    {{ $subject->name }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.courses.create', ['subject_id' => $subject->id]) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            + Course
                                        </a>
                                        <a href="{{ route('admin.subjects.edit', $subject->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this subject?');">
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
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">
                                    No subjects found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $subjects->links() }}
            </div>
        </div>
    </div>
@endsection