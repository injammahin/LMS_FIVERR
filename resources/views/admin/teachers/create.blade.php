@extends('layouts.admin')

@section('title','Add Teacher')

@section('content')
<div class="max-w-2xl mx-auto space-y-6 text-xxs"
     x-data="{ loginType: '{{ old('login_type','email') }}', show: false }">

    <h1 class="text-lg font-semibold text-gray-800">Add Teacher</h1>

    {{-- Errors --}}
    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full px-3 py-2 border rounded-lg">
                @error('name') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-2">Login Type</label>
                <div class="flex gap-6">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio"
                               name="login_type"
                               value="email"
                               x-model="loginType"
                               {{ old('login_type','email') === 'email' ? 'checked' : '' }}>
                        Email
                    </label>

                    <label class="inline-flex items-center gap-2">
                        <input type="radio"
                               name="login_type"
                               value="username"
                               x-model="loginType"
                               {{ old('login_type') === 'username' ? 'checked' : '' }}>
                        Username
                    </label>
                </div>
                @error('login_type') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div x-show="loginType==='email'">
                <label class="block mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full px-3 py-2 border rounded-lg">
                @error('email') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Username --}}
            <div x-show="loginType==='username'">
                <label class="block mb-1">Username</label>
                <input type="text" name="username" value="{{ old('username') }}"
                       class="w-full px-3 py-2 border rounded-lg">
                @error('username') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block mb-1">Password</label>
                <div class="relative">
                    <input :type="show ? 'text':'password'" name="password"
                           class="w-full px-3 py-2 pr-10 border rounded-lg">

                    <button type="button" @click="show=!show"
                            class="absolute right-4 top-3 text-gray-400">
                        <i x-show="!show" class="fa-solid fa-eye"></i>
                        <i x-show="show" class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>
                @error('password') <p class="text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.teachers.index') }}"
                   class="px-4 py-2 border rounded-lg text-xxs">
                    Cancel
                </a>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xxs">
                    Create Teacher
                </button>
            </div>
        </form>
    </div>
</div>
@endsection