<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_transactions(): void
    {
        $response = $this->get(route('transactions.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_transactions(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        Transaction::factory()->forUser($user)->forCategory($category)->count(3)->create();

        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();
        Transaction::factory()->forUser($otherUser)->forCategory($otherCategory)->create();

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Transactions')
                ->has('transactions.data', 3)
        );
    }

    public function test_transactions_are_filtered_by_type(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();
        $incomeCategory = Category::factory()->forUser($user)->income()->create();

        Transaction::factory()->forUser($user)->forCategory($category)->expense()->count(3)->create();
        Transaction::factory()->forUser($user)->forCategory($incomeCategory)->income()->count(2)->create();

        $response = $this->actingAs($user)->get(route('transactions.index', ['type' => 'expense']));

        $response->assertInertia(
            fn ($page) => $page->has('transactions.data', 3)
        );
    }

    public function test_transactions_are_filtered_by_category(): void
    {
        $user = User::factory()->create();
        $foodCategory = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);
        $transportCategory = Category::factory()->forUser($user)->expense()->create(['name' => 'Transport']);

        Transaction::factory()->forUser($user)->forCategory($foodCategory)->count(3)->create();
        Transaction::factory()->forUser($user)->forCategory($transportCategory)->count(2)->create();

        $response = $this->actingAs($user)->get(route('transactions.index', ['category_id' => $foodCategory->id]));

        $response->assertInertia(
            fn ($page) => $page->has('transactions.data', 3)
        );
    }

    public function test_transactions_are_searched_by_description(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create(['description' => 'Grocery shopping']);
        Transaction::factory()->forUser($user)->forCategory($category)->create(['description' => 'Uber ride']);
        Transaction::factory()->forUser($user)->forCategory($category)->create(['description' => 'Monthly salary']);

        $response = $this->actingAs($user)->get(route('transactions.index', ['search' => 'grocery']));

        $response->assertInertia(
            fn ($page) => $page->has('transactions.data', 1)
        );
    }

    public function test_user_can_create_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 150.50,
            'type' => 'expense',
            'category_id' => $category->id,
            'transaction_date' => '2026-08-20',
            'description' => 'Grocery shopping',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 150.50,
            'type' => 'expense',
            'description' => 'Grocery shopping',
        ]);
    }

    public function test_user_cannot_create_transaction_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), []);

        $response->assertSessionHasErrors(['amount', 'type', 'category_id', 'transaction_date']);

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => -10,
            'type' => 'invalid',
            'category_id' => 99999,
            'transaction_date' => 'not-a-date',
            'description' => str_repeat('a', 501),
        ]);

        $response->assertSessionHasErrors(['amount', 'type', 'category_id', 'transaction_date', 'description']);
    }

    public function test_user_cannot_use_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 100,
            'type' => 'expense',
            'category_id' => $otherCategory->id,
            'transaction_date' => '2026-08-20',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $user->id,
            'category_id' => $otherCategory->id,
        ]);
    }

    public function test_user_can_use_system_default_category(): void
    {
        $user = User::factory()->create();
        $systemCategory = Category::factory()->systemDefault()->expense()->create();

        $response = $this->actingAs($user)->post(route('transactions.store'), [
            'amount' => 100,
            'type' => 'expense',
            'category_id' => $systemCategory->id,
            'transaction_date' => '2026-08-20',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'category_id' => $systemCategory->id,
        ]);
    }

    public function test_user_can_update_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create();

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'amount' => 200,
            'type' => 'expense',
            'category_id' => $category->id,
            'transaction_date' => '2026-08-21',
            'description' => 'Updated description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 200,
            'description' => 'Updated description',
        ]);
    }

    public function test_user_cannot_update_other_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();
        $transaction = Transaction::factory()->forUser($otherUser)->forCategory($otherCategory)->create();

        $category = Category::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'amount' => 200,
            'type' => 'expense',
            'category_id' => $category->id,
            'transaction_date' => '2026-08-21',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
            'amount' => 200,
        ]);
    }

    public function test_user_can_delete_own_transaction(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create();

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect();
        $this->assertSoftDeleted('transactions', [
            'id' => $transaction->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_transaction(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();
        $transaction = Transaction::factory()->forUser($otherUser)->forCategory($otherCategory)->create();

        $response = $this->actingAs($user)->delete(route('transactions.destroy', $transaction));

        $response->assertForbidden();
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'deleted_at' => null,
        ]);
    }

    public function test_transactions_are_paginated(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();
        Transaction::factory()->forUser($user)->forCategory($category)->count(20)->create();

        $response = $this->actingAs($user)->get(route('transactions.index'));

        $response->assertInertia(
            fn ($page) => $page
                ->has('transactions.data', 15)
                ->has('transactions.links')
                ->where('transactions.current_page', 1)
                ->where('transactions.last_page', 2)
        );
    }

    public function test_user_cannot_update_transaction_with_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();
        $otherCategory = Category::factory()->forUser($otherUser)->create();
        $transaction = Transaction::factory()->forUser($user)->forCategory($category)->create();

        $response = $this->actingAs($user)->put(route('transactions.update', $transaction), [
            'amount' => 200,
            'type' => 'expense',
            'category_id' => $otherCategory->id,
            'transaction_date' => '2026-08-21',
        ]);

        $response->assertStatus(404);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category_id' => $category->id,
            'amount' => $transaction->amount,
        ]);
    }

    public function test_transactions_are_filtered_by_date_range(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        Transaction::factory()->forUser($user)->forCategory($category)->create(['transaction_date' => '2026-01-15']);
        Transaction::factory()->forUser($user)->forCategory($category)->create(['transaction_date' => '2026-06-15']);
        Transaction::factory()->forUser($user)->forCategory($category)->create(['transaction_date' => '2026-12-15']);

        $response = $this->actingAs($user)->get(route('transactions.index', [
            'from' => '2026-06-01',
            'to' => '2026-06-30',
        ]));

        $response->assertInertia(
            fn ($page) => $page->has('transactions.data', 1)
        );
    }
}
