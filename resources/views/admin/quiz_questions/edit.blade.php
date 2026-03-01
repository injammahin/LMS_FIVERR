@extends('layouts.admin')

@section('title', 'Edit Question')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $quillUploadUrl = Route::has('admin.quill.upload') ? route('admin.quill.upload') : null;
@endphp

<div class="max-w-4xl mx-auto space-y-6" x-data="{ type: @js(old('type', $question->type)) }">

    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-800 dark:text-white">Edit Question</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Quiz: <span class="font-medium">{{ $quiz->title }}</span>
            </p>
        </div>

        <a href="{{ route('admin.quizzes.questions.index', $quiz->id) }}"
           class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
            Back
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-xl border border-gray-200 dark:border-white/10 p-6">
        <form id="questionForm"
              method="POST"
              action="{{ route('admin.quizzes.questions.update', [$quiz->id, $question->id]) }}"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Question Type</label>
                <select name="type" x-model="type"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    <option value="text">Text Answer</option>
                    <option value="file">File Upload Answer</option>
                    <option value="single_choice">Single Choice (Radio)</option>
                    <option value="multiple_choice">Multiple Choice (Checkbox)</option>
                    <option value="true_false">True / False</option>
                </select>
                @error('type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Question (Quill) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Question</label>
                <input type="hidden" name="question" id="questionInput"
                       value="{{ old('question', $question->question) }}">

                <div class="quillWrap border border-gray-300 dark:border-white/10 rounded-lg overflow-hidden bg-white dark:bg-slate-950">
                    <div id="questionEditor" class="dark:text-white"></div>
                </div>

                @error('question') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Existing image --}}
            @if($question->question_image)
                <div class="rounded-lg border border-gray-200 dark:border-white/10 p-4 bg-gray-50 dark:bg-white/5">
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('storage/'.$question->question_image) }}"
                             class="w-28 h-20 rounded-lg border border-gray-200 dark:border-white/10 object-cover"
                             alt="Question image">

                        <div class="space-y-2">
                            <div class="text-sm font-medium text-gray-800 dark:text-white">Current image</div>

                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-white/80">
                                <input type="checkbox" name="remove_image" value="1" class="rounded">
                                Remove image
                            </label>

                            <p class="text-xs text-gray-500 dark:text-white/60">
                                If you upload a new image below, the old one will be replaced automatically.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Upload new question image --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                    Upload New Question Image (optional)
                </label>
                <input type="file" name="question_image"
                       accept="image/png,image/jpeg,image/webp"
                       class="w-full text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white px-3 py-2">
                @error('question_image') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Marks + Position + Required --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Marks</label>
                    <input type="number" name="marks" min="1"
                           value="{{ old('marks', $question->marks) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    @error('marks') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">Position</label>
                    <input type="number" name="position" min="1"
                           value="{{ old('position', $question->position) }}"
                           class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg bg-white dark:bg-slate-950 dark:text-white">
                    @error('position') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_required" value="1" class="rounded"
                           {{ old('is_required', $question->is_required) ? 'checked' : '' }}>
                    <label class="text-sm text-gray-700 dark:text-white/80">Required</label>
                </div>
            </div>

            {{-- Explanation (Quill) --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-white/80 mb-1">
                    Explanation (optional)
                </label>
                <input type="hidden" name="explanation" id="explanationInput"
                       value="{{ old('explanation', $question->explanation) }}">

                <div class="quillWrap border border-gray-300 dark:border-white/10 rounded-lg overflow-hidden bg-white dark:bg-slate-950">
                    <div id="explanationEditor" class="dark:text-white"></div>
                </div>

                @error('explanation') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Buttons --}}
            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('admin.quizzes.questions.index', $quiz->id) }}"
                   class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                    Cancel
                </a>

                <button type="submit"
                        class="px-5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    Update Question
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
        .quillWrap .ql-container { height: auto !important; border: 0 !important; }
        .quillWrap .ql-toolbar { border: 0 !important; border-bottom: 1px solid rgba(0,0,0,.12) !important; }
        .dark .quillWrap .ql-toolbar { border-bottom-color: rgba(255,255,255,.10) !important; }

        #questionEditor .ql-editor { min-height: 140px; }
        #explanationEditor .ql-editor { min-height: 110px; }

        .ql-editor img { height: auto; display: block; max-width: 100%; }

        .dark .ql-toolbar.ql-snow,
        .dark .ql-container.ql-snow { border-color: rgba(255,255,255,.10); }
        .dark .ql-snow .ql-stroke { stroke: rgba(255,255,255,.75); }
        .dark .ql-snow .ql-fill { fill: rgba(255,255,255,.75); }
        .dark .ql-snow .ql-picker { color: rgba(255,255,255,.75); }
        .dark .ql-editor { color: rgba(255,255,255,.90); }
    </style>

    <script>
        (function () {
            const uploadUrl = @json($quillUploadUrl);
            const csrfToken = @json(csrf_token());

            const mod =
                (window.ImageResize && (window.ImageResize.default || window.ImageResize)) ||
                (window.QuillImageResizeModule && (window.QuillImageResizeModule.default || window.QuillImageResizeModule));

            if (mod) {
                try { Quill.import('modules/imageResize'); }
                catch (e) { Quill.register('modules/imageResize', mod); }
            }

            const toolbarOptions = [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ color: [] }, { background: [] }],
                [{ list: 'ordered' }, { list: 'bullet' }],
                [{ align: [] }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean']
            ];

            async function uploadImage(file) {
                if (!uploadUrl) return null;
                const fd = new FormData();
                fd.append('image', file);

                const res = await fetch(uploadUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: fd
                });

                if (!res.ok) return null;
                const data = await res.json();
                return data?.url ?? null;
            }

            function insertImageAtCursor(quill, imageUrl) {
                const range = quill.getSelection(true) || { index: quill.getLength() };
                quill.insertEmbed(range.index, 'image', imageUrl, Quill.sources.USER);
                quill.setSelection(range.index + 1, 0, Quill.sources.SILENT);
            }

            function bindPasteAndDropUpload(quill) {
                quill.root.addEventListener('paste', async (e) => {
                    if (!uploadUrl) return;
                    const items = (e.clipboardData || window.clipboardData)?.items || [];
                    const imgItem = Array.from(items).find(it => it.type && it.type.startsWith('image/'));
                    if (!imgItem) return;

                    e.preventDefault();
                    const file = imgItem.getAsFile();
                    if (!file) return;

                    const url = await uploadImage(file);
                    if (url) insertImageAtCursor(quill, url);
                });

                quill.root.addEventListener('drop', async (e) => {
                    if (!uploadUrl) return;
                    const files = e.dataTransfer?.files;
                    if (!files || !files.length) return;

                    const file = Array.from(files).find(f => f.type && f.type.startsWith('image/'));
                    if (!file) return;

                    e.preventDefault();
                    const url = await uploadImage(file);
                    if (url) insertImageAtCursor(quill, url);
                });

                quill.root.addEventListener('dragover', (e) => {
                    if (uploadUrl) e.preventDefault();
                });
            }

            function initQuill(selector, initialHtml) {
                const quill = new Quill(selector, {
                    theme: 'snow',
                    modules: {
                        toolbar: {
                            container: toolbarOptions,
                            handlers: {
                                image: function () {
                                    if (!uploadUrl) {
                                        const tooltip = this.quill.theme.tooltip;
                                        tooltip.edit('image');
                                        tooltip.textbox.placeholder = 'Paste image URL...';
                                        tooltip.show();
                                        return;
                                    }

                                    const input = document.createElement('input');
                                    input.type = 'file';
                                    input.accept = 'image/*';
                                    input.click();

                                    input.onchange = async () => {
                                        const file = input.files && input.files[0];
                                        if (!file) return;

                                        const url = await uploadImage(file);
                                        if (!url) return alert('Image upload failed.');

                                        insertImageAtCursor(this.quill, url);
                                    };
                                }
                            }
                        },
                        imageResize: mod ? { modules: ['Resize','DisplaySize','Toolbar'] } : undefined
                    }
                });

                if (initialHtml) quill.clipboard.dangerouslyPasteHTML(initialHtml);
                bindPasteAndDropUpload(quill);
                return quill;
            }

            const questionQuill = initQuill('#questionEditor', @json(old('question', $question->question)));
            const explanationQuill = initQuill('#explanationEditor', @json(old('explanation', $question->explanation)));

            document.getElementById('questionForm').addEventListener('submit', function () {
                document.getElementById('questionInput').value = questionQuill.root.innerHTML;
                document.getElementById('explanationInput').value = explanationQuill.root.innerHTML;
            });
        })();
    </script>
@endsection