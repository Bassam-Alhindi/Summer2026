<?php

namespace App\Ai\Tools;

use App\Models\Transaction;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class DeleteTransactions implements Tool
{
    public function description(): Stringable|string
    {
        return 'Delete one or multiple transactions by ID. Use this when the user wants to remove transactions. Always use ListTransactions first to get transaction IDs. Never delete all transactions without explicit confirmation from the user.';
    }

    public function handle(Request $request): Stringable|string
    {
        $user = Auth::user();
        $ids = $request['transaction_ids'] ?? [];
        $deleted = [];
        $notFound = [];

        foreach ($ids as $id) {
            $transaction = Transaction::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (! $transaction) {
                $notFound[] = $id;

                continue;
            }

            $transaction->delete();
            $deleted[] = $id;
        }

        return json_encode([
            'success' => true,
            'deleted_count' => count($deleted),
            'deleted_ids' => $deleted,
            'not_found_ids' => $notFound,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'transaction_ids' => $schema->array()
                ->items($schema->integer()->description('Transaction ID'))
                ->required()
                ->description('Array of transaction IDs to delete'),
        ];
    }
}
