<?php

namespace Tests\Feature;

use App\Ai\Agents\FinanceAssistant;
use App\Ai\Tools\ListTransactions;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

class AssistantContextTest extends TestCase
{
    use RefreshDatabase;

    private function seedLedger(User $user): void
    {
        $income = Category::factory()->forUser($user)->income()->create();
        $expense = Category::factory()->forUser($user)->expense()->create();

        foreach ([600, 15] as $amount) {
            Transaction::factory()->forUser($user)->forCategory($income)->income()->create([
                'amount' => $amount,
                'transaction_date' => now(),
            ]);
        }

        foreach ([500, 20] as $amount) {
            Transaction::factory()->forUser($user)->forCategory($expense)->expense()->create([
                'amount' => $amount,
                'transaction_date' => now(),
            ]);
        }
    }

    public function test_prompt_contains_real_totals_from_the_database(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);

        $prompt = (string) (new FinanceAssistant(user: $user))->instructions();

        $this->assertStringContainsString('income 615 SAR', $prompt);
        $this->assertStringContainsString('expenses 520 SAR', $prompt);
        $this->assertStringContainsString('net balance 95 SAR', $prompt);
    }

    public function test_prompt_carries_the_strict_grounding_rules(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);

        $prompt = (string) (new FinanceAssistant(user: $user))->instructions();

        $this->assertStringContainsString('You are a strict financial assistant.', $prompt);
        $this->assertStringContainsString(
            'NEVER estimate, calculate'.'
'.'outside the data, or hallucinate numbers.',
            $prompt,
        );
        $this->assertStringContainsString('THE ONLY SOURCE OF TRUTH', $prompt);
        $this->assertStringContainsString('Never estimate, approximate, extrapolate', $prompt);
    }

    public function test_prompt_enforces_a_single_scope_per_answer(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);

        $prompt = (string) (new FinanceAssistant(user: $user))->instructions();

        $this->assertStringContainsString('ONE SCOPE PER ANSWER', $prompt);
        $this->assertStringContainsString('DEFAULT SCOPE', $prompt);
        $this->assertStringContainsString(
            'Never place a second number in parentheses',
            $prompt,
        );
        // The all-time block must be explicitly gated behind the user asking for it.
        $this->assertStringContainsString(
            'ONLY when the user explicitly says "all time"',
            $prompt,
        );
    }

    public function test_prompt_enforces_short_direct_replies(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);

        $prompt = (string) (new FinanceAssistant(user: $user))->instructions();

        $this->assertStringContainsString('SHORT AND DIRECT', $prompt);
        $this->assertStringContainsString('ONE sentence.', $prompt);
        $this->assertStringContainsString('Ban filler openers and closers', $prompt);
        $this->assertStringContainsString('No unsolicited advice', $prompt);
    }

    public function test_prompt_excludes_soft_deleted_transactions(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);

        $category = Category::factory()->forUser($user)->expense()->create();
        Transaction::factory()->forUser($user)->forCategory($category)->expense()->create([
            'amount' => 999,
            'transaction_date' => now(),
        ])->delete();

        $prompt = (string) (new FinanceAssistant(user: $user))->instructions();

        $this->assertStringContainsString('expenses 520 SAR', $prompt);
        $this->assertStringNotContainsString('999', $prompt);
    }

    public function test_list_transactions_totals_cover_every_match_even_when_rows_are_truncated(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);
        Auth::login($user);

        $result = json_decode((new ListTransactions)->handle(new Request(['limit' => 1])), true);

        // Only one row comes back, but the totals still describe all four.
        $this->assertSame(1, $result['count']);
        $this->assertSame(4, $result['matched_count']);
        $this->assertTrue($result['truncated']);
        $this->assertEqualsWithDelta(615.0, $result['totals']['total_income'], 0.001);
        $this->assertEqualsWithDelta(520.0, $result['totals']['total_expenses'], 0.001);
        $this->assertEqualsWithDelta(95.0, $result['totals']['net_balance'], 0.001);
    }

    public function test_list_transactions_totals_respect_filters(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);
        Auth::login($user);

        $result = json_decode((new ListTransactions)->handle(new Request(['type' => 'income'])), true);

        $this->assertEqualsWithDelta(615.0, $result['totals']['total_income'], 0.001);
        $this->assertEqualsWithDelta(0.0, $result['totals']['total_expenses'], 0.001);
        $this->assertFalse($result['truncated']);
    }

    public function test_list_transactions_totals_are_scoped_to_the_current_user(): void
    {
        $user = User::factory()->create();
        $this->seedLedger($user);

        $other = User::factory()->create();
        $this->seedLedger($other);

        Auth::login($user);
        $result = json_decode((new ListTransactions)->handle(new Request([])), true);

        $this->assertSame(4, $result['matched_count']);
        $this->assertEqualsWithDelta(615.0, $result['totals']['total_income'], 0.001);
    }
}
