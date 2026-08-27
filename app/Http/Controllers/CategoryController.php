<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CategoryBudget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $categories = Category::forUser($userId)
            ->withBudgetFor($userId)
            ->ordered()
            ->get()
            ->map(function (Category $category) use ($userId) {
                // نطلع الحد الخاص بالمستخدم بدل العمود المشترك
                $category->setAttribute('budget_limit', $category->budgetLimitFor($userId));
                $category->unsetRelation('budgets');

                return $category;
            });

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
            'budget_limit' => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'تنبيه: هذه الفئة موجودة بالفعل!',
        ]);

        $validated['icon'] = $validated['icon'] ?? '';

        $budgetLimit = $validated['budget_limit'] ?? null;
        unset($validated['budget_limit']);

        $category = $request->user()->categories()->create($validated);
        $this->saveBudgetFor($request->user()->id, $category, $budgetLimit);

        return redirect()->back();
    }

    public function update(Request $request, Category $category)
    {
        if ($category->isSystemDefault()) {
            $validated = $request->validate([
                'budget_limit' => 'nullable|numeric|min:0',
            ]);

            // الصف مشترك بين كل المستخدمين، فالحد ينحفظ لكل مستخدم على حدة
            $this->saveBudgetFor($request->user()->id, $category, $validated['budget_limit'] ?? null);

            return redirect()->back();
        }

        if ($category->user_id !== $request->user()->id) {
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
            'budget_limit' => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'تنبيه: هذه الفئة موجودة بالفعل!',
        ]);

        $validated['icon'] = $validated['icon'] ?? '';

        $budgetLimit = $validated['budget_limit'] ?? null;
        unset($validated['budget_limit']);

        $category->update($validated);
        $this->saveBudgetFor($request->user()->id, $category, $budgetLimit);

        return redirect()->back();
    }

    /** حفظ/حذف حد الميزانية لهذا المستخدم فقط. */
    private function saveBudgetFor(int $userId, Category $category, $budgetLimit): void
    {
        if ($budgetLimit === null || (float) $budgetLimit <= 0) {
            CategoryBudget::where('user_id', $userId)
                ->where('category_id', $category->id)
                ->delete();

            return;
        }

        CategoryBudget::updateOrCreate(
            ['user_id' => $userId, 'category_id' => $category->id],
            ['budget_limit' => $budgetLimit],
        );
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
