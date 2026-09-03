<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get(route('reports'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_reports(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Reports')
                ->has('expenseByCategory')
                ->has('categoryBreakdown')
                ->has('totalExpenses')
                ->has('dateRange')
                ->has('dateRange.from')
                ->has('dateRange.to')
        );
    }

    public function test_reports_returns_expense_by_category(): void
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

        $now = now();

        Transaction::factory()->forUser($user)->forCategory($foodCategory)->expense()->create([
            'amount' => 100,
            'transaction_date' => $now->format('Y-m-d'),
        ]);
        Transaction::factory()->forUser($user)->forCategory($foodCategory)->expense()->create([
            'amount' => 200,
            'transaction_date' => $now->format('Y-m-d'),
        ]);
        Transaction::factory()->forUser($user)->forCategory($transportCategory)->expense()->create([
            'amount' => 150,
            'transaction_date' => $now->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('expenseByCategory', 2)
                ->where('totalExpenses', 450)
        );
    }

    public function test_reports_calculates_percentages_correctly(): void
    {
        $user = User::factory()->create();
        $foodCategory = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);
        $transportCategory = Category::factory()->forUser($user)->expense()->create(['name' => 'Transport']);

        $now = now();

        Transaction::factory()->forUser($user)->forCategory($foodCategory)->expense()->create([
            'amount' => 300,
            'transaction_date' => $now->format('Y-m-d'),
        ]);
        Transaction::factory()->forUser($user)->forCategory($transportCategory)->expense()->create([
            'amount' => 100,
            'transaction_date' => $now->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('totalExpenses', 400)
                ->has('categoryBreakdown', 2)
                ->where('categoryBreakdown.0.percentage', 75)
                ->where('categoryBreakdown.1.percentage', 25)
        );
    }

    public function test_reports_handles_empty_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('expenseByCategory', 0)
                ->has('categoryBreakdown', 0)
                ->where('totalExpenses', 0)
        );
    }

    public function test_reports_filters_by_date_range(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);

        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 100,
            'transaction_date' => '2026-01-15',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 200,
            'transaction_date' => '2026-06-15',
        ]);
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 300,
            'transaction_date' => '2026-12-15',
        ]);

        $response = $this->actingAs($user)->get(route('reports', [
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));

        $response->assertInertia(
            fn ($page) => $page
                ->where('totalExpenses', 200)
                ->where('dateRange.from', '2026-06-01')
                ->where('dateRange.to', '2026-06-30')
        );
    }

    public function test_reports_only_shows_authenticated_user_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->expense()->create(['name' => 'Food']);

        Transaction::factory()->forUser($otherUser)->forCategory($category)->expense()->create([
            'amount' => 500,
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('expenseByCategory', 0)
                ->where('totalExpenses', 0)
        );
    }

    public function test_reports_default_to_current_salary_cycle(): void
    {
        // Frozen mid-cycle so the expected 27 -> 26 window is unambiguous.
        Carbon::setTestNow(Carbon::parse('2026-09-03 12:00:00'));

        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);

        // First day of the cycle.
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 50,
            'transaction_date' => '2026-08-27',
        ]);
        // Mid cycle.
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 100,
            'transaction_date' => '2026-09-01',
        ]);
        // The day before the cycle opens belongs to the previous cycle.
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 500,
            'transaction_date' => '2026-08-26',
        ]);

        $response = $this->actingAs($user)->get(route('reports'));

        $response->assertInertia(
            fn ($page) => $page
                ->where('totalExpenses', 150)
                ->where('dateRange.from', '2026-08-27')
                ->where('dateRange.to', '2026-09-26')
        );

        Carbon::setTestNow();
    }
}
