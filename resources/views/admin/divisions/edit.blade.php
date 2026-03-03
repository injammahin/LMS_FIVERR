@extends('layouts.admin')

@section('title', 'Edit Division')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <div>
        <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Division</h1>
        <p class="text-sm text-gray-500 dark:text-white/60">
            Update division details
        </p>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
        <form method="POST" action="{{ route('admin.divisions.update', $division->id) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label class="block text-sm font-medium mb-1">Division Name</label>
                <input type="text" name="name"
                    value="{{ old('name', $division->name) }}"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white">
            </div>

            {{-- Level --}}
            <div>
                <label class="block text-sm font-medium mb-1">Level</label>
                <input type="number" name="level"
                    value="{{ old('level', $division->level) }}"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white">
            </div>

            {{-- Promotion Percent --}}
            <div>
                <label class="block text-sm font-medium mb-1">Promotion Percentage</label>
                <input type="number" name="promotion_percent"
                    value="{{ old('promotion_percent', $division->promotion_percent) }}"
                    class="w-full px-3 py-2 text-sm border rounded-lg dark:bg-slate-950 dark:text-white">
            </div>

            {{-- Auto Promote --}}
            <div class="flex items-center gap-2">
                <input type="checkbox" name="auto_promote" value="1"
                    {{ $division->auto_promote ? 'checked' : '' }}
                    class="rounded border-gray-300">
                <label class="text-sm">Enable Auto Promotion</label>
            </div>

            {{-- Current Image --}}
            @if($division->image)
            <div class="space-y-2">
                <img src="{{ asset('storage/' . $division->image) }}"
                    class="h-16 w-24 object-cover rounded-lg border">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remove_image" value="1">
                    Remove image
                </label>
            </div>
            @endif

            {{-- Upload New Image --}}
            <div>
                <label class="block text-sm font-medium mb-1">Upload New Image</label>
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
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection