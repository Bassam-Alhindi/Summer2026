<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryModelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_create_a_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create([
            'name' => 'Food & Drinks',
            'type' => 'expense',
        ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Food & Drinks',
            'type' => 'expense',
            'user_id' => $user->id,
        ]);
    }

    #[Test]
    public function it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->create();

        $this->assertTrue($category->user->is($user));
    }

    #[Test]
    public function it_can_be_a_system_default(): void
    {
        $category = Category::factory()->systemDefault()->create();

        $this->assertNull($category->user_id);
        $this->assertTrue($category->isSystemDefault());
    }

    #[Test]
    public function it_has_many_transactions(): void
    {
        $category = Category::factory()->create();
        $transactions = Transaction::factory()->count(3)->forCategory($category)->create();

        $this->assertCount(3, $category->transactions);
    }

    #[Test]
    public function it_scopes_by_type(): void
    {
        Category::factory()->count(3)->expense()->create();
        Category::factory()->count(2)->income()->create();

        $this->assertCount(3, Category::expense()->get());
        $this->assertCount(2, Category::income()->get());
    }

    #[Test]
    public function it_scopes_for_user_including_system_defaults(): void
    {
        $user = User::factory()->create();
        Category::factory()->count(2)->systemDefault()->create();
        Category::factory()->count(3)->forUser($user)->create();

        $categories = Category::forUser($user->id)->get();

        $this->assertCount(5, $categories);
    }

    #[Test]
    public function it_orders_by_sort_order_then_name(): void
    {
        Category::factory()->create(['name' => 'Zebra', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Apple', 'sort_order' => 1]);
        Category::factory()->create(['name' => 'Mango', 'sort_order' => 2]);

        $ordered = Category::ordered()->pluck('name')->toArray();

        $this->assertEquals(['Apple', 'Zebra', 'Mango'], $ordered);
    }

    #[Test]
    public function it_cascades_delete_when_user_is_deleted(): void
    {
        $user = User::factory()->create();
        Category::factory()->forUser($user)->create();

        $user->delete();

        $this->assertDatabaseCount('categories', 0);
    }
}
