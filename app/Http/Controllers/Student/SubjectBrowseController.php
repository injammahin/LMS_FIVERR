<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Subject;

class SubjectBrowseController extends Controller
{
    public function show(Division $division, Subject $subject)
    {
        $user = auth()->user();

        // Must be assigned division
        abort_if((int)$user->division_id !== (int)$division->id, 403);

        // Subject must belong to this division
        abort_if((int)$subject->division_id !== (int)$division->id, 404);

        // Load courses and their lessons
        $subject->load([
            'courses' => function ($q) {
                $q->orderBy('title')->with([
                    'lessons' => fn($lq) => $lq->orderBy('position'),
                    'quizzes' => fn($qq) => $qq->latest(),
                    'assignments' => fn($aq) => $aq->latest(),
                ]);
            }
        ]);

        $coursesCount = $subject->courses->count();
        $lessonsCount = $subject->courses->sum(fn($c) => $c->lessons->count());
        $quizzesCount = $subject->courses->sum(fn($c) => $c->quizzes->count());
        $assignmentsCount = $subject->courses->sum(fn($c) => $c->assignments->count());

        return view('student.subject', compact(
            'division','subject',
            'coursesCount','lessonsCount','quizzesCount','assignmentsCount'
        ));
    }
}