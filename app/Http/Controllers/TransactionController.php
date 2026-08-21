<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Transaction::forUser($request->user()->id)
            ->with('category')
            ->latestFirst();

        if ($request->filled('search')) {
            $query->where('description', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('type') && in_array($request->input('type'), ['income', 'expense'])) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category_id')) {
            $query->forCategory((int) $request->input('category_id'));
        }

        if ($request->filled('from')) {
            $query->where('transaction_date', '>=', Carbon::parse($request->input('from')));
        }

        if ($request->filled('to')) {
            $query->where('transaction_date', '<=', Carbon::parse($request->input('to')));
        }

        $transactions = $query->paginate(15)->withQueryString();

        $categories = Category::forUser($request->user()->id)
            ->ordered()
            ->get();

        return Inertia::render('Transactions', [
            'transactions' => $transactions,
            'categories' => $categories,
            'filters' => [
                'search' => $request->input('search', ''),
                'type' => $request->input('type', ''),
                'category_id' => $request->input('category_id', ''),
                'from' => $request->input('from', ''),
                'to' => $request->input('to', ''),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::where('id', $validated['category_id'])
            ->where(function ($q) use ($request) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $request->user()->id);
            })
            ->firstOrFail();

        $request->user()->transactions()->create([
            'category_id' => $category->id,
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'transaction_date' => $validated['transaction_date'],
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'description' => 'nullable|string|max:500',
        ]);

        $category = Category::where('id', $validated['category_id'])
            ->where(function ($q) use ($request) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', $request->user()->id);
            })
            ->firstOrFail();

        $transaction->update([
            'category_id' => $category->id,
            'amount' => $validated['amount'],
            'type' => $validated['type'],
            'description' => $validated['description'] ?? null,
            'transaction_date' => $validated['transaction_date'],
        ]);

        return redirect()->back();
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->back();
    }
}
