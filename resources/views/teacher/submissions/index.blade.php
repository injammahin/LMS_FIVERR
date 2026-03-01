@extends('layouts.teacher')

@section('title', 'Submissions')
@section('page_title', 'Submissions Inbox')

@section('content')

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-5xl font-black text-gray-900">Submissions</h1>
            <p class="text-gray-500 mt-2">Review assignments and quiz attempts.</p>
        </div>

        <form method="GET" class="flex items-center gap-3">
            <select name="type" class="rounded-xl border border-gray-200 px-4 py-2">
                <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All</option>
                <option value="assignment" {{ $filter === 'assignment' ? 'selected' : '' }}>Assignments</option>
                <option value="quiz" {{ $filter === 'quiz' ? 'selected' : '' }}>Quizzes</option>
            </select>
            <button class="px-5 py-2 rounded-xl bg-gray-900 text-white">Filter</button>
        </form>
    </div>

    {{-- Assignment Submissions --}}
    @if($filter === 'all' || $filter === 'assignment')
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Assignment Submissions</h2>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($assignmentSubs as $sub)
                    <a href="{{ route('teacher.assignments.submissions.show', [$sub->assignment_id, $sub->id]) }}"
                        class="p-4 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $sub->assignment->title ?? 'Assignment' }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $sub->user->name ?? 'Student' }} • {{ $sub->created_at->diffForHumans() }}
                            </div>
                        </div>

                        @php $pending = ($sub->status ?? '') === 'submitted'; @endphp
                        <span
                            class="text-xs px-3 py-1 rounded-full border {{ $pending ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                            {{ $pending ? 'Pending' : 'Graded' }}
                        </span>
                    </a>
                @empty
                    <div class="p-10 text-center text-gray-500">No assignment submissions yet.</div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $assignmentSubs?->links() }}
            </div>
        </div>
    @endif

    {{-- Quiz Attempts --}}
    @if($filter === 'all' || $filter === 'quiz')
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-900">Quiz Attempts</h2>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($quizAttempts as $att)
                    <a href="{{ route('teacher.quiz.attempts.show', $att->id) }}"
                        class="p-4 flex items-center justify-between hover:bg-gray-50">
                        <div>
                            <div class="font-semibold text-gray-900">{{ $att->quiz->title ?? 'Quiz' }}</div>
                            <div class="text-sm text-gray-500">
                                {{ $att->user->name ?? 'Student' }} • {{ $att->created_at->diffForHumans() }}
                            </div>
                        </div>

                        <span class="text-xs px-3 py-1 rounded-full border bg-purple-100 text-purple-700 border-purple-200">
                            Submitted
                        </span>
                    </a>
                @empty
                    <div class="p-10 text-center text-gray-500">No quiz attempts yet.</div>
                @endforelse
            </div>

            <div class="p-4">
                {{ $quizAttempts?->links() }}
            </div>
        </div>
    @endif

@endsection