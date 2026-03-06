@extends('layouts.student')

@section('content')

    <div class="max-w-6xl mx-auto py-6 space-y-6">

        <!-- PAGE TITLE -->

        <div>

            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">

                <i class="fa-solid fa-comments text-blue-500"></i>

                Course Teachers

            </h2>

            <p class="text-xs text-gray-500">

                Chat with teachers assigned to your subjects

            </p>

        </div>


        <!-- FILTER BAR -->

        <form method="GET" class="bg-white rounded-xl shadow border p-4 flex flex-wrap md:flex-nowrap gap-3 items-center">

            <!-- SEARCH -->

            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search teacher..."
                class="border rounded-lg px-3 py-2 text-sm w-full md:w-auto flex-1">


            <!-- SUBJECT FILTER -->

            <select name="subject_id" class="border rounded-lg px-3 py-2 text-sm">

                <option value="">All Subjects</option>

                @foreach($subjects as $subject)

                    <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>

                        {{ $subject->name }}

                    </option>

                @endforeach

            </select>


            <!-- COURSE FILTER -->

            <select name="course_id" class="border rounded-lg px-3 py-2 text-sm">

                <option value="">All Courses</option>

                @foreach($courses as $course)

                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>

                        {{ $course->title }}

                    </option>

                @endforeach

            </select>


            <button class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1">

                <i class="fa-solid fa-filter"></i>
                Filter

            </button>


            <a href="{{ route('student.chat.users') }}" class="bg-gray-100 px-4 py-2 rounded-lg text-sm">

                Reset

            </a>

        </form>



        <!-- USERS LIST -->

        <div class="bg-white rounded-xl shadow overflow-hidden">

            @forelse($users as $user)

                <a href="{{ route('student.chat.view', $user->id) }}"
                    class="flex items-center gap-4 px-6 py-4 border-b hover:bg-blue-50 transition">

                    <!-- AVATAR -->

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                        class="w-12 h-12 rounded-full shadow">


                    <!-- USER INFO -->

                    <div class="flex-1">

                        <div class="font-semibold text-gray-800">

                            {{ $user->name }}

                        </div>

                        <div class="text-xs text-gray-500 flex gap-1 items-center">

                            <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px]">
                                Teacher
                            </span>

                            @if($user->coursesTeaching->count())

                                <span class="text-gray-400">•</span>

                                <span>

                                    {{ $user->coursesTeaching->pluck('title')->take(2)->join(', ') }}

                                </span>

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

                <div class="p-10 text-center text-gray-400">

                    <i class="fa-solid fa-user-slash text-xl mb-2"></i>

                    <p>No teachers found for your courses</p>

                </div>

            @endforelse

        </div>

    </div>

@endsection