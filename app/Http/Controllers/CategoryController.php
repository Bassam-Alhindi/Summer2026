<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::forUser($request->user()->id)
            ->ordered()
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $request->user()->categories()->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Category $category)
    {
        if ($category->isSystemDefault() || $category->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ]);

        $category->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->isSystemDefault() || $category->user_id !== $request->user()->id) {
            abort(403);
        }

        $category->delete();

        return redirect()->back();
    }
}
