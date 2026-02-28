<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student')</title>

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    {{-- Alpine --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* avoid flash */
        .preload *,
        .preload {
            transition: none !important;
            animation: none !important;
        }
    </style>

    <script>
        // Theme flash prevention (optional)
        (function () {
            const saved = localStorage.getItem('theme');
            const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            const theme = saved || system;
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();

        document.documentElement.classList.add('preload');
        window.addEventListener('load', () => document.documentElement.classList.remove('preload'));
    </script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('theme', {
                theme: 'light',
                init() {
                    const saved = localStorage.getItem('theme');
                    const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    this.theme = saved || system;
                    this.apply();
                },
                toggle() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('theme', this.theme);
                    this.apply();
                },
                apply() {
                    const html = document.documentElement;
                    if (this.theme === 'dark') html.classList.add('dark');
                    else html.classList.remove('dark');
                }
            });
        });
    </script>
</head>

<body class="h-full bg-gray-50 dark:bg-slate-950"
    x-data="{ uiReady:false, init(){ $store.theme.init(); this.uiReady=true; requestAnimationFrame(()=>document.documentElement.classList.remove('preload')); } }"
    x-init="init()">

    {{-- LOADER --}}
    <div class="fixed inset-0 z-[9999] grid place-items-center bg-white dark:bg-slate-950" x-show="!uiReady">
        <div class="flex flex-col items-center gap-3">
            <div class="h-10 w-10 rounded-full border-4 border-slate-200 border-t-blue-600 animate-spin"></div>
            <p class="text-sm text-slate-600 dark:text-white/70">Loading...</p>
        </div>
    </div>

    <div x-cloak x-show="uiReady" class="min-h-screen flex flex-col">
        {{-- Student Header --}}
        @include('layouts.student-header')

        {{-- Page content --}}
        <main class="flex-1">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900">
            <div class="max-w-7xl mx-auto px-4 py-4 text-sm text-gray-500 dark:text-white/60">
                © {{ date('Y') }} LMS • Student Portal
            </div>
        </footer>

        @yield('scripts')
    </div>

</body>

</html>