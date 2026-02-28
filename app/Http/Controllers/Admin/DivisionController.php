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
        ]);

        return redirect()
            ->route('admin.divisions.index')
            ->with('success', 'Division created successfully.');
    }

    public function edit(Division $division)
    {
        return view('admin.divisions.edit', compact('division'));
    }

    public function update(Request $request, \App\Models\Division $division)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);

        // ensure unique slug (ignore current division)
        $baseSlug = $slug;
        $i = 1;
        while (\App\Models\Division::where('slug', $slug)->where('id', '!=', $division->id)->exists()) {
            $slug = $baseSlug . '-' . $i++;
        }

        // remove old image if requested
        if ($request->boolean('remove_image') && $division->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($division->image);
            $division->image = null;
        }

        // new image upload
        if ($request->hasFile('image')) {
            if ($division->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($division->image);
            }
            $division->image = $request->file('image')->store('divisions', 'public');
        }

        $division->name = $validated['name'];
        $division->slug = $slug;
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