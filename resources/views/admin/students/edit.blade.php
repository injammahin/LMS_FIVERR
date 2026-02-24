@extends('layouts.admin')

@section('title', 'Edit Student')

@section('content')

<div class="max-w-2xl mx-auto space-y-6 text-sm"
     x-data="{ loginType: '{{ $student->username ? 'username' : 'email' }}' }">

    {{-- Header --}}
    <div>
        <h1 class="text-lg font-semibold text-gray-800">Edit Student</h1>
        <p class="text-xxs text-gray-500">Update student information</p>
    </div>

    {{-- Card --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="POST"
              action="{{ route('admin.students.update', $student->id) }}"
              class="space-y-5">

            @csrf
            @method('PUT')

            {{-- Name --}}
            <div>
                <label class="block text-xxs font-medium text-gray-600 mb-1">
                    Full Name
                </label>

                <input type="text"
                       name="name"
                       value="{{ old('name', $student->name) }}"
                       class="w-full px-3 py-2 text-xxs border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:outline-none">

                @error('name')
                    <p class="text-red-500 text-xxs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Login Type --}}
            <div>
                <label class="block text-xxs font-medium text-gray-600 mb-2">
                    Login Type
                </label>

                <div class="flex gap-6 text-xxs">

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                               value="email"
                               x-model="loginType"
                               class="text-blue-600">
                        <span>Email</span>
                    </label>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio"
                               value="username"
                               x-model="loginType"
                               class="text-blue-600">
                        <span>Username</span>
                    </label>

                </div>
            </div>

            {{-- Email --}}
            <div x-show="loginType === 'email'" x-transition>
                <label class="block text-xxs font-medium text-gray-600 mb-1">
                    Email Address
                </label>

                <input type="email"
                       name="email"
                       value="{{ old('email', $student->email) }}"
                       class="w-full px-3 py-2 text-xxs border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:outline-none">

                @error('email')
                    <p class="text-red-500 text-xxs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Username --}}
            <div x-show="loginType === 'username'" x-transition>
                <label class="block text-xxs font-medium text-gray-600 mb-1">
                    Username
                </label>

                <input type="text"
                       name="username"
                       value="{{ old('username', $student->username) }}"
                       class="w-full px-3 py-2 text-xxs border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:outline-none">

                @error('username')
                    <p class="text-red-500 text-xxs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div x-data="{ show: false }">
                <label class="block text-xxs font-medium text-gray-600 mb-1">
                    New Password (Leave empty to keep current)
                </label>

                <div class="relative">

                    <input :type="show ? 'text' : 'password'"
                        name="password"
                        class="w-full px-3 py-2 pr-10 text-xxs border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:outline-none">

                    {{-- Eye Button --}}
                    <button type="button"
                            @click="show = !show"
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">

                        <i x-show="!show" class="fa-solid fa-eye text-xxs"></i>
                        <i x-show="show" class="fa-solid fa-eye-slash text-xxs"></i>

                    </button>

                </div>

                @error('password')
                    <p class="text-red-500 text-xxs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-3 pt-3">

                <a href="{{ route('admin.students.index') }}"
                   class="px-4 py-2 text-xxs border border-gray-300 rounded-lg hover:bg-gray-100">
                    Cancel
                </a>

                <button type="submit"
                        class="px-5 py-2 text-xxs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Update Student
                </button>

            </div>

        </form>

    </div>

</div>

@endsection