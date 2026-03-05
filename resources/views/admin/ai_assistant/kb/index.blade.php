{{-- resources/views/admin/ai_assistant/kb/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'AI Training')

@section('content')
@php
    use Illuminate\Support\Str;

    $total = $entries->total() ?? $entries->count();

    $typeMeta = [
        'qa'  => ['Q/A', 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-200'],
        'doc' => ['Document', 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-200'],
    ];
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-start justify-between gap-4">
        <div class="space-y-2">
            <h1 class="text-md font-black tracking-tight text-gray-900 dark:text-white">
                AI Training (Knowledge Base)
            </h1>

            <div class="text-sm text-gray-500 dark:text-white/60 space-y-1">
                <div>Manage admin training data that AI will use to answer.</div>
                <div class="flex flex-wrap items-center gap-2 pt-1">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80">
                        Total: {{ $total }}
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                        Active: {{ \App\Models\AiKbEntry::where('is_active', true)->count() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('admin.dashboard') }}"
               class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 dark:text-white">
                Back
            </a>

            <a href="{{ route('admin.ai.kb.create') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">
                + Add Training
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-white/70">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold w-[90px]">ID</th>
                        <th class="px-6 py-4 text-left font-semibold w-[200px]">Scope</th>
                        <th class="px-6 py-4 text-left font-semibold w-[140px]">Type</th>
                        <th class="px-6 py-4 text-left font-semibold">Title</th>
                        <th class="px-6 py-4 text-left font-semibold w-[120px]">Status</th>
                        <th class="px-6 py-4 text-left font-semibold w-[180px]">Updated</th>
                        <th class="px-6 py-4 text-right font-semibold w-[220px]">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($entries as $e)
                        @php
                            [$tLabel, $tClass] = $typeMeta[$e->type] ?? [Str::headline($e->type), 'bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80'];
                            $scopeClass = $e->scope === 'global'
                                ? 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-white/80'
                                : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200';

                            $titlePreview = Str::limit($e->title ?? '', 60);

                            $statusClass = $e->is_active
                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200'
                                : 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-200';
                        @endphp

                        <tr class="hover:bg-gray-50/70 dark:hover:bg-white/5 transition">
                            {{-- ID --}}
                            <td class="px-6 py-5">
                                <div class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-gray-100 dark:bg-white/10 text-gray-900 dark:text-white font-bold">
                                    {{ $e->id }}
                                </div>
                            </td>

                            {{-- Scope --}}
                            <td class="px-6 py-5">
                                <div class="space-y-2">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $scopeClass }}">
                                        {{ $e->scope === 'global' ? 'Global' : 'Course' }}
                                    </span>

                                    @if($e->scope === 'course')
                                        <div class="text-xs text-gray-500 dark:text-white/60">
                                            course_id: <span class="font-semibold text-gray-800 dark:text-white/80">{{ $e->course_id }}</span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $tClass }}">
                                    {{ $tLabel }}
                                </span>
                            </td>

                            {{-- Title --}}
                            <td class="px-6 py-5">
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    {{ $titlePreview ?: '—' }}
                                </div>

                                @if($e->type === 'qa' && $e->question)
                                    <div class="mt-1 text-xs text-gray-500 dark:text-white/60">
                                        Q: {{ Str::limit(strip_tags($e->question), 70) }}
                                    </div>
                                @endif

                                @if($e->keywords)
                                    <div class="mt-2">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-white/10 dark:text-white/80">
                                            {{ $e->keywords }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-5">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $e->is_active ? '✅ Active' : '⛔ Disabled' }}
                                </span>
                            </td>

                            {{-- Updated --}}
                            <td class="px-6 py-5">
                                <div class="text-gray-700 dark:text-white/80 font-medium">
                                    {{ $e->updated_at?->format('Y-m-d') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-white/60">
                                    {{ $e->updated_at?->format('h:i A') }}
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-5">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.ai.kb.edit', $e) }}"
                                       class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/5 text-sm dark:text-white">
                                        Edit
                                    </a>

                                    @if($e->is_active)
                                        <form method="POST"
                                              action="{{ route('admin.ai.kb.destroy', $e) }}"
                                              onsubmit="return confirm('Disable this training?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10 text-sm">
                                                Disable
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.ai.kb.edit', $e) }}"
                                           class="px-3 py-1.5 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 text-sm">
                                            Enable (Edit)
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="text-gray-400">
                                    <div class="text-base font-semibold">No training data found</div>
                                    <div class="text-sm mt-1">Click “Add Training” to create your first entry.</div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
            {{ $entries->links() }}
        </div>
    </div>

</div>
@endsection