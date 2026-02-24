<!DOCTYPE html>
<html
  lang="en"
  class="h-full"
  data-sidebar="expanded"
>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin')</title>

  @vite(['resources/js/app.js', 'resources/css/app.css'])

  {{-- Alpine --}}
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>
    [x-cloak]{ display:none !important; }

    /* Disable transitions/animations during first paint to prevent slide/jump */
    .preload *, .preload {
      transition: none !important;
      animation: none !important;
    }

    /* Pre-apply sidebar padding BEFORE Alpine loads (prevents content jump) */
    @media (min-width: 1280px) {
      .sidebar-layout { padding-left: 290px; }
      html[data-sidebar="collapsed"] .sidebar-layout { padding-left: 90px; }
    }
  </style>
<style>
  /* Make native controls (select dropdown, scrollbars, etc.) respect dark mode */
  html { color-scheme: light; }
  html.dark { color-scheme: dark; }

  /* Helps some browsers render the dropdown menu in dark */
  html.dark select,
  html.dark input,
  html.dark textarea {
    color-scheme: dark;
  }

  /* Optional: force option colors (works in some browsers; harmless in others) */
  html.dark select option {
    background: #0b1220; /* slate-ish */
    color: #e5e7eb;
  }
</style>
<style>
  /* Dark-mode pagination override (Laravel tailwind links()) */
  html.dark [aria-label="Pagination Navigation"] a,
  html.dark [aria-label="Pagination Navigation"] span {
    background: rgba(255,255,255,0.04) !important;
    border-color: rgba(255,255,255,0.10) !important;
    color: rgba(255,255,255,0.75) !important;
  }

  /* Hover */
  html.dark [aria-label="Pagination Navigation"] a:hover {
    background: rgba(255,255,255,0.08) !important;
    color: #fff !important;
  }

  /* Active page */
  html.dark [aria-label="Pagination Navigation"] span[aria-current="page"] {
    background: rgb(37 99 235) !important; /* blue-600 */
    border-color: rgb(37 99 235) !important;
    color: #fff !important;
  }

  /* Disabled */
  html.dark [aria-label="Pagination Navigation"] span[aria-disabled="true"] {
    opacity: 0.5 !important;
  }
</style>

  <script>
    // --- prevent theme flash ---
    (function () {
      const saved = localStorage.getItem('theme');
      const system = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
      const theme = saved || system;
      if (theme === 'dark') document.documentElement.classList.add('dark');
    })();

    // --- prevent sidebar layout jump (set html[data-sidebar] ASAP) ---
    (function () {
      const saved = localStorage.getItem('sidebar_expanded'); // '1' or '0' or null
      const desktopDefault = window.innerWidth >= 1280;
      const expanded = saved !== null ? (saved === '1') : desktopDefault;
      document.documentElement.dataset.sidebar = expanded ? 'expanded' : 'collapsed';
    })();

    // --- disable transitions until UI is ready ---
    document.documentElement.classList.add('preload');

    // fallback: remove preload on full load (in case Alpine init is delayed)
    window.addEventListener('load', () => {
      document.documentElement.classList.remove('preload');
    });
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

      Alpine.store('sidebar', {
        isExpanded: true,
        isMobileOpen: false,
        isHovered: false,

        init() {
          const saved = localStorage.getItem('sidebar_expanded'); // '1' or '0'
          const desktopDefault = window.innerWidth >= 1280;
          this.isExpanded = saved !== null ? (saved === '1') : desktopDefault;
          this.applyHtmlState();

          window.addEventListener('resize', () => {
            if (window.innerWidth < 1280) this.isHovered = false;
          });
        },

        applyHtmlState() {
          document.documentElement.dataset.sidebar = this.isExpanded ? 'expanded' : 'collapsed';
        },

        toggleExpanded() {
          this.isExpanded = !this.isExpanded;
          localStorage.setItem('sidebar_expanded', this.isExpanded ? '1' : '0');
          this.applyHtmlState();
          this.isMobileOpen = false;
        },

        toggleMobileOpen() {
          this.isMobileOpen = !this.isMobileOpen;
        },

        setMobileOpen(v) { this.isMobileOpen = v; },

        setHovered(v) {
          if (window.innerWidth >= 1280 && !this.isExpanded) this.isHovered = v;
        }
      });
    });
  </script>
</head>

<body
  class="h-full "
  x-data="{
    uiReady: false,
    minMs: 600,      // minimum loader time (1s)
    start: 0,
    init() {
      this.start = Date.now();

      // init stores
      $store.theme.init();
      $store.sidebar.init();

      // Wait at least 1s, even if Alpine is ready instantly
      const remaining = this.minMs - (Date.now() - this.start);
      setTimeout(() => {
        this.uiReady = true;

        // enable transitions after UI is visible
        requestAnimationFrame(() => document.documentElement.classList.remove('preload'));
      }, Math.max(0, remaining));
    }
  }"
  x-init="init()"
>

  {{-- LOADER (must show minimum 1s) --}}
  <div
    class="fixed inset-0 z-[9999] grid place-items-center bg-white dark:bg-slate-950"
    x-show="!uiReady"
  >
    <div class="flex flex-col items-center gap-3">
      <div class="h-10 w-10 rounded-full border-4 border-slate-200 border-t-orange-500 animate-spin"></div>
      <p class="text-sm text-slate-600 dark:text-white/70">Loading...</p>
    </div>
  </div>

  {{-- APP (hidden until ready) --}}
  <div x-cloak x-show="uiReady" class="h-full">
    @include('layouts.sidebar')

    <div
      class="min-h-screen sidebar-layout transition-all duration-300"
      :class="{
        'xl:pl-[290px]': ($store.sidebar.isExpanded || $store.sidebar.isHovered) && !$store.sidebar.isMobileOpen,
        'xl:pl-[90px]': (!$store.sidebar.isExpanded && !$store.sidebar.isHovered) && !$store.sidebar.isMobileOpen
      }"
    >
            @include('layouts.app-header')

      <main class="p-4 sm:p-6 lg:p-8">
        @yield('content')
      </main>
    </div>

    <div id="toast-container" class="fixed top-24 right-4 z-50"></div>

    @yield('scripts')

    <script>
      var APP_URL = "{{ config('app.url') }}";
    </script>
  </div>

</body>
</html>
