<?php

namespace App\Http\Controllers\Teacher\Concerns;

use App\Models\AssignmentSubmission;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Schema;

trait TeacherCounts
{
    protected function teacherCourseIds(): array
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();
        return $teacher->coursesTeaching()->pluck('courses.id')->all();
    }

    protected function unreadCount(): int
    {
        /** @var \App\Models\User $teacher */
        $teacher = auth()->user();

        // if notifications table not migrated yet, don't crash
        if (!Schema::hasTable('notifications')) return 0;

        return $teacher->unreadNotifications()->count();
    }

    protected function pendingCount(array $courseIds): int
    {
        if (empty($courseIds)) return 0;

        $pendingAssignments = AssignmentSubmission::whereHas('assignment.course', fn ($q) => $q->whereIn('id', $courseIds))
            ->where('status', 'submitted')
            ->count();

        $pendingQuizzes = QuizAttempt::whereHas('quiz.course', fn ($q) => $q->whereIn('id', $courseIds))
            ->where('status', 'submitted')
            ->count();

        return $pendingAssignments + $pendingQuizzes;
    }

    protected function sidebarCounts(): array
    {
        $courseIds = $this->teacherCourseIds();

        return [
            'courses' => count($courseIds),
            'pending' => $this->pendingCount($courseIds),
            'unread'  => $this->unreadCount(),
        ];
    }
}