<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Division;

class AssignmentViewController extends Controller
{
    public function show(Course $course, Assignment $assignment)
    {
        // Ensure assignment belongs to course
        abort_if((int) $assignment->course_id !== (int) $course->id, 404);

        $user = auth()->user();

        // Load course division safely
        $course->loadMissing('subject.division');

        $userDivision = Division::find($user->division_id);
        abort_if(!$userDivision, 403);

        $courseDivision = $course->subject?->division;
        abort_if(!$courseDivision, 404);

        // Same access rule as lesson/subject pages
        abort_if((int) $courseDivision->level > (int) $userDivision->level, 403);

        // Students can only view published assignments
        abort_if(($assignment->status ?? 'draft') !== 'published', 403);

        // Latest submission
        $submission = $assignment->submissions()
            ->where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();

        // Attempt count
        $usedAttempts = $assignment->submissions()
            ->where('user_id', $user->id)
            ->count();

        // Only middle school students get click-to-define
        $showClickDefine = $this->isMiddleSchoolDivision($userDivision);

        return view('student.assignment', compact(
            'course',
            'assignment',
            'submission',
            'usedAttempts',
            'showClickDefine'
        ));
    }

    private function isMiddleSchoolDivision(?Division $division): bool
    {
        if (!$division) {
            return false;
        }

        $name = strtolower((string) $division->name);

        // Prefer explicit name match if your division is named "Middle School"
        if (str_contains($name, 'middle')) {
            return true;
        }

        // Fallback: second-lowest level = middle school
        $levels = Division::orderBy('level')->pluck('level')->values();

        if ($levels->count() < 2) {
            return false;
        }

        $middleLevel = (int) $levels[1];

        return (int) $division->level === $middleLevel;
    }
}