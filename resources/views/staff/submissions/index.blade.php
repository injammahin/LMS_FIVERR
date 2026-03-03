@extends('layouts.staff')

@section('title', 'Submissions')
@section('page_title', 'Submissions Inbox')

@section('content')
@php
    $countAssignments = $assignmentSubs?->total() ?? ($assignmentSubs?->count() ?? 0);
    $countQuizzes = $quizAttempts?->total() ?? ($quizAttempts?->count() ?? 0);

    $badge = function(string $type){
        return match($type){
            'pending' => 'bg-amber-50 text-amber-800 border-amber-200',
            'graded'  => 'bg-emerald-50 text-emerald-800 border-emerald-200',
            'quiz'    => 'bg-purple-50 text-purple-800 border-purple-200',
            default   => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    };
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div class="space-y-1">
            <h1 class="text-xl font-semibold text-gray-900">Submissions</h1>
            <p class="text-sm text-gray-500">Review assignments and quiz attempts.</p>

            <div class="flex flex-wrap gap-2 pt-2">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border bg-white border-gray-200 text-gray-700">
                    <i class="fa-solid fa-file-pen text-amber-600"></i>
                    Assignments: <span class="font-semibold text-gray-900">{{ $countAssignments }}</span>
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border bg-white border-gray-200 text-gray-700">
                    <i class="fa-solid fa-bolt text-purple-600"></i>
                    Quiz Attempts: <span class="font-semibold text-gray-900">{{ $countQuizzes }}</span>
                </span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 sm:items-center">
            {{-- Search (client-side) --}}
            <div class="relative">
                <input id="subSearch" type="text" placeholder="Search student / course / title..."
                       class="w-full sm:w-72 rounded-xl border border-gray-200 px-4 py-2 pl-10 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none bg-white">
                <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-3 top-3"></i>
            </div>

            {{-- Filter --}}
            <form method="GET" class="flex items-center gap-2">
                <select name="type" class="rounded-xl border border-gray-200 px-4 py-2 text-sm bg-white">
                    <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                    <option value="assignment" {{ $filter === 'assignment' ? 'selected' : '' }}>Assignments</option>
                    <option value="quiz" {{ $filter === 'quiz' ? 'selected' : '' }}>Quizzes</option>
                </select>
                <button class="px-4 py-2 rounded-xl bg-gray-900 text-white hover:bg-gray-800 text-sm shadow-sm">
                    Filter
                </button>
            </form>
        </div>
    </div>

    {{-- Assignment Submissions --}}
    @if($filter === 'all' || $filter === 'assignment')
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Assignment Submissions</div>
                    <div class="text-xs text-gray-500 mt-1">Pending + graded submissions from your courses</div>
                </div>

                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border bg-gray-50 border-gray-200 text-gray-700">
                    <i class="fa-solid fa-file-pen text-amber-600"></i>
                    {{ $countAssignments }}
                </span>
            </div>

            <div class="divide-y divide-gray-100" id="assignmentList">
                @forelse($assignmentSubs as $sub)
                    @php
                        $pending = ($sub->status ?? '') === 'submitted';
                        $courseTitle = $sub->assignment?->course?->title ?? '-';
                        $divisionName = optional(optional($sub->assignment?->course?->subject)->division)->name ?? '-';
                        $studentName = $sub->user?->name ?? 'Student';
                        $title = $sub->assignment?->title ?? 'Assignment';
                    @endphp

                    <a href="{{ route('teacher.assignments.submissions.show', [$sub->assignment_id, $sub->id]) }}"
                       class="subRow p-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-2xl border grid place-items-center
                                        {{ $pending ? 'bg-amber-50 border-amber-200 text-amber-800' : 'bg-emerald-50 border-emerald-200 text-emerald-800' }}">
                                <i class="fa-solid {{ $pending ? 'fa-hourglass-half' : 'fa-circle-check' }}"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate subTitle">{{ $title }}</div>
                                <div class="text-xs text-gray-500 mt-1 truncate subMeta">
                                    {{ $studentName }} • {{ $courseTitle }} • {{ $divisionName }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Submitted: {{ optional($sub->created_at)->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs px-3 py-1 rounded-full border {{ $pending ? $badge('pending') : $badge('graded') }}">
                                {{ $pending ? 'Pending' : 'Graded' }}
                            </span>
                            <i class="fa-solid fa-chevron-right text-gray-300"></i>
                        </div>
                    </a>
                @empty
                    <div class="p-10 text-center text-gray-500 text-sm">No assignment submissions yet.</div>
                @endforelse
            </div>

            <div class="p-4 border-t border-gray-200">
                {{ $assignmentSubs?->links() }}
            </div>
        </div>
    @endif

    {{-- Quiz Attempts --}}
    @if($filter === 'all' || $filter === 'quiz')
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-semibold text-gray-900">Quiz Attempts</div>
                    <div class="text-xs text-gray-500 mt-1">Submitted quiz attempts that need review / grading</div>
                </div>

                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs border bg-gray-50 border-gray-200 text-gray-700">
                    <i class="fa-solid fa-bolt text-purple-600"></i>
                    {{ $countQuizzes }}
                </span>
            </div>

            <div class="divide-y divide-gray-100" id="quizList">
                @forelse($quizAttempts as $att)
                    @php
                        $courseTitle = $att->quiz?->course?->title ?? '-';
                        $divisionName = optional(optional($att->quiz?->course?->subject)->division)->name ?? '-';
                        $studentName = $att->user?->name ?? 'Student';
                        $title = $att->quiz?->title ?? 'Quiz';

                        // attempts used for this quiz by this user (submitted attempts)
                        $attemptUsed = \App\Models\QuizAttempt::where('user_id', $att->user_id)
                            ->where('quiz_id', $att->quiz_id)
                            ->whereNotNull('submitted_at')
                            ->count();

                        $max = (int)($att->quiz?->max_attempts ?? 0);
                        $attemptText = $max > 0 ? "{$attemptUsed}/{$max}" : "{$attemptUsed}/∞";
                    @endphp

                    <a href="{{ route('teacher.quiz.attempts.show', $att->id) }}"
                       class="subRow p-4 flex items-center justify-between gap-4 hover:bg-gray-50 transition">
                        <div class="flex items-start gap-3">
                            <div class="w-11 h-11 rounded-2xl border border-purple-200 bg-purple-50 text-purple-800 grid place-items-center">
                                <i class="fa-solid fa-bolt"></i>
                            </div>

                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate subTitle">{{ $title }}</div>
                                <div class="text-xs text-gray-500 mt-1 truncate subMeta">
                                    {{ $studentName }} • {{ $courseTitle }} • {{ $divisionName }}
                                </div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Submitted: {{ optional($att->submitted_at ?? $att->created_at)->diffForHumans() }}
                                    • Attempts: <span class="font-semibold text-gray-600">{{ $attemptText }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="text-xs px-3 py-1 rounded-full border {{ $badge('quiz') }}">
                                Submitted
                            </span>
                            <i class="fa-solid fa-chevron-right text-gray-300"></i>
                        </div>
                    </a>
                @empty
                    <div class="p-10 text-center text-gray-500 text-sm">No quiz attempts yet.</div>
                @endforelse
            </div>

            <div class="p-4 border-t border-gray-200">
                {{ $quizAttempts?->links() }}
            </div>
        </div>
    @endif

</div>
@endsection

@section('scripts')
<script>
(function () {
    const input = document.getElementById('subSearch');
    const rows = document.querySelectorAll('.subRow');

    function norm(s){ return (s || '').toLowerCase(); }

    input?.addEventListener('input', function () {
        const q = norm(this.value);
        rows.forEach(r => {
            const title = norm(r.querySelector('.subTitle')?.innerText);
            const meta  = norm(r.querySelector('.subMeta')?.innerText);
            r.style.display = (title.includes(q) || meta.includes(q)) ? '' : 'none';
        });
    });
})();
</script>
@endsection