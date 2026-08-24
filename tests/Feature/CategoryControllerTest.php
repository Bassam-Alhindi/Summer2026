<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get(route('categories.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_categories(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories')
        );
    }

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'My Category',
            'type' => 'expense',
            'color' => '#ff0000',
            'icon' => 'home',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'My Category',
            'type' => 'expense',
            'color' => '#ff0000',
            'icon' => 'home',
            'user_id' => $user->id,
        ]);
    }

    public function test_user_cannot_create_category_with_invalid_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => '',
            'type' => 'invalid',
        ]);

        $response->assertSessionHasErrors(['name', 'type']);
    }

    public function test_user_can_update_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Updated Name',
            'type' => 'income',
            'color' => '#00ff00',
            'icon' => 'heart',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Name',
            'type' => 'income',
        ]);
    }

    public function test_user_cannot_update_system_default_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->systemDefault()->create();

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Hacked',
            'type' => 'expense',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => $category->name,
            'type' => $category->type,
        ]);
    }

    public function test_user_can_update_system_default_budget_limit(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->systemDefault()->expense()->create();

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'budget_limit' => 750,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'budget_limit' => 750,
        ]);
    }

    public function test_user_cannot_update_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => 'Hacked',
            'type' => 'expense',
        ]);

        $response->assertForbidden();
    }

    public function test_user_can_delete_own_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertRedirect();
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_cannot_delete_system_default_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->systemDefault()->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_category(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $category = Category::factory()->forUser($otherUser)->create();

        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        $response->assertForbidden();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_system_default_categories_are_included_in_index(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(3)->systemDefault()->create();
        Category::factory()->count(2)->forUser($user)->create();

        $response = $this->actingAs($user)->get(route('categories.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Categories/Index')
            ->has('categories', 5)
        );
    }

    public function test_user_can_create_category_with_budget_limit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Groceries',
            'type' => 'expense',
            'color' => '#ff0000',
            'icon' => 'home',
            'budget_limit' => 500.50,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'name' => 'Groceries',
            'user_id' => $user->id,
            'budget_limit' => 500.50,
        ]);
    }

    public function test_user_can_update_category_budget_limit(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create(['budget_limit' => 200]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => $category->name,
            'type' => $category->type,
            'color' => $category->color,
            'icon' => $category->icon,
            'budget_limit' => 800.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'budget_limit' => 800.00,
        ]);
    }

    public function test_budget_limit_can_be_null(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create(['budget_limit' => 500]);

        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => $category->name,
            'type' => $category->type,
            'color' => $category->color,
            'icon' => $category->icon,
            'budget_limit' => null,
        ]);

        $response->assertRedirect();
        $category->refresh();
        $this->assertNull($category->budget_limit);
    }

    public function test_budget_limit_rejects_negative_values(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'Bad Budget',
            'type' => 'expense',
            'color' => '#ff0000',
            'budget_limit' => -100,
        ]);

        $response->assertSessionHasErrors('budget_limit');
    }
}
