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

class UpdateTransactions implements Tool
{
    public function description(): Stringable|string
    {
        return 'Update one or multiple existing transactions. Use this when the user wants to modify transaction details. Always use ListTransactions first to get transaction IDs.';
    }

    public function handle(Request $request): Stringable|string
    {
        $user = Auth::user();
        $updated = [];

        foreach ($request['transactions'] ?? [] as $data) {
            if (! isset($data['id'])) {
                return json_encode([
                    'success' => false,
                    'error' => 'Transaction ID is required for updates',
                    'failed_item' => $data,
                ]);
            }

            $transaction = Transaction::where('id', $data['id'])
                ->where('user_id', $user->id)
                ->first();

            if (! $transaction) {
                return json_encode([
                    'success' => false,
                    'error' => 'Transaction not found or not accessible',
                    'failed_item' => $data,
                ]);
            }

            $updateData = [];

            if (isset($data['amount'])) {
                $validator = Validator::make(['amount' => $data['amount']], [
                    'amount' => 'required|numeric|min:0.01',
                ]);

                if ($validator->fails()) {
                    return json_encode([
                        'success' => false,
                        'error' => 'Invalid amount: '.$validator->errors()->first(),
                        'failed_item' => $data,
                    ]);
                }

                $updateData['amount'] = $data['amount'];
            }

            if (isset($data['type'])) {
                $validator = Validator::make(['type' => $data['type']], [
                    'type' => 'required|in:income,expense',
                ]);

                if ($validator->fails()) {
                    return json_encode([
                        'success' => false,
                        'error' => 'Invalid type: '.$validator->errors()->first(),
                        'failed_item' => $data,
                    ]);
                }

                $updateData['type'] = $data['type'];
            }

            if (isset($data['category_id'])) {
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

                $updateData['category_id'] = $data['category_id'];
            }

            if (isset($data['transaction_date'])) {
                $validator = Validator::make(['transaction_date' => $data['transaction_date']], [
                    'transaction_date' => 'required|date',
                ]);

                if ($validator->fails()) {
                    return json_encode([
                        'success' => false,
                        'error' => 'Invalid date: '.$validator->errors()->first(),
                        'failed_item' => $data,
                    ]);
                }

                $updateData['transaction_date'] = Carbon::parse($data['transaction_date']);
            }

            if (array_key_exists('description', $data)) {
                $validator = Validator::make(['description' => $data['description']], [
                    'description' => 'nullable|string|max:500',
                ]);

                if ($validator->fails()) {
                    return json_encode([
                        'success' => false,
                        'error' => 'Invalid description: '.$validator->errors()->first(),
                        'failed_item' => $data,
                    ]);
                }

                $updateData['description'] = $data['description'];
            }

            if (empty($updateData)) {
                continue;
            }

            $transaction->update($updateData);
            $updated[] = $transaction->id;
        }

        return json_encode([
            'success' => true,
            'count' => count($updated),
            'updated_ids' => $updated,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'transactions' => $schema->array()
                ->items(
                    $schema->object([
                        'id' => $schema->integer()->required()->description('Transaction ID to update'),
                        'amount' => $schema->number()->min(0.01)->description('New transaction amount'),
                        'type' => $schema->string()->enum(['income', 'expense'])->description('New transaction type'),
                        'category_id' => $schema->integer()->description('New category ID'),
                        'transaction_date' => $schema->string()->description('New date in YYYY-MM-DD format'),
                        'description' => $schema->string()->max(500)->description('New description (can be empty string to clear)'),
                    ])
                )
                ->required()
                ->description('Array of transactions to update (only include fields that need to change)'),
        ];
    }
}
