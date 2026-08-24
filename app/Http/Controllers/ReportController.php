<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        $transactions = Transaction::forUser($request->user()->id)
            ->with('category')
            ->dateRange($from, $to)
            ->get();

        $grouped = $transactions->groupBy('category_id');

        $expenseByCategory = [];
        $categoryBreakdown = [];

        // حساب إجمالي المصاريف وإجمالي الدخل بشكل منفصل
        $totalExpenses = (float) $transactions
            ->filter(fn ($t) => ($t->type ?? $t->category?->type) === 'expense')
            ->sum('amount');

        $totalIncome = (float) $transactions
            ->filter(fn ($t) => ($t->type ?? $t->category?->type) === 'income')
            ->sum('amount');

        foreach ($grouped as $categoryId => $categoryTransactions) {
            $category = $categoryTransactions->first()->category;

            if ($category === null) {
                continue;
            }

            $amount = (float) $categoryTransactions->sum('amount');
            $type = $category->type ?? $categoryTransactions->first()->type ?? 'expense';

            // حساب النسبة المئوية الصحيحة بناءً على نوع الفئة
            $totalForType = ($type === 'income') ? $totalIncome : $totalExpenses;
            $percentage = $totalForType > 0
                ? round(($amount / $totalForType) * 100, 1)
                : 0;

            $descriptions = $categoryTransactions
                ->pluck('description')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $expenseByCategory[] = [
                'category' => strtolower($category->name),
                'amount' => $amount,
                'color' => $category->color,
                'type' => $type,
            ];

            $categoryBreakdown[] = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color,
                'amount' => $amount,
                'percentage' => $percentage,
                'descriptions' => $descriptions,
                'type' => $type,
                'budget_limit' => $category->budget_limit ? (float) $category->budget_limit : null,
            ];
        }

        usort($categoryBreakdown, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return Inertia::render('Reports', [
            'expenseByCategory' => $expenseByCategory,
            'categoryBreakdown' => $categoryBreakdown,
            'totalExpenses' => $totalExpenses,
            'totalIncome' => $totalIncome,
            'transactions' => $transactions,
            'dateRange' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        ]);
    }
}
