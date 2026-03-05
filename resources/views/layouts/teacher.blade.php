<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Teacher Panel')</title>

    {{-- Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome (CDN) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-900">
    @php
        $c = $sidebarCounts ?? ['courses' => 0, 'pending' => 0, 'unread' => 0];
        $unread = $topbarUnread ?? 0;
    @endphp

    <div class="min-h-screen">

        {{-- Mobile backdrop --}}
        <div id="sidebarBackdrop" class="fixed inset-0 z-40 bg-gray-900/40 hidden md:hidden" aria-hidden="true"></div>

        {{-- Sidebar (fixed, not scrollable) --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-gray-200
                      -translate-x-full md:translate-x-0 transition-transform duration-200 ease-out">

            {{-- Sidebar header --}}
            <div class="h-16 px-5 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 grid place-items-center text-white shadow-sm">
                        <i class="fa-solid fa-chalkboard-user text-sm"></i>
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold">Teacher Panel</div>
                        <div class="text-[11px] text-gray-500">{{ auth()->user()->name }}</div>
                    </div>
                </div>

                {{-- Close (mobile) --}}
                <button id="sidebarCloseBtn"
                    class="md:hidden w-9 h-9 rounded-xl border border-gray-200 hover:bg-gray-50 grid place-items-center"
                    type="button" aria-label="Close sidebar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Menu --}}
            <nav class="p-4 space-y-2">
                <a href="{{ route('teacher.dashboard') }}"
                    class="group flex items-center justify-between px-4 py-3 rounded-xl hover:bg-gray-50 transition">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-900">
                        <span
                            class="w-9 h-9 rounded-xl bg-gray-50 border border-gray-200 grid place-items-center text-gray-700 group-hover:text-blue-700">
                            <i class="fa-solid fa-chart-line text-sm"></i>
                        </span>
                        Dashboard
                    </span>
                    <i class="fa-solid fa-chevron-right text-xs text-gray-300 group-hover:text-gray-400"></i>
                </a>

                <a href="{{ route('teacher.courses.index') }}"
                    class="group flex items-center justify-between px-4 py-3 rounded-xl hover:bg-gray-50 transition">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-900">
                        <span
                            class="w-9 h-9 rounded-xl bg-gray-50 border border-gray-200 grid place-items-center text-gray-700 group-hover:text-blue-700">
                            <i class="fa-solid fa-book-open text-sm"></i>
                        </span>
                        Courses
                    </span>
                    <span class="text-xs px-2 py-1 rounded-full bg-gray-100 border border-gray-200 text-gray-700">
                        {{ $c['courses'] }}
                    </span>
                </a>

                <a href="{{ route('teacher.submissions.index') }}"
                    class="group flex items-center justify-between px-4 py-3 rounded-xl hover:bg-gray-50 transition">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-900">
                        <span
                            class="w-9 h-9 rounded-xl bg-amber-50 border border-amber-200 grid place-items-center text-amber-700">
                            <i class="fa-solid fa-inbox text-sm"></i>
                        </span>
                        Submissions
                    </span>
                    <span class="text-xs px-2 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-700">
                        {{ $c['pending'] }}
                    </span>
                </a>

                <a href="{{ route('teacher.notifications.index') }}"
                    class="group flex items-center justify-between px-4 py-3 rounded-xl hover:bg-gray-50 transition">
                    <span class="flex items-center gap-3 text-sm font-medium text-gray-900">
                        <span
                            class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 grid place-items-center text-blue-700">
                            <i class="fa-solid fa-bell text-sm"></i>
                        </span>
                        Notifications
                    </span>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-50 border border-blue-200 text-blue-700">
                        {{ $c['unread'] }}
                    </span>
                </a>
            </nav>

            {{-- Sidebar footer --}}
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
                <div class="rounded-2xl bg-gradient-to-r from-gray-50 to-white border border-gray-200 p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-white border border-gray-200 grid place-items-center text-gray-700">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                        </div>
                        <div class="leading-snug">
                            <div class="text-sm font-semibold text-gray-900">Secure Panel</div>
                            <div class="text-xs text-gray-500">Your actions are tracked & protected.</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main area --}}
        <div class="md:pl-72">
            {{-- Topbar (sticky) --}}
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-gray-200">
                <div class="h-16 px-4 sm:px-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        {{-- Hamburger (mobile) --}}
                        <button id="sidebarOpenBtn"
                            class="md:hidden w-10 h-10 rounded-xl border border-gray-200 hover:bg-gray-50 grid place-items-center"
                            type="button" aria-label="Open sidebar">
                            <i class="fa-solid fa-bars"></i>
                        </button>

                        <div class="text-sm font-semibold text-gray-900">
                            @yield('page_title', 'Dashboard')
                        </div>
                    </div>

                    <div class="flex items-center gap-2">

                        {{-- CHAT DROPDOWN --}}
                        <x-teacher.chat-dropdown />

                        {{-- Notification icon --}}
                        <a href="{{ route('teacher.notifications.index') }}"
                            class="relative w-10 h-10 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 grid place-items-center transition">
                            <i class="fa-regular fa-bell"></i>

                            @if($unread > 0)
                                <span
                                    class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] grid place-items-center">
                                    {{ $unread }}
                                </span>
                            @endif
                        </a>

                        {{-- Profile dropdown --}}
                        <div class="relative" id="profileDropdown">
                            <button id="profileBtn" type="button"
                                class="h-10 px-3 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition flex items-center gap-2">
                                <span
                                    class="w-7 h-7 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-600 text-white grid place-items-center text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'T', 0, 1)) }}
                                </span>
                                <span class="hidden sm:inline text-sm text-gray-800 font-medium">
                                    {{ auth()->user()->name }}
                                </span>
                                <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                            </button>

                            <div id="profileMenu"
                                class="hidden absolute right-0 mt-2 w-64 rounded-2xl border border-gray-200 bg-white shadow-xl overflow-hidden">
                                <div class="p-4 border-b border-gray-100">
                                    <div class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">{{ auth()->user()->username ?? '' }}</div>
                                    <div
                                        class="mt-2 inline-flex items-center gap-2 text-[11px] px-2 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700">
                                        <i class="fa-solid fa-user-tie"></i> Teacher
                                    </div>
                                </div>

                                <div class="p-2">
                                    <a href="{{ route('teacher.courses.index') }}"
                                        class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 transition text-sm">
                                        <i class="fa-solid fa-book-open text-gray-500 w-5"></i>
                                        My Courses
                                    </a>

                                    <a href="{{ route('teacher.submissions.index') }}"
                                        class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-50 transition text-sm">
                                        <i class="fa-solid fa-inbox text-gray-500 w-5"></i>
                                        Submissions
                                    </a>

                                    <a href="{{ route('teacher.notifications.index') }}"
                                        class="flex items-center justify-between px-3 py-2 rounded-xl hover:bg-gray-50 transition text-sm">
                                        <span class="flex items-center gap-3">
                                            <i class="fa-solid fa-bell text-gray-500 w-5"></i>
                                            Notifications
                                        </span>
                                        <span
                                            class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 border border-blue-200 text-blue-700">
                                            {{ $c['unread'] }}
                                        </span>
                                    </a>
                                </div>

                                <div class="p-2 border-t border-gray-100">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-red-50 transition text-sm text-red-700">
                                            <i class="fa-solid fa-right-from-bracket w-5"></i>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </header>

            {{-- Content --}}
            <main class="p-4 sm:p-6">
                @if(session('success'))
                    <div
                        class="mb-4 rounded-2xl border border-green-200 bg-green-50 p-4 text-green-800 flex items-start gap-3">
                        <i class="fa-solid fa-circle-check mt-0.5"></i>
                        <div class="text-sm">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800 flex items-start gap-3">
                        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                        <div class="text-sm">{{ session('error') }}</div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        // Sidebar (mobile)
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const openBtn = document.getElementById('sidebarOpenBtn');
        const closeBtn = document.getElementById('sidebarCloseBtn');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            backdrop.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            backdrop.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        openBtn?.addEventListener('click', openSidebar);
        closeBtn?.addEventListener('click', closeSidebar);
        backdrop?.addEventListener('click', closeSidebar);

        // Profile dropdown
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');
        const dropdownWrap = document.getElementById('profileDropdown');

        function toggleMenu() {
            profileMenu.classList.toggle('hidden');
        }

        function closeMenuIfOutside(e) {
            if (!dropdownWrap.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        }

        profileBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            toggleMenu();
        });

        document.addEventListener('click', closeMenuIfOutside);

        // ESC closes
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                profileMenu?.classList.add('hidden');
                closeSidebar();
            }
        });
    </script>

    @stack('scripts')
    <x-ai-live-chat />
</body>

</html>