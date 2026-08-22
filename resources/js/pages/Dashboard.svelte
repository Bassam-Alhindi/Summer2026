<script module lang="ts">
    import { dashboard } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { router, Link } from '@inertiajs/svelte';
    import Plus from 'lucide-svelte/icons/plus';
    import ArrowUpRight from 'lucide-svelte/icons/arrow-up-right';
    import ArrowDownRight from 'lucide-svelte/icons/arrow-down-right';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import Home from 'lucide-svelte/icons/home';
    import Film from 'lucide-svelte/icons/film';
    import Heart from 'lucide-svelte/icons/heart';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Receipt from 'lucide-svelte/icons/receipt';
    import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
    import Car from 'lucide-svelte/icons/car';
    import UtensilsCrossed from 'lucide-svelte/icons/utensils-crossed';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import Banknote from 'lucide-svelte/icons/banknote';
    import Gift from 'lucide-svelte/icons/gift';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import MoreHorizontal from 'lucide-svelte/icons/more-horizontal';
    import Wallet from 'lucide-svelte/icons/wallet';
    import X from 'lucide-svelte/icons/x';
    import { t } from '@/lib/i18n.svelte';
    import * as i18nModule from '@/lib/i18n.svelte';
    import type { TranslationKey } from '@/lib/translations';

    type CategoryObject = {
        id: number;
        name: string;
        type?: 'income' | 'expense';
        color?: string;
        icon?: string;
    };

    type Transaction = {
        id: number;
        title?: string;
        category: string | CategoryObject;
        type: 'income' | 'expense';
        amount: number;
        date: string;
        icon?: string;
        color?: string;
    };

    let {
        netBalance = 0,
        totalIncome = 0,
        totalExpenses = 0,
        recentTransactions = [],
        categories = [],
        period = 'month',
    }: {
        netBalance: number;
        totalIncome: number;
        totalExpenses: number;
        recentTransactions: Transaction[];
        categories: CategoryObject[];
        period: string;
    } = $props();

    const displayedTransactions = $derived(recentTransactions.slice(0, 4));

    let selectedPeriod = $state(period || 'month');
    
    // 1. حالة اللغة المتفاعلة في Svelte 5
    let currentLang = $state(typeof document !== 'undefined' && document.documentElement.lang === 'en' ? 'en' : 'ar');

    function toggleLanguage() {
        const nextLang = currentLang === 'ar' ? 'en' : 'ar';
        currentLang = nextLang;

        const mod = i18nModule as any;
        if (typeof mod.setLocale === 'function') {
            mod.setLocale(nextLang);
        } else if (typeof mod.setLanguage === 'function') {
            mod.setLanguage(nextLang);
        } else {
            router.post('/language', { locale: nextLang }, { preserveScroll: true });
        }

        if (typeof document !== 'undefined') {
            document.documentElement.lang = nextLang;
            document.documentElement.dir = nextLang === 'ar' ? 'rtl' : 'ltr';
        }
    }

    // 2. دالة الترجمة الذكية مع دعم النص البديل للغتين
    function tr(key: string, fallbackAr: string, fallbackEn?: string): string {
        try {
            const translated = t(key as TranslationKey);
            if (translated && translated !== key) return translated;
        } catch {
            // fallback
        }
        return currentLang === 'en' ? (fallbackEn || fallbackAr) : fallbackAr;
    }

    // 3. دالة تحويل أسماء الفئات حسب لغة الصفحة الحالية
    function translateCategory(name?: string): string {
        if (!name) return '';
        const categoriesMap: Record<string, { ar: string; en: string }> = {
            housing: { ar: 'سكن', en: 'Housing' },
            entertainment: { ar: 'ترفيه', en: 'Entertainment' },
            health: { ar: 'صحة', en: 'Health' },
            education: { ar: 'تعليم', en: 'Education' },
            bills: { ar: 'فواتير', en: 'Bills' },
            transportation: { ar: 'مواصلات', en: 'Transportation' },
            shopping: { ar: 'تسوق', en: 'Shopping' },
            'food & drinks': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
            food: { ar: 'طعام', en: 'Food' },
            other: { ar: 'أخرى', en: 'Other' },
            salary: { ar: 'الراتب', en: 'Salary' },
            freelance: { ar: 'عمل حر', en: 'Freelance' },
            investment: { ar: 'استثمار', en: 'Investment' },
            gift: { ar: 'هدية', en: 'Gift' },
            
            // المفاتيح بالعربي
            سكن: { ar: 'سكن', en: 'Housing' },
            ترفيه: { ar: 'ترفيه', en: 'Entertainment' },
            صحة: { ar: 'صحة', en: 'Health' },
            تعليم: { ar: 'تعليم', en: 'Education' },
            فواتير: { ar: 'فواتير', en: 'Bills' },
            مواصلات: { ar: 'مواصلات', en: 'Transportation' },
            تسوق: { ar: 'تسوق', en: 'Shopping' },
            'طعام ومشروبات': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
            طعام: { ar: 'طعام', en: 'Food' },
            أخرى: { ar: 'أخرى', en: 'Other' },
            الراتب: { ar: 'الراتب', en: 'Salary' },
            'عمل حر': { ar: 'عمل حر', en: 'Freelance' },
            استثمار: { ar: 'استثمار', en: 'Investment' },
            هدية: { ar: 'هدية', en: 'Gift' },
        };
        const key = name.toLowerCase().trim();
        return categoriesMap[key] ? (currentLang === 'ar' ? categoriesMap[key].ar : categoriesMap[key].en) : name;
    }

    let periods = $derived([
        { id: 'week', label: tr('period.week', 'هذا الأسبوع', 'This Week') },
        { id: 'month', label: tr('period.month', 'هذا الشهر', 'This Month') },
        { id: 'year', label: tr('period.year', 'هذه السنة', 'This Year') },
    ]);

    function changePeriod(pId: string) {
        selectedPeriod = pId;
        router.get(dashboard.url(), { period: pId }, { preserveState: true });
    }

    let isDialogOpen = $state(false);
    let formAmount = $state('');
    let formType = $state<'income' | 'expense'>('expense');
    let formCategoryId = $state<number | null>(null);
    let formDescription = $state('');
    let formDate = $state(new Date().toISOString().split('T')[0]);
    let errorMessage = $state<string | null>(null);
    let isSubmitting = $state(false);

    let filteredCategories = $derived(
        categories.filter((c) => !c.type || c.type === formType)
    );

    function openAddDialog() {
        formAmount = '';
        formType = 'expense';
        const firstCat = categories.find((c) => c.type === 'expense') || categories[0];
        formCategoryId = firstCat?.id ?? null;
        formDescription = '';
        formDate = new Date().toISOString().split('T')[0];
        errorMessage = null;
        isDialogOpen = true;
    }

    function handleTypeChange(newType: 'income' | 'expense') {
        formType = newType;
        const firstMatchingCat = categories.find((c) => c.type === newType) || categories[0];
        formCategoryId = firstMatchingCat?.id ?? null;
    }

    function handleSubmit() {
        if (!formAmount || !formCategoryId) {
            errorMessage = tr('transaction.error_required', 'يرجى تحديد المبلغ واختيار الفئة', 'Please select amount and category');
            return;
        }

        errorMessage = null;
        isSubmitting = true;

        router.post(
            '/transactions',
            {
                amount: formAmount,
                type: formType,
                category_id: formCategoryId,
                description: formDescription,
                transaction_date: formDate,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    isDialogOpen = false;
                    isSubmitting = false;
                },
                onError: (errors: any) => {
                    isSubmitting = false;
                    errorMessage = Object.values(errors)[0] as string;
                },
            }
        );
    }

    const iconMap: Record<string, typeof Home> = {
        home: Home,
        film: Film,
        heart: Heart,
        'graduation-cap': GraduationCap,
        receipt: Receipt,
        'shopping-bag': ShoppingBag,
        car: Car,
        'utensils-crossed': UtensilsCrossed,
        briefcase: Briefcase,
        banknote: Banknote,
        gift: Gift,
        'trending-up': TrendingUp,
        'more-horizontal': MoreHorizontal,
    };

    function getIcon(iconName?: string, categoryName?: string) {
        if (iconName && iconMap[iconName]) {
            return iconMap[iconName];
        }

        const lowerCat = (categoryName || '').toLowerCase();
        if (lowerCat.includes('freelance') || lowerCat.includes('عمل حر')) return Briefcase;
        if (lowerCat.includes('investment') || lowerCat.includes('استثمار')) return TrendingUp;
        if (lowerCat.includes('gift') || lowerCat.includes('هدية')) return Gift;
        if (lowerCat.includes('food') || lowerCat.includes('طعام')) return UtensilsCrossed;

        return MoreHorizontal;
    }
