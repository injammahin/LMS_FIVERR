<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DivisionController extends Controller
{
    public function index()
    {
        $divisions = Division::latest()->paginate(10);
        return view('admin.divisions.index', compact('divisions'));
    }

    public function create()
    {
        return view('admin.divisions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1'],
            'promotion_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);

        // ensure unique slug
        $baseSlug = $slug;
        $i = 1;
        while (\App\Models\Division::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('divisions', 'public');
        }

        \App\Models\Division::create([
            'name'  => $validated['name'],
            'slug'  => $slug,
            'image' => $imagePath,
            'level' => $validated['level'],
            'promotion_percent' => $validated['promotion_percent'],
        ]);

        return redirect()
            ->route('admin.divisions.index')
            ->with('success', 'Division created successfully.');
    }

    public function edit(Division $division)
    {
        return view('admin.divisions.edit', compact('division'));
    }

    public function update(Request $request, Division $division)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'level' => ['required', 'integer', 'min:1'],
            'promotion_percent' => ['required', 'integer', 'min:1', 'max:100'],
            'auto_promote' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        // Generate unique slug
        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $i = 1;

        while (Division::where('slug', $slug)
            ->where('id', '!=', $division->id)
            ->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        // Remove image
        if ($request->boolean('remove_image') && $division->image) {
            Storage::disk('public')->delete($division->image);
            $division->image = null;
        }

        // Upload new image
        if ($request->hasFile('image')) {
            if ($division->image) {
                Storage::disk('public')->delete($division->image);
            }

            $division->image = $request->file('image')
                ->store('divisions', 'public');
        }

        // Update fields
        $division->name = $validated['name'];
        $division->slug = $slug;
        $division->level = $validated['level'];
        $division->promotion_percent = $validated['promotion_percent'];
        $division->auto_promote = $request->boolean('auto_promote');
        $division->save();

        return redirect()
            ->route('admin.divisions.index')
            ->with('success', 'Division updated successfully.');
    }

    public function destroy(Division $division)
    {
        if ($division->image) {
            Storage::disk('public')->delete($division->image);
        }

        $division->delete();

        return redirect()
            ->route('admin.divisions.index')
            ->with('success', 'Division deleted successfully.');
    }
}