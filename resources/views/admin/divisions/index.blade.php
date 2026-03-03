@extends('layouts.admin')

@section('title', 'Divisions')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Divisions</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Manage school progression levels
            </p>
        </div>

        <a href="{{ route('admin.divisions.create') }}"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            + Add Division
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 text-green-700 px-4 py-3 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-xl border overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                    <th class="px-6 py-3 text-left">Level</th>
                    <th class="px-6 py-3 text-left">Image</th>
                    <th class="px-6 py-3 text-left">Name</th>
                    <th class="px-6 py-3 text-left">Promotion</th>
                    <th class="px-6 py-3 text-right">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @foreach($divisions->sortBy('level') as $division)
                <tr>
                    <td class="px-6 py-4 font-semibold">
                        {{ $division->level }}
                    </td>

                    <td class="px-6 py-4">
                        @if($division->image)
                            <img src="{{ asset('storage/' . $division->image) }}"
                                class="h-10 w-14 object-cover rounded-md">
                        @else
                            <span class="text-gray-400 text-xs">No Image</span>
                        @endif
                    </td>

                    <td class="px-6 py-4 font-medium">
                        {{ $division->name }}
                    </td>

                    <td class="px-6 py-4">
                        @if($division->auto_promote)
                            <span class="text-emerald-600 font-medium">
                                {{ $division->promotion_percent }}% Auto
                            </span>
                        @else
                            <span class="text-gray-400">
                                Manual
                            </span>
                        @endif
                    </td>

                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.divisions.edit', $division->id) }}"
                            class="px-3 py-1 border rounded-lg text-sm">
                            Edit
                        </a>

                        <form action="{{ route('admin.divisions.destroy', $division->id) }}"
                            method="POST"
                            class="inline-block"
                            onsubmit="return confirm('Delete this division?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 border border-red-200 text-red-600 rounded-lg text-sm">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="p-4 border-t">
            {{ $divisions->links() }}
        </div>
    </div>
</div>
@endsection