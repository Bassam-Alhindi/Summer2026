<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use App\Support\SalaryCycle;
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

        // الميزانية اليومية مربوطة بدورة الراتب (27 -> 26) وما تتأثر بفلتر
        // الأسبوع/الشهر/السنة، فنحسب مجاميعها على نافذة الدورة لحالها.
        [$cycleStart, $cycleEnd] = SalaryCycle::currentRange();

        $cycleIncome = Transaction::forUser($userId)
            ->income()
            ->dateRange($cycleStart, $cycleEnd)
            ->sum('amount');

        $cycleExpenses = Transaction::forUser($userId)
            ->expense()
            ->dateRange($cycleStart, $cycleEnd)
            ->sum('amount');

        // صافي الرصيد تراكمي على كل الوقت (كل الدخل ناقص كل المصروفات) وما
        // يتصفّر مع بداية كل فترة، عشان يعكس رصيد المستخدم الفعلي لا رصيد
        // الشهر الحالي فقط.
        $lifetimeIncome = Transaction::forUser($userId)
            ->income()
            ->sum('amount');

        $lifetimeExpenses = Transaction::forUser($userId)
            ->expense()
            ->sum('amount');

        $netBalance = $lifetimeIncome - $lifetimeExpenses;

        // نسبة الادخار تبقى مربوطة بالفترة المختارة عشان توافق بطاقتي الدخل
        // والمصروف المعروضتين فوقها.
        $periodNetBalance = $totalIncome - $totalExpenses;
        $savingsRate = (int) round(($periodNetBalance / max($totalIncome, 1)) * 100);

        $incomeTrend = $this->calculateTrend($totalIncome, $prevIncome);
        $expenseTrend = $this->calculateTrend($totalExpenses, $prevExpenses);

        $recentTransactions = Transaction::forUser($userId)
            ->with('category')
            ->latestFirst()
            ->limit(10)
            ->get()
            ->map(function (Transaction $t) {
                // التأكد من جلب اسم الفئة أو وضع قيمة افتراضية لتجنب خطأ null
                $categoryName = $t->category?->name ?: 'أخرى';
                $dateObj = $t->transaction_date ? Carbon::parse($t->transaction_date) : $t->created_at;

                return [
                    'id' => $t->id,
                    'description' => $t->description ?: $categoryName,
                    'amount' => (float) $t->amount,
                    'type' => $t->type,
                    'category' => $categoryName,
                    'category_id' => $t->category_id,
                    'date' => $dateObj ? $dateObj->format('Y-m-d') : '',
                ];
            });

        $expenseByCategory = Transaction::forUser($userId)
            ->expense()
            ->dateRange($from, $to)
            ->with(['category.budgets' => fn ($q) => $q->where('user_id', $userId)])
            ->get()
            ->groupBy(fn (Transaction $t) => $t->category?->name ?? 'أخرى')
            ->map(function ($transactions, string $category) use ($userId) {
                $firstCat = $transactions->first()?->category;

                return [
                    'category' => $category,
                    'category_id' => $firstCat?->id,
                    'amount' => (float) $transactions->sum('amount'),
                    'color' => $firstCat?->color ?? '#6b7280',
                    'budget_limit' => $firstCat?->budgetLimitFor($userId),
                ];
            })
            ->values();

        $categories = Category::forUser($userId)
            ->withBudgetFor($userId)
            ->ordered()
            ->get()
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'color' => $c->color,
                'icon' => $c->icon,
                'budget_limit' => $c->budgetLimitFor($userId),
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
            'cycleEndsOn' => $cycleEnd->toDateString(),
            'cycleNetBalance' => (float) ($cycleIncome - $cycleExpenses),
            'remainingDays' => SalaryCycle::remainingDays(),
            'trends' => [
                'income' => $incomeTrend,
                'expenses' => $expenseTrend,
            ],
        ]);
    }

    /**
     * فلتر "الشهر" يمشي على دورة الراتب (27 -> 26) مو على الشهر الميلادي،
     * عشان معاملة يوم 28 تدخل في الدورة الحالية. الأسبوع والسنة يبقون تقويميين.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getPeriodRange(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => SalaryCycle::currentRange($now),
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
            default => SalaryCycle::previousRange($now),
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
