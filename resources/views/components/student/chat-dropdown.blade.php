@php
    // =========================
    // CHAT SYSTEM
    // =========================

    use App\Models\Message;

    $chatUnreadCount = Message::where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->count();

    $chatUnreadUsers = Message::with('sender')
        ->where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->latest()
        ->take(5)
        ->get()
        ->unique('sender_id');
@endphp
{{-- =========================
CHAT DROPDOWN
========================= --}}

<div class="relative" x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false">

    <a href="{{ route('student.chat.users') }}"
        class="relative h-10 w-10 rounded-xl border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 grid place-items-center text-gray-700 dark:text-white/80">

        <i class="fa-solid fa-comments text-[16px]"></i>

        @if($chatUnreadCount > 0)
            <span
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[11px] font-bold grid place-items-center border-2 border-white dark:border-slate-900">
                {{ $chatUnreadCount > 9 ? '9+' : $chatUnreadCount }}
            </span>
        @endif

    </a>


    {{-- DROPDOWN --}}
    <div x-show="open" x-transition
        class="absolute right-0 mt-2 w-[360px] rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-900 shadow-xl overflow-hidden"
        style="display:none">

        <div class="px-4 py-3 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">

            <div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                    Messages
                </div>

                <div class="text-xs text-gray-500 dark:text-white/60">
                    {{ $chatUnreadCount }} unread
                </div>
            </div>

            <a href="{{ route('student.chat.users') }}" class="text-xs text-blue-600 hover:underline">
                Open chat
            </a>

        </div>


        <div class="max-h-[340px] overflow-auto">

            @forelse($chatUnreadUsers as $msg)

                <a href="{{ route('student.chat.view', $msg->sender_id) }}"
                    class="flex gap-3 px-4 py-3 border-b border-gray-100 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 transition">

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($msg->sender->name) }}"
                        class="w-10 h-10 rounded-full">

                    <div class="flex-1 min-w-0">

                        <div class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                            {{ $msg->sender->name }}
                        </div>

                        <div class="text-xs text-gray-600 dark:text-white/70 truncate">

                            {{ $msg->message ?? 'Sent a file' }}

                        </div>

                        <div class="text-[11px] text-gray-400 mt-1">

                            {{ $msg->created_at->diffForHumans() }}

                        </div>

                    </div>

                    <span class="w-2 h-2 bg-red-500 rounded-full mt-2"></span>

                </a>

            @empty

                <div class="p-6 text-center text-sm text-gray-500">
                    No unread messages
                </div>

            @endforelse

        </div>


        <div class="px-4 py-3 bg-gray-50 dark:bg-white/5 border-t border-gray-200 dark:border-white/10">

            <a href="{{ route('student.chat.users') }}"
                class="text-sm text-blue-700 dark:text-blue-300 font-semibold hover:underline">

                View all conversations →

            </a>

        </div>

    </div>

</div>