</script>

<AppHead title={tr('dashboard.title', 'محفظتي', 'My Wallet')} />

<div class="flex flex-1 flex-col gap-5 p-4 pb-24 sm:p-6 max-w-lg mx-auto w-full">
    
    <!-- 1. الهيدر الرئيسي -->
    <div class="flex items-start justify-between gap-2">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-foreground">{tr('dashboard.title', 'محفظتي', 'My Wallet')}</h1>
            <p class="text-xs text-muted-foreground mt-0.5 font-medium">{tr('dashboard.subtitle', 'نظمها بذُكاء وتطمن على جيبك', 'Manage smartly and keep track of your budget')}</p>
        </div>

        <div class="flex flex-col items-end gap-1.5 shrink-0">
            <!-- زر تغيير اللغة -->
            <button
                type="button"
                onclick={toggleLanguage}
                class="flex items-center gap-1.5 px-2.5 h-7 rounded-lg bg-muted/20 hover:bg-muted/50 border border-border/30 backdrop-blur-xs text-muted-foreground hover:text-foreground opacity-70 hover:opacity-100 font-bold text-[11px] transition-all active:scale-95 cursor-pointer shrink-0"
            >
                <span class="tracking-tight">{currentLang === 'ar' ? 'English' : 'العربية'}</span>
                
                <svg class="size-3.5 shrink-0 opacity-70" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="15" height="15" rx="4" />
                    <path d="M6 6.5h7M9.5 5v1.5M7 11.5c1.2-.8 2.5-2.2 2.5-4M12 11.5s-1.2-2-2.5-2.5" stroke-width="1.5" />
                    <rect x="11" y="11" width="15" height="15" rx="4" />
                    <path d="M15.5 22l3-7 3 7M16.5 19.5h4" stroke-width="1.5" />
                </svg>
            </button>

            <!-- زر إضافة معاملة -->
            <button
                type="button"
                onclick={openAddDialog}
                class="h-10 px-4 rounded-xl bg-primary text-primary-foreground font-bold text-xs shadow-md hover:bg-primary/90 active:scale-95 transition-all flex items-center gap-1.5 shrink-0 cursor-pointer"
            >
                <Plus class="size-4 stroke-[2.5]" />
                <span>{tr('dashboard.add_transaction', 'إضافة معاملة', 'Add Transaction')}</span>
            </button>
        </div>
    </div>

    <!-- 2. محول الفترة الزمنية -->
    <div class="p-1 rounded-2xl bg-muted/50 border border-border/40 grid grid-cols-3 gap-1">
        {#each periods as p}
            {@const isActive = selectedPeriod === p.id}
            <button
                type="button"
                onclick={() => changePeriod(p.id)}
                class="py-2 rounded-xl text-xs font-bold transition-all duration-200 text-center {isActive ? 'bg-card text-foreground shadow-sm border border-border/50' : 'text-muted-foreground hover:text-foreground'}"
            >
                {p.label}
            </button>
        {/each}
    </div>

    <!-- 3. كارت الرصيد والملخص المالي -->
    <div class="rounded-3xl bg-card border border-border/60 p-5 shadow-sm flex flex-col gap-5">
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground flex items-center gap-1.5">
                    <Wallet class="size-3.5 text-primary" />
                    {tr('dashboard.net_balance', 'الرصيد الصافي', 'Net Balance')}
                </span>
            </div>

            <div class="flex items-baseline gap-1.5 mt-1">
                <span class="text-3xl font-black text-foreground tabular-nums tracking-tight">
                    {netBalance.toLocaleString('en-SA')}
                </span>
                <span class="text-sm font-bold text-muted-foreground/90">{tr('common.currency', 'ر.س', 'SAR')}</span>
            </div>
        </div>

        <div class="h-px bg-border/40 w-full"></div>

        <div class="grid grid-cols-2 gap-4">
            <!-- الدخل -->
            <div class="flex items-center gap-3">
                <div class="size-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                    <ArrowUpRight class="size-4 stroke-[2.5]" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-semibold text-muted-foreground">{tr('dashboard.total_income', 'إجمالي الدخل', 'Total Income')}</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-lg font-black text-emerald-500 tabular-nums">
                            +{totalIncome.toLocaleString('en-SA')}
                        </span>
                        <span class="text-xs font-bold text-muted-foreground/80">{tr('common.currency', 'ر.س', 'SAR')}</span>
                    </div>
                </div>
            </div>

            <!-- المصاريف -->
            <div class="flex items-center gap-3">
                <div class="size-9 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0">
                    <ArrowDownRight class="size-4 stroke-[2.5]" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-semibold text-muted-foreground">{tr('dashboard.total_expenses', 'إجمالي المصاريف', 'Total Expenses')}</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-lg font-black text-rose-500 tabular-nums">
                            -{totalExpenses.toLocaleString('en-SA')}
                        </span>
                        <span class="text-xs font-bold text-muted-foreground/80">{tr('common.currency', 'ر.س', 'SAR')}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. آخر العمليات -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-lg font-black text-foreground">{tr('dashboard.recent_transactions', 'آخر العمليات', 'Recent Transactions')}</h2>
            
            <Link 
                href="/transactions" 
                class="text-xs font-medium text-muted-foreground hover:text-foreground transition-colors flex items-center gap-0.5"
            >
                <span>{tr('dashboard.view_all', 'عرض الكل', 'View All')}</span>
                <ChevronLeft class="size-3.5" />
            </Link>
        </div>

        <div class="flex flex-col gap-2">
            {#if displayedTransactions.length === 0}
                <div class="p-8 text-center rounded-2xl bg-card border border-border/40">
                    <p class="text-xs text-muted-foreground font-semibold">{tr('dashboard.no_recent_transactions', 'لا توجد عمليات مسجلة حديثاً', 'No recent transactions recorded')}</p>
                </div>
            {:else}
                {#each displayedTransactions as item}
                    {@const categoryName = typeof item.category === 'object' ? item.category.name : (item.category || item.title || '')}
                    {@const categoryIcon = item.icon || (typeof item.category === 'object' ? item.category.icon : undefined)}
                    {@const Icon = getIcon(categoryIcon, categoryName)}
                    {@const isIncome = item.type === 'income'}
                    {@const categoryColor = item.color || (typeof item.category === 'object' ? item.category.color : null) || (isIncome ? '#10b981' : '#ef4444')}
                    
                    <div class="p-3.5 px-4 rounded-2xl bg-card border border-border/40 hover:border-border/80 transition-all flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div 
                                class="size-10 rounded-xl flex items-center justify-center shrink-0"
                                style="background-color: {categoryColor}20; color: {categoryColor};"
                            >
                                <Icon class="size-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-foreground">
                                    {translateCategory(categoryName)}
                                </span>
                                <span class="text-[11px] text-muted-foreground font-medium mt-0.5">
                                    {item.date}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 dir-ltr">
                            <span class="text-sm font-bold tabular-nums {isIncome ? 'text-emerald-500' : 'text-rose-500'}">
                                {isIncome ? '+' : '-'}{Math.abs(item.amount).toLocaleString('en-SA')}
                            </span>
                            <span class="text-xs font-bold text-muted-foreground">{tr('common.currency', 'ر.س', 'SAR')}</span>
                        </div>
                    </div>
                {/each}
            {/if}
        </div>
    </div>

</div>

<!-- نافذة إضافة معاملة سريعة -->
{#if isDialogOpen}
    <div class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
        <div class="w-full max-w-sm rounded-3xl bg-[#141414] border border-white/10 p-5 shadow-2xl space-y-4 text-white animate-in fade-in zoom-in-95 duration-150">
            
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-black tracking-tight">{tr('transaction.add_quick_transaction', 'إضافة معاملة سريعة', 'Add Quick Transaction')}</h3>
                <button 
                    type="button" 
                    onclick={() => isDialogOpen = false}
                    class="size-7 rounded-full bg-white/5 flex items-center justify-center text-white/60 hover:text-white transition-colors cursor-pointer"
                >
                    <X class="size-4" />
                </button>
            </div>

            {#if errorMessage}
                <div class="rounded-xl bg-rose-500/15 p-2.5 text-xs font-semibold text-rose-400 text-center border border-rose-500/20">
                    {errorMessage}
                </div>
            {/if}

            <form class="flex flex-col gap-4" onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
                
                <div class="grid grid-cols-2 gap-1 p-1 rounded-xl bg-white/5 border border-white/5">
                    <button
                        type="button"
                        class="py-2 rounded-lg text-xs font-extrabold transition-all cursor-pointer {formType === 'expense' ? 'bg-[#222222] text-white shadow-md border border-white/10' : 'text-white/50 hover:text-white'}"
                        onclick={() => handleTypeChange('expense')}
                    >
                        {tr('transaction.expense', 'مصروف', 'Expense')}
                    </button>
                    <button
                        type="button"
                        class="py-2 rounded-lg text-xs font-extrabold transition-all cursor-pointer {formType === 'income' ? 'bg-[#222222] text-white shadow-md border border-white/10' : 'text-white/50 hover:text-white'}"
                        onclick={() => handleTypeChange('income')}
                    >
                        {tr('transaction.income', 'دخل', 'Income')}
                    </button>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label for="tx-amount" class="text-xs font-bold text-white/80">{tr('transaction.amount_sar', 'المبلغ (ر.س)', 'Amount (SAR)')}</label>
                    <input 
                        id="tx-amount" 
                        type="number" 
                        step="0.01" 
                        min="0.01" 
                        bind:value={formAmount} 
                        placeholder="0.00" 
                        required 
                        class="h-11 w-full rounded-xl border border-white/10 bg-[#1c1c1c] px-3.5 text-left font-mono text-base font-bold text-white placeholder:text-white/20 focus:outline-none focus:border-white/30"
                    />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-white/80">{tr('transaction.category', 'الفئة', 'Category')}</label>
                    <div class="grid grid-cols-3 gap-2 max-h-48 overflow-y-auto pr-0.5 p-1">
                        {#each filteredCategories as cat (cat.id)}
                            {@const isSelected = formCategoryId === cat.id}
                            {@const catColor = cat.color || '#3b82f6'}
                            <button
                                type="button"
                                onclick={() => formCategoryId = cat.id}
                                class="relative py-2.5 px-2 rounded-xl text-xs font-extrabold transition-all duration-200 ease-out text-center truncate cursor-pointer select-none active:scale-90 hover:scale-[1.03] flex items-center justify-center gap-1.5 border"
                                style={isSelected 
                                    ? `background-color: ${catColor}25; border-color: ${catColor}; color: ${catColor}; box-shadow: 0 0 18px ${catColor}50, inset 0 0 10px ${catColor}30; transform: scale(1.04);` 
                                    : `background-color: ${catColor}08; border-color: ${catColor}20; color: ${catColor}cc;`}
                            >
                                {#if isSelected}
                                    <span 
                                        class="size-1.5 rounded-full animate-pulse shrink-0" 
                                        style="background-color: {catColor}; box-shadow: 0 0 6px {catColor};"
                                    ></span>
                                {/if}
                                <span class="truncate">{translateCategory(cat.name)}</span>
                            </button>
                        {/each}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2.5">
                    <div class="flex flex-col gap-1.5">
                        <label for="tx-date" class="text-xs font-bold text-white/80">{tr('transaction.date', 'التاريخ', 'Date')}</label>
                        <input 
                            id="tx-date" 
                            type="date" 
                            bind:value={formDate} 
                            required 
                            class="h-10 w-full rounded-xl border border-white/10 bg-[#1c1c1c] px-2.5 text-xs font-semibold text-white focus:outline-none focus:border-white/30"
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label for="tx-desc" class="text-xs font-bold text-white/80">{tr('transaction.description_optional', 'الوصف (اختياري)', 'Description (Optional)')}</label>
                        <input 
                            id="tx-desc" 
                            type="text" 
                            bind:value={formDescription} 
                            placeholder={tr('transaction.description_placeholder', 'لماذا هذا المبلغ؟', 'What is this for?')} 
                            class="h-10 w-full rounded-xl border border-white/10 bg-[#1c1c1c] px-2.5 text-xs font-semibold text-white placeholder:text-white/20 focus:outline-none focus:border-white/30"
                        />
                    </div>
                </div>

                <div class="flex flex-col gap-2 pt-1">
                    <button 
                        type="submit" 
                        disabled={isSubmitting || !formAmount || !formCategoryId}
                        class="h-11 w-full rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold border border-white/15 disabled:opacity-40 transition-all cursor-pointer active:scale-[0.98]"
                    >
                        {isSubmitting ? tr('common.saving', 'جاري الحفظ...', 'Saving...') : tr('common.save', 'حفظ', 'Save')}
                    </button>
                    
                    <button 
                        type="button" 
                        onclick={() => isDialogOpen = false}
                        class="h-11 w-full rounded-xl bg-white/5 hover:bg-white/10 text-white/80 text-xs font-bold border border-white/5 transition-all cursor-pointer active:scale-[0.98]"
                    >
                        {tr('common.cancel', 'إلغاء', 'Cancel')}
                    </button>
                </div>

            </form>
        </div>
    </div>
{/if}