@extends('layouts.admin')

@section('title', 'Edit Student')

@section('content')
    <div class="max-w-2xl mx-auto space-y-6 text-xxs"
        x-data="{ loginType: '{{ $student->username ? 'username' : 'email' }}', show: false }">

        <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Student</h1>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form method="POST" action="{{ route('admin.students.update', $student->id) }}" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Division --}}
                <div>
                    <label class="block mb-1 text-gray-700 dark:text-white/80">Division</label>
                    <select name="division_id"
                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-950 dark:text-white dark:border-white/10">
                        <option value="">Select Division</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ (string) old('division_id', $student->division_id) === (string) $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Name --}}
                <div>
                    <label class="block mb-1 text-gray-700 dark:text-white/80">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $student->name) }}"
                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-950 dark:text-white dark:border-white/10">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Login Type --}}
                <div>
                    <label class="block mb-2 text-gray-700 dark:text-white/80">Login Type</label>
                    <div class="flex gap-6 text-gray-700 dark:text-white/80">
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" value="email" x-model="loginType">
                            Email
                        </label>
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" value="username" x-model="loginType">
                            Username
                        </label>
                    </div>
                </div>

                {{-- Email --}}
                <div x-show="loginType==='email'">
                    <label class="block mb-1 text-gray-700 dark:text-white/80">Email</label>
                    <input type="email" name="email" value="{{ old('email', $student->email) }}"
                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-950 dark:text-white dark:border-white/10">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Username --}}
                <div x-show="loginType==='username'">
                    <label class="block mb-1 text-gray-700 dark:text-white/80">Username</label>
                    <input type="text" name="username" value="{{ old('username', $student->username) }}"
                        class="w-full px-3 py-2 border rounded-lg bg-white dark:bg-slate-950 dark:text-white dark:border-white/10">
                    @error('username') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block mb-1 text-gray-700 dark:text-white/80">
                        New Password <span class="text-gray-400">(Leave blank to keep current)</span>
                    </label>

                    <div class="relative">
                        <input :type="show ? 'text':'password'" name="password"
                            class="w-full px-3 py-2 pr-10 border rounded-lg bg-white dark:bg-slate-950 dark:text-white dark:border-white/10">

                        <button type="button" @click="show=!show" class="absolute right-4 top-3 text-gray-400">
                            <i x-show="!show" class="fa-solid fa-eye"></i>
                            <i x-show="show" class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.students.index') }}"
                        class="px-4 py-2 border rounded-lg text-xxs dark:text-white dark:border-white/10">
                        Cancel
                    </a>
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xxs">
                        Update Student
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection