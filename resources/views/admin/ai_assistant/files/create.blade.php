@extends('layouts.admin')

@section('title', 'Upload AI File')

@section('content')
<div class="max-w-3xl space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">Upload File</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Upload PDF/DOC/TXT/MD to improve assistant answers.
            </p>
        </div>

        <a href="{{ route('admin.ai.files.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5">
            Back
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-rose-200 bg-rose-50 text-rose-700 px-4 py-3 text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.ai.files.store') }}" enctype="multipart/form-data"
          class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-5 shadow-sm">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Scope</label>
                <select name="scope" id="scope"
                        class="mt-1 w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm">
                    <option value="global">Global</option>
                    <option value="course">Course</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="text-sm font-semibold text-gray-700 dark:text-white/80">Course (optional)</label>
                <select name="course_id" id="course_id"
                        class="mt-1 w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm">
                    <option value="">-- select course --</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->id }} — {{ $c->title ?? $c->name ?? 'Course' }}</option>
                    @endforeach
                </select>
                <div class="text-xs text-gray-500 dark:text-white/60 mt-1">Used only when scope = course</div>
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-white/80">File</label>
            <input type="file" name="file" required
                   class="mt-1 block w-full rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-950 px-3 py-2 text-sm" />
            <div class="text-xs text-gray-500 dark:text-white/60 mt-1">Max: 50MB</div>
        </div>

        <button class="w-full md:w-auto px-5 py-2.5 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
            Upload & Sync
        </button>
    </form>
</div>

<script>
(function(){
    const scope = document.getElementById('scope');
    const course = document.getElementById('course_id');
    function toggle(){
        course.disabled = scope.value !== 'course';
        if(scope.value !== 'course') course.value = '';
    }
    scope.addEventListener('change', toggle);
    toggle();
})();
</script>
@endsection