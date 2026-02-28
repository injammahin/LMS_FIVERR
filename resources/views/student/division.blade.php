@extends('layouts.student')

@section('title', $division->name)

@section('content')
    <div class="min-h-screen bg-gray-50">
        {{-- Top Header --}}
        <div class="bg-gradient-to-r from-blue-700 to-blue-900 text-white">
            <div class="max-w-7xl mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                    <div>
                        <p class="text-white/80 text-sm">Division</p>
                        <h1 class="text-2xl md:text-3xl font-semibold tracking-tight">
                            {{ $division->name }}
                        </h1>
                        <p class="text-white/80 text-sm mt-1">
                            Welcome, {{ auth()->user()->name }} — pick a subject to start learning.
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('student.dashboard') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/15 border border-white/20 transition">
                            <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                    clip-rule="evenodd" />
                            </svg>
                            Back
                        </a>

                        <a href="#"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-blue-800 hover:bg-blue-50 transition font-medium">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                <path d="M12 20h9" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                <path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4 12.5-12.5z" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            Progress
                        </a>
                    </div>

                </div>

                {{-- Small stats bar --}}
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Subjects</p>
                        <p class="text-xl font-semibold">{{ $subjects->count() }}</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Lessons Completed</p>
                        <p class="text-xl font-semibold">—</p>
                    </div>
                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3">
                        <p class="text-xs text-white/80">Quizzes Completed</p>
                        <p class="text-xl font-semibold">—</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">

            {{-- Search --}}
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="subjectSearch" placeholder="Search subjects..."
                            class="w-full pl-10 pr-3 py-2 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none">
                            <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                            <path d="M11 19a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="2" />
                        </svg>
                    </div>

                    <div class="text-sm text-gray-500">
                        Tip: choose a subject to see courses.
                    </div>
                </div>
            </div>

            {{-- Subject Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6" id="subjectGrid">
                @forelse($subjects as $subject)
                    <a href="{{ route('student.subjects.show', [$division->id, $subject->id]) }}"
                        class="subject-card group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition overflow-hidden">
                        {{-- Card top --}}
                        <div class="h-24 bg-gradient-to-r from-indigo-500 to-blue-600 relative">
                            <div
                                class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_40%)]">
                            </div>
                            <div
                                class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_40%)]">
                            </div>

                            <div class="absolute top-3 left-3">
                                <span
                                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-white text-xs">
                                    Subject
                                </span>
                            </div>

                            <div class="absolute top-3 right-3">
                                <span
                                    class="w-9 h-9 rounded-xl bg-white/15 border border-white/20 flex items-center justify-center text-white">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none">
                                        <path d="M4 19.5A2.5 2.5 0 016.5 17H20" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" />
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5V4.5A2.5 2.5 0 016.5 2z"
                                            stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                                    </svg>
                                </span>
                            </div>
                        </div>

                        {{-- Card body --}}
                        <div class="p-5">
                            <h3 class="text-lg font-semibold text-gray-900 subject-name">
                                {{ $subject->name }}
                            </h3>

                            <p class="text-sm text-gray-500 mt-1">
                                Explore courses, lessons, quizzes and assignments.
                            </p>

                            <div class="mt-4 flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    Courses: <span class="font-semibold text-gray-800">{{ $subject->courses_count }}</span>
                                </div>

                                <span
                                    class="inline-flex items-center gap-2 text-sm font-medium text-blue-700 group-hover:text-blue-800">
                                    Open
                                    <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </div>

                            {{-- progress bar placeholder --}}
                            <div class="mt-4">
                                <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                                    <div class="h-2 rounded-full bg-emerald-500 w-[0%]"></div>
                                </div>
                                <p class="text-xs text-gray-400 mt-2">Progress: 0%</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full">
                        <div class="bg-white rounded-2xl border border-gray-200 p-8 text-center text-gray-500">
                            No subjects found in this division yet.
                        </div>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    <script>
        // simple client-side search
        const input = document.getElementById('subjectSearch');
        const cards = document.querySelectorAll('.subject-card');

        input?.addEventListener('input', function () {
            const q = this.value.toLowerCase();
            cards.forEach(card => {
                const name = card.querySelector('.subject-name')?.innerText?.toLowerCase() ?? '';
                card.style.display = name.includes(q) ? '' : 'none';
            });
        });
    </script>
@endsection