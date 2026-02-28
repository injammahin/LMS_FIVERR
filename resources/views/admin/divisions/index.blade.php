@extends('layouts.admin')

@section('title', 'Divisions')

@section('content')
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Divisions</h1>
                <p class="text-sm text-gray-500 dark:text-white/60">Manage divisions (Middle School, Secondary School, etc.)
                </p>
            </div>

            <a href="{{ route('admin.divisions.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">
                + Add Division
            </a>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- Card --}}
        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium">Image</th>
                            <th class="px-6 py-3 text-left font-medium">Name</th>
                            <th class="px-6 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($divisions as $division)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4">
                                    @if($division->image)
                                        <img src="{{ asset('storage/' . $division->image) }}"
                                            class="h-10 w-14 object-cover rounded-md border border-gray-200 dark:border-white/10"
                                            alt="{{ $division->name }}">
                                    @else
                                        <div
                                            class="h-10 w-14 rounded-md border border-dashed border-gray-300 dark:border-white/15 grid place-items-center text-gray-400 text-xs">
                                            No image
                                        </div>
                                    @endif
                                </td>

                                <td class="px-6 py-4 font-medium text-gray-800 dark:text-white">
                                    {{ $division->name }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.subjects.create', ['division_id' => $division->id]) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            + Subject
                                        </a>
                                        <a href="{{ route('admin.divisions.edit', $division->id) }}"
                                            class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm">
                                            Edit
                                        </a>

                                        <form action="{{ route('admin.divisions.destroy', $division->id) }}" method="POST"
                                            onsubmit="return confirm('Delete this division?');">
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
                                    No divisions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-200 dark:border-white/10">
                {{ $divisions->links() }}
            </div>
        </div>
    </div>
@endsection