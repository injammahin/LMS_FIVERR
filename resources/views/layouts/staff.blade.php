<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff Panel')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @stack('styles')
</head>

<body class="bg-gray-50 text-gray-900">
    @php
        $c = $sidebarCounts ?? ['courses' => 0, 'pending' => 0, 'unread' => 0];
        $unread = $topbarUnread ?? 0;
    @endphp

    <div class="min-h-screen">

        <div id="sidebarBackdrop" class="fixed inset-0 z-40 bg-gray-900/40 hidden md:hidden" aria-hidden="true"></div>

        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-72 bg-white border-r border-gray-200
                      -translate-x-full md:translate-x-0 transition-transform duration-200 ease-out">

            <div class="h-16 px-5 border-b border-gray-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 grid place-items-center text-white shadow-sm">
                        <i class="fa-solid fa-user-gear text-sm"></i>
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-semibold">Staff Panel</div>
                        <div class="text-[11px] text-gray-500">{{ auth()->user()->name }}</div>
                    </div>
                </div>

                <button id="sidebarCloseBtn"
                    class="md:hidden w-9 h-9 rounded-xl border border-gray-200 hover:bg-gray-50 grid place-items-center"
                    type="button" aria-label="Close sidebar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <nav class="p-4 space-y-2">
                <a href="{{ route('staff.dashboard') }}"
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

                <a href="{{ route('staff.courses.index') }}"
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

                <a href="{{ route('staff.submissions.index') }}"
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

                {{-- Optional: only show if you later add staff notifications route --}}
                @if(\Illuminate\Support\Facades\Route::has('staff.notifications.index'))
                    <a href="{{ route('staff.notifications.index') }}"
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
                @endif
            </nav>

            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-200 bg-white">
                <div class="rounded-2xl bg-gradient-to-r from-gray-50 to-white border border-gray-200 p-4">
                    <div class="flex items-start gap-3">
                        <div
                            class="w-9 h-9 rounded-xl bg-white border border-gray-200 grid place-items-center text-gray-700">
                            <i class="fa-solid fa-shield-halved text-sm"></i>
                        </div>
                        <div class="leading-snug">
                            <div class="text-sm font-semibold text-gray-900">Secure Panel</div>
                            <div class="text-xs text-gray-500">Read-only staff mode (no grading).</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="md:pl-72">
            <header class="sticky top-0 z-30 bg-white/80 backdrop-blur border-b border-gray-200">
                <div class="h-16 px-4 sm:px-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
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
                        <x-staff.chat-dropdown />
                        {{-- Optional notifications --}}
                        @if(\Illuminate\Support\Facades\Route::has('staff.notifications.index'))
                            <a href="{{ route('staff.notifications.index') }}"
                                class="relative w-10 h-10 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 grid place-items-center transition">
                                <i class="fa-regular fa-bell"></i>
                                @if($unread > 0)
                                    <span
                                        class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] grid place-items-center">
                                        {{ $unread }}
                                    </span>
                                @endif
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="h-10 px-4 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 transition text-sm text-red-700">
                                <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
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

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeSidebar();
        });
    </script>

    @stack('scripts')
</body>

</html>