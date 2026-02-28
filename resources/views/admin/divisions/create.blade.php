@extends('layouts.admin')

@section('title', 'Add Division')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Division</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">Create a new division</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.divisions.store') }}" enctype="multipart/form-data"
                class="space-y-5">
                @csrf

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division Name</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                        placeholder="Middle School">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                
                {{-- Image --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Image (optional)</label>
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
                        Create Division
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection