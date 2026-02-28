<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizQuestionController extends Controller
{
    public function index(Quiz $quiz)
    {
        $questions = $quiz->questions()->with('options')->orderBy('position')->paginate(20);
        return view('admin.quiz_questions.index', compact('quiz','questions'));
    }

    public function create(Quiz $quiz)
    {
        return view('admin.quiz_questions.create', compact('quiz'));
    }

    public function store(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'type' => ['required','in:text,file,single_choice,multiple_choice,true_false'],
            'question' => ['required','string','max:5000'],
            'question_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'explanation' => ['nullable','string'],
            'marks' => ['required','integer','min:1','max:100'],
            'position' => ['required','integer','min:1','max:100000'],
            'is_required' => ['nullable','boolean'],
        ]);

        $imgPath = null;
        if ($request->hasFile('question_image')) {
            $imgPath = $request->file('question_image')->store('quiz_questions', 'public');
        }

        $question = QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'type' => $validated['type'],
            'question' => $validated['question'],
            'question_image' => $imgPath,
            'explanation' => $validated['explanation'] ?? null,
            'marks' => $validated['marks'],
            'position' => $validated['position'],
            'is_required' => $request->boolean('is_required', true),
        ]);

        // If true_false, auto-create True/False options
        if ($question->type === 'true_false') {
            $question->options()->createMany([
                ['option_text' => 'True', 'is_correct' => true, 'position' => 1],
                ['option_text' => 'False', 'is_correct' => false, 'position' => 2],
            ]);
        }

        return redirect()
            ->route('admin.quizzes.questions.index', $quiz->id)
            ->with('success', 'Question created successfully.');
    }

    public function edit(Quiz $quiz, QuizQuestion $question)
    {
        abort_if($question->quiz_id !== $quiz->id, 404);
        $question->load('options');

        return view('admin.quiz_questions.edit', compact('quiz','question'));
    }

    public function update(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        abort_if($question->quiz_id !== $quiz->id, 404);

        $validated = $request->validate([
            'type' => ['required','in:text,file,single_choice,multiple_choice,true_false'],
            'question' => ['required','string','max:5000'],
            'question_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'remove_image' => ['nullable','boolean'],
            'explanation' => ['nullable','string'],
            'marks' => ['required','integer','min:1','max:100'],
            'position' => ['required','integer','min:1','max:100000'],
            'is_required' => ['nullable','boolean'],
        ]);

        if ($request->boolean('remove_image') && $question->question_image) {
            Storage::disk('public')->delete($question->question_image);
            $question->question_image = null;
        }

        if ($request->hasFile('question_image')) {
            if ($question->question_image) Storage::disk('public')->delete($question->question_image);
            $question->question_image = $request->file('question_image')->store('quiz_questions', 'public');
        }

        $oldType = $question->type;

        $question->update([
            'type' => $validated['type'],
            'question' => $validated['question'],
            'question_image' => $question->question_image,
            'explanation' => $validated['explanation'] ?? null,
            'marks' => $validated['marks'],
            'position' => $validated['position'],
            'is_required' => $request->boolean('is_required', true),
        ]);

        // If type changed to true_false and no options exist, auto-create
        if ($question->type === 'true_false' && $question->options()->count() === 0) {
            $question->options()->createMany([
                ['option_text' => 'True', 'is_correct' => true, 'position' => 1],
                ['option_text' => 'False', 'is_correct' => false, 'position' => 2],
            ]);
        }

        // If changed from option-type to non-option type, you may want to keep options or delete them.
        // We'll KEEP them (safe). You can delete later if you want.

        return redirect()
            ->route('admin.quizzes.questions.index', $quiz->id)
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(Quiz $quiz, QuizQuestion $question)
    {
        abort_if($question->quiz_id !== $quiz->id, 404);

        if ($question->question_image) Storage::disk('public')->delete($question->question_image);

        // delete option images too
        foreach ($question->options as $opt) {
            if ($opt->option_image) Storage::disk('public')->delete($opt->option_image);
        }

        $question->delete();

        return redirect()
            ->route('admin.quizzes.questions.index', $quiz->id)
            ->with('success', 'Question deleted successfully.');
    }
}