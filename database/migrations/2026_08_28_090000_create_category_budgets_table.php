<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * حدود الميزانية كانت تُخزَّن على صف الفئة نفسها. الفئات الافتراضية
 * مشتركة بين كل المستخدمين (user_id = NULL)، فأي مستخدم يضبط حداً كان
 * يكتبه على الصف المشترك ويغيّره على الجميع. الحل: جدول لكل مستخدم.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->decimal('budget_limit', 12, 2);
            $table->timestamps();

            $table->unique(['user_id', 'category_id']);
            $table->index('user_id');
        });

        // ننقل الحدود الموجودة على الفئات المملوكة لمستخدم (هذي أصلاً خاصة به)
        DB::table('categories')
            ->whereNotNull('user_id')
            ->whereNotNull('budget_limit')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $now = now();
                $payload = [];
                foreach ($rows as $row) {
                    $payload[] = [
                        'user_id' => $row->user_id,
                        'category_id' => $row->id,
                        'budget_limit' => $row->budget_limit,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                if ($payload) {
                    DB::table('category_budgets')->insertOrIgnore($payload);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_budgets');
    }
};
