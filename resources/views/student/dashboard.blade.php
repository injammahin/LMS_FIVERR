@extends('layouts.student')

@section('title', 'Student Dashboard')

@section('content')
    @php
        $unlockCelebration = session('division_unlock_celebration');
        $newlyUnlockedDivisionId = (int) data_get($unlockCelebration, 'to_division_id', 0);
    @endphp


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
                        Choose your division to start learning.
                    </p>

                    <div
                        class="mt-4 inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/15 text-sm">
                        <span class="opacity-80">Role:</span>
                        <span class="font-semibold">{{ $user->role }}</span>
                        @if($user->division_id)
                            <span class="mx-2 opacity-40">•</span>
                            <span class="opacity-80">Assigned:</span>
                            <span class="font-semibold">{{ $assignedDivision?->name }}</span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full md:w-auto md:min-w-[520px]">

                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3 backdrop-blur-sm">
                        <p class="text-xs text-white/80">Your Division</p>
                        <p class="text-md font-semibold">
                            {{ $assignedDivision?->name ?? 'Not assigned' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3 backdrop-blur-sm">
                        <p class="text-xs text-white/80">Subjects (your division)</p>
                        <p class="text-md font-semibold">
                            {{ $user->division_id ? $assignedSubjectsCount : '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white/10 border border-white/15 px-4 py-3 backdrop-blur-sm">
                        <p class="text-xs text-white/80">Courses (your division)</p>
                        <p class="text-md font-semibold">
                            {{ $user->division_id ? $assignedCoursesCount : '—' }}
                        </p>
                    </div>

                    @if(auth()->user()->division && auth()->user()->division->level >= 3)
                        <div class="sm:col-span-3">
                            <a href="{{ route('student.notebook.index') }}"
                                class="group relative flex items-center justify-between gap-4 rounded-2xl border border-white/20 bg-white/12 backdrop-blur-md px-5 py-4 shadow-lg hover:bg-white/18 hover:border-white/30 transition-all duration-300 hover:-translate-y-0.5">

                                <div
                                    class="absolute inset-0 rounded-2xl bg-gradient-to-r from-indigo-400/10 via-transparent to-cyan-300/10 opacity-0 group-hover:opacity-100 transition duration-300">
                                </div>

                                <div class="relative flex items-center gap-4 min-w-0">
                                    <div
                                        class="w-14 h-14 rounded-2xl bg-white/15 border border-white/20 text-white grid place-items-center shadow-inner shrink-0">
                                        <i class="fa-solid fa-book-open text-md"></i>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="text-md font-bold text-white leading-tight">
                                                My Notebook
                                            </h3>
                                            <span
                                                class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-[0.18em] bg-amber-300 text-slate-900">
                                                High School
                                            </span>
                                        </div>

                                        <p class="text-sm text-white/80 mt-1">
                                            Write, autosave, and organize your study notes.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="relative shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white text-indigo-700 font-semibold shadow-sm group-hover:bg-indigo-50 transition">
                                    <span>Open Notebook</span>
                                    <i class="fa-solid fa-arrow-right text-sm"></i>
                                </div>
                            </a>
                        </div>
                    @endif

                </div>

            </div>
        </div>
    </div>


    <div class="max-w-7xl mx-auto px-4 py-10 space-y-8">


        {{-- GRAND INLINE CELEBRATION --}}
        @if(!empty($unlockCelebration['show']))
            <section id="grandDivisionCelebration"
                class="relative overflow-hidden rounded-[36px] border border-yellow-200/70 bg-white shadow-[0_30px_80px_rgba(245,158,11,0.18)]">
                <div
                    class="absolute inset-0 bg-[radial-gradient(circle_at_15%_20%,rgba(250,204,21,0.26),transparent_28%),radial-gradient(circle_at_85%_15%,rgba(59,130,246,0.14),transparent_26%),radial-gradient(circle_at_50%_100%,rgba(16,185,129,0.10),transparent_30%)]">
                </div>
                <div class="absolute inset-0 celebration-shimmer"></div>

                <div class="absolute top-8 left-10 text-4xl celebration-float opacity-70">✨</div>
                <div class="absolute top-10 right-16 text-5xl celebration-float opacity-70" style="animation-delay:.4s;">🎉
                </div>
                <div class="absolute bottom-10 left-20 text-4xl celebration-float opacity-60" style="animation-delay:.8s;">⭐
                </div>
                <div class="absolute bottom-10 right-20 text-4xl celebration-float opacity-60" style="animation-delay:1.2s;">🚀
                </div>

                <div class="relative px-6 py-8 md:px-10 md:py-10">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">

                        <div class="flex items-start gap-5">
                            <div
                                class="h-20 w-20 md:h-24 md:w-24 rounded-[28px] bg-gradient-to-br from-yellow-300 via-amber-400 to-orange-500 text-white grid place-items-center shadow-[0_20px_50px_rgba(245,158,11,0.35)] celebration-icon">
                                <i class="fa-solid fa-trophy text-4xl"></i>
                            </div>

                            <div class="space-y-3">
                                <div
                                    class="inline-flex items-center gap-2 rounded-full bg-yellow-100 border border-yellow-200 px-4 py-2 text-xs md:text-sm font-bold text-yellow-800 tracking-[0.2em] uppercase">
                                    Division Unlocked
                                </div>

                                <h2 class="text-2xl md:text-4xl font-extrabold tracking-tight text-gray-900">
                                    Grand Celebration!
                                </h2>

                                <p class="text-gray-600 text-sm md:text-base max-w-2xl leading-7">
                                    {{ $unlockCelebration['message'] ?? 'You unlocked a new division.' }}
                                </p>

                                <div class="flex flex-wrap items-center gap-3 pt-1">
                                    <div class="rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3">
                                        <div class="text-[11px] uppercase tracking-[0.2em] text-blue-600 font-semibold">
                                            Completed</div>
                                        <div class="text-lg md:text-xl font-extrabold text-gray-900 mt-1">
                                            {{ $unlockCelebration['from_division_name'] ?? 'Previous Division' }}
                                        </div>
                                    </div>

                                    <div
                                        class="rounded-2xl bg-emerald-50 border border-emerald-100 px-4 py-3 celebration-unlocked-chip">
                                        <div class="text-[11px] uppercase tracking-[0.2em] text-emerald-600 font-semibold">
                                            Unlocked</div>
                                        <div class="text-lg md:text-xl font-extrabold text-gray-900 mt-1">
                                            {{ $unlockCelebration['to_division_name'] ?? 'New Division' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row lg:flex-col gap-3 shrink-0">
                            <a href="{{ !empty($unlockCelebration['to_division_id']) ? route('student.division.show', $unlockCelebration['to_division_id']) : route('student.dashboard') }}"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-green-600 px-6 py-3.5 text-white font-semibold shadow-lg hover:shadow-xl transition duration-300">
                                <i class="fa-solid fa-rocket"></i>
                                Enter Unlocked Division
                            </a>

                            <button type="button" id="dismissGrandDivisionCelebration"
                                class="inline-flex items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white px-6 py-3.5 text-gray-700 font-semibold hover:bg-gray-50 transition">
                                Keep Exploring
                            </button>
                        </div>
                    </div>
                </div>
            </section>
        @endif

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
            <div>
            <h2 class="text-xl font-semibold text-[#D4AF37] dark:text-[#D4AF37]">
                University of Yahweh
            </h2>     
           <p class="text-sm text-gray-500 dark:text-white/60">
                    Available divisions are unlocked based on your progress.
                </p>
            </div>

            <div class="w-full md:w-80 relative">
                <input id="divisionSearch"
                    class="w-full pl-10 pr-3 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 text-gray-800 dark:text-white placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    placeholder="Search divisions...">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none">
                    <path d="M21 21l-4.3-4.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <path d="M11 19a8 8 0 100-16 8 8 0 000 16z" stroke="currentColor" stroke-width="2" />
                </svg>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="divisionGrid">
            @foreach($divisions as $division)
                @php
                    $userDivision = $assignedDivision;
                    $isAllowed = $userDivision && $division->level <= $userDivision->level;
                    $imageUrl = $division->image ? asset('storage/' . $division->image) : null;
                    $gradient = $loop->odd ? 'from-emerald-500 to-emerald-700' : 'from-purple-500 to-indigo-700';
                    $subjectsCount = (int) ($division->subjects_count ?? 0);
                    $coursesCount = (int) ($division->courses_count ?? 0);
                    $progress = $divisionProgress[$division->id] ?? ['percent' => 0, 'done' => 0, 'total' => 0];
                    $isNewlyUnlocked = $newlyUnlockedDivisionId > 0 && (int) $division->id === $newlyUnlockedDivisionId;
                @endphp

                <div class="division-card {{ $isNewlyUnlocked ? 'newly-unlocked-card' : '' }}"
                    data-name="{{ strtolower($division->name) }}" data-division-id="{{ $division->id }}">

                    @if($isAllowed)
                        <a href="{{ route('student.division.show', $division->id) }}"
                            class="group relative block overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm hover:shadow-xl transition-all duration-300 {{ $isNewlyUnlocked ? 'ring-4 ring-yellow-300/70 shadow-[0_0_0_6px_rgba(250,204,21,0.12)]' : '' }}">

                            @if($isNewlyUnlocked)
                                <div class="absolute top-4 right-4 z-20">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-yellow-400 text-slate-900 px-4 py-2 text-xs font-extrabold shadow-lg celebration-pill">
                                        ✨ Newly Unlocked
                                    </span>
                                </div>
                            @endif

                            <div class="relative h-44 md:h-52">
                                @if($imageUrl)
                                    <img src="{{ $imageUrl }}" alt="{{ $division->name }}"
                                        class="absolute inset-0 h-full w-full object-cover transform group-hover:scale-[1.03] transition duration-500">
                                    <div class="absolute inset-0 bg-gradient-to-r from-black/55 via-black/25 to-black/55"></div>
                                @else
                                    <div class="absolute inset-0 bg-gradient-to-r {{ $gradient }}"></div>
                                    <div
                                        class="absolute inset-0 opacity-15 bg-[radial-gradient(circle_at_20%_20%,white,transparent_35%)]">
                                    </div>
                                    <div
                                        class="absolute inset-0 opacity-10 bg-[radial-gradient(circle_at_80%_70%,white,transparent_35%)]">
                                    </div>
                                @endif

                                <div class="absolute top-4 left-4 flex gap-2 flex-wrap">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white border border-white/20 backdrop-blur">
                                        @if($division->level == $userDivision?->level)
                                            Access: Current Level
                                        @elseif($division->level < $userDivision?->level)
                                            Access: Previous Level
                                        @else
                                            Access: Locked
                                        @endif
                                    </span>

                                    @if($isNewlyUnlocked)
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-extrabold bg-yellow-400 text-slate-900 shadow-md">
                                            🎉 Unlocked Now
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute inset-0 flex items-center justify-center text-center px-6">
                                    <div class="space-y-2">
                                        <h3 class="text-2xl md:text-3xl font-semibold text-white drop-shadow">
                                            {{ $division->name }}
                                        </h3>
                                        <p class="text-white/85 text-sm">
                                            {{ $isNewlyUnlocked ? 'Your next journey starts here' : 'Tap to enter' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 space-y-4">
                                <div class="flex items-center justify-between">
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

                                    <div class="relative w-14 h-14">
                                        <svg class="w-14 h-14 transform -rotate-90">
                                            <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="5" fill="transparent" />

                                            <circle cx="28" cy="28" r="24" stroke="#10b981" stroke-width="5" fill="transparent"
                                                stroke-dasharray="150"
                                                stroke-dashoffset="{{ 150 - (150 * $progress['percent'] / 100) }}"
                                                stroke-linecap="round" class="transition-all duration-700" />
                                        </svg>

                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-xs font-bold text-gray-800 dark:text-white">
                                                {{ $progress['percent'] }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="relative h-3 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                                        <div class="h-3 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600 transition-all duration-700"
                                            style="width: {{ $progress['percent'] }}%">
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between mt-1 text-xs">
                                        <span class="text-gray-500 dark:text-white/60">
                                            {{ $progress['done'] }} of {{ $progress['total'] }} completed
                                        </span>

                                        @if($isAllowed && $division->auto_promote)
                                            <span class="text-blue-600 font-medium">
                                                Promotion at {{ $division->promotion_percent }}%
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($isAllowed && $division->auto_promote)
                                    @if($progress['percent'] >= $division->promotion_percent)
                                        <div
                                            class="px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                                            🎉 Eligible for Promotion!
                                        </div>
                                    @else
                                        <div class="px-3 py-2 rounded-xl bg-blue-50 border border-blue-200 text-blue-700 text-xs">
                                            {{ $division->promotion_percent - $progress['percent'] }}% left to unlock next level
                                        </div>
                                    @endif
                                @endif

                                <div class="flex justify-end">
                                    <span
                                        class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-700 dark:text-emerald-300">
                                        {{ $isNewlyUnlocked ? 'Enter Now' : 'Continue' }}
                                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div
                                class="pointer-events-none absolute inset-0 rounded-3xl ring-0 ring-emerald-400/0 group-hover:ring-4 group-hover:ring-emerald-400/25 transition">
                            </div>
                        </a>
                    @else
                        <div
                            class="group relative overflow-hidden rounded-3xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-sm">
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

    @if(!empty($unlockCelebration['show']))
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const banner = document.getElementById('grandDivisionCelebration');
                const dismissBtn = document.getElementById('dismissGrandDivisionCelebration');
                const unlockedCard = document.querySelector('.newly-unlocked-card');

                if (banner) {
                    setTimeout(() => {
                        banner.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 200);
                }

                if (unlockedCard) {
                    setTimeout(() => {
                        unlockedCard.scrollIntoView({
                            behavior: 'smooth',
                            block: 'center'
                        });
                    }, 1200);
                }

                const duration = 5000;
                const animationEnd = Date.now() + duration;

                const defaults = {
                    startVelocity: 30,
                    spread: 360,
                    ticks: 90,
                    zIndex: 9999
                };

                function randomInRange(min, max) {
                    return Math.random() * (max - min) + min;
                }

                confetti({
                    particleCount: 200,
                    spread: 120,
                    origin: { y: 0.45 },
                    colors: ['#facc15', '#f59e0b', '#ffffff', '#60a5fa', '#34d399']
                });

                const interval = setInterval(function () {
                    const timeLeft = animationEnd - Date.now();

                    if (timeLeft <= 0) {
                        clearInterval(interval);
                        return;
                    }

                    const particleCount = 16 * (timeLeft / duration);

                    confetti(Object.assign({}, defaults, {
                        particleCount,
                        origin: {
                            x: randomInRange(0.08, 0.28),
                            y: randomInRange(0.0, 0.18)
                        },
                        colors: ['#facc15', '#f59e0b', '#ffffff']
                    }));

                    confetti(Object.assign({}, defaults, {
                        particleCount,
                        origin: {
                            x: randomInRange(0.72, 0.92),
                            y: randomInRange(0.0, 0.18)
                        },
                        colors: ['#60a5fa', '#34d399', '#ffffff', '#facc15']
                    }));

                    confetti(Object.assign({}, defaults, {
                        particleCount: 10,
                        scalar: 1.2,
                        origin: {
                            x: randomInRange(0.35, 0.65),
                            y: randomInRange(0.0, 0.12)
                        },
                        colors: ['#fde68a', '#fcd34d', '#ffffff']
                    }));
                }, 260);

                const hideBanner = () => {
                    if (!banner) return;
                    banner.classList.add('grand-celebration-hide');
                    setTimeout(() => {
                        banner.remove();
                    }, 700);
                };

                dismissBtn?.addEventListener('click', hideBanner);

                setTimeout(hideBanner, 5200);
            });
        </script>

        <style>
            @keyframes celebrationFloat {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-10px);
                }
            }

            @keyframes celebrationIconPulse {

                0%,
                100% {
                    transform: scale(1) rotate(0deg);
                    box-shadow: 0 20px 50px rgba(245, 158, 11, 0.35);
                }

                50% {
                    transform: scale(1.08) rotate(4deg);
                    box-shadow: 0 25px 70px rgba(245, 158, 11, 0.5);
                }
            }

            @keyframes celebrationShimmer {
                0% {
                    transform: translateX(-120%);
                }

                100% {
                    transform: translateX(120%);
                }
            }

            @keyframes unlockedGlow {

                0%,
                100% {
                    box-shadow: 0 0 0 0 rgba(250, 204, 21, 0.0), 0 0 0 0 rgba(250, 204, 21, 0.0);
                    transform: translateY(0);
                }

                50% {
                    box-shadow: 0 0 0 8px rgba(250, 204, 21, 0.14), 0 0 40px rgba(250, 204, 21, 0.30);
                    transform: translateY(-4px);
                }
            }

            @keyframes chipPulse {

                0%,
                100% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.03);
                }
            }

            @keyframes pillBounce {

                0%,
                100% {
                    transform: translateY(0);
                }

                50% {
                    transform: translateY(-3px);
                }
            }

            @keyframes grandCelebrationHide {
                0% {
                    opacity: 1;
                    transform: scale(1);
                    max-height: 500px;
                    margin-bottom: 0;
                }

                100% {
                    opacity: 0;
                    transform: scale(.97);
                    max-height: 0;
                    margin-bottom: -20px;
                    padding-top: 0;
                    padding-bottom: 0;
                }
            }

            .celebration-float {
                animation: celebrationFloat 2.8s ease-in-out infinite;
            }

            .celebration-icon {
                animation: celebrationIconPulse 1.8s ease-in-out infinite;
            }

            .celebration-shimmer::before {
                content: "";
                position: absolute;
                top: 0;
                left: -30%;
                width: 30%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
                animation: celebrationShimmer 2.6s linear infinite;
            }

            .celebration-unlocked-chip {
                animation: chipPulse 1.6s ease-in-out infinite;
            }

            .newly-unlocked-card {
                animation: unlockedGlow 1.8s ease-in-out infinite;
            }

            .celebration-pill {
                animation: pillBounce 1.1s ease-in-out infinite;
            }

            .grand-celebration-hide {
                overflow: hidden;
                animation: grandCelebrationHide .7s ease forwards;
            }
        </style>
    @endif
@endsection