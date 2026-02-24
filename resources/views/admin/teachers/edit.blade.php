@extends('layouts.admin')

@section('title','Edit Teacher')

@section('content')

<div class="max-w-2xl mx-auto space-y-6 text-xxs"
     x-data="{ loginType: '{{ $teacher->username ? 'username':'email' }}', show: false }">

    <h1 class="text-lg font-semibold text-gray-800">Edit Teacher</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="POST"
              action="{{ route('admin.teachers.update',$teacher->id) }}"
              class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block mb-1">Full Name</label>
                <input type="text" name="name"
                       value="{{ $teacher->name }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block mb-2">Login Type</label>
                <div class="flex gap-6">
                    <label><input type="radio" value="email" x-model="loginType"> Email</label>
                    <label><input type="radio" value="username" x-model="loginType"> Username</label>
                </div>
            </div>

            <div x-show="loginType==='email'">
                <label>Email</label>
                <input type="email" name="email"
                       value="{{ $teacher->email }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div x-show="loginType==='username'">
                <label>Username</label>
                <input type="text" name="username"
                       value="{{ $teacher->username }}"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block mb-1">
                    New Password <span class="text-gray-400">(Leave blank to keep current)</span>
                </label>

                <div class="relative">
                    <input :type="show?'text':'password'"
                           name="password"
                           class="w-full px-3 py-2 pr-10 border rounded-lg">

                    <button type="button"
                            @click="show=!show"
                            class="absolute right-4 top-3 text-gray-400">
                        <i x-show="!show" class="fa-solid fa-eye"></i>
                        <i x-show="show" class="fa-solid fa-eye-slash"></i>
                    </button>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.teachers.index') }}"
                   class="px-4 py-2 border rounded-lg text-xxs">
                    Cancel
                </a>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xxs">
                    Update Teacher
                </button>
            </div>

        </form>

    </div>

</div>

@endsection