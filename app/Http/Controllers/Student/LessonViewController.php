<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonViewController extends Controller
{
    public function show(Course $course, Lesson $lesson)
    {
        abort_if((int) $lesson->course_id !== (int) $course->id, 404);
        abort_if($course->status !== 'published', 404);

        $user = auth()->user();

        $course->loadMissing('subject.division');

        $userDivision = Division::find($user->division_id);
        abort_if(!$userDivision, 403);

        $courseDivision = $course->subject?->division;
        abort_if(!$courseDivision, 404);

        abort_if((int) $courseDivision->level > (int) $userDivision->level, 403);

        LessonProgress::updateOrCreate(
            ['lesson_id' => $lesson->id, 'user_id' => $user->id],
            ['viewed_at' => now()]
        );

        $progress = LessonProgress::where('lesson_id', $lesson->id)
            ->where('user_id', $user->id)
            ->first();

        $isElementaryRewardDivision = $this->isElementaryRewardDivision($courseDivision);

        return view('student.lesson', compact(
            'course',
            'lesson',
            'progress',
            'isElementaryRewardDivision'
        ));
    }

    public function markDone(Request $request, Course $course, Lesson $lesson)
    {
        abort_if((int) $lesson->course_id !== (int) $course->id, 404);
        abort_if($course->status !== 'published', 404);

        $user = auth()->user();

        $course->loadMissing('subject.division');

        $userDivision = Division::find($user->division_id);
        abort_if(!$userDivision, 403);

        $courseDivision = $course->subject?->division;
        abort_if(!$courseDivision, 404);

        abort_if((int) $courseDivision->level > (int) $userDivision->level, 403);

        $reward = DB::transaction(function () use ($lesson, $user, $courseDivision) {
            $progress = LessonProgress::where('lesson_id', $lesson->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (!$progress) {
                $progress = new LessonProgress();
                $progress->lesson_id = $lesson->id;
                $progress->user_id = $user->id;
            }

            if (empty($progress->viewed_at)) {
                $progress->viewed_at = now();
            }

            $wasAlreadyDone = !empty($progress->completed_at);

            if (!$wasAlreadyDone) {
                $progress->completed_at = now();
            }

            $earnedStars = 0;
            $eligibleForStar = $this->isElementaryRewardDivision($courseDivision);

            if ($eligibleForStar && !$wasAlreadyDone && !$progress->star_earned) {
                $progress->star_earned = true;
                $earnedStars = 1;

                $user->increment('gold_stars', 1);
                $user->refresh();
            }

            $progress->save();

            return [
                'earned_stars'   => $earnedStars,
                'total_stars'    => (int) ($user->gold_stars ?? 0),
                'play_animation' => $earnedStars > 0,
            ];
        });

        return redirect()->route('student.lessons.show', [
            'course' => $course->id,
            'lesson' => $lesson->id,
            'back'   => $request->input('back'),
        ])
        ->with('success', $reward['earned_stars'] > 0
            ? 'Lesson completed. You earned 1 gold star!'
            : 'Lesson marked as done.')
        ->with('reward_animation', $reward['play_animation'])
        ->with('reward_stars', $reward['earned_stars'])
        ->with('reward_total_stars', $reward['total_stars']);
    }

    private function isElementaryRewardDivision(?Division $division): bool
    {
        if (!$division) {
            return false;
        }

        $lowestLevel = (int) Division::min('level');

        return (int) $division->level === $lowestLevel;
    }
}