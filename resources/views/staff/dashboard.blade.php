@extends('layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Staff Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Hi, {{ auth()->user()->name }} 👋</p>
            </div>

            <span class="inline-flex items-center rounded-full bg-purple-50 px-3 py-1 text-sm font-medium text-purple-700">
                Role: {{ auth()->user()->role }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Students</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">540</p>
                <p class="text-xs text-gray-500 mt-2">Demo number</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Teachers</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">38</p>
                <p class="text-xs text-gray-500 mt-2">Demo number</p>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">Support Tickets</p>
                <p class="text-3xl font-bold text-gray-900 mt-2">9</p>
                <p class="text-xs text-gray-500 mt-2">Demo number</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
                <div class="mt-4 space-y-3">
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">New student registered</p>
                        <p class="text-sm text-gray-500">Username: student_1001</p>
                    </div>
                    <div class="rounded-xl bg-gray-50 p-4">
                        <p class="font-medium text-gray-900">Teacher profile updated</p>
                        <p class="text-sm text-gray-500">Teacher: Mr. Karim</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-900">Quick Links</h2>
                <div class="mt-4 space-y-3">
                    <a href="{{ route('admin.students.index') }}"
                        class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900">Manage Students</p>
                        <p class="text-sm text-gray-500">Go to students list</p>
                    </a>

                    <a href="{{ route('admin.teachers.index') }}"
                        class="block rounded-xl border border-gray-200 p-4 hover:bg-gray-50">
                        <p class="font-medium text-gray-900">Manage Teachers</p>
                        <p class="text-sm text-gray-500">Go to teachers list</p>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection