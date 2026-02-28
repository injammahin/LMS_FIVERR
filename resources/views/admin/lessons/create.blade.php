@extends('layouts.admin')

@section('title', 'Add Lesson')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
            blocks: @js(old('blocks', [])),
            addBlock(type='text'){ this.blocks.push({type:type}); },
            removeBlock(i){ this.blocks.splice(i,1); }
         }">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Add Lesson</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Course: <span class="font-medium">{{ $course->title }}</span>
            </p>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
            <form id="lessonForm" method="POST" action="{{ route('admin.courses.lessons.store', $course->id) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('title') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Position --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Position</label>
                    <input type="number" name="position" min="1" value="{{ old('position', 1) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('position') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Description (Quill Editor) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Description
                        (optional)</label>

                    <input type="hidden" name="description" id="descriptionInput" value="{{ old('description') }}">

                    <div id="quillToolbar"
                        class="border border-gray-300 dark:border-white/10 rounded-t-lg bg-gray-50 dark:bg-white/5"></div>
                    <div id="quillEditor"
                        class="border border-gray-300 dark:border-white/10 rounded-b-lg bg-white dark:bg-slate-950 dark:text-white min-h-[180px]">
                    </div>

                    @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Content Blocks --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80">Content Blocks
                            (optional)</label>

                        <div class="flex gap-2">
                            <button type="button" @click="addBlock('text')"
                                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                                + Text
                            </button>
                            <button type="button" @click="addBlock('video')"
                                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                                + Video
                            </button>
                            <button type="button" @click="addBlock('file')"
                                class="px-3 py-1.5 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                                + File
                            </button>
                        </div>
                    </div>

                    <template x-for="(block, i) in blocks" :key="i">
                        <div
                            class="rounded-xl border border-gray-200 dark:border-white/10 p-4 bg-gray-50 dark:bg-white/5 space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-gray-700 dark:text-white/80">
                                    Block <span x-text="i+1"></span>
                                    <span class="ml-2 text-xs px-2 py-1 rounded-full bg-white/70 dark:bg-white/10"
                                        x-text="block.type"></span>
                                </div>

                                <button type="button" @click="removeBlock(i)"
                                    class="text-sm px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 dark:hover:bg-red-500/10">
                                    Remove
                                </button>
                            </div>

                            <input type="hidden" :name="`blocks[${i}][type]`" x-model="block.type">

                            {{-- TEXT --}}
                            <div x-show="block.type === 'text'">
                                <label class="block text-sm text-gray-600 dark:text-white/70 mb-1">Text</label>
                                <textarea :name="`blocks[${i}][text]`" rows="5"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    placeholder="Write lesson text..."></textarea>
                            </div>

                            {{-- VIDEO --}}
                            <div x-show="block.type === 'video'">
                                <label class="block text-sm text-gray-600 dark:text-white/70 mb-1">Video URL</label>
                                <input type="text" :name="`blocks[${i}][video_url]`"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    placeholder="https://youtube.com/...">
                            </div>

                            {{-- FILE --}}
                            <div x-show="block.type === 'file'">
                                <label class="block text-sm text-gray-600 dark:text-white/70 mb-1">Upload File</label>
                                <input type="file" :name="`blocks[${i}][file]`"
                                    class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                                <p class="text-xs text-gray-400 mt-1">pdf/docx/pptx/xlsx/images/zip up to 10MB</p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('admin.courses.lessons.index', $course->id) }}"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                        Cancel
                    </a>

                    <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Create Lesson
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    {{-- Quill (Free) --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

    <script>
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, 4, 5, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['blockquote', 'code-block'],
            ['link', 'image', 'video'],
            ['clean']
        ];

        const quill = new Quill('#quillEditor', {
            theme: 'snow',
            modules: { toolbar: toolbarOptions }
        });

        // Load old value
        const oldHtml = @json(old('description', ''));
        if (oldHtml) quill.root.innerHTML = oldHtml;

        // On submit, copy HTML to hidden input
        document.getElementById('lessonForm').addEventListener('submit', function () {
            document.getElementById('descriptionInput').value = quill.root.innerHTML;
        });
    </script>
@endsection