@extends('layouts.admin')

@section('content')

    <div class="max-w-6xl mx-auto py-6">

        <div class="bg-white rounded-2xl shadow-xl flex flex-col h-[80vh] overflow-hidden">

            <!-- HEADER -->

            <div class="flex items-center justify-between px-6 py-4 border-b bg-gradient-to-r from-blue-50 to-indigo-50">

                <div class="flex items-center gap-3">

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($receiver->name) }}&background=random"
                        class="w-11 h-11 rounded-full shadow">

                    <div>

                        <div class="font-semibold text-gray-800 flex items-center gap-2">

                            {{ $receiver->name }}

                            <span class="w-2 h-2 bg-green-500 rounded-full"></span>

                        </div>

                        <div class="text-xs text-gray-500">

                            {{ ucfirst($receiver->role) }} • Online

                        </div>

                    </div>

                </div>

                <div class="flex items-center gap-4 text-gray-500">

                    {{-- <i class="fa-solid fa-phone cursor-pointer hover:text-blue-500"></i>

                    <i class="fa-solid fa-video cursor-pointer hover:text-blue-500"></i> --}}

                    <i class="fa-solid fa-ellipsis cursor-pointer hover:text-blue-500"></i>

                </div>

            </div>



            <!-- CHAT BODY -->

            <div id="chatContainer"
                class="flex-1 overflow-y-auto p-6 space-y-5 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] bg-gray-50">

                @foreach($messages as $msg)

                    @if($msg->sender_id == auth()->id())

                        <!-- SENT MESSAGE -->

                        <div class="flex justify-end animate-fade-in">

                            <div class="max-w-xs bg-blue-500 text-white px-4 py-3 rounded-2xl shadow-lg">

                                @if($msg->message)

                                    <p class="text-sm leading-relaxed">

                                        {{ $msg->message }}

                                    </p>

                                @endif


                                @if($msg->file)

                                    <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                        class="flex items-center gap-1 mt-2 text-xs underline">

                                        <i class="fa-solid fa-paperclip"></i>

                                        Download File

                                    </a>

                                @endif


                                <div class="flex justify-end items-center gap-1 text-[10px] opacity-80 mt-1">

                                    <span>

                                        {{ $msg->created_at->format('H:i') }}

                                    </span>

                                    @if($msg->seen_at)

                                        <span class="text-blue-200">

                                            <i class="fa-solid fa-check-double"></i>

                                            Seen

                                        </span>

                                    @else

                                        <span class="text-blue-200 animate-pulse">

                                            <i class="fa-solid fa-check"></i>

                                            Delivered

                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>

                    @else

                        <!-- RECEIVED MESSAGE -->

                        <div class="flex items-end gap-2 animate-fade-in">

                            <img src="https://ui-avatars.com/api/?name={{ urlencode($receiver->name) }}&background=random"
                                class="w-7 h-7 rounded-full shadow">

                            <div class="max-w-xs bg-white px-4 py-3 rounded-2xl shadow">

                                @if($msg->message)

                                    <p class="text-sm text-gray-800 leading-relaxed">

                                        {{ $msg->message }}

                                    </p>

                                @endif

                                @if($msg->file)

                                    <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                        class="flex items-center gap-1 mt-2 text-xs text-blue-600">

                                        <i class="fa-solid fa-paperclip"></i>

                                        Download File

                                    </a>

                                @endif

                                <div class="text-[10px] text-gray-400 mt-1">

                                    {{ $msg->created_at->format('H:i') }}

                                </div>

                            </div>

                        </div>

                    @endif

                @endforeach


                <!-- TYPING INDICATOR -->

                <div id="typingIndicator" class="hidden flex items-center gap-2">

                    <img src="https://ui-avatars.com/api/?name={{ urlencode($receiver->name) }}"
                        class="w-6 h-6 rounded-full">

                    <div class="bg-white px-3 py-2 rounded-xl shadow text-xs text-gray-600 flex gap-1">

                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>

                    </div>

                </div>

            </div>



            <!-- INPUT BAR -->

            <form id="chatForm" enctype="multipart/form-data" class="border-t p-4 flex items-center gap-3 bg-white">

                @csrf
                <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">

                <label class="cursor-pointer text-gray-500 hover:text-blue-500">
                    <i class="fa-solid fa-paperclip text-sm"></i>
                    <input type="file" name="file" class="hidden">
                </label>

                <input id="messageInput" type="text" name="message" placeholder="Type your message..."
                    class="flex-1 border rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">

                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full shadow flex items-center gap-1">

                    <i class="fa-solid fa-paper-plane"></i>
                    Send
                </button>

            </form>

        </div>

    </div>



    <style>
        /* smooth message animation */

        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fadeIn .25s ease-in-out;
        }


        /* typing dots */

        .dot {
            width: 6px;
            height: 6px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing 1.2s infinite;
        }

        .dot:nth-child(2) {
            animation-delay: .2s;
        }

        .dot:nth-child(3) {
            animation-delay: .4s;
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

            const form = document.getElementById("chatForm");
            const input = document.getElementById("messageInput");
            const chatContainer = document.getElementById("chatContainer");

            chatContainer.scrollTop = chatContainer.scrollHeight;

            form.addEventListener("submit", function (e) {

                e.preventDefault();

                const message = input.value.trim();

                if (message === "") return;

                const formData = new FormData(form);

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
                <div class="flex justify-end animate-fade-in">

                    <div class="max-w-xs bg-blue-500 text-white px-4 py-3 rounded-2xl shadow-lg">

                        <p class="text-sm leading-relaxed">
                            ${data.message}
                        </p>

                        <div class="flex justify-end items-center gap-1 text-[10px] opacity-80 mt-1">

                            <span>${data.time}</span>

                            <span class="text-blue-200">
                                <i class="fa-solid fa-check"></i> Delivered
                            </span>

                        </div>

                    </div>

                </div>
                `;

                        chatContainer.insertAdjacentHTML("beforeend", html);

                        chatContainer.scrollTop = chatContainer.scrollHeight;

                        /* CLEAR INPUT */
                        input.value = "";

                    });

            });

        });


        /* typing indicator */

        const input = document.getElementById('messageInput');
        const typing = document.getElementById('typingIndicator');

        input.addEventListener("input", function () {

            typing.classList.remove("hidden");

            clearTimeout(window.typingTimer);

            window.typingTimer = setTimeout(() => {
                typing.classList.add("hidden");
            }, 1200);

        });

    </script>

@endsection