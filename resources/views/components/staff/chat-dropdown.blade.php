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
        ->latest()
        ->take(30)
        ->get()
        ->reverse();

    $unreadCount = Message::where('sender_id', $admin->id)
        ->where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->count();
@endphp


<div class="relative" x-data="chatDropdown()">

    <!-- CHAT BUTTON -->

    <button @click="toggleChat"
        class="relative w-10 h-10 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 grid place-items-center">

        <i class="fa-solid fa-comments text-gray-700"></i>

        @if($unreadCount)
            <span id="staffChatBadge"
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-red-600 text-white text-[10px] grid place-items-center">
                {{ $unreadCount }}
            </span>
        @endif

    </button>



    <!-- CHAT PANEL -->

    <div x-show="open" @click.outside="open=false" x-transition
        class="absolute right-0 mt-3 w-[360px] bg-white rounded-2xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden"
        style="display:none">

        <!-- HEADER -->

        <div class="px-4 py-3 border-b flex items-center justify-between bg-gradient-to-r from-blue-50 to-indigo-50">

            <div class="flex items-center gap-2">

                <img src="https://ui-avatars.com/api/?name=Admin" class="w-8 h-8 rounded-full">

                <div>
                    <div class="text-sm font-semibold text-gray-800">Admin Support</div>
                    <div class="text-[10px] text-green-600">Online</div>
                </div>

            </div>

            <i class="fa-solid fa-headset text-blue-600"></i>

        </div>



        <!-- CHAT BODY -->

        <div id="staffChatBox" class="h-[320px] overflow-y-auto p-4 space-y-3 bg-gray-50">

            @foreach($messages as $msg)

                @if($msg->sender_id == auth()->id())

                    <div class="flex justify-end">

                        <div class="bg-blue-600 text-white text-xs px-3 py-2 rounded-2xl shadow max-w-[70%]">

                            @if($msg->message)
                                {{ $msg->message }}
                            @endif

                            @if($msg->file)
                                <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                    class="block underline text-[10px] mt-1">
                                    📎 Download file
                                </a>
                            @endif

                            <div class="text-[9px] text-right opacity-70 mt-1">
                                {{ $msg->created_at->format('H:i') }}
                                @if($msg->seen_at) ✔✔ @else ✔ @endif
                            </div>

                        </div>

                    </div>

                @else

                    <div class="flex gap-2">

                        <img src="https://ui-avatars.com/api/?name=Admin" class="w-6 h-6 rounded-full">

                        <div class="bg-white text-xs px-3 py-2 rounded-2xl shadow max-w-[70%]">

                            @if($msg->message)
                                {{ $msg->message }}
                            @endif

                            @if($msg->file)
                                <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                    class="text-blue-600 underline text-[10px]">
                                    📎 Download file
                                </a>
                            @endif

                            <div class="text-[9px] text-gray-400 mt-1">
                                {{ $msg->created_at->format('H:i') }}
                            </div>

                        </div>

                    </div>

                @endif

            @endforeach

        </div>



        <!-- MESSAGE INPUT -->

        <form x-ref="chatForm" @submit.prevent="sendMessage" class="border-t p-3 flex items-center gap-2"
            enctype="multipart/form-data">

            @csrf

            <input type="hidden" name="receiver_id" value="{{ $admin->id }}">

            <label class="cursor-pointer text-gray-400 hover:text-blue-500">
                <i class="fa-solid fa-paperclip"></i>
                <input type="file" name="file" class="hidden">
            </label>

            <input x-ref="messageInput" type="text" name="message" placeholder="Type message..."
                class="flex-1 border rounded-full px-3 py-1 text-xs focus:ring-2 focus:ring-blue-400">

            <button x-ref="sendBtn" type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white w-8 h-8 rounded-full grid place-items-center">

                <i class="fa-solid fa-paper-plane text-xs"></i>

            </button>

        </form>

    </div>

</div>


@php
    // use App\Models\Message;
    // use App\Models\User;

    $admin = User::where('role', 'admin')->first();

    $messages = Message::where(function ($q) use ($admin) {
        $q->where('sender_id', auth()->id())
            ->where('receiver_id', $admin->id);
    })
        ->orWhere(function ($q) use ($admin) {
            $q->where('sender_id', $admin->id)
                ->where('receiver_id', auth()->id());
        })
        ->latest()
        ->take(30)
        ->get()
        ->reverse();

    $unreadCount = Message::where('sender_id', $admin->id)
        ->where('receiver_id', auth()->id())
        ->whereNull('seen_at')
        ->count();
@endphp
<script>

    function chatDropdown() {

        return {

            open: false,

            sending: false,

            toggleChat() {

                this.open = !this.open;

                if (this.open) {
                    markSeen();
                }

            },

            sendMessage() {

                if (this.sending) return;

                let form = this.$refs.chatForm;
                let input = this.$refs.messageInput;
                let btn = this.$refs.sendBtn;

                let message = input.value.trim();

                if (message === "") return;

                this.sending = true;

                btn.disabled = true;

                let formData = new FormData(form);

                fetch("{{ route('chat.send') }}", {

                    method: "POST",

                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "accept": "application/json"
                    },

                    body: formData

                })

                    .then(res => res.json())

                    .then(data => {

                        /* RESET FORM */

                        form.reset();

                        /* APPEND MESSAGE */

                        let chatBox = document.getElementById("staffChatBox");

                        let html = `
<div class="flex justify-end">

<div class="bg-blue-600 text-white text-xs px-3 py-2 rounded-2xl shadow max-w-[70%]">

${data.message}

<div class="text-[9px] text-right opacity-70 mt-1">

${data.time} ✔

</div>

</div>

</div>
`;

                        chatBox.insertAdjacentHTML("beforeend", html);

                        /* SCROLL */

                        chatBox.scrollTop = chatBox.scrollHeight;

                    })

                    .finally(() => {

                        this.sending = false;

                        btn.disabled = false;

                    });

            }

        }

    }



    function markSeen() {

        fetch("{{ route('chat.seen') }}", {

            method: "POST",

            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }

        })

        let badge = document.getElementById("staffChatBadge");

        if (badge) {
            badge.remove();
        }

    }


    document.addEventListener("DOMContentLoaded", () => {

        let chatBox = document.getElementById("staffChatBox");

        if (chatBox) {
            chatBox.scrollTop = chatBox.scrollHeight;
        }

    });

</script>