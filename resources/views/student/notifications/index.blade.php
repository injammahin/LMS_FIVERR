@extends('layouts.student')

@section('title', 'Notifications')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 py-8 space-y-6">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Notifications</h1>
                <p class="text-sm text-gray-500">Manage your alerts (read/unread).</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('student.dashboard') }}"
                   class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-sm">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back
                </a>

                <form method="POST" action="{{ route('student.notifications.readAll') }}">
                    @csrf
                    <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm">
                        Mark all read
                    </button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-green-200 bg-green-50 text-green-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @isset($noDb)
            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900 text-sm">
                Notifications table not found. Run:
                <div class="mt-2 text-xs bg-white/70 border border-amber-200 rounded-xl p-2">
                    php artisan notifications:table<br>
                    php artisan migrate
                </div>
            </div>
        @endisset

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-900">All Notifications</div>

                <div class="flex items-center gap-2 text-sm">
                    <a href="{{ route('student.notifications.index', ['filter'=>'all']) }}"
                       class="px-3 py-1.5 rounded-full border {{ ($filter ?? 'all') === 'all' ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 hover:bg-gray-50' }}">
                        All
                    </a>
                    <a href="{{ route('student.notifications.index', ['filter'=>'unread']) }}"
                       class="px-3 py-1.5 rounded-full border {{ ($filter ?? 'all') === 'unread' ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 hover:bg-gray-50' }}">
                        Unread
                    </a>
                </div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($notifications as $n)
                    @php
                        $isUnread = is_null($n->read_at);
                        $data = (array)$n->data;
                        $title = $data['title'] ?? 'Notification';
                        $msg = $data['message'] ?? '';
                        $url = $data['url'] ?? null;
                    @endphp

                    <div class="p-4 {{ $isUnread ? 'bg-blue-50/60' : '' }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ $title }}
                                </div>
                                @if($msg)
                                    <div class="text-sm text-gray-600 mt-1">
                                        {{ $msg }}
                                    </div>
                                @endif
                                <div class="text-xs text-gray-500 mt-2">
                                    {{ optional($n->created_at)->diffForHumans() }}
                                </div>

                                @if($url)
                                    <a class="text-sm text-blue-700 font-semibold hover:underline mt-2 inline-block" href="{{ $url }}">
                                        Open →
                                    </a>
                                @endif
                            </div>

                            <div class="shrink-0 flex gap-2">
                                @if($isUnread)
                                    <form method="POST" action="{{ route('student.notifications.read', $n->id) }}">
                                        @csrf
                                        <button class="text-xs px-3 py-1.5 rounded-full border border-blue-200 text-blue-700 hover:bg-blue-50">
                                            Mark read
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('student.notifications.unread', $n->id) }}">
                                        @csrf
                                        <button class="text-xs px-3 py-1.5 rounded-full border border-gray-200 text-gray-700 hover:bg-gray-50">
                                            Mark unread
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-gray-500">
                        No notifications found.
                    </div>
                @endforelse
            </div>

            <div class="p-4 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        </div>

    </div>
</div>
@endsection