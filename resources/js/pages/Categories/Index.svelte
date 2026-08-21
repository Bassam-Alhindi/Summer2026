<script module lang="ts">
    import { index as categoriesIndex, store as categoriesStore, update as categoriesUpdate, destroy as categoriesDestroy } from '@/routes/categories';

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

    let expenseCategories = $derived(categories.filter((c) => c.type === 'expense'));
    let incomeCategories = $derived(categories.filter((c) => c.type === 'income'));

    let isDialogOpen = $state(false);
    let editingCategory = $state<Category | null>(null);

    let formName = $state('');
    let formType = $state<'income' | 'expense'>('expense');
    let formColor = $state('#6b7280');
    let formIcon = $state('circle-dollar-sign');

    function getIconComponent(iconName: string): Component {
        return iconMap[iconName] ?? CircleDollarSign;
    }

    function openAddDialog() {
        editingCategory = null;
        formName = '';
        formType = 'expense';
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
    <div class="flex flex-col gap-3 border-b border-border/40 pb-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('categories.title')}</h1>
                <p class="mt-0.5 text-xs text-muted-foreground">{t('categories.subtitle')}</p>
            </div>
            <Button onclick={openAddDialog} size="sm">
                <Plus class="size-4" />
                {t('categories.addCategory')}
            </Button>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <section>
            <h2 class="mb-3 text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                {t('categories.expenseCategories')}
            </h2>
            <div class="flex flex-col gap-2">
                {#each expenseCategories as category (category.id)}
                    <div class="flex items-center justify-between rounded-xl border border-border/60 bg-card px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg"
                                style="background-color: {category.color}20; color: {category.color}"
                            >
                                {@const IconComp = getIconComponent(category.icon)}
                                <IconComp class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">{category.name}</p>
                                {#if category.user_id === null}
                                    <span class="text-[10px] text-muted-foreground">{t('categories.systemDefault')}</span>
                                {/if}
                            </div>
                        </div>
                        {#if category.user_id !== null}
                            <div class="flex items-center gap-1">
                                <Button variant="ghost" size="icon-sm" onclick={() => openEditDialog(category)}>
                                    <Pencil class="size-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon-sm" onclick={() => deleteCategory(category)}>
                                    <Trash2 class="size-3.5 text-destructive" />
                                </Button>
                            </div>
                        {/if}
                    </div>
                {/each}
            </div>
        </section>

        <section>
            <h2 class="mb-3 text-sm font-semibold text-muted-foreground uppercase tracking-wide">
                {t('categories.incomeCategories')}
            </h2>
            <div class="flex flex-col gap-2">
                {#each incomeCategories as category (category.id)}
                    <div class="flex items-center justify-between rounded-xl border border-border/60 bg-card px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-9 items-center justify-center rounded-lg"
                                style="background-color: {category.color}20; color: {category.color}"
                            >
                                {@const IconComp = getIconComponent(category.icon)}
                                <IconComp class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-medium">{category.name}</p>
                                {#if category.user_id === null}
                                    <span class="text-[10px] text-muted-foreground">{t('categories.systemDefault')}</span>
                                {/if}
                            </div>
                        </div>
                        {#if category.user_id !== null}
                            <div class="flex items-center gap-1">
                                <Button variant="ghost" size="icon-sm" onclick={() => openEditDialog(category)}>
                                    <Pencil class="size-3.5" />
                                </Button>
                                <Button variant="ghost" size="icon-sm" onclick={() => deleteCategory(category)}>
                                    <Trash2 class="size-3.5 text-destructive" />
                                </Button>
                            </div>
                        {/if}
                    </div>
                {/each}
            </div>
        </section>
    </div>
</div>

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
                <div class="grid grid-cols-8 gap-1.5 overflow-y-auto max-h-32 rounded-lg border border-input p-2">
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
