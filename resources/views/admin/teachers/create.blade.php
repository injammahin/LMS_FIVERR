@extends('layouts.admin')

@section('title','Add Teacher')

@section('content')

<div class="max-w-2xl mx-auto space-y-6 text-xxs"
     x-data="{ loginType: 'email', show: false }">

    <h1 class="text-lg font-semibold text-gray-800">Add Teacher</h1>

    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <form method="POST" action="{{ route('admin.teachers.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block mb-1">Full Name</label>
                <input type="text" name="name"
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
                <label class="block mb-1">Email</label>
                <input type="email" name="email"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div x-show="loginType==='username'">
                <label class="block mb-1">Username</label>
                <input type="text" name="username"
                       class="w-full px-3 py-2 border rounded-lg">
            </div>

            <div>
                <label class="block mb-1">Password</label>

                <div class="relative">
                    <input :type="show ? 'text':'password'"
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
                    Create Teacher
                </button>
            </div>

        </form>

    </div>

</div>

@endsection