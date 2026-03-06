@extends('layouts.admin')

@section('title', 'Edit Lesson')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6" x-data="{
                blocks: @js(old('blocks', ($lesson->content_blocks ?? []))),
                addBlock(type = 'text') {
                    if (type === 'text') this.blocks.push({ type: 'text', text: '' });
                    if (type === 'video') this.blocks.push({ type: 'video', video_url: '' });
                    if (type === 'file') this.blocks.push({ type: 'file', path: '' });
                    if (type === 'h5p') this.blocks.push({ type: 'h5p', embed: '', h5p_embed: '' });
                },
                removeBlock(i) { this.blocks.splice(i, 1); }
             }">

        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Lesson</h1>
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
            <form id="lessonForm" method="POST"
                action="{{ route('admin.courses.lessons.update', [$course->id, $lesson->id]) }}"
                enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Title</label>
                    <input type="text" name="title" value="{{ old('title', $lesson->title) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Position --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Position</label>
                    <input type="number" name="position" min="1" value="{{ old('position', $lesson->position) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none">
                    @error('position')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                        Description (optional)
                    </label>

                    <input type="hidden" name="description" id="descriptionInput"
                        value="{{ old('description', $lesson->description) }}">

                    <div id="quillToolbar"
                        class="border border-gray-300 dark:border-white/10 rounded-t-lg bg-gray-50 dark:bg-white/5"></div>
                    <div id="quillEditor"
                        class="border border-gray-300 dark:border-white/10 rounded-b-lg bg-white dark:bg-slate-950 dark:text-white min-h-[180px]">
                    </div>

                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Content Blocks --}}
                <div class="space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-white/80">
                            Content Blocks (optional)
                        </label>

                        <div class="flex gap-2 flex-wrap">
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
                            <button type="button" @click="addBlock('h5p')"
                                class="px-3 py-1.5 text-sm border border-indigo-300 text-indigo-700 dark:border-indigo-400/30 dark:text-indigo-300 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-500/10">
                                + H5P
                            </button>
                        </div>
                    </div>

                    <template x-for="(block, i) in blocks" :key="i">
                        <div
                            class="rounded-xl border border-gray-200 dark:border-white/10 p-4 bg-gray-50 dark:bg-white/5 space-y-3">

                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-gray-700 dark:text-white/80">
                                    Block <span x-text="i + 1"></span>
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
                                <textarea :name="`blocks[${i}][text]`" rows="5" x-model="block.text"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"></textarea>
                            </div>

                            {{-- VIDEO --}}
                            <div x-show="block.type === 'video'">
                                <label class="block text-sm text-gray-600 dark:text-white/70 mb-1">Video URL</label>
                                <input type="text" :name="`blocks[${i}][video_url]`" x-model="block.video_url"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    placeholder="https://youtube.com/... or other video URL">
                            </div>

                            {{-- FILE --}}
                            <div x-show="block.type === 'file'" class="space-y-2">
                                <label class="block text-sm text-gray-600 dark:text-white/70">File</label>

                                <template x-if="block.path">
                                    <div class="text-xs text-gray-600 dark:text-white/70">
                                        Current:
                                        <a class="text-blue-600 underline" target="_blank"
                                            :href="`{{ asset('storage') }}/` + block.path">
                                            Open file
                                        </a>
                                    </div>
                                </template>

                                <input type="hidden" :name="`blocks[${i}][existing_path]`" :value="block.path ?? ''">

                                <input type="file" :name="`blocks[${i}][file]`"
                                    class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">

                                <template x-if="block.path">
                                    <label class="inline-flex items-center gap-2 text-sm text-gray-600 dark:text-white/70">
                                        <input type="checkbox" :name="`blocks[${i}][remove_file]`" value="1"
                                            class="rounded border-gray-300 dark:border-white/20">
                                        Remove current file
                                    </label>
                                </template>
                            </div>

                            {{-- H5P --}}
                            <div x-show="block.type === 'h5p'">
                                <label class="block text-sm text-gray-600 dark:text-white/70 mb-1">
                                    H5P Embed Code
                                </label>
                                <textarea :name="`blocks[${i}][h5p_embed]`" rows="5"
                                    x-model="block.h5p_embed ? block.h5p_embed : (block.embed ? block.embed : '')"
                                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                                    placeholder='<iframe src="https://your-h5p-url/embed/123" width="1090" height="645" frameborder="0" allowfullscreen="allowfullscreen"></iframe>'></textarea>

                                <p class="text-xs text-gray-400 mt-1">
                                    Paste or update the full H5P iframe embed code here.
                                </p>
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
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script src="https://unpkg.com/quill-image-resize-module@3.0.0/image-resize.min.js"></script>

    <style>
        #quillEditor .ql-editor img {
            max-width: 100%;
            height: auto;
            display: block;
        }
    </style>

    <script>
        (function () {
            const mod =
                (window.ImageResize && (window.ImageResize.default || window.ImageResize)) ||
                (window.QuillImageResizeModule && (window.QuillImageResizeModule.default || window.QuillImageResizeModule));

            if (mod) {
                try {
                    Quill.import('modules/imageResize');
                } catch (e) {
                    Quill.register('modules/imageResize', mod);
                }
            }

            const toolbarOptions = [
                [{ header: [1, 2, 3, 4, 5, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean']
            ];

            const quill = new Quill('#quillEditor', {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions,
                    imageResize: mod ? {
                        modules: ['Resize', 'DisplaySize', 'Toolbar']
                    } : false
                }
            });

            const initialHtml = @json(old('description', $lesson->description ?? ''));
            if (initialHtml) {
                quill.root.innerHTML = initialHtml;
            }

            document.getElementById('lessonForm').addEventListener('submit', function () {
                document.getElementById('descriptionInput').value = quill.root.innerHTML;
            });
        })();
    </script>
@endsection