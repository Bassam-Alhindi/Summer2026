<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryBudget;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Dashboard')
                ->has('totalIncome')
                ->has('totalExpenses')
                ->has('netBalance')
                ->has('savingsRate')
                ->has('recentTransactions')
                ->has('expenseByCategory')
                ->has('categories')
                ->has('period')
                ->has('remainingDays')
                ->has('trends')
        );
    }

    public function test_dashboard_calculates_totals_correctly(): void
    {
        $user = User::factory()->create();
        $incomeCategory = Category::factory()->forUser($user)->income()->create();
        $expenseCategory = Category::factory()->forUser($user)->expense()->create();

        Transaction::factory()->forUser($user)->forCategory($incomeCategory)->income()->create([
            'amount' => 5000,
            'transaction_date' => now(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($incomeCategory)->income()->create([
            'amount' => 3000,
            'transaction_date' => now(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($expenseCategory)->expense()->create([
            'amount' => 2000,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('totalIncome', 8000)
                ->where('totalExpenses', 2000)
                ->where('netBalance', 6000)
                ->where('savingsRate', 75)
        );
    }

    public function test_dashboard_returns_recent_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);

        Transaction::factory()->forUser($user)->forCategory($category)->count(15)->create([
            'transaction_date' => now(),
            'description' => 'Test transaction',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page->has('recentTransactions', 10)
        );
    }

    public function test_dashboard_returns_expense_by_category(): void
    {
        $user = User::factory()->create();
        $foodCategory = Category::factory()->forUser($user)->expense()->create([
            'name' => 'Food',
            'color' => '#ef4444',
        ]);
        $transportCategory = Category::factory()->forUser($user)->expense()->create([
            'name' => 'Transport',
            'color' => '#f59e0b',
        ]);

        Transaction::factory()->forUser($user)->forCategory($foodCategory)->expense()->create([
            'amount' => 300,
            'transaction_date' => now(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($transportCategory)->expense()->create([
            'amount' => 150,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('expenseByCategory', 2)
                ->where('expenseByCategory.0.category', 'Food')
                ->where('expenseByCategory.0.amount', 300)
                ->where('expenseByCategory.0.color', '#ef4444')
        );
    }

    public function test_dashboard_handles_empty_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('totalIncome', 0)
                ->where('totalExpenses', 0)
                ->where('netBalance', 0)
                ->where('savingsRate', 0)
                ->has('recentTransactions', 0)
                ->has('expenseByCategory', 0)
        );
    }

    public function test_dashboard_period_filtering(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();

        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 100,
            'transaction_date' => now(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 200,
            'transaction_date' => now()->subMonth(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 500,
            'transaction_date' => now()->subMonths(6),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['period' => 'week']));
        $response->assertInertia(fn ($page) => $page->where('period', 'week'));

        $response = $this->actingAs($user)->get(route('dashboard', ['period' => 'month']));
        $response->assertInertia(fn ($page) => $page->where('period', 'month'));

        $response = $this->actingAs($user)->get(route('dashboard', ['period' => 'year']));
        $response->assertInertia(fn ($page) => $page->where('period', 'year'));
    }

    public function test_dashboard_includes_categories(): void
    {
        $user = User::factory()->create();
        $cat1 = Category::factory()->forUser($user)->expense()->create(['name' => 'Food', 'sort_order' => 1]);
        $cat2 = Category::factory()->forUser($user)->income()->create(['name' => 'Salary', 'sort_order' => 2]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('categories', 2)
                ->where('categories.0.name', 'Food')
                ->where('categories.0.type', 'expense')
                ->where('categories.1.name', 'Salary')
                ->where('categories.1.type', 'income')
        );
    }

    public function test_dashboard_includes_budget_limit_in_categories(): void
    {
        $user = User::factory()->create();
        $food = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);
        CategoryBudget::create([
            'user_id' => $user->id,
            'category_id' => $food->id,
            'budget_limit' => 500.00,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('categories.0.budget_limit', fn ($value) => $value == 500)
        );
    }

    public function test_dashboard_includes_budget_limit_in_expense_by_category(): void
    {
        $user = User::factory()->create();
        $foodCategory = Category::factory()->forUser($user)->expense()->create([
            'name' => 'Food',
            'color' => '#ef4444',
        ]);
        CategoryBudget::create([
            'user_id' => $user->id,
            'category_id' => $foodCategory->id,
            'budget_limit' => 1000.00,
        ]);

        Transaction::factory()->forUser($user)->forCategory($foodCategory)->expense()->create([
            'amount' => 850,
            'transaction_date' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('expenseByCategory.0.budget_limit', fn ($value) => $value == 1000)
                ->where('expenseByCategory.0.amount', fn ($value) => $value == 850)
        );
    }

    public function test_dashboard_net_balance_is_cumulative_across_all_time(): void
    {
        $user = User::factory()->create();
        $incomeCategory = Category::factory()->forUser($user)->income()->create();
        $expenseCategory = Category::factory()->forUser($user)->expense()->create();

        // Current period transactions.
        Transaction::factory()->forUser($user)->forCategory($incomeCategory)->income()->create([
            'amount' => 5000,
            'transaction_date' => now(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($expenseCategory)->expense()->create([
            'amount' => 1500,
            'transaction_date' => now(),
        ]);

        // Previous month transactions: outside the current period, but still part
        // of the lifetime balance.
        Transaction::factory()->forUser($user)->forCategory($incomeCategory)->income()->create([
            'amount' => 400,
            'transaction_date' => now()->subMonth(),
        ]);
        Transaction::factory()->forUser($user)->forCategory($expenseCategory)->expense()->create([
            'amount' => 800,
            'transaction_date' => now()->subMonth(),
        ]);

        // The income and expense cards stay scoped to the selected period, while
        // the net balance card is the all-time total and must not reset monthly.
        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('totalIncome', 5000)
                ->where('totalExpenses', 1500)
                ->where('netBalance', 3100)
                ->where('savingsRate', 70)
        );

        // The cumulative balance is the same whichever period filter is active.
        foreach (['week', 'month', 'year'] as $period) {
            $props = $this->actingAs($user)
                ->get(route('dashboard', ['period' => $period]))
                ->viewData('page')['props'];

            $this->assertEqualsWithDelta(
                3100,
                $props['netBalance'],
                0.001,
                "netBalance must be the all-time total for period [{$period}]",
            );
        }
    }

    public function test_dashboard_returns_remaining_days(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('remainingDays')
                ->where('remainingDays', fn ($value) => $value >= 1)
        );
    }
}
