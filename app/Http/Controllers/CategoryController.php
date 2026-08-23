<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where(function ($q) use ($request) {
                        $q->where('user_id', $request->user()->id)
                          ->orWhereNull('user_id');
                    })->where('type', $request->input('type'));
                }),
            ],
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ], [
            'name.unique' => 'تنبيه: هذه الفئة موجودة بالفعل!',
        ]);

        $validated['icon'] = $validated['icon'] ?? '';

        $request->user()->categories()->create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Category $category)
    {
        if ($category->isSystemDefault() || $category->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where(function ($q) use ($request) {
                        $q->where('user_id', $request->user()->id)
                          ->orWhereNull('user_id');
                    })->where('type', $request->input('type'));
                })->ignore($category->id),
            ],
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string',
            'icon' => 'nullable|string',
        ], [
            'name.unique' => 'تنبيه: هذه الفئة موجودة بالفعل!',
        ]);

        $validated['icon'] = $validated['icon'] ?? '';

        $category->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Category $category)
    {
        if ($category->isSystemDefault() || $category->user_id !== $request->user()->id) {
            abort(403);
        }

        DB::transaction(function () use ($category) {
            // 1. حذف كافة المعاملات المرتبطة بهذه الفئة مباشرة من جدول المعاملات
            DB::table('transactions')->where('category_id', $category->id)->delete();

            // 2. حذف الفئة
            $category->delete();
        });

        return redirect()->back();
    }
}