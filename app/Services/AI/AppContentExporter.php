<?php

namespace App\Services\AI;

use App\Models\Course;
use Illuminate\Support\Str;

class AppContentExporter
{
    public function exportCourseMarkdown(Course $course): string
    {
        $course->loadMissing([
            'subject.division',
            'lessons.blocks',    // if you have blocks relation
            'quizzes.questions.options', // adjust if needed
            'assignments',       // adjust if needed
        ]);

        $out = [];
        $out[] = "# Course: {$course->title}";
        $out[] = "";

        if ($course->subject?->division) {
            $out[] = "Division: {$course->subject->division->name}";
        }
        if ($course->subject) {
            $out[] = "Subject: {$course->subject->name}";
        }

        $out[] = "Course ID: {$course->id}";
        $out[] = "";

        if (!empty($course->description)) {
            $out[] = "## Course Description";
            $out[] = $this->plain($course->description);
            $out[] = "";
        }

        // Lessons
        if ($course->lessons && $course->lessons->count()) {
            $out[] = "## Lessons";
            foreach ($course->lessons->sortBy('position') as $lesson) {
                $out[] = "### Lesson {$lesson->position}: {$lesson->title}";
                if (!empty($lesson->description)) $out[] = $this->plain($lesson->description);

                // If you store lesson content in blocks
                if (isset($lesson->blocks) && $lesson->blocks) {
                    foreach ($lesson->blocks as $b) {
                        if (($b->type ?? '') === 'text' && !empty($b->text)) {
                            $out[] = "- " . $this->plain($b->text);
                        }
                        if (($b->type ?? '') === 'video' && !empty($b->video_url)) {
                            $out[] = "- Video: {$b->video_url}";
                        }
                        if (($b->type ?? '') === 'file' && !empty($b->file_path)) {
                            $out[] = "- File: {$b->file_path}";
                        }
                    }
                }

                $out[] = "";
            }
        }

        // Quizzes (avoid leaking correct answers)
        if ($course->quizzes && $course->quizzes->count()) {
            $out[] = "## Quizzes";
            foreach ($course->quizzes as $quiz) {
                $out[] = "### Quiz: {$quiz->title}";
                foreach ($quiz->questions ?? [] as $q) {
                    $out[] = "- Q: " . $this->plain($q->question ?? '');
                    // Only show option text, NOT is_correct
                    foreach (($q->options ?? []) as $opt) {
                        $out[] = "  - Option: " . $this->plain($opt->option_text ?? '');
                    }
                }
                $out[] = "";
            }
        }

        // Assignments
        if ($course->assignments && $course->assignments->count()) {
            $out[] = "## Assignments";
            foreach ($course->assignments as $a) {
                $out[] = "### Assignment: {$a->title}";
                if (!empty($a->description)) $out[] = $this->plain($a->description);
                $out[] = "";
            }
        }

        // General support style instruction inside doc
        $out[] = "## Support Notes";
        $out[] = "Answer users clearly with steps based on the content above.";
        $out[] = "If user asks about account-specific issues, ask for details or advise contacting support.";

        return implode("\n", $out);
    }

    private function plain(string $html): string
    {
        return Str::of($html)
            ->replace('&nbsp;', ' ')
            ->stripTags()
            ->squish()
            ->toString();
    }
}