<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SyncKbEntryToOpenAI;
use App\Models\AiKbEntry;
use App\Models\Course;
use Illuminate\Http\Request;

class AiTrainingController extends Controller
{
    public function index()
    {
        $entries = AiKbEntry::latest()->paginate(20);
        return view('admin.ai_assistant.kb.index', compact('entries'));
    }

    public function create()
    {
        $courses = class_exists(Course::class) ? Course::orderBy('id','desc')->get() : collect();
        return view('admin.ai_assistant.kb.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'scope' => ['required','in:global,course'],
            'course_id' => ['nullable','integer'],
            'type' => ['required','in:qa,doc'],
            'title' => ['required','string','max:255'],
            'question' => ['nullable','string'],
            'answer' => ['nullable','string'],
            'body' => ['nullable','string'],
            'keywords' => ['nullable','string','max:255'],
            'is_active' => ['nullable'],
        ]);

        if ($data['scope'] === 'global') $data['course_id'] = null;

        $data['is_active'] = $request->boolean('is_active');
        $data['created_by'] = auth()->id();

        $entry = AiKbEntry::create($data);

        SyncKbEntryToOpenAI::dispatch($entry->id);

        return redirect()->route('admin.ai.kb.index')->with('success', 'Training saved & syncing to AI.');
    }

    public function edit(AiKbEntry $kb)
    {
        $courses = class_exists(Course::class) ? Course::orderBy('id','desc')->get() : collect();
        return view('admin.ai_assistant.kb.edit', ['entry' => $kb, 'courses' => $courses]);
    }

    public function update(Request $request, AiKbEntry $kb)
    {
        $data = $request->validate([
            'scope' => ['required','in:global,course'],
            'course_id' => ['nullable','integer'],
            'type' => ['required','in:qa,doc'],
            'title' => ['required','string','max:255'],
            'question' => ['nullable','string'],
            'answer' => ['nullable','string'],
            'body' => ['nullable','string'],
            'keywords' => ['nullable','string','max:255'],
            'is_active' => ['nullable'],
        ]);

        if ($data['scope'] === 'global') $data['course_id'] = null;

        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = auth()->id();

        $kb->update($data);

        SyncKbEntryToOpenAI::dispatch($kb->id);

        return redirect()->route('admin.ai.kb.index')->with('success', 'Training updated & syncing to AI.');
    }

    public function destroy(AiKbEntry $kb)
    {
        $kb->update(['is_active' => false, 'updated_by' => auth()->id()]);
        return back()->with('success','Training disabled.');
    }
}