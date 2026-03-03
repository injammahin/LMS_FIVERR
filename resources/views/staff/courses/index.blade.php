@extends('layouts.staff')
@section('title', 'My Courses')
@section('page_title', 'Courses')

@section('content')
@php
    $isPaginator = $courses instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
        || $courses instanceof \Illuminate\Pagination\LengthAwarePaginator
        || $courses instanceof \Illuminate\Pagination\Paginator;

    $totalCourses = $isPaginator ? (int)$courses->total() : (int)$courses->count();
@endphp

<div class="space-y-6" x-data="{ view: 'grid' }">

    {{-- HERO --}}
    <div class="rounded-3xl overflow-hidden border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
        <div class="relative">
            <div class="h-24 bg-gradient-to-r from-indigo-700 via-blue-700 to-sky-700"></div>

            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute -top-16 -left-20 w-72 h-72 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute -top-10 right-0 w-96 h-96 rounded-full bg-white/10 blur-3xl"></div>
            </div>

            <div class="p-5 md:p-6 -mt-10">
                <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-white/90 dark:bg-slate-950 border border-white/30 dark:border-white/10 shadow grid place-items-center text-indigo-700 dark:text-indigo-300">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-lg md:text-xl font-semibold text-gray-900 dark:text-white">
                                My Courses
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-white/60 mt-1">
                                Only courses assigned to you by admin. View content & monitor submissions (no grading).
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ route('staff.dashboard') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                            <i class="fa-solid fa-arrow-left"></i>
                            Dashboard
                        </a>

                        <button type="button"
                                @click="view = (view === 'grid' ? 'list' : 'grid')"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm">
                            <i class="fa-solid" :class="view === 'grid' ? 'fa-list' : 'fa-grip'"></i>
                            <span x-text="view === 'grid' ? 'List view' : 'Grid view'"></span>
                        </button>
                    </div>
                </div>

                {{-- Search + KPI row --}}
                <div class="mt-5 grid grid-cols-1 lg:grid-cols-12 gap-3">
                    {{-- KPI --}}
                    <div class="lg:col-span-4 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-4 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs text-gray-500 dark:text-white/60">Total Courses</div>
                                <div class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $totalCourses }}</div>
                                <div class="text-xs text-gray-500 dark:text-white/60 mt-2">Assigned courses only</div>
                            </div>
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 border border-indigo-100 dark:border-indigo-500/20 text-indigo-700 dark:text-indigo-200 grid place-items-center">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="lg:col-span-8 rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/80 grid place-items-center">
                                <i class="fa-solid fa-magnifying-glass"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <label class="text-xs text-gray-500 dark:text-white/60">Search course / subject / division</label>
                                <input id="courseSearch" type="text" placeholder="Type to filter…"
                                       class="mt-1 w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 px-4 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <button type="button" id="clearSearch"
                                    class="hidden sm:inline-flex px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- COURSES --}}
    <div id="courseWrap"
         class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"
         :class="view === 'list' ? 'xl:grid-cols-1' : ''">

        @forelse($courses as $course)
            @php
                $subjectName  = optional($course->subject)->name ?? '—';
                $divisionName = optional(optional($course->subject)->division)->name ?? '—';

                $lessons = (int)($course->lessons_count ?? 0);
                $quizzes = (int)($course->quizzes_count ?? 0);
                $assigns = (int)($course->assignments_count ?? 0);
            @endphp

            <a href="{{ route('staff.courses.show', $course->id) }}"
               class="courseCard group rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition overflow-hidden"
               data-title="{{ strtolower($course->title) }}"
               data-subject="{{ strtolower($subjectName) }}"
               data-division="{{ strtolower($divisionName) }}">

                {{-- top gradient bar --}}
                <div class="relative h-20 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600">
                    <div class="absolute inset-0 opacity-20 bg-[radial-gradient(circle_at_30%_30%,white,transparent_45%)]"></div>
                    <div class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_70%_70%,white,transparent_45%)]"></div>

                    <div class="absolute top-3 left-3">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs">
                            <i class="fa-solid fa-book-open"></i> Course
                        </span>
                    </div>

                    <div class="absolute top-3 right-3">
                        <span class="w-10 h-10 rounded-2xl bg-white/15 border border-white/25 grid place-items-center text-white">
                            <i class="fa-solid fa-arrow-right"></i>
                        </span>
                    </div>
                </div>

                <div class="p-5 space-y-4">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900 dark:text-white courseTitle truncate">
                            {{ $course->title }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-white/60 mt-1 truncate">
                            Division: <span class="font-semibold courseDivision">{{ $divisionName }}</span>
                            • Subject: <span class="font-semibold courseSubject">{{ $subjectName }}</span>
                        </div>
                    </div>

                    {{-- chips --}}
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-gray-700 dark:text-white/80">
                            <i class="fa-solid fa-sitemap text-gray-400"></i>
                            {{ $divisionName }}
                        </span>

                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border border-indigo-100 dark:border-indigo-500/20 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-200">
                            <i class="fa-solid fa-tag"></i>
                            {{ $subjectName }}
                        </span>

                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border border-emerald-100 dark:border-emerald-500/20 bg-emerald-50 dark:bg-emerald-500/10 text-emerald-800 dark:text-emerald-200">
                            <i class="fa-solid fa-eye"></i>
                            View-only
                        </span>
                    </div>

                    {{-- stats --}}
                    <div class="grid grid-cols-3 gap-2">
                        <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-3">
                            <div class="text-[10px] text-gray-500 dark:text-white/60">Lessons</div>
                            <div class="text-sm font-extrabold text-gray-900 dark:text-white mt-1">{{ $lessons }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-3">
                            <div class="text-[10px] text-gray-500 dark:text-white/60">Quizzes</div>
                            <div class="text-sm font-extrabold text-gray-900 dark:text-white mt-1">{{ $quizzes }}</div>
                        </div>
                        <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 p-3">
                            <div class="text-[10px] text-gray-500 dark:text-white/60">Assignments</div>
                            <div class="text-sm font-extrabold text-gray-900 dark:text-white mt-1">{{ $assigns }}</div>
                        </div>
                    </div>

                    {{-- footer --}}
                    <div class="flex items-center justify-between pt-1">
                        <div class="text-xs text-gray-500 dark:text-white/60">
                            Open course details
                        </div>

                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-2xl text-xs font-semibold
                                     border border-blue-100 dark:border-blue-500/20 bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-200
                                     group-hover:bg-blue-100 dark:group-hover:bg-blue-500/20 transition">
                            View <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
            </a>

        @empty
            <div class="col-span-full">
                <div class="rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 p-10 text-center shadow-sm">
                    <div class="mx-auto w-12 h-12 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 grid place-items-center text-gray-600 dark:text-white/70">
                        <i class="fa-solid fa-circle-info"></i>
                    </div>
                    <div class="mt-3 text-base font-semibold text-gray-900 dark:text-white">No courses assigned</div>
                    <div class="text-sm text-gray-500 dark:text-white/60 mt-1">Ask admin to assign courses to your staff account.</div>

                    <a href="{{ route('staff.dashboard') }}"
                       class="inline-flex items-center gap-2 mt-5 px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 hover:bg-gray-50 dark:hover:bg-white/5 text-sm">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($isPaginator)
        <div class="pt-2">
            {{ $courses->links() }}
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
(function () {
    const input = document.getElementById('courseSearch');
    const clearBtn = document.getElementById('clearSearch');
    const cards = document.querySelectorAll('.courseCard');

    function normalize(s){ return (s || '').toLowerCase().trim(); }

    function applyFilter(q){
        const query = normalize(q);
        let visible = 0;

        cards.forEach(card => {
            const title = card.dataset.title || '';
            const subject = card.dataset.subject || '';
            const division = card.dataset.division || '';

            const show = !query || title.includes(query) || subject.includes(query) || division.includes(query);
            card.style.display = show ? '' : 'none';
            if(show) visible++;
        });

        if (clearBtn) clearBtn.classList.toggle('hidden', !query);
    }

    input?.addEventListener('input', function () {
        applyFilter(this.value);
    });

    clearBtn?.addEventListener('click', function(){
        input.value = '';
        applyFilter('');
        input.focus();
    });
})();
</script>
@endsection