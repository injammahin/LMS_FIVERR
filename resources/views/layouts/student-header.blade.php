<header
    class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-gray-200 dark:border-white/10">
    <div class="max-w-7xl mx-auto px-4">
        <div class="h-16 flex items-center justify-between">

            {{-- Left: Logo/Brand --}}
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3">
                {{-- replace with your logo if you have --}}
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white grid place-items-center font-bold">
                    L
                </div>
                <div class="leading-tight">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white">LMS</div>
                    <div class="text-xs text-gray-500 dark:text-white/60">Student Portal</div>
                </div>
            </a>

            {{-- Middle: nav links --}}
            <nav class="hidden md:flex items-center gap-6 text-sm">
                <a href="{{ route('student.dashboard') }}"
                    class="text-gray-700 dark:text-white/80 hover:text-blue-600 dark:hover:text-white transition">
                    Dashboard
                </a>
                {{-- add later --}}
                <a href="#"
                    class="text-gray-700 dark:text-white/80 hover:text-blue-600 dark:hover:text-white transition">
                    Courses
                </a>
                <a href="#"
                    class="text-gray-700 dark:text-white/80 hover:text-blue-600 dark:hover:text-white transition">
                    Grades
                </a>
            </nav>

            {{-- Right: actions --}}
            <div class="flex items-center gap-3">
                {{-- theme toggle --}}
                <button type="button" @click="$store.theme.toggle()"
                    class="h-10 w-10 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 grid place-items-center text-gray-700 dark:text-white/80">
                    <svg x-show="!document.documentElement.classList.contains('dark')" class="w-5 h-5"
                        viewBox="0 0 24 24" fill="none">
                        <path d="M12 18a6 6 0 100-12 6 6 0 000 12z" stroke="currentColor" stroke-width="2" />
                        <path
                            d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <svg x-show="document.documentElement.classList.contains('dark')" class="w-5 h-5"
                        viewBox="0 0 24 24" fill="none">
                        <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>

                {{-- user dropdown --}}
                <div class="relative" x-data="{ open:false }">
                    <button @click="open=!open"
                        class="h-10 px-3 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 flex items-center gap-2 text-sm text-gray-700 dark:text-white/80">
                        <span class="font-medium">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open=false" x-transition
                        class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-lg overflow-hidden">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/10">
                            <p class="text-xs text-gray-500 dark:text-white/60">Signed in as</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ auth()->user()->email ?? auth()->user()->username }}
                            </p>
                        </div>

                        <a href="{{ route('profile.edit') }}"
                            class="block px-4 py-2 text-sm text-gray-700 dark:text-white/80 hover:bg-gray-100 dark:hover:bg-white/5">
                            Profile
                        </a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button
                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</header>