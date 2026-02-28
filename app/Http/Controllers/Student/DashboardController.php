<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Division;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // ✅ Real counts (no dummy)
        // Count subjects and courses for user's assigned division only
        $assignedDivision = null;
        $assignedSubjectsCount = 0;
        $assignedCoursesCount = 0;

        if ($user->division_id) {
            $assignedDivision = Division::withCount('subjects')->find($user->division_id);

            if ($assignedDivision) {
                $assignedSubjectsCount = (int) $assignedDivision->subjects_count;

                // courses count under this division through subjects -> courses
                $assignedCoursesCount = \App\Models\Course::whereHas('subject', function ($q) use ($user) {
                    $q->where('division_id', $user->division_id);
                })->count();
            }
        }

        // ✅ Divisions list with real counts
        // subjects_count is real
        // courses_count is real using withCount with nested relationship
        $divisions = Division::query()
            ->withCount([
                'subjects',
                // courses via subjects
                'subjects as courses_count' => function ($q) {
                    $q->join('courses', 'courses.subject_id', '=', 'subjects.id');
                }
            ])
            ->orderBy('name')
            ->get();

        return view('student.dashboard', compact(
            'user',
            'divisions',
            'assignedDivision',
            'assignedSubjectsCount',
            'assignedCoursesCount'
        ));
    }

public function division(\App\Models\Division $division)
{
    $user = auth()->user();

    abort_if((int)$user->division_id !== (int)$division->id, 403);

    $subjects = $division->subjects()
        ->withCount('courses')
        ->orderBy('name')
        ->get();

    return view('student.division', compact('division', 'subjects'));
}
}