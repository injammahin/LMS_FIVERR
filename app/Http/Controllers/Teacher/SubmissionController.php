<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\TeacherCounts;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    use TeacherCounts;

    public function index(Request $request)
    {
        $courseIds = $this->teacherCourseIds();
        $filter = $request->get('type', 'all'); // all|assignment|quiz

        $assignmentSubs = null;
        $quizAttempts = null;

        if ($filter === 'all' || $filter === 'assignment') {
            $assignmentSubs = AssignmentSubmission::query()
                ->with(['user', 'assignment.course'])
                ->whereHas('assignment.course', fn($q) => $q->whereIn('id', $courseIds))
                ->latest()
                ->paginate(15, ['*'], 'assignments_page')
                ->appends($request->query());
        }

        if ($filter === 'all' || $filter === 'quiz') {
            $quizAttempts = QuizAttempt::query()
                ->with(['user', 'quiz.course'])
                ->whereHas('quiz.course', fn($q) => $q->whereIn('id', $courseIds))
                ->where('status', 'submitted')
                ->latest()
                ->paginate(15, ['*'], 'quizzes_page')
                ->appends($request->query());
        }

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.submissions.index', compact('assignmentSubs', 'quizAttempts', 'sidebarCounts', 'filter'))
            ->with('topbarUnread', $unread);
    }

    public function showAssignment(Assignment $assignment, AssignmentSubmission $submission)
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        abort_if(!$teacher->coursesTeaching()->where('courses.id', $assignment->course_id)->exists(), 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $submission->load(['user', 'assignment.course']);

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.submissions.assignment_show', compact('assignment', 'submission', 'sidebarCounts'))
            ->with('topbarUnread', $unread);
    }

    public function gradeAssignment(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        abort_if(!$teacher->coursesTeaching()->where('courses.id', $assignment->course_id)->exists(), 403);
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $validated = $request->validate([
            'feedback' => ['nullable', 'string'],
            'marks_awarded' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_passed' => ['nullable', 'boolean'],
        ]);

        if ($assignment->grading_type === 'points') {
            if (!isset($validated['marks_awarded'])) {
                return back()->withErrors(['marks_awarded' => 'Marks required.'])->withInput();
            }
            if ($assignment->total_marks && $validated['marks_awarded'] > $assignment->total_marks) {
                return back()->withErrors(['marks_awarded' => 'Marks cannot exceed total marks.'])->withInput();
            }
            $submission->marks_awarded = $validated['marks_awarded'];
            $submission->is_passed = null;
        } else {
            if (!isset($validated['is_passed'])) {
                return back()->withErrors(['is_passed' => 'Pass/Fail required.'])->withInput();
            }
            $submission->is_passed = (bool)$validated['is_passed'];
            $submission->marks_awarded = null;
        }

        $submission->feedback = $validated['feedback'] ?? null;
        $submission->status = 'graded';
        $submission->save();

        return redirect()
            ->route('teacher.assignments.submissions.show', [$assignment->id, $submission->id])
            ->with('success', 'Submission graded.');
    }

    public function showAttempt(QuizAttempt $attempt)
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $attempt->load(['user', 'quiz.course', 'answers.question']);

        abort_if(!$teacher->coursesTeaching()->where('courses.id', $attempt->quiz->course_id)->exists(), 403);

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.submissions.quiz_attempt_show', compact('attempt', 'sidebarCounts'))
            ->with('topbarUnread', $unread);
    }

    public function gradeAttempt(Request $request, QuizAttempt $attempt)
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $attempt->load(['quiz.course', 'answers.question']);

        abort_if(!$teacher->coursesTeaching()->where('courses.id', $attempt->quiz->course_id)->exists(), 403);

        $validated = $request->validate([
            'awards' => ['required', 'array'],
            'awards.*' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ]);

        $totalMarks = 0;
        $score = 0;

        foreach ($attempt->answers as $ans) {
            $q = $ans->question;
            $qMarks = (int)($q->marks ?? 0);
            $totalMarks += $qMarks;

            $award = (int)($validated['awards'][$ans->id] ?? 0);
            if ($award > $qMarks) $award = $qMarks;

            $ans->awarded_marks = $award;
            $ans->save();

            $score += $award;
        }

        // Your table has total_marks, so use it:
        $attempt->score = $score;
        $attempt->total_marks = $totalMarks;
        $attempt->status = 'graded';
        $attempt->save();

        return back()->with('success', 'Quiz attempt graded.');
    }
}