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
            ? Carbon::parse($request->input('from'))
            : Carbon::now()->startOfMonth();

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))
            : Carbon::now()->endOfMonth();

        $transactions = Transaction::forUser($request->user()->id)
            ->expense()
            ->with('category')
            ->dateRange($from, $to)
            ->get();

        $grouped = $transactions->groupBy('category_id');

        $expenseByCategory = [];
        $categoryBreakdown = [];
        $totalExpenses = (float) $transactions->sum('amount');

        foreach ($grouped as $categoryId => $categoryTransactions) {
            $category = $categoryTransactions->first()->category;

            if ($category === null) {
                continue;
            }

            $amount = (float) $categoryTransactions->sum('amount');
            $percentage = $totalExpenses > 0
                ? round(($amount / $totalExpenses) * 100, 1)
                : 0;

            // تجميع الأوصاف الخاصة بكل فئة
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
            ];

            $categoryBreakdown[] = [
                'id' => $category->id,
                'name' => $category->name,
                'icon' => $category->icon,
                'color' => $category->color,
                'amount' => $amount,
                'percentage' => $percentage,
                'descriptions' => $descriptions, // تم إضافة الأوصاف هنا
            ];
        }

        usort($categoryBreakdown, fn ($a, $b) => $b['amount'] <=> $a['amount']);

        return Inertia::render('Reports', [
            'expenseByCategory' => $expenseByCategory,
            'categoryBreakdown' => $categoryBreakdown,
            'totalExpenses' => $totalExpenses,
            'transactions' => $transactions, // تم إرسال المعاملات هنا
            'dateRange' => [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ],
        ]);
    }
}