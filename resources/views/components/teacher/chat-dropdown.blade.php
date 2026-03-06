@php
    use App\Models\Message;
    use App\Models\User;

    $unreadMessages = Message::where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->latest()
        ->take(10)
        ->get();

    $unreadCount = Message::where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->count();
@endphp


<div x-data="{open:false, timer:null}" class="relative" @mouseenter="clearTimeout(timer); open=true"
    @mouseleave="timer=setTimeout(()=>open=false,200)">

    <!-- CHAT ICON -->

    <a href="{{ route('teacher.chat.users') }}"
        class="relative w-10 h-10 rounded-xl border border-gray-200 bg-white hover:bg-blue-50 transition grid place-items-center shadow-sm">

        <i class="fa-solid fa-comments text-gray-700"></i>

        @if($unreadCount)
            <span
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] grid place-items-center animate-pulse">
                {{ $unreadCount }}
            </span>
        @endif

    </a>


    <!-- DROPDOWN -->

    <div x-show="open" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-3 w-[340px] bg-white rounded-2xl shadow-2xl border border-gray-200 z-50 overflow-hidden"
        style="display:none">

        <!-- HEADER -->

        <div class="px-4 py-3 border-b flex justify-between items-center bg-gray-50">

            <div class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                <i class="fa-solid fa-comment-dots text-blue-500"></i>
                Unread Messages
            </div>

            <span class="text-xs text-gray-400">
                {{ $unreadCount }} new
            </span>

        </div>


        <!-- MESSAGE LIST -->

        <div class="max-h-[320px] overflow-y-auto">

            @forelse($unreadMessages as $msg)

                @php
                    $sender = User::find($msg->sender_id);
                @endphp

                <a href="{{ route('chat.view', $sender->id) }}"
                    class="flex gap-3 px-4 py-3 hover:bg-blue-50 transition border-b items-center">

                    <!-- AVATAR -->

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($sender->name) }}&background=random"
                        class="w-9 h-9 rounded-full shadow" />


                    <!-- MESSAGE -->

                    <div class="flex-1 min-w-0">

                        <div class="flex justify-between items-center">

                            <span class="text-sm font-semibold text-gray-800 truncate">
                                {{ $sender->name }}
                            </span>

                            <span class="text-[10px] text-gray-400">
                                {{ $msg->created_at->diffForHumans() }}
                            </span>

                        </div>

                        <div class="text-xs text-gray-500 truncate flex items-center gap-1">

                            @if($msg->message)

                                <i class="fa-solid fa-message text-gray-400 text-[10px]"></i>
                                {{ $msg->message }}

                            @else

                                <i class="fa-solid fa-paperclip text-gray-400 text-[10px]"></i>
                                File sent

                            @endif

                        </div>

                    </div>

                </a>

            @empty

                <div class="text-center text-xs text-gray-400 py-10">

                    <i class="fa-regular fa-face-smile text-lg mb-2"></i>

                    <p>No unread messages</p>

                </div>

            @endforelse

        </div>


        <!-- FOOTER -->

        <div class="p-3 text-center border-t bg-gray-50">

            <a href="{{ route('teacher.chat.users') }}"
                class="text-sm text-blue-600 hover:text-blue-700 font-medium flex items-center justify-center gap-1">

                <i class="fa-solid fa-comments"></i>
                Open Chat

            </a>

        </div>

    </div>

</div>