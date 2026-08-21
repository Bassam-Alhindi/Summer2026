<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Housing', 'icon' => 'home', 'color' => '#6366f1', 'type' => 'expense', 'sort_order' => 1],
            ['name' => 'Entertainment', 'icon' => 'film', 'color' => '#3b82f6', 'type' => 'expense', 'sort_order' => 2],
            ['name' => 'Health', 'icon' => 'heart', 'color' => '#ec4899', 'type' => 'expense', 'sort_order' => 3],
            ['name' => 'Education', 'icon' => 'graduation-cap', 'color' => '#14b8a6', 'type' => 'expense', 'sort_order' => 4],
            ['name' => 'Bills', 'icon' => 'receipt', 'color' => '#10b981', 'type' => 'expense', 'sort_order' => 5],
            ['name' => 'Shopping', 'icon' => 'shopping-bag', 'color' => '#8b5cf6', 'type' => 'expense', 'sort_order' => 6],
            ['name' => 'Transportation', 'icon' => 'car', 'color' => '#f59e0b', 'type' => 'expense', 'sort_order' => 7],
            ['name' => 'Food & Drinks', 'icon' => 'utensils-crossed', 'color' => '#ef4444', 'type' => 'expense', 'sort_order' => 8],
            ['name' => 'Other', 'icon' => 'more-horizontal', 'color' => '#6b7280', 'type' => 'expense', 'sort_order' => 9],
            ['name' => 'Salary', 'icon' => 'banknote', 'color' => '#10b981', 'type' => 'income', 'sort_order' => 10],
            ['name' => 'Freelance', 'icon' => 'laptop', 'color' => '#3b82f6', 'type' => 'income', 'sort_order' => 11],
            ['name' => 'Investment', 'icon' => 'trending-up', 'color' => '#8b5cf6', 'type' => 'income', 'sort_order' => 12],
            ['name' => 'Gift', 'icon' => 'gift', 'color' => '#ec4899', 'type' => 'income', 'sort_order' => 13],
            ['name' => 'Other Income', 'icon' => 'circle-dollar-sign', 'color' => '#6b7280', 'type' => 'income', 'sort_order' => 14],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
