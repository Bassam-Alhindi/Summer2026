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
    import SummaryCard from '@/components/dashboard/SummaryCard.svelte';
    import PeriodSelector from '@/components/dashboard/PeriodSelector.svelte';
    import RecentTransactions from '@/components/dashboard/RecentTransactions.svelte';
    import ExpensePieChart from '@/components/dashboard/ExpensePieChart.svelte';
    import QuickAddModal from '@/components/dashboard/QuickAddModal.svelte';
    import LanguageSwitcher from '@/components/LanguageSwitcher.svelte';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import Wallet from 'lucide-svelte/icons/wallet';
    import PiggyBank from 'lucide-svelte/icons/piggy-bank';
    import Plus from 'lucide-svelte/icons/plus';
    import { t } from '@/lib/i18n.svelte';
    import { router } from '@inertiajs/svelte';

    type Transaction = {
        id: number;
        description: string;
        amount: number;
        type: 'income' | 'expense';
        category: string;
        date: string;
    };

    type CategoryData = {
        category: string;
        amount: number;
        color: string;
    };

    type CategoryItem = {
        id: number;
        name: string;
        type: string;
        color?: string;
    };

    type Trends = {
        income: number;
        expenses: number;
    };

    let {
        totalIncome = 0,
        totalExpenses = 0,
        netBalance = 0,
        savingsRate = 0,
        recentTransactions = [],
        expenseByCategory = [],
        categories = [],
        period: initialPeriod = 'month',
        trends = { income: 0, expenses: 0 } as Trends,
    }: {
        totalIncome: number;
        totalExpenses: number;
        netBalance: number;
        savingsRate: number;
        recentTransactions: Transaction[];
        expenseByCategory: CategoryData[];
        categories: CategoryItem[];
        period: string;
        trends: Trends;
    } = $props();

    // قاموس الألوان الموحد للفئات
    const CATEGORY_COLORS: Record<string, string> = {
        // --- المصاريف ---
        food: '#EC4899',             // طعام ومشروبات (وردي)
        'food & drinks': '#EC4899',
        طعام: '#EC4899',
        'طعام ومشروبات': '#EC4899',

        housing: '#10B981',          // سكن (أخضر زمرّدي)
        سكن: '#10B981',

        entertainment: '#3B82F6',    // ترفيه (أزرق)
        ترفيه: '#3B82F6',

        health: '#A855F7',           // صحة (بنفسجي)
        صحة: '#A855F7',

        education: '#F59E0B',        // تعليم (برتقالي / أصفر)
        تعليم: '#F59E0B',

        bills: '#EF4444',            // فواتير (أحمر)
        فواتير: '#EF4444',

        shopping: '#6366F1',         // تسوق (نيلي)
        تسوق: '#6366F1',

        transportation: '#06B6D4',   // مواصلات (سماوي)
        transport: '#06B6D4',
        مواصلات: '#06B6D4',

        other: '#6B7280',            // أخرى (رمادي)
        أخرى: '#6B7280',

        // --- الدخل ---
        salary: '#10B981',           // الراتب
        الراتب: '#10B981',
        freelance: '#06B6D4',        // عمل حر
        'عمل حر': '#06B6D4',
        investment: '#8B5CF6',       // استثمار
        استثمار: '#8B5CF6',
        gift: '#EC4899',             // هدية
        هدية: '#EC4899',
    };

    function getCategoryColor(name: string): string {
        if (!name) return '#3B82F6';
        const key = name.toLowerCase().trim();
        return CATEGORY_COLORS[key] ?? '#3B82F6';
    }

    // تطبيق الألوان الموحدة على بيانات الرسم البياني
    const formattedExpenseByCategory = $derived(
        expenseByCategory.map((item) => ({
            ...item,
            color: getCategoryColor(item.category),
        }))
    );

    // تطبيق الألوان الموحدة على الفئات الممررة للمودال
    const formattedCategories = $derived(
        categories.map((cat) => ({
            ...cat,
            color: cat.color || getCategoryColor(cat.name),
        }))
    );

    let period = $state(initialPeriod);
    let isQuickAddOpen = $state(false);
    let isFirstRender = true;

    function openModal() {
        isQuickAddOpen = true;
    }

    const periodVsLabel = $derived.by(() => {
        const map = { week: 'summary.vsLastWeek', month: 'summary.vsLastMonth', year: 'summary.vsLastYear' } as const;
        return map[period as keyof typeof map] ?? 'summary.vsLastMonth';
    });

    const periodBalanceLabel = $derived.by(() => {
        const map = { week: 'summary.surplusWeek', month: 'summary.surplusMonth', year: 'summary.surplusYear' } as const;
        return map[period as keyof typeof map] ?? 'summary.surplusMonth';
    });

    const periodDeficitLabel = $derived.by(() => {
        const map = { week: 'summary.deficitWeek', month: 'summary.deficitMonth', year: 'summary.deficitYear' } as const;
        return map[period as keyof typeof map] ?? 'summary.deficitMonth';
    });

    $effect(() => {
        if (isFirstRender) {
            isFirstRender = false;
            return;
        }
        router.get(dashboard(), { period }, { preserveState: true, preserveScroll: true });
    });
