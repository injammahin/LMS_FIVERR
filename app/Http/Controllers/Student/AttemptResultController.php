<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;

class AttemptResultController extends Controller
{
    public function show(QuizAttempt $attempt)
    {
        // ✅ must belong to logged-in student
        abort_if((int)$attempt->user_id !== (int)auth()->id(), 403);

        // ✅ must be submitted (teacher grading may change status, so don't block by status)
        abort_if(empty($attempt->submitted_at), 404);

        $attempt->load([
            'quiz.course',
            'quiz.questions.options',
            'answers.question.options',
        ]);

        $quiz = $attempt->quiz;
        $course = $quiz->course;

        $questions = $quiz->questions()->with('options')->orderBy('position')->get();
        $answers = $attempt->answers->keyBy('question_id');

        // ✅ Calculate total marks from questions (always correct)
        $total = (int) $questions->sum(fn($q) => (int)($q->marks ?? 0));

        // ✅ Score: use attempt->score if you store it, else sum awarded_marks
        $score = (int)($attempt->score ?? $attempt->answers->sum('awarded_marks'));

        // objective breakdown (optional)
        $objectiveTypes = ['true_false','single_choice','multiple_choice'];
        $objectiveTotal = (int) $questions->whereIn('type', $objectiveTypes)->sum('marks');

        $objectiveScore = 0;
        foreach ($attempt->answers as $a) {
            $qt = $a->question?->type;
            if (in_array($qt, $objectiveTypes, true)) {
                $objectiveScore += (int)($a->awarded_marks ?? 0);
            }
        }

        // pending review (manual types)
        $pendingReview = $questions->whereIn('type', ['text','file'])->count();

        return view('student.quiz_result', compact(
            'attempt','quiz','course','questions','answers',
            'score','total','objectiveScore','objectiveTotal','pendingReview'
        ));
    }
}