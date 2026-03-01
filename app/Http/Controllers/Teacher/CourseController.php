<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Teacher\Concerns\TeacherCounts;
use App\Models\Course;
use App\Models\User;

class CourseController extends Controller
{
    use TeacherCounts;

    private function teacherOwnsCourse(Course $course): void
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();
        abort_if(!$teacher->coursesTeaching()->where('courses.id', $course->id)->exists(), 403);
    }

    public function index()
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        $courses = $teacher->coursesTeaching()
            ->with(['subject.division'])
            ->orderBy('title')
            ->get();

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.courses.index', compact('courses', 'sidebarCounts'))
            ->with('topbarUnread', $unread);
    }

    public function show(Course $course)
    {
        $this->teacherOwnsCourse($course);

        $course->load(['subject.division', 'lessons', 'quizzes', 'assignments']);

        $divisionId = optional($course->subject)->division_id;

        $students = User::where('role', 'student')
            ->when($divisionId, fn($q) => $q->where('division_id', $divisionId))
            ->orderBy('name')
            ->paginate(20);

        $sidebarCounts = $this->sidebarCounts();
        $unread = $sidebarCounts['unread'];

        return view('teacher.courses.show', compact('course', 'students', 'sidebarCounts'))
            ->with('topbarUnread', $unread);
    }
}