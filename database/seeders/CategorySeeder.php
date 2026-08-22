<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // مصاريف
            ['name' => 'Education',         'color' => '#eab308', 'icon' => 'graduation-cap', 'type' => 'expense'], // أصفر
            ['name' => 'Housing',           'color' => '#3b82f6', 'icon' => 'home',           'type' => 'expense'], // أزرق صريح
            ['name' => 'Entertainment',     'color' => '#a855f7', 'icon' => 'film',           'type' => 'expense'], // بنفسجي
            ['name' => 'Health',            'color' => '#ef4444', 'icon' => 'heart',          'type' => 'expense'], // أحمر
            ['name' => 'Bills',             'color' => '#10b981', 'icon' => 'receipt',        'type' => 'expense'], // أخضر زمردي
            ['name' => 'Shopping',          'color' => '#ec4899', 'icon' => 'shopping-bag',   'type' => 'expense'], // وردي
            ['name' => 'Food & Drinks',     'color' => '#f97316', 'icon' => 'utensils-crossed','type' => 'expense'], // برتقالي
            ['name' => 'Transportation',    'color' => '#06b6d4', 'icon' => 'car',            'type' => 'expense'], // سماوي
            ['name' => 'Other',             'color' => '#6b7280', 'icon' => 'more-horizontal','type' => 'expense'], // رمادي

            // دخل
            ['name' => 'Salary',            'color' => '#22c55e', 'icon' => 'banknote',       'type' => 'income'],  // أخضر زاهي
            ['name' => 'Freelance',         'color' => '#14b8a6', 'icon' => 'laptop',         'type' => 'income'],  // تركوازي
            ['name' => 'Investments',       'color' => '#6366f1', 'icon' => 'trending-up',    'type' => 'income'],  // نيلي
            ['name' => 'Gifts',             'color' => '#f43f5e', 'icon' => 'gift',           'type' => 'income'],  // توتي / وردي غامق
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}