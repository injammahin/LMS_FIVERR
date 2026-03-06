@extends(auth()->user()->role == 'teacher' ? 'layouts.teacher' : 'layouts.admin')

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
                            {{ ucfirst($receiver->role) }}
                        </div>

                    </div>

                </div>

                <a href="{{ url()->previous() }}" class="text-gray-500 hover:text-blue-500">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>

            </div>


            <!-- CHAT BODY -->

            <div id="chatContainer" class="flex-1 overflow-y-auto p-6 space-y-5
            bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]
            bg-gray-50">

                @foreach($messages as $msg)

                    @if($msg->sender_id == auth()->id())

                        <div class="flex justify-end">

                            <div class="max-w-xs bg-blue-500 text-white px-4 py-3 rounded-2xl shadow-lg">

                                @if($msg->message)
                                    <p class="text-sm">{{ $msg->message }}</p>
                                @endif

                                @if($msg->file)
                                    <a href="{{ asset('storage/' . $msg->file) }}" target="_blank"
                                        class="flex items-center gap-1 mt-2 text-xs underline">

                                        <i class="fa-solid fa-paperclip"></i>
                                        Download File
                                    </a>
                                @endif

                                <div class="flex justify-end text-[10px] opacity-80 mt-1 gap-1">

                                    <span>{{ $msg->created_at->format('H:i') }}</span>

                                    @if($msg->seen_at)
                                        <span class="text-blue-200">
                                            <i class="fa-solid fa-check-double"></i> Seen
                                        </span>
                                    @else
                                        <span class="text-blue-200">
                                            <i class="fa-solid fa-check"></i> Delivered
                                        </span>
                                    @endif

                                </div>

                            </div>

                        </div>

                    @else

                        <div class="flex items-end gap-2">

                            <img src="https://ui-avatars.com/api/?name={{ urlencode($receiver->name) }}"
                                class="w-7 h-7 rounded-full">

                            <div class="max-w-xs bg-white px-4 py-3 rounded-2xl shadow">

                                @if($msg->message)
                                    <p class="text-sm text-gray-800">{{ $msg->message }}</p>
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

            </div>


            <!-- FILE PREVIEW -->

            <div id="filePreview" class="hidden px-5 py-2 bg-gray-100 border-t flex items-center justify-between text-sm">

                <div class="flex items-center gap-2 text-gray-600">

                    <i class="fa-solid fa-paperclip"></i>

                    <span id="fileName"></span>

                </div>

                <button type="button" id="removeFile" class="text-red-500 text-xs hover:underline">

                    Remove

                </button>

            </div>


            <!-- INPUT BAR -->

            <form id="chatForm" method="POST" enctype="multipart/form-data"
                class="border-t p-4 flex items-center gap-3 bg-white">

                @csrf

                <input type="hidden" name="receiver_id" value="{{ $receiver->id }}">


                <!-- FILE BUTTON -->

                <label class="cursor-pointer text-gray-500 hover:text-blue-500">

                    <i class="fa-solid fa-paperclip text-md"></i>

                    <input id="fileInput" type="file" name="file" class="hidden">

                </label>


                <!-- MESSAGE -->

                <input id="messageInput" type="text" name="message" placeholder="Type message..."
                    class="flex-1 border rounded-full px-4 py-2 focus:ring-2 focus:ring-blue-400 focus:outline-none">


                <!-- SEND -->

                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full flex items-center gap-1">

                    <i class="fa-solid fa-paper-plane"></i>

                    Send

                </button>

            </form>

        </div>

    </div>


    <script>

        const form = document.getElementById("chatForm");
        const input = document.getElementById("messageInput");
        const chat = document.getElementById("chatContainer");

        const fileInput = document.getElementById("fileInput");
        const filePreview = document.getElementById("filePreview");
        const fileName = document.getElementById("fileName");
        const removeFile = document.getElementById("removeFile");

        chat.scrollTop = chat.scrollHeight;


        /* FILE SELECT */

        fileInput.addEventListener("change", function () {

            if (fileInput.files.length) {

                filePreview.classList.remove("hidden");

                fileName.innerText = fileInput.files[0].name;

            }

        });


        /* REMOVE FILE */

        removeFile.addEventListener("click", function () {

            fileInput.value = "";

            filePreview.classList.add("hidden");

        });


        /* SEND MESSAGE */

        form.addEventListener("submit", function (e) {

            e.preventDefault();

            const message = input.value.trim();
            const file = fileInput.files.length;

            if (!message && !file) return;

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

                    let fileHtml = '';

                    if (data.file) {

                        fileHtml = `
            <a href="/storage/${data.file}" target="_blank"
            class="flex items-center gap-1 mt-2 text-xs underline">

            <i class="fa-solid fa-paperclip"></i>
            Download File

            </a>
            `;

                    }

                    const html = `

            <div class="flex justify-end">

            <div class="max-w-xs bg-blue-500 text-white px-4 py-3 rounded-2xl shadow-lg">

            ${data.message ? `<p class="text-sm">${data.message}</p>` : ''}

            ${fileHtml}

            <div class="text-[10px] opacity-80 mt-1 text-right">

            ${data.time}

            </div>

            </div>

            </div>

            `;

                    chat.insertAdjacentHTML("beforeend", html);

                    chat.scrollTop = chat.scrollHeight;

                    form.reset();
                    filePreview.classList.add("hidden");

                });

        });

    </script>

@endsection