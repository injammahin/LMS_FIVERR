<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $divisionId = $request->get('division_id');

        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->latest();

        if ($divisionId) {
            $subjectsQuery->where('division_id', $divisionId);
        }

        $subjects = $subjectsQuery->paginate(10)->withQueryString();

        return view('admin.subjects.index', compact('subjects', 'divisions', 'divisionId'));
    }

    public function create(Request $request)
    {
        $divisions = Division::orderBy('name')->get();
        $selectedDivisionId = $request->get('division_id'); // from ?division_id=...

        return view('admin.subjects.create', compact('divisions', 'selectedDivisionId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'names'       => ['required', 'array', 'min:1'],
            'names.*'     => ['required', 'string', 'max:255'],
        ]);

        $divisionId = $validated['division_id'];

        // clean & unique input (remove empty, trim, remove duplicates in request)
        $rawNames = collect($validated['names'])
            ->map(fn ($n) => trim($n))
            ->filter()
            ->values();

        if ($rawNames->isEmpty()) {
            return back()->withErrors(['names' => 'Please enter at least one subject name.'])->withInput();
        }

        $uniqueNames = $rawNames->unique(fn ($n) => mb_strtolower($n))->values();

        $toInsert = [];

        foreach ($uniqueNames as $name) {
            $slug = \Illuminate\Support\Str::slug($name);

            // unique slug within division
            $baseSlug = $slug;
            $i = 1;
            while (
                \App\Models\Subject::where('division_id', $divisionId)
                    ->where('slug', $slug)
                    ->exists()
                || collect($toInsert)->contains(fn ($row) => $row['slug'] === $slug) // prevent duplicates in same request
            ) {
                $slug = $baseSlug . '-' . $i++;
            }

            $toInsert[] = [
                'division_id' => $divisionId,
                'name'        => $name,
                'slug'        => $slug,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        \App\Models\Subject::insert($toInsert);

        return redirect()
            ->route('admin.subjects.index', ['division_id' => $divisionId])
            ->with('success', count($toInsert) . ' subject(s) created successfully.');
    }

    public function edit(Subject $subject)
    {
        $divisions = Division::orderBy('name')->get();
        return view('admin.subjects.edit', compact('subject', 'divisions'));
    }

    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'division_id' => ['required', 'exists:divisions,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $slug = Str::slug($validated['name']);

        // unique within division (ignore current subject)
        $baseSlug = $slug;
        $i = 1;
        while (
            Subject::where('division_id', $validated['division_id'])
                ->where('slug', $slug)
                ->where('id', '!=', $subject->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $i++;
        }

        $subject->update([
            'division_id' => $validated['division_id'],
            'name' => $validated['name'],
            'slug' => $slug,
        ]);

        return redirect()
            ->route('admin.subjects.index', ['division_id' => $validated['division_id']])
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy(Subject $subject)
    {
        $divisionId = $subject->division_id;
        $subject->delete();

        return redirect()
            ->route('admin.subjects.index', ['division_id' => $divisionId])
            ->with('success', 'Subject deleted successfully.');
    }
}