@extends('layouts.admin')

@section('content')

    <div class="max-w-7xl mx-auto py-6">

        <!-- TITLE -->

        <div class="mb-6 flex items-center justify-between">

            <div>

                <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">

                    <i class="fa-solid fa-comments text-blue-500"></i>

                    Chat Users

                </h2>

                <p class="text-xs text-gray-500">
                    Message teachers and staff members
                </p>

            </div>

        </div>


        <!-- FILTER BAR -->

        <form method="GET"
            class="bg-white rounded-xl shadow-sm border p-3 mb-6 flex flex-wrap lg:flex-nowrap items-center gap-3">

            <!-- SEARCH -->

            <div class="flex-1 min-w-[200px]">

                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..."
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400">

            </div>


            <!-- ROLE FILTER -->

            <select name="role" class="border rounded-lg px-3 py-2 text-sm">

                <option value="">All Roles</option>

                <option value="teacher" {{ request('role') == 'teacher' ? 'selected' : '' }}>
                    Teachers
                </option>

                <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>
                    Staff
                </option>

            </select>


            <!-- DIVISION -->

            <select name="division_id" class="border rounded-lg px-3 py-2 text-sm">

                <option value="">All Divisions</option>

                @foreach($divisions as $division)

                    <option value="{{ $division->id }}" {{ request('division_id') == $division->id ? 'selected' : '' }}>

                        {{ $division->name }}

                    </option>

                @endforeach

            </select>


            <!-- COURSE -->

            <select name="course_id" class="border rounded-lg px-3 py-2 text-sm">

                <option value="">All Courses</option>

                @foreach($courses as $course)

                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>

                        {{ $course->title }}

                    </option>

                @endforeach

            </select>


            <!-- FILTER BUTTON -->

            <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">

                <i class="fa-solid fa-filter"></i>

                Filter

            </button>


            <!-- RESET -->

            <a href="{{ route('chat.users') }}"
                class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg flex items-center gap-2">

                <i class="fa-solid fa-rotate-left"></i>

                Reset

            </a>

        </form>



        <!-- USERS LIST -->

        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border">

            @forelse($users as $user)

                <a href="{{ route('chat.view', $user->id) }}"
                    class="flex items-center gap-4 px-6 py-4 border-b hover:bg-blue-50 transition">

                    <!-- AVATAR -->

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                        class="w-12 h-12 rounded-full shadow">

                    <!-- USER INFO -->

                    <div class="flex-1">

                        <div class="font-semibold text-gray-800">

                            {{ $user->name }}

                        </div>

                        <div class="text-xs text-gray-500 flex items-center gap-2">

                            @if($user->role == 'teacher')

                                <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px]">
                                    Teacher
                                </span>

                            @else

                                <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-[10px]">
                                    Staff
                                </span>

                            @endif


                            @if($user->division)

                                <span class="text-gray-400">•</span>

                                <i class="fa-solid fa-building text-gray-400"></i>

                                {{ $user->division->name }}

                            @endif

                        </div>

                    </div>


                    <!-- ACTION -->

                    <div class="text-blue-600 text-sm font-medium flex items-center gap-1">

                        Chat

                        <i class="fa-solid fa-arrow-right text-xs"></i>

                    </div>

                </a>

            @empty

                <div class="text-center p-12 text-gray-400">

                    <i class="fa-solid fa-user-slash text-2xl mb-2"></i>

                    <p>No users found</p>

                </div>

            @endforelse

        </div>

    </div>

@endsection