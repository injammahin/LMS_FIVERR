@php
    use App\Helpers\MenuHelper;
    $menuGroups = MenuHelper::getMenuGroups();

    // Get current path
    $currentPath = request()->path();
@endphp

<aside id="sidebar"
    class="fixed flex flex-col mt-0 top-0 px-5 left-0
    bg-white dark:bg-gray-900 dark:border-gray-800
    text-gray-900 h-screen transition-all duration-300 ease-in-out
    z-[60] border-r border-gray-200 shadow-md"
    x-data="{
        openSubmenus: {},
        init() {
            this.initializeActiveMenus();
        },
        initializeActiveMenus() {
            const currentPath = '{{ $currentPath }}';

            @foreach ($menuGroups as $groupIndex => $menuGroup)
                @foreach ($menuGroup['items'] as $itemIndex => $item)
                    @if (isset($item['subItems']))
                        @foreach ($item['subItems'] as $subItem)
                            if (currentPath === '{{ ltrim($subItem['path'], '/') }}' ||
                                window.location.pathname === '{{ $subItem['path'] }}') {
                                this.openSubmenus['{{ $groupIndex }}-{{ $itemIndex }}'] = true;
                            }
                        @endforeach
                    @endif
                @endforeach
            @endforeach
        },
        toggleSubmenu(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            const newState = !this.openSubmenus[key];

            if (newState) {
                this.openSubmenus = {};
            }

            this.openSubmenus[key] = newState;
        },
        isSubmenuOpen(groupIndex, itemIndex) {
            const key = groupIndex + '-' + itemIndex;
            return this.openSubmenus[key] || false;
        },
        isActive(path) {
            return window.location.pathname === path || '{{ $currentPath }}' === path.replace(/^\//, '');
        }
    }"
    :class="{
        'w-[290px]': $store.sidebar.isExpanded || $store.sidebar.isMobileOpen || $store.sidebar.isHovered,
        'w-[90px]': !$store.sidebar.isExpanded && !$store.sidebar.isHovered,
        'translate-x-0': $store.sidebar.isMobileOpen,
        '-translate-x-full xl:translate-x-0': !$store.sidebar.isMobileOpen
    }"
    @mouseenter="if (!$store.sidebar.isExpanded) $store.sidebar.setHovered(true)"
    @mouseleave="$store.sidebar.setHovered(false)">

    <!-- Logo Section -->
    <div class="pt-6 pb-6 flex items-center transition-all duration-200"
        :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'xl:justify-center' : 'justify-start'">

        <!-- Expanded Sidebar Logo Area -->
        <div x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
            x-cloak
            class="w-full flex items-center gap-4">

            <!-- Main LMS Logo Light Mode -->
            <img class="dark:hidden h-16 w-auto object-contain shrink-0"
                src="{{ asset('img/logo_lms.png') }}"
                alt="Logo" />

            <!-- Main LMS Logo Dark Mode -->
            <img class="hidden dark:block h-16 w-auto object-contain shrink-0"
                src="{{ asset('img/logo_lms.png') }}"
                alt="Logo" />

            <!-- Hebrew Letters Image -->
            <img class="h-9 w-auto object-contain shrink-0"
                src="{{ asset('img/text.png') }}"
                alt="י ה ו ה" />

            {{-- 
                If you do not want to use image and want text instead,
                remove the image above and use this span:

                <span class="text-2xl font-bold tracking-[0.35em] text-gray-800 dark:text-white"
                    style="font-family: 'Times New Roman', serif;">
                    י ה ו ה
                </span>
            --}}
        </div>

        <!-- Collapsed Sidebar Small Logo -->
        <div x-show="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen"
            x-cloak
            class="flex items-center justify-center w-full">
            <img class="h-8 w-auto object-contain"
                src="{{ asset('img/logo_lms.png') }}"
                alt="Logo" />
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="flex flex-col overflow-y-auto duration-300 ease-linear no-scrollbar space-y-4">
        <nav class="mb-6">
            <div class="flex flex-col gap-4">
                @foreach ($menuGroups as $groupIndex => $menuGroup)
                    <div>
                        <!-- Menu Group Title -->
                        <h2 class="mb-4 text-xs uppercase flex items-center space-x-2 leading-[20px] text-gray-400"
                            :class="(!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen) ? 'lg:justify-center' : 'justify-start'">
                            <template
                                x-if="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen">
                                <span class="font-semibold">{{ $menuGroup['title'] }}</span>
                            </template>

                            <template
                                x-if="!$store.sidebar.isExpanded && !$store.sidebar.isHovered && !$store.sidebar.isMobileOpen">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M5.99915 10.2451C6.96564 10.2451 7.74915 11.0286 7.74915 11.9951V12.0051C7.74915 12.9716 6.96564 13.7551 5.99915 13.7551C5.03265 13.7551 4.24915 12.9716 4.24915 12.0051V11.9951C4.24915 11.0286 5.03265 10.2451 5.99915 10.2451ZM17.9991 10.2451C18.9656 10.2451 19.7491 11.0286 19.7491 11.9951V12.0051C19.7491 12.9716 18.9656 13.7551 17.9991 13.7551C17.0326 13.7551 16.2491 12.9716 16.2491 12.0051V11.9951C16.2491 11.0286 17.0326 10.2451 17.9991 10.2451ZM13.7491 11.9951C13.7491 11.0286 12.9656 10.2451 11.9991 10.2451C11.0326 10.2451 10.2491 11.0286 10.2491 11.9951V12.0051C10.2491 12.9716 11.0326 13.7551 11.9991 13.7551C12.9656 13.7551 13.7491 12.9716 13.7491 12.0051V11.9951Z"
                                        fill="currentColor" />
                                </svg>
                            </template>
                        </h2>

                        <!-- Menu Items -->
                        <ul class="flex flex-col gap-2">
                            @foreach ($menuGroup['items'] as $itemIndex => $item)
                                <li>
                                    @if (isset($item['subItems']))
                                        <!-- Menu Item with Submenu -->
                                        <button @click="toggleSubmenu({{ $groupIndex }}, {{ $itemIndex }})"
                                            class="menu-item group w-full py-2 px-3 flex items-center gap-3 font-medium rounded-lg text-sm transition-all hover:bg-gray-100 dark:hover:bg-gray-700"
                                            :class="[isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ? 'bg-gray-200 text-gray-800 dark:bg-gray-600' : 'text-gray-600 dark:text-gray-300']">

                                            <!-- Icon -->
                                            <span
                                                :class="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) ? 'text-brand-600' : ''">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <!-- Text -->
                                            <span
                                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="menu-item-text flex items-center gap-2">
                                                {{ $item['name'] }}

                                                @if (!empty($item['new']))
                                                    <span
                                                        class="absolute right-10 text-xs px-2 py-0.5 rounded-full bg-red-500 text-white">
                                                        new
                                                    </span>
                                                @endif
                                            </span>

                                            <!-- Chevron Down Icon -->
                                            <svg x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="ml-auto w-5 h-5 transition-transform duration-200"
                                                :class="{
                                                    'rotate-180 text-brand-500': isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }})
                                                }"
                                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <!-- Submenu -->
                                        <div
                                            x-show="isSubmenuOpen({{ $groupIndex }}, {{ $itemIndex }}) && ($store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen)">
                                            <ul class="mt-2 space-y-1 ml-9">
                                                @foreach ($item['subItems'] as $subItem)
                                                    <li>
                                                        <a href="{{ $subItem['path'] }}"
                                                            class="menu-dropdown-item text-sm py-1 px-3 flex items-center justify-between rounded-md transition-all hover:bg-gray-100 dark:hover:bg-gray-700"
                                                            :class="isActive('{{ $subItem['path'] }}') ? 'bg-gray-200 text-gray-800 dark:bg-gray-600' : 'text-gray-600 dark:text-gray-300'">
                                                            {{ $subItem['name'] }}

                                                            @if (!empty($subItem['new']))
                                                                <span
                                                                    class="ml-auto text-xs px-2 py-0.5 rounded-full bg-red-500 text-white">
                                                                    new
                                                                </span>
                                                            @endif

                                                            @if (!empty($subItem['pro']))
                                                                <span
                                                                    class="ml-2 text-xs font-semibold text-white bg-blue-600 px-2 py-0.5 rounded">
                                                                    pro
                                                                </span>
                                                            @endif
                                                        </a>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @else
                                        <!-- Simple Menu Item -->
                                        <a href="{{ $item['path'] }}"
                                            class="menu-item group w-full py-2 px-3 flex items-center gap-3 font-medium rounded-lg text-sm transition-all hover:bg-gray-100 dark:hover:bg-gray-700"
                                            :class="isActive('{{ $item['path'] }}') ? 'bg-gray-200 text-gray-800 dark:bg-gray-600' : 'text-gray-600 dark:text-gray-300'">

                                            <!-- Icon -->
                                            <span class="text-sm text-gray-600 dark:text-gray-300">
                                                {!! MenuHelper::getIconSvg($item['icon']) !!}
                                            </span>

                                            <!-- Text -->
                                            <span
                                                x-show="$store.sidebar.isExpanded || $store.sidebar.isHovered || $store.sidebar.isMobileOpen"
                                                class="menu-item-text flex items-center gap-2">
                                                {{ $item['name'] }}

                                                @if (!empty($item['new']))
                                                    <span
                                                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-500 text-white">
                                                        new
                                                    </span>
                                                @endif
                                            </span>
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </nav>
    </div>
</aside>

<!-- Mobile Overlay -->
<div x-show="$store.sidebar.isMobileOpen"
    @click="$store.sidebar.setMobileOpen(false)"
    class="fixed inset-0 z-50 bg-gray-900/50 xl:hidden">
</div>