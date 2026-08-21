<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransactionModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create([
            'amount' => 350.50,
            'type' => 'expense',
            'description' => 'Groceries',
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 350.50,
            'type' => 'expense',
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->forUser($user)->create();

        $this->assertTrue($transaction->user->is($user));
    }

    #[Test]
    public function it_belongs_to_a_category(): void
    {
        $category = Category::factory()->create();
        $transaction = Transaction::factory()->forCategory($category)->create();

        $this->assertTrue($transaction->category->is($category));
    }

    #[Test]
    public function it_identifies_income_and_expense(): void
    {
        $income = Transaction::factory()->income()->create();
        $expense = Transaction::factory()->expense()->create();

        $this->assertTrue($income->isIncome());
        $this->assertFalse($income->isExpense());
        $this->assertTrue($expense->isExpense());
        $this->assertFalse($expense->isIncome());
    }

    #[Test]
    public function it_casts_amount_as_decimal(): void
    {
        $transaction = Transaction::factory()->create(['amount' => 1234.56]);

        $this->assertSame('1234.56', $transaction->amount);
    }

    #[Test]
    public function it_casts_transaction_date_as_date(): void
    {
        $transaction = Transaction::factory()->create(['transaction_date' => '2026-08-15']);

        $this->assertInstanceOf(CarbonInterface::class, $transaction->transaction_date);
        $this->assertEquals('2026-08-15', $transaction->transaction_date->format('Y-m-d'));
    }

    #[Test]
    public function it_supports_soft_deletes(): void
    {
        $transaction = Transaction::factory()->create();
        $transaction->delete();

        $this->assertSoftDeleted('transactions', ['id' => $transaction->id]);
        $this->assertNotNull(Transaction::withTrashed()->find($transaction->id)->deleted_at);
    }

    #[Test]
    public function it_scopes_by_type(): void
    {
        Transaction::factory()->count(3)->income()->create();
        Transaction::factory()->count(5)->expense()->create();

        $this->assertCount(3, Transaction::income()->get());
        $this->assertCount(5, Transaction::expense()->get());
    }

    #[Test]
    public function it_scopes_for_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        Transaction::factory()->count(3)->forUser($user1)->create();
        Transaction::factory()->count(2)->forUser($user2)->create();

        $this->assertCount(3, Transaction::forUser($user1->id)->get());
        $this->assertCount(2, Transaction::forUser($user2->id)->get());
    }

    #[Test]
    public function it_scopes_by_date_range(): void
    {
        Transaction::factory()->create(['transaction_date' => '2026-01-01']);
        Transaction::factory()->create(['transaction_date' => '2026-06-15']);
        Transaction::factory()->create(['transaction_date' => '2026-12-31']);

        $results = Transaction::dateRange(
            Carbon::parse('2026-01-01'),
            Carbon::parse('2026-06-30')
        )->get();

        $this->assertCount(2, $results);
    }

    #[Test]
    public function it_scopes_by_category(): void
    {
        $food = Category::factory()->create();
        $transport = Category::factory()->create();
        Transaction::factory()->count(3)->forCategory($food)->create();
        Transaction::factory()->count(2)->forCategory($transport)->create();

        $this->assertCount(3, Transaction::forCategory($food->id)->get());
    }

    #[Test]
    public function it_orders_latest_first(): void
    {
        Transaction::factory()->create(['transaction_date' => '2026-01-01']);
        Transaction::factory()->create(['transaction_date' => '2026-08-20']);
        Transaction::factory()->create(['transaction_date' => '2026-03-15']);

        $ordered = Transaction::latestFirst()->pluck('transaction_date')->map->format('Y-m-d')->toArray();

        $this->assertEquals(['2026-08-20', '2026-03-15', '2026-01-01'], $ordered);
    }

    #[Test]
    public function it_restricts_category_deletion_when_transactions_exist(): void
    {
        $category = Category::factory()->create();
        Transaction::factory()->forCategory($category)->create();

        $this->expectException(QueryException::class);
        $category->delete();
    }

    #[Test]
    public function it_cascades_delete_when_user_is_deleted(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->count(3)->forUser($user)->create();

        $user->delete();

        $this->assertDatabaseCount('transactions', 0);
    }
}
