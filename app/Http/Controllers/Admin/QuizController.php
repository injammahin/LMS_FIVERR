<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function index(Course $course)
    {
        $quizzes = $course->quizzes()->latest()->paginate(15);
        return view('admin.quizzes.index', compact('course', 'quizzes'));
    }

    public function create(Course $course)
    {
        return view('admin.quizzes.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],

            'time_limit_minutes' => ['nullable','integer','min:1','max:600'],
            'pass_mark' => ['nullable','integer','min:0','max:100'],
            'max_attempts' => ['nullable','integer','min:1','max:100'],

            'shuffle_questions' => ['nullable','boolean'],
            'shuffle_options' => ['nullable','boolean'],
            'status' => ['required','in:draft,published'],
        ]);

        Quiz::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'pass_mark' => $validated['pass_mark'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_options' => $request->boolean('shuffle_options'),
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.courses.quizzes.index', $course->id)
            ->with('success', 'Quiz created successfully.');
    }

    public function edit(Course $course, Quiz $quiz)
    {
        abort_if($quiz->course_id !== $course->id, 404);
        return view('admin.quizzes.edit', compact('course','quiz'));
    }

    public function update(Request $request, Course $course, Quiz $quiz)
    {
        abort_if($quiz->course_id !== $course->id, 404);

        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],

            'time_limit_minutes' => ['nullable','integer','min:1','max:600'],
            'pass_mark' => ['nullable','integer','min:0','max:100'],
            'max_attempts' => ['nullable','integer','min:1','max:100'],

            'shuffle_questions' => ['nullable','boolean'],
            'shuffle_options' => ['nullable','boolean'],
            'status' => ['required','in:draft,published'],
        ]);

        $quiz->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'time_limit_minutes' => $validated['time_limit_minutes'] ?? null,
            'pass_mark' => $validated['pass_mark'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,
            'shuffle_questions' => $request->boolean('shuffle_questions'),
            'shuffle_options' => $request->boolean('shuffle_options'),
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.courses.quizzes.index', $course->id)
            ->with('success', 'Quiz updated successfully.');
    }

    public function destroy(Course $course, Quiz $quiz)
    {
        abort_if($quiz->course_id !== $course->id, 404);

        $quiz->delete();

        return redirect()
            ->route('admin.courses.quizzes.index', $course->id)
            ->with('success', 'Quiz deleted successfully.');
    }
}