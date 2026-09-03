<?php

namespace App\Ai\Tools;

use App\Models\Category;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CreateTransactions implements Tool
{
    public function description(): Stringable|string
    {
        return 'Create one or multiple transactions at once. Use this when the user wants to add expenses or income.';
    }

    public function handle(Request $request): Stringable|string
    {
        $user = Auth::user();
        $created = [];

        foreach ($request['transactions'] ?? [] as $data) {
            $validator = Validator::make($data, [
                'amount' => 'required|numeric|min:0.01',
                'type' => 'required|in:income,expense',
                'category_id' => 'required|exists:categories,id',
                'transaction_date' => 'required|date',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return json_encode([
                    'success' => false,
                    'error' => $validator->errors()->first(),
                    'failed_item' => $data,
                ]);
            }

            $category = Category::where('id', $data['category_id'])
                ->where(function ($query) use ($user) {
                    $query->whereNull('user_id')->orWhere('user_id', $user->id);
                })
                ->first();

            if (! $category) {
                return json_encode([
                    'success' => false,
                    'error' => 'Category not found or not accessible',
                    'failed_item' => $data,
                ]);
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'category_id' => $data['category_id'],
                'transaction_date' => Carbon::parse($data['transaction_date']),
                'description' => $data['description'] ?? null,
            ]);

            $created[] = $transaction->id;
        }

        return json_encode([
            'success' => true,
            'count' => count($created),
            'created_ids' => $created,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'transactions' => $schema->array()
                ->items(
                    $schema->object([
                        'amount' => $schema->number()->min(0.01)->required()->description('Transaction amount'),
                        'type' => $schema->string()->enum(['income', 'expense'])->required()->description('Transaction type'),
                        'category_id' => $schema->integer()->required()->description('Category ID'),
                        'transaction_date' => $schema->string()->required()->description('Date in YYYY-MM-DD format'),
                        'description' => $schema->string()->max(500)->description('Optional description'),
                    ])
                )
                ->required()
                ->description('Array of transactions to create'),
        ];
    }
}
