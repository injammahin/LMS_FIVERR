<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizOptionController extends Controller
{
    public function index(QuizQuestion $question)
    {
        $question->load('quiz');
        $options = $question->options()->orderBy('position')->get();

        return view('admin.quiz_options.index', compact('question','options'));
    }

    public function create(QuizQuestion $question)
    {
        $question->load('quiz');
        return view('admin.quiz_options.create', compact('question'));
    }

    public function store(Request $request, QuizQuestion $question)
    {
        if (!$question->needsOptions()) {
            return back()->with('success', 'This question type does not use options.');
        }

        $validated = $request->validate([
            'option_text' => ['nullable','string','max:5000'],
            'option_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'is_correct' => ['nullable','boolean'],
            'position' => ['required','integer','min:1','max:100000'],
        ]);

        $imgPath = null;
        if ($request->hasFile('option_image')) {
            $imgPath = $request->file('option_image')->store('quiz_options', 'public');
        }

        $question->options()->create([
            'option_text' => $validated['option_text'] ?? null,
            'option_image' => $imgPath,
            'is_correct' => $request->boolean('is_correct'),
            'position' => $validated['position'],
        ]);

        return redirect()
            ->route('admin.questions.options.index', $question->id)
            ->with('success', 'Option added successfully.');
    }

    public function edit(QuizQuestion $question, QuizOption $option)
    {
        abort_if($option->question_id !== $question->id, 404);
        $question->load('quiz');
        return view('admin.quiz_options.edit', compact('question','option'));
    }

    public function update(Request $request, QuizQuestion $question, QuizOption $option)
    {
        abort_if($option->question_id !== $question->id, 404);

        $validated = $request->validate([
            'option_text' => ['nullable','string','max:5000'],
            'option_image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:4096'],
            'remove_image' => ['nullable','boolean'],
            'is_correct' => ['nullable','boolean'],
            'position' => ['required','integer','min:1','max:100000'],
        ]);

        if ($request->boolean('remove_image') && $option->option_image) {
            Storage::disk('public')->delete($option->option_image);
            $option->option_image = null;
        }

        if ($request->hasFile('option_image')) {
            if ($option->option_image) Storage::disk('public')->delete($option->option_image);
            $option->option_image = $request->file('option_image')->store('quiz_options', 'public');
        }

        $option->update([
            'option_text' => $validated['option_text'] ?? null,
            'option_image' => $option->option_image,
            'is_correct' => $request->boolean('is_correct'),
            'position' => $validated['position'],
        ]);

        return redirect()
            ->route('admin.questions.options.index', $question->id)
            ->with('success', 'Option updated successfully.');
    }

    public function destroy(QuizQuestion $question, QuizOption $option)
    {
        abort_if($option->question_id !== $question->id, 404);

        if ($option->option_image) Storage::disk('public')->delete($option->option_image);
        $option->delete();

        return redirect()
            ->route('admin.questions.options.index', $question->id)
            ->with('success', 'Option deleted successfully.');
    }
}