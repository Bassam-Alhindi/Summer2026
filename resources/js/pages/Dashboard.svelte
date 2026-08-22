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
    import { t } from '@/lib/i18n.svelte';
    import type { TranslationKey } from '@/lib/translations';

    type Transaction = {
        id: number;
        title?: string;
        category: string;
        type: 'income' | 'expense';
        amount: number;
        date: string;
        icon?: string;
    };

    let {
        netBalance = 36,
        totalIncome = 420,
        totalExpenses = 384,
        recentTransactions = [],
        period = 'this_month',
    }: {
        netBalance: number;
        totalIncome: number;
        totalExpenses: number;
        recentTransactions: Transaction[];
        period: string;
    } = $props();

    const displayedTransactions = $derived(recentTransactions.slice(0, 4));

    let selectedPeriod = $state(period || 'this_month');

    const periods = [
        { id: 'this_week', label: 'هذا الأسبوع' },
        { id: 'this_month', label: 'هذا الشهر' },
        { id: 'this_year', label: 'هذه السنة' },
    ];

    function changePeriod(pId: string) {
        selectedPeriod = pId;
        router.get(dashboard.url(), { period: pId }, { preserveState: true });
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

    function getIcon(iconName?: string) {
        if (!iconName) return MoreHorizontal;
        return iconMap[iconName] ?? MoreHorizontal;
    }

    function translateCategory(name: string): string {
        if (!name) return '';
        const key = `category.${name.toLowerCase()}` as TranslationKey;
        const translated = t(key);
        if (translated && translated !== key) return translated;

        const arabicMap: Record<string, string> = {
            investment: 'استثمار',
            freelance: 'عمل حر',
            gift: 'هدية',
            'food & drinks': 'طعام ومشروبات',
            food: 'طعام',
            shopping: 'تسوق',
            bills: 'فواتير',
            salary: 'الراتب',
        };

        return arabicMap[name.toLowerCase()] ?? name;
    }
</script>

<AppHead title="محفظتي" />

<div class="flex flex-1 flex-col gap-6 p-4 pb-24 sm:p-6 max-w-lg mx-auto w-full">
    
    <!-- 1. الهيدر الرئيسي -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-foreground">محفظتي</h1>
            <p class="text-xs text-muted-foreground mt-0.5 font-medium">نظمها بذُكاء وتطمن على جيبك</p>
        </div>

        <button
            type="button"
            class="h-10 px-4 rounded-xl bg-primary text-primary-foreground font-bold text-xs shadow-md hover:bg-primary/90 active:scale-95 transition-all flex items-center gap-1.5 shrink-0"
        >
            <Plus class="size-4 stroke-[2.5]" />
            <span>إضافة معاملة</span>
        </button>
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

    <!-- 3. كارت الرصيد والملخص المالي الموحد -->
    <div class="rounded-3xl bg-card border border-border/60 p-5 shadow-sm flex flex-col gap-5">
        
        <!-- القسم العلوي: الرصيد الصافي -->
        <div class="flex flex-col gap-1.5">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-muted-foreground flex items-center gap-1.5">
                    <Wallet class="size-3.5 text-primary" />
                    الرصيد الصافي
                </span>
                <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                    فائض هذا الشهر
                </span>
            </div>

            <div class="flex items-baseline gap-1.5 mt-1">
                <span class="text-3xl font-black text-foreground tabular-nums tracking-tight">
                    {netBalance.toLocaleString('en-SA')}
                </span>
                <span class="text-xs font-bold text-muted-foreground">{t('common.currency')}</span>
            </div>
        </div>

        <div class="h-px bg-border/40 w-full"></div>

        <!-- القسم السفلي: إجمالي الدخل والمصاريف -->
        <div class="grid grid-cols-2 gap-4">
            
            <!-- الدخل -->
            <div class="flex items-center gap-3">
                <div class="size-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                    <ArrowUpRight class="size-4 stroke-[2.5]" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-semibold text-muted-foreground">إجمالي الدخل</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-sm font-bold text-foreground tabular-nums">
                            +{totalIncome.toLocaleString('en-SA')}
                        </span>
                        <span class="text-[10px] text-muted-foreground">{t('common.currency')}</span>
                    </div>
                </div>
            </div>

            <!-- المصاريف -->
            <div class="flex items-center gap-3">
                <div class="size-9 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0">
                    <ArrowDownRight class="size-4 stroke-[2.5]" />
                </div>
                <div class="flex flex-col">
                    <span class="text-[11px] font-semibold text-muted-foreground">إجمالي المصاريف</span>
                    <div class="flex items-baseline gap-1 mt-0.5">
                        <span class="text-sm font-bold text-foreground tabular-nums">
                            -{totalExpenses.toLocaleString('en-SA')}
                        </span>
                        <span class="text-[10px] text-muted-foreground">{t('common.currency')}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- 4. آخر 4 عمليات مع زر "عرض الكل" باللون الرصاصي الهادئ -->
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between px-1">
            <h2 class="text-xs font-bold text-muted-foreground">آخر العمليات</h2>
            
            <Link 
                href="/transactions" 
                class="text-xs font-medium text-muted-foreground hover:text-foreground transition-colors flex items-center gap-0.5"
            >
                <span>عرض الكل</span>
                <ChevronLeft class="size-3.5" />
            </Link>
        </div>

        <div class="flex flex-col gap-2">
            {#if displayedTransactions.length === 0}
                <div class="p-8 text-center rounded-2xl bg-card border border-border/40">
                    <p class="text-xs text-muted-foreground font-semibold">لا توجد عمليات مسجلة حديثاً</p>
                </div>
            {:else}
                {#each displayedTransactions as item}
                    {@const Icon = getIcon(item.icon)}
                    {@const isIncome = item.type === 'income'}
                    
                    <div class="p-3.5 px-4 rounded-2xl bg-card border border-border/40 hover:border-border/80 transition-all flex items-center justify-between gap-3">
                        
                        <div class="flex items-center gap-3">
                            <div class="size-10 rounded-xl flex items-center justify-center shrink-0 {isIncome ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500'}">
                                <Icon class="size-5" />
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-foreground">
                                    {translateCategory(item.category || item.title || '')}
                                </span>
                                <span class="text-[11px] text-muted-foreground font-medium mt-0.5">
                                    {item.date}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1 dir-ltr">
                            <span class="text-sm font-bold tabular-nums {isIncome ? 'text-emerald-500' : 'text-foreground'}">
                                {isIncome ? '+' : '-'}{Math.abs(item.amount).toLocaleString('en-SA')}
                            </span>
                            <span class="text-xs font-semibold text-muted-foreground">{t('common.currency')}</span>
                        </div>

                    </div>
                {/each}
            {/if}
        </div>
    </div>

</div>