@php
    use App\Models\Message;

    $unseenChats = Message::with('sender')
        ->where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->latest()
        ->take(5)
        ->get();

    $unreadCount = Message::where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->count();
@endphp


<div class="relative" x-data="{open:false}" @mouseenter="open=true" @mouseleave="open=false">

    <!-- CHAT ICON -->

    <a href="{{ route('chat.users') }}"
        class="relative flex items-center justify-center w-11 h-11 text-gray-500 transition bg-white border border-gray-200 rounded-full hover:bg-blue-50 hover:text-blue-600 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400 dark:hover:bg-gray-800">

        <!-- FONT AWESOME ICON -->

        <i class="fa-solid fa-comments text-sm"></i>


        <!-- UNREAD BADGE -->

        @if($unreadCount > 0)

            <span
                class="absolute -top-1 -right-1 min-w-[20px] h-[20px] px-1 text-[11px] flex items-center justify-center text-white bg-red-500 rounded-full animate-pulse">

                {{ $unreadCount }}

            </span>

        @endif

    </a>


    <!-- DROPDOWN -->

    <div x-show="open" x-transition
        class="absolute right-0 mt-3 w-[360px] bg-white rounded-xl shadow-xl border border-gray-200 dark:bg-gray-900 dark:border-gray-800"
        style="display:none">

        <!-- HEADER -->

        <div class="flex items-center justify-between px-4 py-3 border-b">

            <h3 class="font-semibold text-gray-800 dark:text-white flex items-center gap-2">

                <i class="fa-solid fa-comment-dots text-blue-500"></i>

                Unread Messages

            </h3>

            <span class="text-xs text-gray-400">

                {{ $unreadCount }} new

            </span>

        </div>



        <!-- MESSAGE LIST -->

        <div class="max-h-[320px] overflow-y-auto">

            @forelse($unseenChats as $chat)

                <a href="{{ route('chat.view', $chat->sender_id) }}"
                    class="flex items-start gap-3 px-4 py-3 border-b hover:bg-gray-50 dark:hover:bg-gray-800 transition">

                    <!-- AVATAR -->

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($chat->sender->name) }}&background=random"
                        class="w-10 h-10 rounded-full shadow">


                    <!-- MESSAGE BODY -->

                    <div class="flex-1">

                        <div class="flex items-center justify-between">

                            <span class="text-sm font-semibold text-gray-800 dark:text-white">

                                {{ $chat->sender->name }}

                            </span>

                            <span class="text-xs text-gray-400">

                                {{ $chat->created_at->diffForHumans() }}

                            </span>

                        </div>


                        <p class="text-sm text-gray-500 truncate flex items-center gap-1">

                            @if($chat->message)

                                <i class="fa-solid fa-message text-gray-400 text-xs"></i>

                                {{ $chat->message }}

                            @else

                                <i class="fa-solid fa-paperclip text-gray-400 text-xs"></i>

                                Sent a file

                            @endif

                        </p>

                    </div>

                </a>

            @empty

                <div class="p-6 text-center text-gray-400">

                    <i class="fa-regular fa-face-smile text-lg mb-1"></i>

                    <p class="text-sm">No unread messages</p>

                </div>

            @endforelse

        </div>


        <!-- FOOTER -->

        <div class="p-3 text-center border-t">

            <a href="{{ route('chat.users') }}"
                class="text-sm font-medium text-blue-600 hover:underline flex items-center justify-center gap-1">

                <i class="fa-solid fa-comments"></i>

                Open Chat

            </a>

        </div>

    </div>

</div>