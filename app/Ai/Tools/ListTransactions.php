<?php

namespace App\Ai\Tools;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListTransactions implements Tool
{
    public function description(): Stringable|string
    {
        return 'List and search transactions with optional filters. Use this to query transactions before updating or deleting. '
            .'The response includes a `totals` object with exact income, expense and net sums computed over every matching '
            .'transaction - always read totals from there instead of adding up the returned rows.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = Transaction::where('user_id', Auth::id())
            ->with('category');

        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', Carbon::parse($request['date_from']));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', Carbon::parse($request['date_to']));
        }

        if ($request->filled('type')) {
            $query->where('type', $request['type']);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request['category_id']);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request['min_amount']);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request['max_amount']);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request['search'].'%');
        }

        // المجاميع تُحسب على كل الصفوف المطابقة قبل تطبيق الحد، لأن النموذج
        // كان يجمع الصفوف المقصوصة يدوياً فيطلع بأرقام غلط.
        $matchedCount = (clone $query)->count();
        $sumIncome = (float) (clone $query)->where('type', 'income')->sum('amount');
        $sumExpenses = (float) (clone $query)->where('type', 'expense')->sum('amount');

        $limit = min($request['limit'] ?? 20, 100);
        $transactions = $query->latest('transaction_date')->limit($limit)->get();

        return json_encode([
            'count' => $transactions->count(),
            'matched_count' => $matchedCount,
            'truncated' => $matchedCount > $transactions->count(),
            'totals' => [
                'total_income' => round($sumIncome, 2),
                'total_expenses' => round($sumExpenses, 2),
                'net_balance' => round($sumIncome - $sumExpenses, 2),
            ],
            'transactions' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'amount' => $t->amount,
                'category_id' => $t->category_id,
                'category_name' => $t->category?->name,
                'description' => $t->description,
                'transaction_date' => $t->transaction_date->format('Y-m-d'),
            ]),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'date_from' => $schema->string()->description('Start date in YYYY-MM-DD format'),
            'date_to' => $schema->string()->description('End date in YYYY-MM-DD format'),
            'type' => $schema->string()->enum(['income', 'expense'])->description('Transaction type'),
            'category_id' => $schema->integer()->description('Category ID'),
            'min_amount' => $schema->number()->min(0)->description('Minimum amount'),
            'max_amount' => $schema->number()->min(0)->description('Maximum amount'),
            'search' => $schema->string()->description('Search in description'),
            'limit' => $schema->integer()->min(1)->max(100)->description('Number of results (default 20, max 100)'),
        ];
    }
}
