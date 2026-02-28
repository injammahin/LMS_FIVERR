@extends('layouts.admin')

@section('title', 'Edit Division')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Division</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">Update division details</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.divisions.update', $division->id) }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division Name</label>
                    <input type="text" name="name" value="{{ old('name', $division->name) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Current Image --}}
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Current Image</label>

                    @if($division->image)
                        <div class="flex items-center gap-4">
                            <img src="{{ asset('storage/' . $division->image) }}"
                                class="h-16 w-24 object-cover rounded-lg border border-gray-200 dark:border-white/10"
                                alt="{{ $division->name }}">

                            <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-white/70">
                                <input type="checkbox" name="remove_image" value="1"
                                    class="rounded border-gray-300 dark:border-white/20">
                                Remove image
                            </label>
                        </div>
                    @else
                        <p class="text-sm text-gray-400">No image uploaded.</p>
                    @endif
                </div>

                {{-- New Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Upload New Image
                        (optional)</label>
                    <input type="file" name="image"
                        class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                    @error('image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.divisions.index') }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection