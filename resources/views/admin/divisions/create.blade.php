@extends('layouts.admin')

@section('title', 'Add Division')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Division</h1>
        <p class="text-sm text-gray-500 dark:text-white/60">
            Create division (Elementary, Middle, Higher)
        </p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
        <form method="POST" action="{{ route('admin.divisions.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium mb-1">Division Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white"
                    placeholder="Elementary School">
                @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Level --}}
            <div>
                <label class="block text-sm font-medium mb-1">
                    Level (Order)
                </label>
                <input type="number" name="level" value="{{ old('level', 1) }}"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white">
                <p class="text-xs text-gray-400 mt-1">
                    Lower number = lower class (1 = Elementary, 2 = Middle, 3 = Higher)
                </p>
            </div>

            {{-- Promotion Percent --}}
            <div>
                <label class="block text-sm font-medium mb-1">
                    Promotion Percentage
                </label>
                <input type="number" name="promotion_percent"
                    value="{{ old('promotion_percent', 70) }}"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white">
                <p class="text-xs text-gray-400 mt-1">
                    Required completion % to auto promote
                </p>
            </div>

            {{-- Auto Promote --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" name="auto_promote" value="1"
                    checked
                    class="rounded border-gray-300">
                <label class="text-sm">Enable Auto Promotion</label>
            </div>

            {{-- Image --}}
            <div>
                <label class="block text-sm font-medium mb-1">Image (optional)</label>
                <input type="file" name="image"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white">
            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.divisions.index') }}"
                    class="px-4 py-2 text-sm border rounded-lg">
                    Cancel
                </a>

                <button type="submit"
                    class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Create Division
                </button>
            </div>
        </form>
    </div>
</div>
@endsection