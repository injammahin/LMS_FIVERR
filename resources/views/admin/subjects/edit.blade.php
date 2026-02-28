@extends('layouts.admin')

@section('title', 'Edit Subject')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Subject</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">Update subject details.</p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.subjects.update', $subject->id) }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Division --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division</label>
                    <select name="division_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ (string) old('division_id', $subject->division_id) === (string) $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Subject Name</label>
                    <input type="text" name="name" value="{{ old('name', $subject->name) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.subjects.index') }}"
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