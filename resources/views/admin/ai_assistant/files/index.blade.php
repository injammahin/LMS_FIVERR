@extends('layouts.admin')

@section('title', 'AI Files')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white">AI Assistant Files</h1>
            <p class="text-sm text-gray-500 dark:text-white/60">
                Upload PDFs / docs so the assistant can answer from your LMS documents.
            </p>
        </div>

        <a href="{{ route('admin.ai.files.create') }}"
           class="px-4 py-2 rounded-lg bg-blue-600 text-white font-semibold hover:bg-blue-700 transition">
            + Upload File
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 text-green-700 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                <tr>
                    <th class="px-6 py-4 text-left font-semibold w-[70px]">ID</th>
                    <th class="px-6 py-4 text-left font-semibold">Name</th>
                    <th class="px-6 py-4 text-left font-semibold w-[140px]">Scope</th>
                    <th class="px-6 py-4 text-left font-semibold w-[140px]">Status</th>
                    <th class="px-6 py-4 text-left font-semibold w-[180px]">Uploaded</th>
                </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse($files as $f)
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                        <td class="px-6 py-5 font-semibold text-gray-900 dark:text-white">
                            {{ $f->id }}
                        </td>

                        <td class="px-6 py-5">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $f->original_name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-white/60">
                                {{ $f->mime }} • {{ number_format(($f->size ?? 0)/1024, 1) }} KB
                            </div>
                        </td>

                        <td class="px-6 py-5">
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold
                                {{ $f->scope === 'global'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200'
                                    : 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-200' }}">
                                {{ $f->scope }} {{ $f->course_id ? '(course '.$f->course_id.')' : '' }}
                            </span>
                        </td>

                        <td class="px-6 py-5">
                            @php
                                $status = $f->status ?? 'pending';
                                $badge = match($status){
                                    'indexed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200',
                                    'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-200',
                                    'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/70',
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $badge }}">
                                {{ str_replace('_',' ', $status) }}
                            </span>

                            @if($status === 'failed' && $f->last_error)
                                <div class="mt-2 text-xs text-rose-600 dark:text-rose-300">
                                    {{ Str::limit($f->last_error, 120) }}
                                </div>
                            @endif
                        </td>

                        <td class="px-6 py-5 text-gray-600 dark:text-white/70">
                            {{ $f->created_at?->format('Y-m-d H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-14 text-center">
                            <div class="text-gray-400">
                                <div class="text-base font-semibold">No files uploaded</div>
                                <div class="text-sm mt-1">Click “Upload File” to add the first document.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $files->links() }}
    </div>

</div>
@endsection