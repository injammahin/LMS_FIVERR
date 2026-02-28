@extends('layouts.admin')

@section('title', 'Add Subjects')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6" x-data="{
                    names: @js(old('names', [''])), // keep old input if validation fails
                    add() { this.names.push(''); },
                    remove(i) {
                        if (this.names.length > 1) this.names.splice(i, 1);
                    }
                 }">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Subjects</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Select a division and add multiple subjects at once.
            </p>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.subjects.store') }}" class="space-y-5">
                @csrf

                {{-- Division --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Division</label>
                    <select name="division_id"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                        <option value="">Select Division</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ (string) old('division_id', $selectedDivisionId) === (string) $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Subject Names (multiple) --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Subject Names</label>

                        <button type="button" @click="add()"
                            class="inline-flex items-center gap-2 px-1 py-1 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                            <span class="text-xm leading-none">+</span>
                            Add more
                        </button>
                    </div>

                    @error('names') <p class="text-red-500 text-sm">{{ $message }}</p> @enderror

                    <template x-for="(val, i) in names" :key="i">
                        <div class="flex gap-2 items-start">
                            <div class="flex-1">
                                <input type="text" :name="`names[${i}]`" x-model="names[i]"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Math 1">

                                {{-- show validation error for specific index --}}
                                @if($errors->has('names.*'))
                                    <p class="text-red-500 text-sm mt-1" x-show="$el" x-text="''"></p>
                                @endif
                            </div>

                            <button type="button" @click="remove(i)"
                                class="mt-0.5 inline-flex items-center justify-center w-10 h-10 rounded-lg border border-gray-300 dark:border-white/10 hover:bg-red-50 dark:hover:bg-red-500/10 text-red-600"
                                title="Remove">
                                ×
                            </button>
                        </div>
                    </template>

                    {{-- Laravel indexed errors display (simple) --}}
                    @foreach($errors->get('names.*') as $key => $messages)
                        <p class="text-red-500 text-sm">{{ $messages[0] }}</p>
                    @endforeach

                    <p class="text-xs text-gray-400">
                        Tip: You can add 10–20 subjects quickly from here.
                    </p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.subjects.index') }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Save Subjects
                    </button>
                </div>
            </form>
        </div>

    </div>
@endsection