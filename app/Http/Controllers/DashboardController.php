<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $period = $request->input('period', 'month');
        $userId = $request->user()->id;

        [$from, $to] = $this->getPeriodRange($period);
        [$prevFrom, $prevTo] = $this->getPreviousPeriodRange($period);

        $totalIncome = Transaction::forUser($userId)
            ->income()
            ->dateRange($from, $to)
            ->sum('amount');

        $totalExpenses = Transaction::forUser($userId)
            ->expense()
            ->dateRange($from, $to)
            ->sum('amount');

        $prevIncome = Transaction::forUser($userId)
            ->income()
            ->dateRange($prevFrom, $prevTo)
            ->sum('amount');

        $prevExpenses = Transaction::forUser($userId)
            ->expense()
            ->dateRange($prevFrom, $prevTo)
            ->sum('amount');

        $netBalance = $totalIncome - $totalExpenses;
        $savingsRate = (int) round(($netBalance / max($totalIncome, 1)) * 100);

        $incomeTrend = $this->calculateTrend($totalIncome, $prevIncome);
        $expenseTrend = $this->calculateTrend($totalExpenses, $prevExpenses);

        $recentTransactions = Transaction::forUser($userId)
            ->with('category')
            ->latestFirst()
            ->limit(10)
            ->get()
            ->map(fn (Transaction $t) => [
                'id' => $t->id,
                'description' => $t->description ?? $t->category->name,
                'amount' => (float) $t->amount,
                'type' => $t->type,
                'category' => $t->category->name,
                'date' => $t->transaction_date->format('M d'),
            ]);

        $expenseByCategory = Transaction::forUser($userId)
            ->expense()
            ->dateRange($from, $to)
            ->with('category')
            ->get()
            ->groupBy(fn (Transaction $t) => strtolower($t->category->name))
            ->map(function ($transactions, string $category) {
                return [
                    'category' => $category,
                    'amount' => (float) $transactions->sum('amount'),
                    'color' => $transactions->first()->category->color,
                ];
            })
            ->values();

        $categories = Category::forUser($userId)
            ->ordered()
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
            ]);

        return Inertia::render('Dashboard', [
            'totalIncome' => (float) $totalIncome,
            'totalExpenses' => (float) $totalExpenses,
            'netBalance' => (float) $netBalance,
            'savingsRate' => $savingsRate,
            'recentTransactions' => $recentTransactions,
            'expenseByCategory' => $expenseByCategory,
            'categories' => $categories,
            'period' => $period,
            'trends' => [
                'income' => $incomeTrend,
                'expenses' => $expenseTrend,
            ],
        ]);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getPeriodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getPreviousPeriodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
        };
    }

    private function calculateTrend(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return (float) round((($current - $previous) / $previous) * 100, 1);
    }
}
