@extends('layouts.admin')

@section('title', 'All Students')

@section('content')

<div class="space-y-6 text-xxs"
     x-data="{ openDelete: false, deleteUrl: '' }">

    {{-- Page Header --}}
    <div class="flex justify-between items-center">
        <h1 class="text-lg font-semibold text-gray-800">All Students</h1>

        <a href="{{ route('admin.students.create') }}"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-xxs">
            + Add Student
        </a>
    </div>


    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                <i class="fa-solid fa-users text-xxs"></i>
            </div>
            <div>
                <p class="text-xxs text-gray-500">Total Students</p>
                <p class="font-semibold text-gray-800">{{ $students->total() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                <i class="fa-solid fa-user-check text-xxs"></i>
            </div>
            <div>
                <p class="text-xxs text-gray-500">This Page</p>
                <p class="font-semibold text-gray-800">{{ $students->count() }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-600">
                <i class="fa-solid fa-clock text-xxs"></i>
            </div>
            <div>
                <p class="text-xxs text-gray-500">Latest Added</p>
                <p class="font-semibold text-gray-800">
                    {{ optional($students->first())->created_at?->format('d M') ?? '-' }}
                </p>
            </div>
        </div>

    </div>


    {{-- Main Card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Top Bar --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between p-4 border-b border-gray-200">

            <div>
                <h2 class="text-xxs font-medium text-gray-700">Student List</h2>
                <p class="text-xxs text-gray-400">Your most recent students</p>
            </div>

            <form method="GET" class="flex gap-3 mt-3 md:mt-0">

                <div class="relative">
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by name..."
                           class="pl-8 pr-3 py-2 text-xxs border border-gray-300 rounded-lg focus:ring-1 focus:ring-blue-500 focus:outline-none">

                    <i class="fa fa-search absolute left-2 top-2.5 text-gray-400 text-xxs"></i>
                </div>

                <select name="per_page"
                        onchange="this.form.submit()"
                        class="px-3 py-2 text-xxs border border-gray-300 rounded-lg focus:outline-none">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>

            </form>

        </div>


        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="min-w-full text-xxs text-gray-700">

                <thead class="bg-gray-50 text-gray-500 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="px-6 py-3 text-left font-medium">ID</th>
                        <th class="px-6 py-3 text-left font-medium">Name</th>
                        <th class="px-6 py-3 text-left font-medium">Login</th>
                        <th class="px-6 py-3 text-left font-medium">Password</th>
                        <th class="px-6 py-3 text-left font-medium">Joined</th>
                        <th class="px-6 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200">

                    @forelse($students as $student)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-6 py-4 font-medium text-gray-800">
                            {{ $student->id }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $student->name }}
                        </td>

                        <td class="px-6 py-4">
                            {{ $student->email ?? $student->username }}
                        </td>

                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-gray-100 rounded text-[10px]">
                                {{ $student->plain_password }}
                            </span>
                        </td>

                        <td class="px-6 py-4">
                            {{ $student->created_at->format('d M Y') }}
                        </td>

                        <td class="px-6 py-4 text-right space-x-2">

                            <a href="{{ route('admin.students.edit',$student->id) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-100">
                                <i class="fa-solid fa-edit text-gray-600 text-xxs"></i>
                            </a>

                            <button type="button"
                                    @click="openDelete = true; deleteUrl = '{{ route('admin.students.destroy',$student->id) }}'"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 hover:bg-red-50">
                                <i class="fa-solid fa-trash text-red-500 text-xxs"></i>
                            </button>

                        </td>

                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8 text-gray-400">
                            No students found.
                        </td>
                    </tr>
                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Footer --}}
        <div class="flex justify-between items-center p-4 border-t border-gray-200 text-xxs text-gray-500">
            <span>
                Showing {{ $students->firstItem() ?? 0 }}
                to {{ $students->lastItem() ?? 0 }}
                of {{ $students->total() }}
            </span>

            {{ $students->links() }}
        </div>

    </div>


    {{-- DELETE CONFIRMATION MODAL --}}
    <div x-show="openDelete"
         x-transition
         class="fixed inset-0 flex items-center justify-center bg-black/40 z-50">

        <div @click.away="openDelete = false"
             class="bg-white rounded-xl shadow-lg w-80 p-6">

            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-exclamation text-red-500 text-xxs"></i>
                </div>
                <h3 class="font-semibold text-gray-800 text-sm">Delete Student</h3>
            </div>

            <p class="text-gray-500 text-xxs mb-6">
                Are you sure you want to delete this student?
                This action cannot be undone.
            </p>

            <div class="flex justify-end gap-3">

                <button @click="openDelete = false"
                        class="px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-100 text-xxs">
                    Cancel
                </button>

                <form :action="deleteUrl" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xxs">
                        Delete
                    </button>
                </form>

            </div>

        </div>
    </div>

</div>

@endsection