@php
    use App\Models\Message;
    use App\Models\User;

    $admin = User::where('role', 'admin')->first();

    $messages = Message::where(function ($q) use ($admin) {
        $q->where('sender_id', auth()->id())
            ->where('receiver_id', $admin->id);
    })
        ->orWhere(function ($q) use ($admin) {
            $q->where('sender_id', $admin->id)
                ->where('receiver_id', auth()->id());
        })
        ->latest()->take(30)->get()->reverse();

    $unreadCount = Message::where('sender_id', $admin->id)
        ->where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->count();
@endphp


<div class="relative" x-data="{open:false,typing:false}">

    <!-- CHAT BUTTON -->

    <button @click="open=!open; markSeen()"
        class="relative w-10 h-10 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 grid place-items-center">

        <i class="fa-solid fa-comments text-gray-700"></i>

        @if($unreadCount)
            <span id="chatBadge"
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] grid place-items-center">
                {{ $unreadCount }}
            </span>
        @endif

    </button>



    <!-- CHAT PANEL -->

    <div x-show="open" @click.outside="open=false" x-transition
        class="absolute right-0 mt-3 w-[360px] bg-white rounded-2xl shadow-2xl border flex flex-col overflow-hidden"
        style="display:none">


        <!-- HEADER -->

        <div class="px-4 py-3 border-b flex items-center gap-2 bg-gradient-to-r from-blue-50 to-indigo-50">

            <img src="https://ui-avatars.com/api/?name=Admin" class="w-8 h-8 rounded-full">

            <div>
                <div class="text-sm font-semibold">Admin</div>
                <div class="text-[10px] text-green-600">Online</div>
            </div>

        </div>



        <!-- CHAT BODY -->

        <div id="chatMini" class="flex-1 max-h-[320px] overflow-y-auto p-4 space-y-3 bg-gray-50">

            @foreach($messages as $msg)

                @if($msg->sender_id == auth()->id())

                    <div class="flex justify-end">

                        <div class="bg-blue-600 text-white text-xs px-3 py-2 rounded-2xl shadow max-w-[70%]">

                            {{ $msg->message }}

                            <div class="text-[9px] text-right opacity-70 mt-1">
                                {{ $msg->created_at->format('H:i') }}
                            </div>

                        </div>

                    </div>

                @else

                    <div class="flex gap-2">

                        <img src="https://ui-avatars.com/api/?name=Admin" class="w-6 h-6 rounded-full">

                        <div class="bg-white text-xs px-3 py-2 rounded-2xl shadow max-w-[70%]">

                            {{ $msg->message }}

                            <div class="text-[9px] text-gray-400 mt-1">
                                {{ $msg->created_at->format('H:i') }}
                            </div>

                        </div>

                    </div>

                @endif

            @endforeach


            <!-- TYPING -->

            <div x-show="typing" class="flex gap-2">

                <img src="https://ui-avatars.com/api/?name=Admin" class="w-6 h-6 rounded-full">

                <div class="bg-white px-3 py-2 rounded-xl shadow text-xs text-gray-600 flex gap-1">

                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>

                </div>

            </div>

        </div>



        <!-- INPUT -->

        <form id="miniChatForm" class="border-t p-3 flex items-center gap-2 bg-white">

            @csrf

            <input type="hidden" name="receiver_id" value="{{ $admin->id }}">

            <input id="miniMessage" type="text" name="message" placeholder="Type message..."
                @input="typing=true; setTimeout(()=>typing=false,1000)"
                class="flex-1 border rounded-full px-3 py-1 text-xs focus:ring-2 focus:ring-blue-400">

            <button type="submit" class="bg-blue-600 text-white w-8 h-8 rounded-full grid place-items-center">

                <i class="fa-solid fa-paper-plane text-xs"></i>

            </button>

        </form>

    </div>
</div>



<style>
    .dot {
        width: 6px;
        height: 6px;
        background: #9ca3af;
        border-radius: 50%;
        animation: typing 1.2s infinite;
    }

    .dot:nth-child(2) {
        animation-delay: .2s
    }

    .dot:nth-child(3) {
        animation-delay: .4s
    }

    @keyframes typing {
        0% {
            opacity: .2
        }

        50% {
            opacity: 1
        }

        100% {
            opacity: .2
        }
    }
</style>



<script>

    document.addEventListener("DOMContentLoaded", function () {

        const form = document.getElementById("miniChatForm");
        const input = document.getElementById("miniMessage");
        const chat = document.getElementById("chatMini");

        chat.scrollTop = chat.scrollHeight;


        form.addEventListener("submit", function (e) {

            e.preventDefault();

            let message = input.value.trim();

            if (message === "") return;

            let formData = new FormData(form);

            fetch("{{ route('chat.send') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {

                    const html = `
<div class="flex justify-end">

<div class="bg-blue-600 text-white text-xs px-3 py-2 rounded-2xl shadow max-w-[70%]">

${data.message}

<div class="text-[9px] text-right opacity-70 mt-1">
${data.time}
</div>

</div>

</div>
`;

                    chat.insertAdjacentHTML("beforeend", html);

                    chat.scrollTop = chat.scrollHeight;

                    input.value = "";

                });

        });

    });


    function markSeen() {

        fetch("{{ route('chat.seen') }}", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
        });

        let badge = document.getElementById("chatBadge");

        if (badge) badge.remove();

    }

</script>