</script>

<AppHead title={t('nav.dashboard')} />

<div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto p-4 pb-24 sm:p-6 lg:pb-6">
    <div class="flex flex-col gap-3 border-b border-border/40 pb-3">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('dashboard.title')}</h1>
                <p class="mt-0.5 text-xs text-muted-foreground">{t('dashboard.subtitle')}</p>
            </div>
            
            <div>
                <LanguageSwitcher />
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 pt-1">
            <PeriodSelector bind:period />

            <button
                type="button"
                onclick={openModal}
                class="group relative inline-flex shrink-0 cursor-cursor select-none items-center gap-2 overflow-hidden rounded-xl bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 px-3 py-2 text-white shadow-md shadow-emerald-500/20 transition-all duration-300 hover:scale-[1.02] hover:shadow-emerald-500/35 focus:outline-none active:scale-95"
            >
                <span class="absolute inset-0 bg-white/20 opacity-0 transition-opacity group-hover:opacity-100"></span>
                <div class="flex h-5 w-5 shrink-0 items-center justify-center rounded-lg bg-white/25 backdrop-blur-xs">
                    <Plus class="h-3.5 w-3.5 stroke-[3]" />
                </div>
                
                <div class="flex flex-col text-start text-[10px] font-bold leading-tight sm:text-[11px]">
                    <span>{t('common.add')}</span>
                    <span>{t('common.transaction')}</span>
                </div>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3 auto-rows-min md:grid-cols-6 lg:grid-cols-12">
        <div class="col-span-2 md:col-span-4 lg:col-span-5">
            <SummaryCard
                title={t('summary.netBalance')}
                value={`${netBalance.toLocaleString('en-SA')} ${t('common.currency')}`}
                Icon={Wallet}
                trend={netBalance >= 0 ? 'up' : 'down'}
                trendLabel={netBalance >= 0 ? t(periodBalanceLabel) : t(periodDeficitLabel)}
                colorClass="text-blue-500 bg-blue-500/10"
                size="large"
                class="h-full min-h-[110px]"
            />
        </div>

        <div class="col-span-1 md:col-span-2 lg:col-span-3">
            <SummaryCard
                title={t('summary.totalIncome')}
                value={`${totalIncome.toLocaleString('en-SA')}`}
                subtitle={t('common.currency')}
                Icon={TrendingUp}
                trend={trends.income >= 0 ? 'up' : 'down'}
                trendLabel={`${trends.income >= 0 ? '+' : ''}${trends.income}% ${t(periodVsLabel)}`}
                colorClass="text-emerald-600 bg-emerald-500/10"
                class="h-full min-h-[110px]"
            />
        </div>

        <div class="col-span-1 md:col-span-2 lg:col-span-4">
            <SummaryCard
                title={t('summary.totalExpenses')}
                value={`${totalExpenses.toLocaleString('en-SA')}`}
                subtitle={t('common.currency')}
                Icon={TrendingDown}
                trend={trends.expenses >= 0 ? 'down' : 'up'}
                trendLabel={`${trends.expenses >= 0 ? '+' : ''}${trends.expenses}% ${t(periodVsLabel)}`}
                colorClass="text-rose-400 bg-rose-500/10"
                class="h-full min-h-[110px]"
            />
        </div>

        <div class="col-span-2 md:col-span-2 lg:col-span-3">
            <SummaryCard
                title={t('summary.savingsRate')}
                value={`${savingsRate}%`}
                Icon={PiggyBank}
                trend={savingsRate >= 20 ? 'up' : 'down'}
                trendLabel={savingsRate >= 20 ? t('summary.onTrack') : t('summary.belowTarget')}
                colorClass="text-primary bg-primary/10"
                class="h-full min-h-[110px]"
            />
        </div>

        <div class="col-span-2 md:col-span-3 lg:col-span-5">
            <ExpensePieChart data={formattedExpenseByCategory} class="h-full" />
        </div>

        <div class="col-span-2 md:col-span-3 lg:col-span-7">
            <RecentTransactions transactions={recentTransactions} class="h-full" compact />
        </div>
    </div>

    <QuickAddModal bind:open={isQuickAddOpen} categories={formattedCategories} />
</div>