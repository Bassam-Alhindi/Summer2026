<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string $icon
 * @property string $color
 * @property string $type
 * @property int $sort_order
 * @property string|null $budget_limit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<Transaction> $transactions
 */
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'type',
        'sort_order',
        'budget_limit',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'budget_limit' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(CategoryBudget::class);
    }

    /**
     * حد الميزانية الخاص بهذا المستخدم. الفئات الافتراضية مشتركة، فالحد
     * لازم يجي من category_budgets مو من عمود الفئة نفسها.
     * تعتمد على relation محمّلة مسبقاً لتفادي N+1.
     */
    public function budgetLimitFor(?int $userId): ?float
    {
        if ($userId === null) {
            return null;
        }

        $budget = $this->relationLoaded('budgets')
            ? $this->budgets->firstWhere('user_id', $userId)
            : $this->budgets()->where('user_id', $userId)->first();

        return $budget ? (float) $budget->budget_limit : null;
    }

    public function isSystemDefault(): bool
    {
        return $this->user_id === null;
    }

    public function scopeForUser($query, ?int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->whereNull('user_id');
            if ($userId !== null) {
                $q->orWhere('user_id', $userId);
            }
        });
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeWithBudgetFor($query, ?int $userId)
    {
        return $query->with(['budgets' => fn ($q) => $q->where('user_id', $userId)]);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
