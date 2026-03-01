<header
    class="sticky top-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur border-b border-gray-200 dark:border-white/10">
    @php
        use Illuminate\Support\Facades\Schema;

        // =========================
        // Notifications
        // =========================
        $notifEnabled = Schema::hasTable('notifications');
        $unreadCount = $notifEnabled ? auth()->user()->unreadNotifications()->count() : 0;

        $latestNotifs = $notifEnabled
            ? auth()->user()->notifications()->latest()->limit(6)->get()
            : collect();

        // =========================
        // Courses menu (hover dropdown / mega menu)
        // =========================
        $student = auth()->user();
        $divisionId = (int)($student->division_id ?? 0);

        $subjectsWithCourses = collect();
        $totalCourses = 0;

        if ($divisionId > 0) {
            $subjectsWithCourses = \App\Models\Subject::query()
                ->where('division_id', $divisionId)
                ->with(['courses' => function ($q) {
                    $q->orderBy('title')->select('id','title','subject_id');
                }])
                ->orderBy('name')
                ->get(['id','name','division_id'])
                ->filter(fn($s) => $s->courses->count() > 0)
                ->values();

            $totalCourses = $subjectsWithCourses->sum(fn($s) => $s->courses->count());
        }

        // change thresholds if you want
        $MEGA_THRESHOLD = 10; // if total courses > 10 show mega menu
        $useMega = $totalCourses > $MEGA_THRESHOLD;
    @endphp

    <div class="max-w-7xl mx-auto px-4">
        <div class="h-16 flex items-center justify-between">

            {{-- Left: Logo/Brand --}}
            <a href="{{ route('student.dashboard') }}" class="flex items-center gap-3">
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
                   class="{{ request()->routeIs('student.dashboard') ? 'text-blue-600 dark:text-blue-300 font-semibold' : 'text-gray-700 dark:text-white/80' }} hover:text-blue-600 dark:hover:text-white transition">
                    Dashboard
                </a>

                {{-- Courses hover menu --}}
                <div class="relative"
                     x-data="{
                        open:false,
                        t:null,
                        openNow(){ clearTimeout(this.t); this.open=true; },
                        closeLater(){ this.t=setTimeout(()=>this.open=false, 140); }
                     }"
                     @mouseenter="openNow()"
                     @mouseleave="closeLater()">

                    <button type="button"
                            @click="open=!open"
                            class="inline-flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition
                                   {{ request()->routeIs('student.division.show','student.subjects.show') ? 'text-blue-600 dark:text-blue-300 font-semibold' : 'text-gray-700 dark:text-white/80' }}">
                        Courses
                        <i class="fa-solid fa-chevron-down text-[11px] opacity-70"></i>
                    </button>

                    {{-- Dropdown / Mega --}}
                    <div x-show="open" x-transition.origin.top.left
                         class="absolute left-0 mt-2 w-[380px] rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-xl overflow-hidden"
                         style="display:none">

                        {{-- Header --}}
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Courses</div>
                                <div class="text-xs text-gray-500 dark:text-white/60">
                                    {{ $divisionId ? $totalCourses.' total' : 'No division assigned' }}
                                </div>
                            </div>

                            @if($divisionId)
                                <a href="{{ route('student.division.show', $divisionId) }}"
                                   class="text-xs px-3 py-1.5 rounded-full border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5">
                                    Browse Division
                                </a>
                            @endif
                        </div>

                        {{-- Body --}}
                        @if(!$divisionId)
                            <div class="p-4 text-sm text-gray-600 dark:text-white/70">
                                You are not assigned to any division yet.
                            </div>
                        @elseif($totalCourses === 0)
                            <div class="p-4 text-sm text-gray-600 dark:text-white/70">
                                No courses available yet.
                            </div>
                        @else

                            {{-- ✅ Normal list (few courses) --}}
                            @if(!$useMega)
                                <div class="max-h-[360px] overflow-auto">
                                    @foreach($subjectsWithCourses as $sub)
                                        <div class="px-4 pt-3 pb-2">
                                            <div class="text-[11px] uppercase tracking-wider text-gray-500 dark:text-white/60">
                                                {{ $sub->name }}
                                            </div>
                                        </div>

                                        @foreach($sub->courses as $c)
                                            <a href="{{ route('student.subjects.show', [$divisionId, $sub->id]) }}?course={{ $c->id }}"
                                               class="px-4 py-2.5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                        {{ $c->title }}
                                                    </div>
                                                    <div class="text-xs text-gray-500 dark:text-white/60">
                                                        Open inside subject
                                                    </div>
                                                </div>
                                                <i class="fa-solid fa-chevron-right text-gray-300"></i>
                                            </a>
                                        @endforeach

                                        <div class="h-px bg-gray-100 dark:bg-white/10 my-2"></div>
                                    @endforeach
                                </div>

                            {{-- ✅ Mega menu (many courses) --}}
                            @else
                                <div class="max-h-[420px] overflow-auto">
                                    <div class="p-4">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                            @foreach($subjectsWithCourses as $sub)
                                                <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-gray-50/60 dark:bg-white/5 overflow-hidden">
                                                    <div class="px-3 py-2 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                                                        <div class="text-xs font-semibold text-gray-900 dark:text-white truncate">
                                                            {{ $sub->name }}
                                                        </div>
                                                        <a class="text-[11px] text-blue-700 dark:text-blue-300 hover:underline"
                                                           href="{{ route('student.subjects.show', [$divisionId, $sub->id]) }}">
                                                            Open
                                                        </a>
                                                    </div>

                                                    <div class="px-2 py-2 space-y-1">
                                                        @foreach($sub->courses as $c)
                                                            <a href="{{ route('student.subjects.show', [$divisionId, $sub->id]) }}?course={{ $c->id }}"
                                                               class="block px-2 py-2 rounded-xl hover:bg-white dark:hover:bg-slate-950 transition">
                                                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                                                    {{ $c->title }}
                                                                </div>
                                                                <div class="text-xs text-gray-500 dark:text-white/60">
                                                                    Jump to course
                                                                </div>
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                        @endif

                        {{-- Footer --}}
                        <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-t border-gray-200 dark:border-white/10">
                            <a href="{{ route('student.grades.index') }}"
                               class="text-sm text-blue-700 dark:text-blue-300 font-semibold hover:underline">
                                Go to Grades →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Grades --}}
                <a href="{{ route('student.grades.index') }}"
                   class="{{ request()->routeIs('student.grades.*') ? 'text-blue-600 dark:text-blue-300 font-semibold' : 'text-gray-700 dark:text-white/80' }} hover:text-blue-600 dark:hover:text-white transition">
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

                {{-- Notifications --}}
                <div class="relative" x-data="{ open:false }">
                    <button @click="open=!open"
                            class="relative h-10 w-10 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 grid place-items-center text-gray-700 dark:text-white/80">
                        <i class="fa-regular fa-bell text-[16px]"></i>

                        @if($unreadCount > 0)
                            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[11px] font-bold grid place-items-center border-2 border-white dark:border-slate-900">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </button>

                    <div x-show="open" @click.away="open=false" x-transition
                         class="absolute right-0 mt-2 w-[340px] rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-xl overflow-hidden"
                         style="display:none">
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</div>
                                <div class="text-xs text-gray-500 dark:text-white/60">
                                    {{ $unreadCount }} unread
                                </div>
                            </div>

                            @if($notifEnabled && $unreadCount > 0)
                                <form method="POST" action="{{ route('student.notifications.readAll') }}">
                                    @csrf
                                    <button class="text-xs px-3 py-1.5 rounded-full border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5">
                                        Mark all read
                                    </button>
                                </form>
                            @endif
                        </div>

                        <div class="max-h-[360px] overflow-auto">
                            @if(!$notifEnabled)
                                <div class="p-4 text-sm text-gray-600 dark:text-white/70">
                                    Notifications table not found. Run:
                                    <div class="mt-2 text-xs bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-xl p-2">
                                        php artisan notifications:table<br>
                                        php artisan migrate
                                    </div>
                                </div>
                            @else
                                @forelse($latestNotifs as $n)
                                    @php
                                        $isUnread = is_null($n->read_at);
                                        $data = (array) $n->data;
                                        $title = $data['title'] ?? 'Notification';
                                        $msg = $data['message'] ?? '';
                                        $url = $data['url'] ?? route('student.notifications.index');
                                    @endphp

                                    <div class="px-4 py-3 border-b border-gray-100 dark:border-white/10 {{ $isUnread ? 'bg-blue-50/60 dark:bg-white/5' : '' }}">
                                        <div class="flex items-start justify-between gap-3">
                                            <a href="{{ $url }}" class="min-w-0">
                                                <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ $title }}
                                                </div>
                                                @if($msg)
                                                    <div class="text-xs text-gray-600 dark:text-white/70 mt-1">
                                                        {{ $msg }}
                                                    </div>
                                                @endif
                                                <div class="text-[11px] text-gray-500 dark:text-white/50 mt-2">
                                                    {{ optional($n->created_at)->diffForHumans() }}
                                                </div>
                                            </a>

                                            <div class="shrink-0">
                                                @if($isUnread)
                                                    <form method="POST" action="{{ route('student.notifications.read', $n->id) }}">
                                                        @csrf
                                                        <button class="text-[11px] px-2 py-1 rounded-full border border-blue-200 text-blue-700 hover:bg-blue-50">
                                                            Read
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('student.notifications.unread', $n->id) }}">
                                                        @csrf
                                                        <button class="text-[11px] px-2 py-1 rounded-full border border-gray-200 dark:border-white/10 text-gray-700 dark:text-white/70 hover:bg-gray-50 dark:hover:bg-white/5">
                                                            Unread
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-sm text-gray-500 dark:text-white/60">
                                        No notifications yet.
                                    </div>
                                @endforelse
                            @endif
                        </div>

                        <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-t border-gray-200 dark:border-white/10">
                            <a href="{{ route('student.notifications.index') }}"
                               class="text-sm text-blue-700 dark:text-blue-300 font-semibold hover:underline">
                                View all notifications →
                            </a>
                        </div>
                    </div>
                </div>

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
                         class="absolute right-0 mt-2 w-56 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-lg overflow-hidden"
                         style="display:none">
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
                            <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>

            </div>

        </div>
    </div>
</header>