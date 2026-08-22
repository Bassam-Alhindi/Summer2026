<script module lang="ts">
    import transactions from '@/routes/transactions';
    import type { TranslationKey } from '@/lib/translations';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Transactions',
                href: transactions.index.url(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Card, CardContent } from '@/components/ui/card';
    import { Button } from '@/components/ui/button';
    import {
        ShoppingBag,
        Car,
        Receipt,
        Banknote,
        Film,
        Heart,
        UtensilsCrossed,
        CircleDollarSign,
        Home,
        GraduationCap,
        Gift,
        Briefcase,
        TrendingUp,
        Trash2,
        ChevronLeft,
        ChevronRight,
        ChevronDown,
        Check,
        Filter,
        MoreHorizontal
    } from 'lucide-svelte';
    import { router } from '@inertiajs/svelte';
    import { toast } from 'svelte-sonner';
    import { t, getLocale } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';

    type CategoryItem = {
        id: number;
        name: string;
        icon: string;
        color: string;
        type: string;
    };

    type TransactionItem = {
        id: number;
        amount: string;
        type: 'income' | 'expense';
        description: string | null;
        transaction_date: string;
        category: CategoryItem;
    };

    type PaginationData = {
        data: TransactionItem[];
        links: { url: string | null; label: string; active: boolean }[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };

    let {
        transactions: transactionsProp,
        categories,
        filters,
    }: {
        transactions: PaginationData;
        categories: CategoryItem[];
        filters: {
            search: string;
            type: string;
            category_id: string;
            from: string;
            to: string;
        };
    } = $props();

    let selectedTxId = $state<number | null>(null);

    const categoryIconMap: Record<string, any> = {
        food: UtensilsCrossed,
        utensils: UtensilsCrossed,
        dining: UtensilsCrossed,
        restaurant: UtensilsCrossed,
        'طعام': UtensilsCrossed,
        'طعام ومشروبات': UtensilsCrossed,
        'أكل': UtensilsCrossed,
        'مطاعم': UtensilsCrossed,

        grocery: ShoppingBag,
        shopping: ShoppingBag,
        basket: ShoppingBag,
        cart: ShoppingBag,
        'تسوق': ShoppingBag,
        'مقاضي': ShoppingBag,
        'سلة': ShoppingBag,
        'السلة': ShoppingBag,

        transport: Car,
        car: Car,
        'مواصلات': Car,
        'سيارة': Car,

        bills: Receipt,
        receipt: Receipt,
        'فواتير': Receipt,
        'فاتورة': Receipt,

        salary: Banknote,
        income: Banknote,
        'راتب': Banknote,
        'الراتب': Banknote,

        entertainment: Film,
        film: Film,
        'ترفيه': Film,

        health: Heart,
        medical: Heart,
        'صحة': Heart,

        housing: Home,
        home: Home,
        'سكن': Home,

        education: GraduationCap,
        school: GraduationCap,
        university: GraduationCap,
        'تعليم': GraduationCap,
        'دراسة': GraduationCap,

        gift: Gift,
        'هدية': Gift,
        'هدايا': Gift,

        freelance: Briefcase,
        work: Briefcase,
        'عمل حر': Briefcase,

        investment: TrendingUp,
        'استثمار': TrendingUp,

        other: MoreHorizontal,
        'أخرى': MoreHorizontal,
    };

    const imageCategoryColors: Record<string, string> = {
        shopping: '#9ca3af',
        grocery: '#9ca3af',
        basket: '#9ca3af',
        cart: '#9ca3af',
        'تسوق': '#9ca3af',
        'مقاضي': '#9ca3af',
        'سلة': '#9ca3af',
        'السلة': '#9ca3af',

        health: '#c084fc',
        medical: '#c084fc',
        'صحة': '#c084fc',

        entertainment: '#3b82f6',
        film: '#3b82f6',
        'ترفيه': '#3b82f6',

        housing: '#10b981',
        home: '#10b981',
        'سكن': '#10b981',

        bills: '#ef4444',
        receipt: '#ef4444',
        utilities: '#ef4444',
        'فواتير': '#ef4444',
        'فاتورة': '#ef4444',

        education: '#f59e0b',
        school: '#f59e0b',
        university: '#f59e0b',
        'تعليم': '#f59e0b',
        'دراسة': '#f59e0b',

        other: '#9ca3af',
        'أخرى': '#9ca3af',

        food: '#ec4899',
        utensils: '#ec4899',
        dining: '#ec4899',
        restaurant: '#ec4899',
        'طعام': '#ec4899',
        'طعام ومشروبات': '#ec4899',
        'أكل': '#ec4899',
        'مطاعم': '#ec4899',

        transport: '#06b6d4',
        car: '#06b6d4',
        'مواصلات': '#06b6d4',
        'سيارة': '#06b6d4',

        salary: '#10b981',
        income: '#10b981',
        'راتب': '#10b981',
        'الراتب': '#10b981',
    };

    function translateCategoryName(name: string): string {
        if (!name) return '';
        const cleanName = name.toLowerCase().trim();

        const translatedDirect = t(cleanName as TranslationKey);
        if (translatedDirect !== cleanName) return translatedDirect;

        const categoryKey = `category.${cleanName}` as TranslationKey;
        const translatedCat = t(categoryKey);
        if (translatedCat !== categoryKey) return translatedCat;

        if (cleanName.includes('shop') || cleanName.includes('grocer') || cleanName.includes('تسوق') || cleanName.includes('سلة') || cleanName.includes('مقاضي') || cleanName.includes('cart') || cleanName.includes('basket')) {
            return t('category.shopping' as TranslationKey);
        }
        if (cleanName.includes('food') || cleanName.includes('dine') || cleanName.includes('utensil') || cleanName.includes('طعام') || cleanName.includes('مطاعم') || cleanName.includes('أكل')) {
            return t('category.food' as TranslationKey);
        }
        if (cleanName.includes('transp') || cleanName.includes('car') || cleanName.includes('مواصلات') || cleanName.includes('سيارة')) {
            return t('category.transport' as TranslationKey);
        }
        if (cleanName.includes('bill') || cleanName.includes('receipt') || cleanName.includes('فواتير') || cleanName.includes('فاتورة')) {
            return t('category.bills' as TranslationKey);
        }
        if (cleanName.includes('salary') || cleanName.includes('income') || cleanName.includes('راتب')) {
            return t('category.salary' as TranslationKey);
        }
        if (cleanName.includes('entertain') || cleanName.includes('film') || cleanName.includes('ترفيه')) {
            return t('category.entertainment' as TranslationKey);
        }
        if (cleanName.includes('health') || cleanName.includes('medic') || cleanName.includes('صحة')) {
            return t('category.health' as TranslationKey);
        }
        if (cleanName.includes('house') || cleanName.includes('home') || cleanName.includes('سكن')) {
            return t('category.housing' as TranslationKey);
        }
        if (cleanName.includes('educat') || cleanName.includes('school') || cleanName.includes('تعليم') || cleanName.includes('دراسة')) {
            return t('category.education' as TranslationKey);
        }
        if (cleanName.includes('gift') || cleanName.includes('هدية') || cleanName.includes('هدايا')) {
            return t('category.gift' as TranslationKey);
        }
        if (cleanName.includes('freelance') || cleanName.includes('work') || cleanName.includes('عمل')) {
            return t('category.freelance' as TranslationKey);
        }
        if (cleanName.includes('invest') || cleanName.includes('استثمار')) {
            return t('category.investment' as TranslationKey);
        }
        if (cleanName.includes('other') || cleanName.includes('أخرى')) {
            return t('category.other' as TranslationKey);
        }

        return name;
    }

    function getIcon(categoryName: string): any {
        if (!categoryName) return CircleDollarSign;
        const lowerName = categoryName.toLowerCase().trim();

        for (const [key, icon] of Object.entries(categoryIconMap)) {
            if (lowerName.includes(key.toLowerCase())) {
                return icon;
            }
        }
        return CircleDollarSign;
    }

    function getCategoryColor(category: CategoryItem | undefined | null, type?: string): string {
        if (!category) return type === 'income' ? '#10b981' : '#f43f5e';

        const lowerName = (category.name || '').toLowerCase().trim();

        for (const [key, color] of Object.entries(imageCategoryColors)) {
            if (lowerName.includes(key.toLowerCase())) {
                return color;
            }
        }

        if (category.color && category.color.trim() !== '') {
            return category.color;
        }

        return type === 'income' ? '#10b981' : '#f43f5e';
    }

    let typeFilter = $state(filters.type || 'all');
    let categoryFilter = $state(filters.category_id || 'all');
    let isCategoryDropdownOpen = $state(false);

    let availableCategories = $derived(
        categories.filter((cat) => {
            const name = cat.name.toLowerCase().trim();
            return name !== 'أخرى' && name !== 'other income' && name !== 'other';
        })
    );

    let typeTabs = $derived([
        { id: 'all', label: t('transactions.all' as TranslationKey) || 'الكل' },
        { id: 'expense', label: t('transactions.expense' as TranslationKey) },
        { id: 'income', label: t('transactions.income' as TranslationKey) },
    ]);

    function applyFilters() {
        router.get(
            transactions.index.url(),
            {
                type: typeFilter === 'all' ? '' : typeFilter,
                category_id: categoryFilter === 'all' ? '' : categoryFilter,
            },
            { preserveState: true, only: ['transactions'] }
        );
    }

    function changeType(val: string) {
        typeFilter = val;
        applyFilters();
    }

    function selectCategory(catId: string) {
        categoryFilter = catId;
        isCategoryDropdownOpen = false;
        applyFilters();
    }

    function toggleSelectTx(id: number) {
        selectedTxId = selectedTxId === id ? null : id;
    }

    function formatAmount(tx: TransactionItem): string {
        const num = Math.abs(parseFloat(tx.amount)).toLocaleString('en-US');
        return tx.type === 'income' ? `+${num}` : `-${num}`;
    }

    function formatDate(dateStr: string): string {
        const currentLang = getLocale();
        const localeCode = currentLang === 'ar' ? 'ar-u-nu-latn' : 'en-US';
        return new Date(dateStr).toLocaleDateString(localeCode, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    function deleteTransaction(id: number, event: MouseEvent) {
        event.stopPropagation();
        if (!confirm(t('transactions.deleteConfirm' as TranslationKey))) return;

        router.delete(transactions.destroy.url(id), {
            preserveScroll: true,
            onSuccess: () => {
                selectedTxId = null;
                toast.success(t('transactions.deletedSuccessfully' as TranslationKey) || 'تم حذف المعاملة بنجاح');
            },
            onError: () => {
                toast.error(t('transactions.deleteError' as TranslationKey) || 'حدث خطأ أثناء حذف المعاملة');
            },
        });
    }

    function goToPage(url: string) {
        router.get(url, {}, { preserveState: true, only: ['transactions'] });
    }

    let currentSelectedCategory = $derived(
        availableCategories.find((c) => String(c.id) === String(categoryFilter))
    );
</script>

<AppHead title={t('transactions.title' as TranslationKey)} />

<div class="flex flex-1 flex-col gap-4 p-4 pb-24 sm:p-6 lg:pb-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('transactions.title' as TranslationKey)}</h1>
    </div>

    <!-- تبويبات الفلترة -->
    <div class="inline-flex p-1 bg-muted/60 rounded-2xl gap-1 w-full">
        {#each typeTabs as tab}
            <button
                type="button"
                onclick={() => changeType(tab.id)}
                class={cn(
                    "flex-1 px-4 py-2 text-xs font-bold rounded-xl transition-all duration-200",
                    typeFilter === tab.id
                        ? "bg-card text-foreground shadow-xs"
                        : "text-muted-foreground hover:text-foreground"
                )}
            >
                {tab.label}
            </button>
        {/each}
    </div>

    <!-- قائمة اختيار الفئات -->
    <div class="relative w-full">
        <button
            type="button"
            onclick={() => (isCategoryDropdownOpen = !isCategoryDropdownOpen)}
            class={cn(
                "w-full flex items-center justify-between h-11 px-4 rounded-2xl bg-card/60 border border-border/60 text-sm font-medium transition-all hover:bg-card active:scale-[0.99]",
                isCategoryDropdownOpen && "ring-2 ring-primary/20 border-primary/40 bg-card"
            )}
        >
            <div class="flex items-center gap-2.5 truncate">
                {#if currentSelectedCategory}
                    {@const SelectedIcon = getIcon(currentSelectedCategory.name)}
                    {@const selColor = getCategoryColor(currentSelectedCategory, currentSelectedCategory.type)}
                    <div
                        class="size-6 rounded-lg flex items-center justify-center shrink-0"
                        style="background-color: color-mix(in srgb, {selColor} 15%, transparent); color: {selColor};"
                    >
                        <SelectedIcon class="size-3.5" />
                    </div>
                    <span class="font-semibold text-foreground text-xs sm:text-sm truncate">
                        {translateCategoryName(currentSelectedCategory.name)}
                    </span>
                {:else}
                    <div class="size-6 rounded-lg bg-muted text-muted-foreground flex items-center justify-center shrink-0">
                        <Filter class="size-3.5" />
                    </div>
                    <span class="text-muted-foreground text-xs sm:text-sm">{t('transactions.selectCategory' as TranslationKey)}</span>
                {/if}
            </div>

            <ChevronDown
                class={cn(
                    "size-4 text-muted-foreground transition-transform duration-200 shrink-0",
                    isCategoryDropdownOpen && "rotate-180 text-foreground"
                )}
            />
        </button>

        {#if isCategoryDropdownOpen}
            <button
                type="button"
                aria-label="Close"
                onclick={() => (isCategoryDropdownOpen = false)}
                class="fixed inset-0 z-30 cursor-default bg-transparent"
            ></button>

            <div class="absolute top-full start-0 end-0 mt-2 z-40 bg-card/95 backdrop-blur-xl border border-border/80 rounded-2xl p-1.5 shadow-2xl animate-in fade-in zoom-in-95 duration-150 max-h-64 overflow-y-auto space-y-0.5">
                <button
                    type="button"
                    onclick={() => selectCategory('all')}
                    class={cn(
                        "w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all",
                        categoryFilter === 'all'
                            ? "bg-primary/10 text-primary"
                            : "text-foreground hover:bg-muted/60"
                    )}
                >
                    <div class="flex items-center gap-2.5">
                        <Filter class="size-4 text-muted-foreground" />
                        <span>{t('transactions.allCategories' as TranslationKey)}</span>
                    </div>
                    {#if categoryFilter === 'all'}
                        <Check class="size-4 text-primary" />
                    {/if}
                </button>

                <div class="h-px bg-border/40 my-1"></div>

                {#each availableCategories as cat}
                    {@const CatIcon = getIcon(cat.name)}
                    {@const itemColor = getCategoryColor(cat, cat.type)}
                    {@const isSelected = String(categoryFilter) === String(cat.id)}
                    <button
                        type="button"
                        onclick={() => selectCategory(String(cat.id))}
                        class={cn(
                            "w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all",
                            isSelected
                                ? "bg-primary/10 text-primary"
                                : "text-foreground hover:bg-muted/60"
                        )}
                    >
                        <div class="flex items-center gap-2.5 truncate">
                            <CatIcon class="size-4 shrink-0" style="color: {itemColor};" />
                            <span class="truncate">{translateCategoryName(cat.name)}</span>
                        </div>
                        {#if isSelected}
                            <Check class="size-4 text-primary" />
                        {/if}
                    </button>
                {/each}
            </div>
        {/if}
    </div>

    <!-- قائمة المعاملات -->
    <Card class="border-border/60 shadow-xs">
        <CardContent class="p-0">
            {#if transactionsProp.data.length === 0}
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <p class="text-sm font-medium">{t('transactions.noTransactions' as TranslationKey)}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{t('transactions.noTransactionsHint' as TranslationKey)}</p>
                </div>
            {:else}
                <ul class="divide-y divide-border/60">
                    {#each transactionsProp.data as tx (tx.id)}
                        {@const IconComponent = getIcon(tx.category.name)}
                        {@const catColor = getCategoryColor(tx.category, tx.type)}
                        {@const translatedCategory = translateCategoryName(tx.category.name)}
                        {@const isSelected = selectedTxId === tx.id}

                        <li 
                            role="button"
                            tabindex="0"
                            onclick={() => toggleSelectTx(tx.id)}
                            onkeydown={(e) => (e.key === 'Enter' || e.key === ' ') && toggleSelectTx(tx.id)}
                            class={cn(
                                "flex items-center gap-3 px-4 py-3.5 transition-all duration-200 cursor-pointer select-none",
                                isSelected ? "bg-muted/80" : "hover:bg-muted/40"
                            )}
                        >
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl transition-transform"
                                style="background-color: color-mix(in srgb, {catColor} 15%, transparent); color: {catColor};"
                            >
                                <IconComponent class="size-5" />
                            </div>
                            <div class="flex flex-1 flex-col min-w-0">
                                <span class="text-sm font-bold text-foreground truncate">
                                    {translatedCategory}
                                </span>
                                <span class="text-xs text-muted-foreground mt-0.5">
                                    {formatDate(tx.transaction_date)}
                                </span>
                            </div>
                            <div class="flex items-center gap-3">
                                <div dir="ltr" class="flex items-baseline gap-1 shrink-0">
                                    <span
                                        class={cn(
                                            'text-sm font-bold tabular-nums',
                                            tx.type === 'income' ? 'text-emerald-500' : 'text-rose-500'
                                        )}
                                    >
                                        {formatAmount(tx)}
                                    </span>
                                    <span
                                        class={cn(
                                            'text-xs font-semibold',
                                            tx.type === 'income' ? 'text-emerald-500/80' : 'text-rose-500/80'
                                        )}
                                    >
                                        {t('common.currency' as TranslationKey)}
                                    </span>
                                </div>

                                {#if isSelected}
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title={t('transactions.deleteConfirm' as TranslationKey)}
                                        class="size-8 text-muted-foreground hover:text-foreground hover:bg-muted rounded-xl shrink-0 transition-all animate-in fade-in zoom-in-95 duration-150"
                                        onclick={(e) => deleteTransaction(tx.id, e)}
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                {/if}
                            </div>
                        </li>
                    {/each}
                </ul>
            {/if}
        </CardContent>
    </Card>

    <!-- الترقيم (Pagination) -->
    {#if transactionsProp.last_page > 1}
        <div class="flex items-center justify-between">
            <p class="text-xs text-muted-foreground">
                {transactionsProp.from}–{transactionsProp.to} / {transactionsProp.total}
            </p>
            <div class="flex items-center gap-1">
                {#each transactionsProp.links as link}
                    {#if link.label.includes('Previous') || link.label.includes('السابق') || link.label.includes('&laquo;')}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8 rounded-lg"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronLeft class="size-4 rtl:rotate-180" />
                        </Button>
                    {:else if link.label.includes('Next') || link.label.includes('التالي') || link.label.includes('&raquo;')}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8 rounded-lg"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronRight class="size-4 rtl:rotate-180" />
                        </Button>
                    {:else}
                        <Button
                            variant={link.active ? 'default' : 'outline'}
                            size="icon"
                            class="size-8 rounded-lg text-xs"
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            {link.label}
                        </Button>
                    {/if}
                {/each}
            </div>
        </div>
    {/if}
</div>