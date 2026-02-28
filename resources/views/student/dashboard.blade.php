@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-slate-950">

    {{-- HERO --}}
    <div class="bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 text-white">
        <div class="max-w-7xl mx-auto px-4 py-10">
            <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">

                <div class="space-y-2">
                    <p class="text-white/80 text-sm">Student Portal</p>
                    <h1 class="text-3xl md:text-4xl font-semibold tracking-tight">
                        Welcome back, {{ $user->name }} 👋
                    </h1>
                    <p class="text-white/80 text-sm max-w-2xl">
                        Choose your division to start learning. Other divisions are locked until admin assigns them.
                    </p>

                    <div class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-sm">
                        <span class="opacity-80">Role:</span>
                        <span class="font-semibold">{{ $user->role }}</span>
                        @if($user->division_id)
                            <span class="mx-2 opacity-40">•</span>
                            <span class="opacity-80">Assigned:</span>
                            <span class="font-semibold">{{ $assignedDivision?->name }}</span>
                        @endif
                    </div>
                </div>

                {{-- Real stats (no dummy) --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full md:w-auto">
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Your Division</p>
                        <p class="text-md font-semibold">
                            {{ $assignedDivision?->name ?? 'Not assigned' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Subjects (your division)</p>
                        <p class="text-md font-semibold">
                            {{ $user->division_id ? $assignedSubjectsCount : '—' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Courses (your division)</p>
                        <p class="text-md font-semibold">
                            {{ $user->division_id ? $assignedCoursesCount : '—' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="max-w-7xl mx-auto px-4 py-10 space-y-8">

        {{-- Section Title + Search --}}
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Choose Your Division</h2>
                <p class="text-sm text-gray-500 dark:text-white/60">
                    Only your assigned division is clickable.
                </p>
            </div>

            <div class="w-full md:w-80 relative">
                <input id="divisionSearch"
                       class="w-full pl-10 pr-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 text-gray-800 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                       placeholder="Search divisions...">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none">
                    <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <path d="M11 19a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="2"/>
                </svg>
            </div>
        </div>

        {{-- Division Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="divisionGrid">
            @foreach($divisions as $division)
                @php
                    $isAllowed = (int)$user->division_id === (int)$division->id;

                    $imageUrl = $division->image ? asset('storage/'.$division->image) : null;

                    // fallback gradients when no image
                    $gradient = $loop->odd ? 'from-emerald-500 to-emerald-700' : 'from-purple-500 to-indigo-700';

                    // real counts
                    $subjectsCount = (int)($division->subjects_count ?? 0);
                    $coursesCount  = (int)($division->courses_count ?? 0);
                @endphp

                {{-- Card wrapper (search uses this text) --}}
                <div class="division-card" data-name="{{ strtolower($division->name) }}">
                    @if($isAllowed)
                        <a href="{{ route('student.division.show', $division->id) }}"
                           class="group relative block overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm hover:shadow-xl transition-all duration-300">
                            {{-- Media --}}
                            <div class="relative h-44 md:h-52">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $division->name }}"
                                         class="absolute inset-0 h-full w-full object-cover transform group-hover:scale-[1.03] transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-r from-black/55 via-black/25 to-black/55"></div>
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-r {{ $gradient }}"></div>
                                    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_20%_20%,white,transparent_35%)]"></div>
                                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_80%_70%,white,transparent_35%)]"></div>
                                @endif

                                {{-- Top badges --}}
                                <div class="absolute top-4 left-4 flex gap-2">
                                    <span class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white border border-white/20 backdrop-blur">
                                        Access: Granted
                                    </span>
                                </div>

                                {{-- Title --}}
                                <div class="absolute inset-0 flex items-center justify-center text-center px-6">
                                    <div class="space-y-2">
                                        <h3 class="text-2xl md:text-3xl font-semibold text-white drop-shadow">
                                            {{ $division->name }}
                                        </h3>
                                        <p class="text-white/85 text-sm">Tap to enter</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Bar --}}
                            <div class="p-5 flex items-center justify-between">
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-white/70">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Subjects: <b class="text-gray-900 dark:text-white">{{ $subjectsCount }}</b>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                        Courses: <b class="text-gray-900 dark:text-white">{{ $coursesCount }}</b>
                                    </span>
                                </div>

                                <span class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                                    Continue
                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>

                            {{-- Glow ring on hover --}}
                            <div class="pointer-events-none absolute inset-0 rounded-3xl ring-0 ring-emerald-400/0 group-hover:ring-4 group-hover:ring-emerald-400/25 transition"></div>
                        </a>
                    @else
                        {{-- Locked Card --}}
                        <div class="group relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
                            <div class="relative h-44 md:h-52">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $division->name }}"
                                         class="absolute inset-0 h-full w-full object-cover">
                                    <div class="absolute inset-0 bg-black/55"></div>
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-r {{ $gradient }}"></div>
                                    <div class="absolute inset-0 bg-black/40"></div>
                                @endif

                                <div class="absolute inset-0 flex items-center justify-center text-center px-6">
                                    <div class="space-y-2">
                                        <h3 class="text-2xl md:text-3xl font-semibold text-white drop-shadow">
                                            {{ $division->name }}
                                        </h3>
                                        <p class="text-white/80 text-sm">Locked</p>
                                    </div>
                                </div>

                                {{-- Lock badge + tooltip --}}
                                <div class="absolute top-4 right-4" x-data="{ open:false }">
                                    <button type="button"
                                            @mouseenter="open=true" @mouseleave="open=false"
                                            class="w-10 h-10 rounded-xl bg-white/15 border border-white/20 backdrop-blur text-white grid place-items-center">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M7 10V8a5 5 0 0110 0v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M6 10h12a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2v-7a2 2 0 012-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>

                                    <div x-show="open" x-transition
                                         class="absolute right-0 mt-2 w-64 text-left rounded-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-white/10 shadow-lg p-3">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Not assigned</p>
                                        <p class="text-xs text-gray-600 dark:text-white/60 mt-1">
                                            This division is locked. Ask admin to assign you to this division.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 flex items-center justify-between">
                                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-white/70">
                                    <span>Subjects: <b class="text-gray-900 dark:text-white">{{ $subjectsCount }}</b></span>
                                    <span>Courses: <b class="text-gray-900 dark:text-white">{{ $coursesCount }}</b></span>
                                </div>

                                <span class="text-sm font-semibold text-red-600">Blocked</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Search divisions (client-side)
    const input = document.getElementById('divisionSearch');
    const cards = document.querySelectorAll('.division-card');

    input?.addEventListener('input', function () {
        const q = this.value.toLowerCase().trim();
        cards.forEach(c => {
            const name = c.getAttribute('data-name') || '';
            c.style.display = name.includes(q) ? '' : 'none';
        });
    });
</script>
@endsection