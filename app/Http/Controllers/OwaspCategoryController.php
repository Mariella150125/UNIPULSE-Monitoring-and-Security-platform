<?php

namespace App\Http\Controllers;

use App\Models\OwaspCategory;
use Illuminate\Http\Request;

class OwaspCategoryController extends Controller
{
    public function index()
    {
        $categories = OwaspCategory::orderBy('code')->get();
        return view('security.owasp-index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|url',
        ]);
        OwaspCategory::create($validated);
        return redirect()->route('owasp-categories.index')->with('success', 'Catégorie ajoutée.');
    }

    public function update(Request $request, OwaspCategory $owaspCategory)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'url' => 'required|url',
        ]);
        $owaspCategory->update($validated);
        return redirect()->route('owasp-categories.index')->with('success', 'Catégorie modifiée.');
    }

    public function toggleActive(OwaspCategory $owaspCategory)
    {
        $owaspCategory->update(['is_active' => !$owaspCategory->is_active]);
        return redirect()->route('owasp-categories.index')->with('success', 'Visibilité modifiée.');
    }

    public function destroy(OwaspCategory $owaspCategory)
    {
        $owaspCategory->delete();
        return redirect()->route('owasp-categories.index')->with('success', 'Catégorie supprimée.');
    }
}