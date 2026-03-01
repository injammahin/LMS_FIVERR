@extends('layouts.teacher')
@section('title', 'My Courses')

@section('content')
@php
    $total = $courses->count();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold text-gray-900">My Courses</h1>
            <p class="text-sm text-gray-500">Only courses assigned to you.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('teacher.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                <i class="fa-solid fa-arrow-left"></i>
                Dashboard
            </a>
        </div>
    </div>

    {{-- Stats + Search --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs text-gray-500">Total Courses</div>
                    <div class="text-lg font-extrabold text-gray-900 mt-1">{{ $total }}</div>
                </div>
                <div class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-100 text-blue-700 grid place-items-center">
                    <i class="fa-solid fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gray-50 border border-gray-200 text-gray-700 grid place-items-center">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <div class="flex-1">
                    <label class="text-xs text-gray-500">Search course / subject / division</label>
                    <input id="courseSearch" type="text" placeholder="Type to filter..."
                           class="mt-1 w-full rounded-xl border border-gray-200 px-4 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
        </div>
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4" id="courseGrid">
        @forelse($courses as $course)
            @php
                $subjectName  = optional($course->subject)->name ?? '—';
                $divisionName = optional(optional($course->subject)->division)->name ?? '—';
            @endphp

            <a href="{{ route('teacher.courses.show', $course->id) }}"
               class="courseCard group rounded-2xl border border-gray-200 bg-white shadow-sm hover:shadow-md transition overflow-hidden">

                {{-- Accent bar --}}
                <div class="h-16 bg-gradient-to-r from-blue-600 to-indigo-600 relative">
                    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_30%_30%,white,transparent_45%)]"></div>
                    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]"></div>

                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs">
                            <i class="fa-solid fa-book-open"></i> Course
                        </span>
                    </div>

                    <div class="absolute top-3 right-3">
                        <span class="w-9 h-9 rounded-xl bg-white/15 border border-white/25 grid place-items-center text-white">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>
                </div>

                {{-- Body --}}
                <div class="p-5 space-y-4">
                    <div class="space-y-1">
                        <div class="text-sm font-semibold text-gray-900 courseTitle">
                            {{ $course->title }}
                        </div>

                        <div class="text-xs text-gray-500">
                            Teaching overview for this course.
                        </div>
                    </div>

                    {{-- Chips --}}
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs border bg-gray-50 border-gray-200 text-gray-700">
                            <i class="fa-solid fa-tag text-gray-400"></i>
                            Subject: <span class="font-semibold text-gray-900 courseSubject">{{ $subjectName }}</span>
                        </span>

                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs border bg-emerald-50 border-emerald-100 text-emerald-800">
                            <i class="fa-solid fa-sitemap"></i>
                            Division: <span class="font-semibold courseDivision">{{ $divisionName }}</span>
                        </span>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-between pt-1">
                        <div class="text-xs text-gray-500">
                            Click to view course details & students
                        </div>

                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border border-blue-100 bg-blue-50 text-blue-700 group-hover:bg-blue-100 transition">
                            View <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
            </a>

        @empty
            <div class="col-span-full">
                <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center shadow-sm">
                    <div class="mx-auto w-12 h-12 rounded-2xl bg-gray-50 border border-gray-200 grid place-items-center text-gray-600">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div class="mt-3 text-sm font-semibold text-gray-900">No courses assigned</div>
                    <div class="text-sm text-gray-500 mt-1">When an admin assigns courses to you, they will appear here.</div>

                    <a href="{{ route('teacher.dashboard') }}"
                       class="inline-flex items-center gap-2 mt-5 px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection

@section('scripts')
<script>
(function () {
    const input = document.getElementById('courseSearch');
    const cards = document.querySelectorAll('.courseCard');

    function normalize(s){ return (s || '').toLowerCase(); }

    input?.addEventListener('input', function () {
        const q = normalize(this.value);
        cards.forEach(card => {
            const title = normalize(card.querySelector('.courseTitle')?.innerText);
            const subject = normalize(card.querySelector('.courseSubject')?.innerText);
            const division = normalize(card.querySelector('.courseDivision')?.innerText);

            const show = title.includes(q) || subject.includes(q) || division.includes(q);
            card.style.display = show ? '' : 'none';
        });
    });
})();
</script>
@endsection