<script module lang="ts">
    import { index as categoriesIndex } from '@/routes/categories';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Categories',
                href: categoriesIndex.url(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Dialog,
        DialogContent,
        DialogHeader,
        DialogTitle,
        DialogFooter,
    } from '@/components/ui/dialog';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { t } from '@/lib/i18n.svelte';
    import { router } from '@inertiajs/svelte';
    import Home from 'lucide-svelte/icons/home';
    import Film from 'lucide-svelte/icons/film';
    import Heart from 'lucide-svelte/icons/heart';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Receipt from 'lucide-svelte/icons/receipt';
    import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
    import Car from 'lucide-svelte/icons/car';
    import UtensilsCrossed from 'lucide-svelte/icons/utensils-crossed';
    import MoreHorizontal from 'lucide-svelte/icons/more-horizontal';
    import Banknote from 'lucide-svelte/icons/banknote';
    import Laptop from 'lucide-svelte/icons/laptop';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Gift from 'lucide-svelte/icons/gift';
    import CircleDollarSign from 'lucide-svelte/icons/circle-dollar-sign';
    import Plane from 'lucide-svelte/icons/plane';
    import Gamepad2 from 'lucide-svelte/icons/gamepad-2';
    import Dumbbell from 'lucide-svelte/icons/dumbbell';
    import PawPrint from 'lucide-svelte/icons/paw-print';
    import Baby from 'lucide-svelte/icons/baby';
    import Smartphone from 'lucide-svelte/icons/smartphone';
    import Wifi from 'lucide-svelte/icons/wifi';
    import Wrench from 'lucide-svelte/icons/wrench';
    import Pencil from 'lucide-svelte/icons/pencil';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import Plus from 'lucide-svelte/icons/plus';
    import Ban from 'lucide-svelte/icons/ban';
    import type { Component } from 'svelte';
    import { fly, scale, fade } from 'svelte/transition';
    import { flip } from 'svelte/animate';
    import { store as categoriesStore, update as categoriesUpdate, destroy as categoriesDestroy } from '@/routes/categories';

    type Category = {
        id: number;
        user_id: number | null;
        name: string;
        icon: string | null;
        color: string;
        type: 'income' | 'expense';
        sort_order: number;
        budget_limit: string | null;
    };

    let { categories } = $props<{ categories: Category[] }>();

    const iconMap: Record<string, Component> = {
        home: Home,
        film: Film,
        heart: Heart,
        'graduation-cap': GraduationCap,
        receipt: Receipt,
        'shopping-bag': ShoppingBag,
        car: Car,
        'utensils-crossed': UtensilsCrossed,
        'more-horizontal': MoreHorizontal,
        banknote: Banknote,
        laptop: Laptop,
        'trending-up': TrendingUp,
        gift: Gift,
        'circle-dollar-sign': CircleDollarSign,
        plane: Plane,
        'gamepad-2': Gamepad2,
        dumbbell: Dumbbell,
        'paw-print': PawPrint,
        baby: Baby,
        smartphone: Smartphone,
        wifi: Wifi,
        wrench: Wrench,
    };

    const iconOptions = Object.keys(iconMap);

    const DISTINCT_PALETTE = [
        '#eab308', '#3b82f6', '#a855f7', '#ef4444', '#10b981',
        '#ec4899', '#f97316', '#06b6d4', '#22c55e', '#6366f1',
        '#14b8a6', '#f43f5e', '#84cc16', '#d946ef', '#0284c7'
    ];

    // قاموس التجميع للترجمة والتوحيد لكافة الفئات الرئيسية
    const CATEGORY_TRANSLATIONS: Record<string, { key: string; ar: string; en: string; defaultColor: string; defaultIcon: string }> = {
        // Housing / سكن
        'housing': { key: 'housing', ar: 'سكن', en: 'Housing', defaultColor: '#3b82f6', defaultIcon: 'home' },
        'home': { key: 'housing', ar: 'سكن', en: 'Housing', defaultColor: '#3b82f6', defaultIcon: 'home' },
        'سكن': { key: 'housing', ar: 'سكن', en: 'Housing', defaultColor: '#3b82f6', defaultIcon: 'home' },
        'سكن': { key: 'housing', ar: 'سكن', en: 'Housing', defaultColor: '#3b82f6', defaultIcon: 'home' },

        // Entertainment / ترفيه
        'entertainment': { key: 'entertainment', ar: 'ترفيه', en: 'Entertainment', defaultColor: '#a855f7', defaultIcon: 'film' },
        'ترفيه': { key: 'entertainment', ar: 'ترفيه', en: 'Entertainment', defaultColor: '#a855f7', defaultIcon: 'film' },

        // Health / الصحة
        'health': { key: 'health', ar: 'الصحة', en: 'Health', defaultColor: '#ef4444', defaultIcon: 'heart' },
        'الصحة': { key: 'health', ar: 'الصحة', en: 'Health', defaultColor: '#ef4444', defaultIcon: 'heart' },

        // Education / تعليم
        'education': { key: 'education', ar: 'تعليم', en: 'Education', defaultColor: '#eab308', defaultIcon: 'graduation-cap' },
        'تعليم': { key: 'education', ar: 'تعليم', en: 'Education', defaultColor: '#eab308', defaultIcon: 'graduation-cap' },

        // Shopping / تسوق
        'shopping': { key: 'shopping', ar: 'تسوق', en: 'Shopping', defaultColor: '#ec4899', defaultIcon: 'shopping-bag' },
        'تسوق': { key: 'shopping', ar: 'تسوق', en: 'Shopping', defaultColor: '#ec4899', defaultIcon: 'shopping-bag' },

        // Bills  / الفواتير
        'bills': { key: 'bills', ar: 'الفواتير', en: 'Bills ', defaultColor: '#10b981', defaultIcon: 'receipt' },
        'Bills ': { key: 'bills', ar: 'الفواتير', en: 'Bills ', defaultColor: '#10b981', defaultIcon: 'receipt' },
        'bills_utilities': { key: 'bills', ar: 'الفواتير', en: 'Bills ', defaultColor: '#10b981', defaultIcon: 'receipt' },
        'الفواتير': { key: 'bills', ar: 'الفواتير', en: 'Bills ', defaultColor: '#10b981', defaultIcon: 'receipt' },
        'الفواتير': { key: 'bills', ar: 'الفواتير', en: 'Bills ', defaultColor: '#10b981', defaultIcon: 'receipt' },

        // Food & Drinks / اكل وشرب
        'food': { key: 'food', ar: 'اكل وشرب', en: 'Food & Drinks', defaultColor: '#f97316', defaultIcon: 'utensils-crossed' },
        'food & drinks': { key: 'food', ar: 'اكل وشرب', en: 'Food & Drinks', defaultColor: '#f97316', defaultIcon: 'utensils-crossed' },
        'food & dining': { key: 'food', ar: 'اكل وشرب', en: 'Food & Drinks', defaultColor: '#f97316', defaultIcon: 'utensils-crossed' },
        'food_drinks': { key: 'food', ar: 'اكل وشرب', en: 'Food & Drinks', defaultColor: '#f97316', defaultIcon: 'utensils-crossed' },
        'الطعام': { key: 'food', ar: 'اكل وشرب', en: 'Food & Drinks', defaultColor: '#f97316', defaultIcon: 'utensils-crossed' },
        'اكل وشرب': { key: 'food', ar: 'اكل وشرب', en: 'Food & Drinks', defaultColor: '#f97316', defaultIcon: 'utensils-crossed' },

        // Transportation / مواصلات
        'transportation': { key: 'transportation', ar: 'مواصلات', en: 'Transportation', defaultColor: '#06b6d4', defaultIcon: 'car' },
        'car': { key: 'transportation', ar: 'مواصلات', en: 'Transportation', defaultColor: '#06b6d4', defaultIcon: 'car' },
        'مواصلات': { key: 'transportation', ar: 'مواصلات', en: 'Transportation', defaultColor: '#06b6d4', defaultIcon: 'car' },

        // Salary / الراتب
        'salary': { key: 'salary', ar: 'الراتب', en: 'Salary', defaultColor: '#22c55e', defaultIcon: 'banknote' },
        'الراتب': { key: 'salary', ar: 'الراتب', en: 'Salary', defaultColor: '#22c55e', defaultIcon: 'banknote' },

        // Freelance / عمل حر
        'freelance': { key: 'freelance', ar: 'عمل حر', en: 'Freelance', defaultColor: '#14b8a6', defaultIcon: 'laptop' },
        'عمل حر': { key: 'freelance', ar: 'عمل حر', en: 'Freelance', defaultColor: '#14b8a6', defaultIcon: 'laptop' },

        // Investments / استثمار
        'investments': { key: 'investments', ar: 'استثمار', en: 'Investments', defaultColor: '#6366f1', defaultIcon: 'trending-up' },
        'investment': { key: 'investments', ar: 'استثمار', en: 'Investments', defaultColor: '#6366f1', defaultIcon: 'trending-up' },
        'استثمار': { key: 'investments', ar: 'استثمار', en: 'Investments', defaultColor: '#6366f1', defaultIcon: 'trending-up' },

        // Gifts / هدايا
        'gifts': { key: 'gifts', ar: 'هدايا', en: 'Gifts', defaultColor: '#f43f5e', defaultIcon: 'gift' },
        'gift': { key: 'gifts', ar: 'هدايا', en: 'Gifts', defaultColor: '#f43f5e', defaultIcon: 'gift' },
        'هدايا': { key: 'gifts', ar: 'هدايا', en: 'Gifts', defaultColor: '#f43f5e', defaultIcon: 'gift' },
        'هدايا': { key: 'gifts', ar: 'هدايا', en: 'Gifts', defaultColor: '#f43f5e', defaultIcon: 'gift' },

        // Other / أخرى
        'other': { key: 'other', ar: 'أخرى', en: 'Other', defaultColor: '#6b7280', defaultIcon: 'more-horizontal' },
        'أخرى': { key: 'other', ar: 'أخرى', en: 'Other', defaultColor: '#6b7280', defaultIcon: 'more-horizontal' },
        'other income': { key: 'other_income', ar: 'دخل آخر', en: 'Other Income', defaultColor: '#6b7280', defaultIcon: 'circle-dollar-sign' },
        'other_income': { key: 'other_income', ar: 'دخل آخر', en: 'Other Income', defaultColor: '#6b7280', defaultIcon: 'circle-dollar-sign' },
        'دخل آخر': { key: 'other_income', ar: 'دخل آخر', en: 'Other Income', defaultColor: '#6b7280', defaultIcon: 'circle-dollar-sign' },
        'other expense': { key: 'other_expense', ar: 'مصروف آخر', en: 'Other Expense', defaultColor: '#6b7280', defaultIcon: 'more-horizontal' },
        'other_expense': { key: 'other_expense', ar: 'مصروف آخر', en: 'Other Expense', defaultColor: '#6b7280', defaultIcon: 'more-horizontal' },
        'مصروف آخر': { key: 'other_expense', ar: 'مصروف آخر', en: 'Other Expense', defaultColor: '#6b7280', defaultIcon: 'more-horizontal' },
    };

    function getCategoryInfo(name: string) {
        const raw = (name || '').trim().toLowerCase();
        return CATEGORY_TRANSLATIONS[raw] ?? null;
    }

    function isArabicUi(): boolean {
        const titleText = t('categories.title');
        return /[\u0600-\u06FF]/.test(titleText);
    }

    function tr(key: string, arFallback: string, enFallback: string): string {
        const translated = t(key);
        if (!translated || translated === key || translated.startsWith('categories.') || translated.startsWith('transactions.')) {
            return isArabicUi() ? arFallback : enFallback;
        }
        return translated;
    }

    function getTranslatedCategoryName(category: Category): string {
        const raw = category.name ? category.name.trim() : '';
        if (!raw) return '';

        const info = getCategoryInfo(raw);
        if (info) {
            return isArabicUi() ? info.ar : info.en;
        }

        return raw;
    }

    function getCategoryColor(category: Category): string {
        const info = getCategoryInfo(category.name);
        if (info) {
            return info.defaultColor;
        }
        return category.color || '#6b7280';
    }

    function getCategoryIcon(category: Category): Component | null {
        const info = getCategoryInfo(category.name);
        const iconName = category.icon || (info ? info.defaultIcon : null);
        if (!iconName) return null;
        return iconMap[iconName] ?? null;
    }

    function hslToHex(h: number, s: number, l: number): string {
        l /= 100;
        const a = (s * Math.min(l, 1 - l)) / 100;
        const f = (n: number) => {
            const k = (n + h / 30) % 12;
            const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * color).toString(16).padStart(2, '0');
        };
        return `#${f(0)}${f(8)}${f(4)}`;
    }

    function generateUniqueColor(): string {
        const usedColors = new Set(categories.map((c) => c.color?.toLowerCase()).filter(Boolean));
        const availableColors = DISTINCT_PALETTE.filter(
            (color) => !usedColors.has(color.toLowerCase())
        );

        if (availableColors.length > 0) {
            return availableColors[Math.floor(Math.random() * availableColors.length)];
        }

        let newColor = '';
        let attempts = 0;
        do {
            const randomHue = Math.floor(Math.random() * 360);
            newColor = hslToHex(randomHue, 65, 55);
            attempts++;
        } while (usedColors.has(newColor.toLowerCase()) && attempts < 50);

        return newColor;
    }

    let activeTab = $state<'expense' | 'income'>('expense');

    // تصفية وحذف الفئات المكررة (مثل Gift و Gifts) حسب المفتاح الموحد
    let filteredCategories = $derived(
        categories
            .filter((c) => c.type === activeTab)
            .filter((category, index, self) => {
                const info = getCategoryInfo(category.name);
                const uniqueKey = info ? info.key : category.name.trim().toLowerCase();
                return (
                    self.findIndex((c) => {
                        const cInfo = getCategoryInfo(c.name);
                        const cKey = cInfo ? cInfo.key : c.name.trim().toLowerCase();
                        return cKey === uniqueKey;
                    }) === index
                );
            })
    );

    let isDialogOpen = $state(false);
    let editingCategory = $state<Category | null>(null);
    let selectedCategoryId = $state<number | null>(null);

    let formName = $state('');
    let formType = $state<'income' | 'expense'>('expense');
    let formColor = $state('#6b7280');
    let formIcon = $state<string | null>(null);
    let formBudgetLimit = $state('');
    let errorMessage = $state<string | null>(null);

    let isEditingSystemDefault = $derived(editingCategory !== null && editingCategory.user_id === null);

    function handleCardClick(category: Category) {
        selectedCategoryId = selectedCategoryId === category.id ? null : category.id;
    }

    function openAddDialog() {
        editingCategory = null;
        formName = '';
        formType = activeTab;
        formColor = generateUniqueColor();
        formIcon = null;
        formBudgetLimit = '';
        errorMessage = null;
        isDialogOpen = true;
    }

    function openEditDialog(category: Category) {
        editingCategory = category;
        formName = category.name;
        formType = category.type;
        formColor = category.color || generateUniqueColor();
        formIcon = category.icon || null;
        formBudgetLimit = category.budget_limit ? String(category.budget_limit) : '';
        errorMessage = null;
        isDialogOpen = true;
    }

    function handleSubmit() {
        errorMessage = null;

        if (isEditingSystemDefault && editingCategory) {
            const data = {
                budget_limit: formBudgetLimit ? Number(formBudgetLimit) : null,
            };

            router.put(categoriesUpdate.url(editingCategory.id), data, {
                preserveScroll: true,
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
            return;
        }

        const inputNameClean = formName.trim().toLowerCase();

        const isDuplicateName = categories.some((c) => {
            if (c.id === editingCategory?.id) return false;
            if (c.type !== formType) return false;

            const inputInfo = getCategoryInfo(formName);
            const inputKey = inputInfo ? inputInfo.key : inputNameClean;

            const cInfo = getCategoryInfo(c.name);
            const cKey = cInfo ? cInfo.key : c.name.trim().toLowerCase();

            return inputKey === cKey;
        });

        if (isDuplicateName) {
            errorMessage = isArabicUi() ? 'عفواً، هذه الفئة موجودة بالفعل!' : 'Category already exists!';
            return;
        }

        let finalColor = formColor;
        const usedColors = new Set(categories.map((c) => c.color?.toLowerCase()).filter(Boolean));
        if (!editingCategory && usedColors.has(finalColor.toLowerCase())) {
            finalColor = generateUniqueColor();
        }

        const data = {
            name: formName.trim(),
            type: formType,
            color: finalColor,
            icon: formIcon,
            budget_limit: formBudgetLimit ? Number(formBudgetLimit) : null,
        };

        const options = {
            preserveScroll: true,
            onError: (errors: any) => {
                if (errors.name) {
                    errorMessage = errors.name;
                }
            },
            onSuccess: () => {
                isDialogOpen = false;
            },
        };

        if (editingCategory) {
            router.put(categoriesUpdate.url(editingCategory.id), data, options);
        } else {
            router.post(categoriesStore.url(), data, options);
        }
    }

    function deleteCategory(category: Category) {
        if (!confirm(t('categories.deleteConfirm'))) {
            return;
        }

        router.delete(categoriesDestroy.url(category.id), {
            preserveScroll: true,
        });
    }
</script>

<AppHead title={t('categories.title')} />

<div class="flex h-full flex-1 flex-col gap-6 overflow-y-auto p-4 pb-24 sm:p-6 lg:pb-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                {t('categories.title')}
            </h1>
            <p class="mt-1 text-xs text-zinc-400 sm:text-sm">
                {tr('categories.subtitle', 'لكل ريال وجهة.. اعطِ أموالك الهوية التي تستحقها', 'Give every unit of your money the identity it deserves')}
            </p>
        </div>
        <Button onclick={openAddDialog} size="sm" class="rounded-xl">
            <Plus class="size-4" />
            {tr('categories.addCategory', 'إضافة فئة', 'Add Category')}
        </Button>
    </div>

    <!-- Segmented Tabs -->
    <div class="flex justify-center">
        <div class="inline-flex rounded-xl bg-zinc-900 p-1 border border-zinc-800">
            <button
                type="button"
                class="px-5 py-1.5 text-xs font-semibold rounded-lg transition-all {activeTab === 'expense' ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-400 hover:text-zinc-200'}"
                onclick={() => activeTab = 'expense'}
            >
                {tr('transactions.expense', 'المصروفات', 'Expenses')}
            </button>
            <button
                type="button"
                class="px-5 py-1.5 text-xs font-semibold rounded-lg transition-all {activeTab === 'income' ? 'bg-zinc-800 text-white shadow-sm' : 'text-zinc-400 hover:text-zinc-200'}"
                onclick={() => activeTab = 'income'}
            >
                {tr('transactions.income', 'الدخل', 'Income')}
            </button>
        </div>
    </div>

    <!-- Grid مع العرض المترجم بدون تكرار وبألوان موحدة -->
    {#key activeTab}
        <div 
            in:fade={{ duration: 150 }}
            class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
        >
            {#each filteredCategories as category (category.id)}
                {@const catColor = getCategoryColor(category)}
                {@const IconComp = getCategoryIcon(category)}
                <div
                    animate:flip={{ duration: 250 }}
                    in:fly={{ y: -15, duration: 200 }}
                    out:scale={{ start: 0.95, duration: 150 }}
                    class="group relative flex cursor-pointer items-center justify-between rounded-xl p-3.5 transition-all duration-300 hover:-translate-y-0.5"
                    style="
                        background: radial-gradient(circle at top right, {catColor}14 0%, rgba(24, 24, 27, 0.65) 75%);
                        border: 1px solid {catColor}28;
                        box-shadow: 0 4px 16px -4px {catColor}18, inset 0 0 10px -2px {catColor}0D;
                    "
                    onclick={() => handleCardClick(category)}
                    role="button"
                    tabindex="0"
                    onkeydown={(e) => { if (e.key === 'Enter') handleCardClick(category); }}
                >
                    <div class="min-w-0 flex-1 pr-2 rtl:pr-0 rtl:pl-2">
                        <p 
                            class="truncate text-base font-bold transition-all duration-300" 
                            style="color: {catColor}; text-shadow: 0 0 12px {catColor}35;"
                        >
                            {getTranslatedCategoryName(category)}
                        </p>
                    </div>

                    <div class="shrink-0">
                        {#if IconComp}
                            <div
                                class="flex size-9 items-center justify-center rounded-lg border bg-zinc-950/80 transition-all duration-300 group-hover:scale-105"
                                style="
                                    color: {catColor};
                                    border-color: {catColor}30;
                                    box-shadow: 0 0 10px -2px {catColor}25;
                                "
                            >
                                <IconComp class="size-4" />
                            </div>
                        {:else}
                            <div 
                                class="flex size-9 items-center justify-center rounded-lg border bg-zinc-950/80"
                                style="border-color: {catColor}30;"
                            >
                                <div 
                                    class="size-2.5 rounded-full" 
                                    style="
                                        background-color: {catColor};
                                        box-shadow: 0 0 8px {catColor}80;
                                    "
                                ></div>
                            </div>
                        {/if}
                    </div>

                    {#if category.user_id !== null || category.user_id === null}
                        <div
                            class="absolute top-2 left-2 flex items-center gap-1 rtl:left-auto rtl:right-2 {selectedCategoryId === category.id ? 'opacity-100' : 'opacity-0 group-hover:opacity-100'} transition-opacity"
                        >
                            <button
                                type="button"
                                class="flex size-6 items-center justify-center rounded bg-zinc-800 text-zinc-300 hover:text-white"
                                onclick={(e) => {
                                    e.stopPropagation();
                                    openEditDialog(category);
                                }}
                            >
                                <Pencil class="size-3" />
                            </button>
                            {#if category.user_id !== null}
                            <button
                                type="button"
                                class="flex size-6 items-center justify-center rounded bg-zinc-800 text-zinc-300 hover:text-red-400"
                                onclick={(e) => {
                                    e.stopPropagation();
                                    deleteCategory(category);
                                }}
                            >
                                <Trash2 class="size-3" />
                            </button>
                            {/if}
                        </div>
                    {/if}
                </div>
            {/each}
        </div>
    {/key}
</div>

<!-- Modal Dialog -->
<Dialog bind:open={isDialogOpen}>
    <DialogContent class="max-w-sm rounded-2xl border border-zinc-800 bg-zinc-950 p-5 shadow-xl">
        <DialogHeader class="pb-2 border-b border-zinc-800/60">
            <DialogTitle class="text-lg font-bold text-white">
                {editingCategory 
                    ? tr('categories.editCategory', 'تعديل الفئة', 'Edit Category') 
                    : tr('categories.addCategory', 'إضافة فئة', 'Add Category')}
            </DialogTitle>
        </DialogHeader>

        <form class="flex flex-col gap-4 pt-3" autocomplete="off" onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
            {#if errorMessage}
                <div class="rounded-lg bg-red-950/40 p-2.5 text-xs font-semibold text-red-400 text-center border border-red-900/50">
                    {errorMessage}
                </div>
            {/if}

            {#if isEditingSystemDefault}
                <div class="rounded-lg bg-zinc-800/40 p-2.5 text-[11px] font-semibold text-zinc-400 text-center border border-zinc-700/40">
                    {tr('categories.systemDefaultHint', 'يمكنك تعديل حد الميزانية فقط للفئات الأساسية', 'You can only modify the budget limit for system categories')}
                </div>
            {/if}

            <!-- اسم الفئة -->
            {#if !isEditingSystemDefault}
            <div class="flex flex-col gap-1.5">
                <Label for="cat-name" class="text-xs font-semibold text-zinc-300">
                    {tr('categories.categoryName', 'اسم الفئة', 'Category Name')}
                </Label>
                <Input 
                    id="cat-name" 
                    type="text" 
                    autocomplete="off"
                    bind:value={formName} 
                    required 
                    placeholder={isArabicUi() ? 'ادخل اسم الفئة...' : 'Category name...'}
                    class="h-10 rounded-xl border-zinc-800 bg-zinc-900/80 px-3 text-sm text-white focus:border-zinc-700"
                />
            </div>

            <!-- نوع الفئة -->
            <div class="flex flex-col gap-1.5">
                <Label class="text-xs font-semibold text-zinc-300">
                    {tr('categories.categoryType', 'نوع الفئة', 'Category Type')}
                </Label>
                <div class="grid grid-cols-2 gap-1.5 p-1 rounded-xl bg-zinc-900 border border-zinc-800/80">
                    <button
                        type="button"
                        class="h-9 text-xs font-semibold rounded-lg transition-all {formType === 'expense' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700/60' : 'text-zinc-400 hover:text-zinc-200'}"
                        onclick={() => formType = 'expense'}
                    >
                        {tr('transactions.expense', 'المصروفات', 'Expenses')}
                    </button>
                    <button
                        type="button"
                        class="h-9 text-xs font-semibold rounded-lg transition-all {formType === 'income' ? 'bg-zinc-800 text-white shadow-sm border border-zinc-700/60' : 'text-zinc-400 hover:text-zinc-200'}"
                        onclick={() => { formType = 'income'; formBudgetLimit = ''; }}
                    >
                        {tr('transactions.income', 'الدخل', 'Income')}
                    </button>
                </div>
            </div>

            <!-- اختيار الأيقونة -->
            <div class="flex flex-col gap-1.5">
                <Label class="text-xs font-semibold text-zinc-300">
                    {tr('categories.categoryIcon', 'الأيقونة', 'Icon')}
                </Label>
                <div class="grid max-h-32 grid-cols-6 gap-1.5 overflow-y-auto rounded-xl border border-zinc-800 bg-zinc-900/40 p-2">
                    <button
                        type="button"
                        class="flex size-8 items-center justify-center rounded-lg transition-colors {formIcon === null ? 'bg-zinc-800 text-white border border-zinc-600' : 'text-zinc-400 hover:text-white'}"
                        onclick={() => formIcon = null}
                    >
                        <Ban class="size-3.5" />
                    </button>

                    {#each iconOptions as iconName (iconName)}
                        {@const IconComp = iconMap[iconName]}
                        <button
                            type="button"
                            class="flex size-8 items-center justify-center rounded-lg transition-colors {formIcon === iconName ? 'bg-zinc-800 text-white border border-zinc-600' : 'text-zinc-400 hover:text-white'}"
                            onclick={() => formIcon = iconName}
                        >
                            <IconComp class="size-3.5" />
                        </button>
                    {/each}
                </div>
            </div>
            {/if}

            <!-- حد الميزانية -->
            {#if formType === 'expense' || isEditingSystemDefault}
            <div class="flex flex-col gap-1.5">
                <Label for="cat-budget" class="text-xs font-semibold text-zinc-300">
                    {tr('categories.budgetLimit', 'حد الميزانية (اختياري)', 'Budget Limit (Optional)')} (<span class="text-xs font-bold text-white">⃁</span>)
                </Label>
                <Input 
                    id="cat-budget" 
                    type="number" 
                    step="0.01" 
                    min="0"
                    bind:value={formBudgetLimit} 
                    placeholder={isArabicUi() ? 'مثال: 500' : 'e.g. 500'}
                    class="h-10 rounded-xl border-zinc-800 bg-zinc-900/80 px-3 text-sm text-white focus:border-zinc-700"
                />
                <p class="text-[11px] text-zinc-500 font-medium">
                    {tr('categories.budgetHint', 'سيتم تنبيهك عند تجاوز 80% من الحد', 'You will be alerted when 80% is reached')}
                </p>
            </div>
            {/if}

            <!-- أزرار الحفظ والإلغاء -->
            <DialogFooter class="mt-2 flex flex-row items-center justify-end gap-2">
                <Button 
                    type="button" 
                    variant="ghost" 
                    class="h-9 flex-1 rounded-xl text-zinc-300 hover:bg-zinc-900" 
                    onclick={() => isDialogOpen = false}
                >
                    {tr('categories.cancel', 'إلغاء', 'Cancel')}
                </Button>
                <Button 
                    type="submit" 
                    disabled={!formName.trim()} 
                    class="h-9 flex-1 rounded-xl font-semibold"
                >
                    {tr('categories.save', 'حفظ', 'Save')}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>