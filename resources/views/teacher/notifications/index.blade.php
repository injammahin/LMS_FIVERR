@extends('layouts.teacher')

@section('title', 'Notifications')

@section('content')
    <div class="space-y-6">

        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Notifications</h1>
                <p class="text-sm text-gray-500">All updates from your courses.</p>
            </div>

            <form method="POST" action="{{ route('teacher.notifications.read_all') }}">
                @csrf
                <button class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                    <i class="fa-solid fa-check-double mr-2"></i> Mark all read
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="divide-y divide-gray-100">
                @forelse($notifications as $n)
                    @php
                        $isUnread = is_null($n->read_at);
                        $data = $n->data ?? [];
                        $title = $data['title'] ?? 'New notification';
                        $desc = $data['message'] ?? null;
                        $url = $data['url'] ?? null;
                    @endphp

                    <div class="p-5 flex items-start justify-between gap-4 {{ $isUnread ? 'bg-blue-50/40' : '' }}">
                        <div class="flex gap-3">
                            <div class="w-10 h-10 rounded-xl border border-gray-200 bg-white grid place-items-center">
                                <i class="fa-regular fa-bell"></i>
                            </div>

                            <div>
                                <div class="font-semibold text-gray-900">{{ $title }}</div>
                                @if($desc)
                                    <div class="text-sm text-gray-600 mt-1">{{ $desc }}</div>
                                @endif
                                <div class="text-xs text-gray-500 mt-1">{{ $n->created_at->diffForHumans() }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($url)
                                <a href="{{ $url }}" class="px-3 py-2 rounded-xl bg-blue-600 text-white hover:bg-blue-700">
                                    Open
                                </a>
                            @endif

                            @if($isUnread)
                                <form method="POST" action="{{ route('teacher.notifications.read', $n->id) }}">
                                    @csrf
                                    <button class="px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50">
                                        Mark read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center text-gray-500">No notifications yet.</div>
                @endforelse
            </div>
        </div>

        {{ $notifications->links() }}
    </div>
@endsection