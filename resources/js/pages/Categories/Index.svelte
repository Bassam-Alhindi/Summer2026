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
    import type { Component } from 'svelte';
    import { store as categoriesStore, update as categoriesUpdate, destroy as categoriesDestroy } from '@/routes/categories';

    type Category = {
        id: number;
        user_id: number | null;
        name: string;
        icon: string;
        color: string;
        type: 'income' | 'expense';
        sort_order: number;
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

    let activeTab = $state<'expense' | 'income'>('expense');

    let filteredCategories = $derived(categories.filter((c) => c.type === activeTab));

    let isDialogOpen = $state(false);
    let editingCategory = $state<Category | null>(null);

    let formName = $state('');
    let formType = $state<'income' | 'expense'>('expense');
    let formColor = $state('#6b7280');
    let formIcon = $state('circle-dollar-sign');

    function getIconComponent(iconName: string): Component {
        return iconMap[iconName] ?? CircleDollarSign;
    }

    function isArabicUi(): boolean {
        const titleText = t('categories.title');
        return /[\u0600-\u06FF]/.test(titleText);
    }

    function getTranslatedCategoryName(category: Category): string {
        const raw = category.name ? category.name.trim() : '';
        if (!raw) return '';

        const isAr = isArabicUi();

        const categoriesMap: Record<string, { ar: string; en: string }> = {
            'housing': { ar: 'السكن', en: 'Housing' },
            'home': { ar: 'السكن', en: 'Housing' },
            'entertainment': { ar: 'الترفيه', en: 'Entertainment' },
            'health': { ar: 'الصحة', en: 'Health' },
            'education': { ar: 'التعليم', en: 'Education' },
            'shopping': { ar: 'التسوق', en: 'Shopping' },
            'bills': { ar: 'الفواتير', en: 'Bills' },
            'bills & utilities': { ar: 'الفواتير', en: 'Bills' },
            'food & drinks': { ar: 'الطعام والمشروبات', en: 'Food & Drinks' },
            'food & dining': { ar: 'الطعام والمشروبات', en: 'Food & Drinks' },
            'food_drinks': { ar: 'الطعام والمشروبات', en: 'Food & Drinks' },
            'transportation': { ar: 'المواصلات', en: 'Transportation' },
            'other': { ar: 'أخرى', en: 'Other' },
            'salary': { ar: 'الراتب', en: 'Salary' },
            'freelance': { ar: 'عمل حر', en: 'Freelance' },
            'investments': { ar: 'الاستثمار', en: 'Investments' },
            'gifts': { ar: 'الهدايا', en: 'Gifts' },
        };

        const key = raw.toLowerCase();
        if (categoriesMap[key]) {
            return isAr ? categoriesMap[key].ar : categoriesMap[key].en;
        }

        for (const item of Object.values(categoriesMap)) {
            if (item.ar === raw) {
                return isAr ? item.ar : item.en;
            }
        }

        return raw;
    }

    function getSubtextLabel(): string {
        return isArabicUi() ? 'افتراضي' : 'Default';
    }

    function openAddDialog() {
        editingCategory = null;
        formName = '';
        formType = activeTab;
        formColor = '#6b7280';
        formIcon = 'circle-dollar-sign';
        isDialogOpen = true;
    }

    function openEditDialog(category: Category) {
        editingCategory = category;
        formName = category.name;
        formType = category.type;
        formColor = category.color;
        formIcon = category.icon;
        isDialogOpen = true;
    }

    function handleSubmit() {
        const data = {
            name: formName,
            type: formType,
            color: formColor,
            icon: formIcon,
        };

        if (editingCategory) {
            router.put(categoriesUpdate.url(editingCategory.id), data, {
                preserveScroll: true,
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
        } else {
            router.post(categoriesStore.url(), data, {
                preserveScroll: true,
                onSuccess: () => {
                    isDialogOpen = false;
                },
            });
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

<div class="flex h-full flex-1 flex-col gap-4 overflow-y-auto p-4 pb-24 sm:p-6 lg:pb-6">
    <!-- Header -->
    <div class="flex items-center justify-between border-b border-border/40 pb-3">
        <div>
            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('categories.title')}</h1>
            <p class="mt-0.5 text-xs text-muted-foreground">{t('categories.subtitle')}</p>
        </div>
        <Button onclick={openAddDialog} size="sm">
            <Plus class="size-4" />
            {t('categories.addCategory')}
        </Button>
    </div>

    <!-- التبديل المصغر (مصروف / دخل) -->
    <div class="flex justify-center">
        <div class="inline-flex w-[140px] rounded-lg bg-zinc-900/90 p-0.5 border border-zinc-800 text-muted-foreground">
            <button
                type="button"
                class="flex-1 rounded-md py-1 text-xs font-semibold transition-all {activeTab === 'expense' ? 'bg-zinc-800 text-white shadow-xs' : 'hover:text-foreground'}"
                onclick={() => activeTab = 'expense'}
            >
                {t('transactions.expense')}
            </button>
            <button
                type="button"
                class="flex-1 rounded-md py-1 text-xs font-semibold transition-all {activeTab === 'income' ? 'bg-zinc-800 text-white shadow-xs' : 'hover:text-foreground'}"
                onclick={() => activeTab = 'income'}
            >
                {t('transactions.income')}
            </button>
        </div>
    </div>

    <!-- العرض المباشر للون من قاعدة البيانات (category.color) -->
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
        {#each filteredCategories as category (category.id)}
            {@const IconComp = getIconComponent(category.icon)}
            <div
                class="group relative flex items-center justify-between rounded-xl border p-3 transition-all"
                style="
                    background-color: rgba(18, 18, 22, 0.75);
                    border-color: {category.color}40;
                "
            >
                <!-- اسم الفئة وتحتها (افتراضي) -->
                <div class="min-w-0 flex-1">
                    <p
                        class="truncate text-base font-extrabold leading-tight sm:text-lg"
                        style="color: {category.color};"
                    >
                        {getTranslatedCategoryName(category)}
                    </p>
                    {#if category.user_id === null}
                        <span class="mt-0.5 block text-xs font-medium text-zinc-400">
                            {getSubtextLabel()}
                        </span>
                    {/if}
                </div>

                <!-- الأيقونة بخلفية مشتقة من لون الفئة الأصلي -->
                <div
                    class="flex size-8 shrink-0 items-center justify-center rounded-lg"
                    style="
                        background-color: {category.color}20;
                        color: {category.color};
                    "
                >
                    <IconComp class="size-4" />
                </div>

                <!-- أزرار التعديل والحذف للفئات الخاصة -->
                {#if category.user_id !== null}
                    <div class="absolute top-2 left-2 flex items-center gap-1 opacity-0 transition-opacity group-hover:opacity-100 rtl:left-auto rtl:right-2">
                        <button
                            type="button"
                            class="flex size-5 items-center justify-center rounded bg-zinc-800 text-zinc-300 hover:text-white"
                            onclick={() => openEditDialog(category)}
                        >
                            <Pencil class="size-3" />
                        </button>
                        <button
                            type="button"
                            class="flex size-5 items-center justify-center rounded bg-zinc-800 text-zinc-300 hover:text-red-400"
                            onclick={() => deleteCategory(category)}
                        >
                            <Trash2 class="size-3" />
                        </button>
                    </div>
                {/if}
            </div>
        {/each}
    </div>
</div>

<!-- Modal Dialog -->
<Dialog bind:open={isDialogOpen}>
    <DialogContent>
        <DialogHeader>
            <DialogTitle>
                {editingCategory ? t('categories.editCategory') : t('categories.addCategory')}
            </DialogTitle>
        </DialogHeader>
        <form class="flex flex-col gap-4" onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
            <div class="flex flex-col gap-1.5">
                <Label for="cat-name">{t('categories.categoryName')}</Label>
                <Input id="cat-name" type="text" bind:value={formName} required />
            </div>

            <div class="flex flex-col gap-1.5">
                <Label for="cat-type">{t('categories.categoryType')}</Label>
                <select
                    id="cat-type"
                    class="h-8 w-full rounded-lg border border-input bg-background px-2 text-sm"
                    bind:value={formType}
                >
                    <option value="expense">{t('transactions.expense')}</option>
                    <option value="income">{t('transactions.income')}</option>
                </select>
            </div>

            <div class="flex flex-col gap-1.5">
                <Label for="cat-color">{t('categories.categoryColor')}</Label>
                <div class="flex items-center gap-2">
                    <input id="cat-color" type="color" bind:value={formColor} class="size-8 cursor-pointer rounded border border-input" />
                    <Input type="text" bind:value={formColor} class="flex-1" />
                </div>
            </div>

            <div class="flex flex-col gap-1.5">
                <Label>{t('categories.categoryIcon')}</Label>
                <div class="grid max-h-32 grid-cols-8 gap-1.5 overflow-y-auto rounded-lg border border-input p-2">
                    {#each iconOptions as iconName (iconName)}
                        {@const IconComp = iconMap[iconName]}
                        <button
                            type="button"
                            class="flex size-8 items-center justify-center rounded-md transition-colors {formIcon === iconName ? 'bg-primary text-primary-foreground' : 'hover:bg-muted'}"
                            onclick={() => formIcon = iconName}
                        >
                            <IconComp class="size-4" />
                        </button>
                    {/each}
                </div>
            </div>

            <DialogFooter>
                <Button type="button" variant="outline" onclick={() => isDialogOpen = false}>
                    {t('categories.cancel')}
                </Button>
                <Button type="submit" disabled={!formName}>
                    {t('categories.save')}
                </Button>
            </DialogFooter>
        </form>
    </DialogContent>
</Dialog>