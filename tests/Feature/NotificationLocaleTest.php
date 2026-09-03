<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationLocaleTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function transactionPayload(Category $category): array
    {
        return [
            'amount' => 25,
            'type' => 'expense',
            'category_id' => $category->id,
            'transaction_date' => now()->toDateString(),
            'description' => null,
        ];
    }

    public function test_toast_is_arabic_when_no_locale_cookie_is_set(): void
    {
        // الافتراضي عربي؛ الزائر الجديد ما عنده كوكي لغة بعد.
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();

        $this->actingAs($user)
            ->post(route('transactions.store'), $this->transactionPayload($category));

        $this->assertSame(
            __('messages.transaction_created', [], 'ar'),
            session('toast')['message'],
        );
    }

    public function test_toast_follows_the_locale_cookie(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create();

        $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'en')
            ->post(route('transactions.store'), $this->transactionPayload($category));

        $this->assertSame(
            __('messages.transaction_created', [], 'en'),
            session('toast')['message'],
        );

        $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'ar')
            ->post(route('transactions.store'), $this->transactionPayload($category));

        $this->assertSame(
            __('messages.transaction_created', [], 'ar'),
            session('toast')['message'],
        );
    }

    public function test_budget_warning_is_localized(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);
        $category->budgets()->create(['user_id' => $user->id, 'budget_limit' => 10]);

        $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'ar')
            ->post(route('transactions.store'), $this->transactionPayload($category));

        $message = session('toast')['message'];

        $this->assertStringContainsString('تنبيه الميزانية', $message);
        $this->assertStringContainsString('طعام ومشروبات', $message);
    }

    public function test_validation_message_is_localized(): void
    {
        $user = User::factory()->create();
        Category::factory()->forUser($user)->expense()->create(['name' => 'Food']);

        $response = $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'ar')
            ->from(route('categories.index'))
            ->post(route('categories.store'), [
                'name' => 'Food',
                'type' => 'expense',
            ]);

        $response->assertSessionHasErrors([
            'name' => __('messages.category_exists', [], 'ar'),
        ]);
    }

    public function test_settings_toast_reaches_the_shared_flash_prop(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'ar')
            ->patch(route('profile.update'), [
                'name' => 'اسم جديد',
                'email' => $user->email,
            ]);

        $this->actingAs($user)
            ->withUnencryptedCookie('locale', 'ar')
            ->get(route('profile.edit'))
            ->assertInertia(
                fn ($page) => $page->where('flash.toast.message', __('messages.profile_updated', [], 'ar'))
            );
    }
}
