<script module lang="ts">
    import transactions from '@/routes/transactions';

    export const layout = {
        breadcrumbs: [
            {
                title: 'آخر العمليات',
                href: transactions.index.url(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Card, CardContent } from '@/components/ui/card';
    import { Button } from '@/components/ui/button';
    import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
    import Car from 'lucide-svelte/icons/car';
    import Receipt from 'lucide-svelte/icons/receipt';
    import Banknote from 'lucide-svelte/icons/banknote';
    import Film from 'lucide-svelte/icons/film';
    import Heart from 'lucide-svelte/icons/heart';
    import UtensilsCrossed from 'lucide-svelte/icons/utensils-crossed';
    import CircleDollarSign from 'lucide-svelte/icons/circle-dollar-sign';
    import Home from 'lucide-svelte/icons/home';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Gift from 'lucide-svelte/icons/gift';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import Check from 'lucide-svelte/icons/check';
    import Filter from 'lucide-svelte/icons/filter';
    import MoreHorizontal from 'lucide-svelte/icons/more-horizontal';
    import { router } from '@inertiajs/svelte';
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
        'تسوق': ShoppingBag,
        'مقاضي': ShoppingBag,

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

    // خريطة ألوان الفئات المترجمة من الصورة
    const imageCategoryColors: Record<string, string> = {
        // صحة -> بنفسجي
        health: '#c084fc',
        medical: '#c084fc',
        'صحة': '#c084fc',

        // ترفيه -> أزرق
        entertainment: '#3b82f6',
        film: '#3b82f6',
        'ترفيه': '#3b82f6',

        // سكن -> أخضر زمردي
        housing: '#10b981',
        home: '#10b981',
        'سكن': '#10b981',

        // تسوق -> أزرق بنفسجي / نيلي
        shopping: '#818cf8',
        grocery: '#818cf8',
        'تسوق': '#818cf8',
        'مقاضي': '#818cf8',

        // فواتير -> أحمر
        bills: '#ef4444',
        receipt: '#ef4444',
        utilities: '#ef4444',
        'فواتير': '#ef4444',
        'فاتورة': '#ef4444',

        // تعليم -> أصفر عنباري
        education: '#f59e0b',
        school: '#f59e0b',
        university: '#f59e0b',
        'تعليم': '#f59e0b',
        'دراسة': '#f59e0b',

        // أخرى -> رمادي
        other: '#9ca3af',
        'أخرى': '#9ca3af',

        // طعام ومشروبات -> وردي ماجنتا
        food: '#ec4899',
        utensils: '#ec4899',
        dining: '#ec4899',
        restaurant: '#ec4899',
        'طعام': '#ec4899',
        'طعام ومشروبات': '#ec4899',
        'أكل': '#ec4899',
        'مطاعم': '#ec4899',

        // مواصلات -> سماوي
        transport: '#06b6d4',
        car: '#06b6d4',
        'مواصلات': '#06b6d4',
        'سيارة': '#06b6d4',

        // راتب ودخل
        salary: '#10b981',
        income: '#10b981',
        'راتب': '#10b981',
        'الراتب': '#10b981',
    };

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

        // 1. المطابقة المباشرة مع ألوان الصورة
        for (const [key, color] of Object.entries(imageCategoryColors)) {
            if (lowerName.includes(key.toLowerCase())) {
                return color;
            }
        }

        // 2. إذا كانت الفئة تحتوي على لون خاص غير معرّف
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

    const typeTabs = [
        { id: 'all', label: 'الكل' },
        { id: 'expense', label: 'المصروفات' },
        { id: 'income', label: 'الدخل' },
    ];

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

    function formatAmount(tx: TransactionItem): string {
        const num = Math.abs(parseFloat(tx.amount)).toLocaleString('en-US');
        return tx.type === 'income' ? `+${num}` : `-${num}`;
    }

    function formatDate(dateStr: string): string {
        const localeCode = getLocale() === 'ar' ? 'ar-u-nu-latn' : 'en-US';
        return new Date(dateStr).toLocaleDateString(localeCode, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    function deleteTransaction(id: number) {
        if (!confirm(t('transactions.deleteConfirm'))) return;
        router.delete(transactions.destroy.url(id), {
            preserveScroll: true,
        });
    }

    function goToPage(url: string) {
        router.get(url, {}, { preserveState: true, only: ['transactions'] });
    }

    let currentSelectedCategory = $derived(
        availableCategories.find((c) => String(c.id) === String(categoryFilter))
    );
</script>

<AppHead title={t('transactions.pageTitle')} />

<div class="flex flex-1 flex-col gap-4 p-4 pb-24 sm:p-6 lg:pb-6">
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">آخر العمليات</h1>
    </div>

    <!-- تبويبات الفلترة الأفقية -->
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
                        {t(currentSelectedCategory.name)}
                    </span>
                {:else}
                    <div class="size-6 rounded-lg bg-muted text-muted-foreground flex items-center justify-center shrink-0">
                        <Filter class="size-3.5" />
                    </div>
                    <span class="text-muted-foreground text-xs sm:text-sm">اختر الفئة</span>
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
                aria-label="إغلاق القائمة"
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
                        <span>عرض كل الفئات</span>
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
                            <span class="truncate">{t(cat.name)}</span>
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
                    <p class="text-sm font-medium">{t('transactions.noTransactions')}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{t('transactions.noTransactionsHint')}</p>
                </div>
            {:else}
                <ul class="divide-y divide-border/60">
                    {#each transactionsProp.data as tx (tx.id)}
                        {@const IconComponent = getIcon(tx.category.name)}
                        {@const catColor = getCategoryColor(tx.category, tx.type)}
                        {@const translatedCategory = t(tx.category.name)}
                        {@const cleanDesc = tx.description ? tx.description.trim() : ''}
                        {@const hasCustomDesc = cleanDesc !== '' && cleanDesc.toLowerCase() !== tx.category.name.trim().toLowerCase() && cleanDesc !== translatedCategory}

                        <li class="flex items-center gap-3 px-4 py-3.5 transition-colors hover:bg-muted/40">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-xl"
                                style="background-color: color-mix(in srgb, {catColor} 15%, transparent); color: {catColor};"
                            >
                                <IconComponent class="size-5" />
                            </div>
                            <div class="flex flex-1 flex-col min-w-0">
                                <span class="text-sm font-bold text-foreground truncate">
                                    {hasCustomDesc ? tx.description : translatedCategory}
                                </span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-xs text-muted-foreground">{formatDate(tx.transaction_date)}</span>
                                    {#if hasCustomDesc}
                                        <span class="text-xs text-muted-foreground/40">&middot;</span>
                                        <span class="text-xs text-muted-foreground">{translatedCategory}</span>
                                    {/if}
                                </div>
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
                                        {t('common.currency')}
                                    </span>
                                </div>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 text-muted-foreground/60 hover:text-destructive hover:bg-destructive/10 rounded-lg"
                                    onclick={() => deleteTransaction(tx.id)}
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </div>
                        </li>
                    {/each}
                </ul>
            {/if}
        </CardContent>
    </Card>

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
                            <ChevronLeft class="size-4" />
                        </Button>
                    {:else if link.label.includes('Next') || link.label.includes('التالي') || link.label.includes('&raquo;')}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8 rounded-lg"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronRight class="size-4" />
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