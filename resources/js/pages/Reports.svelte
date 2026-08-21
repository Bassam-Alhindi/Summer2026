<script module lang="ts">
    import { reports } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Reports',
                href: reports(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
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
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import Banknote from 'lucide-svelte/icons/banknote';
    import Gift from 'lucide-svelte/icons/gift';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Calendar from 'lucide-svelte/icons/calendar';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import Filter from 'lucide-svelte/icons/filter';
    import Check from 'lucide-svelte/icons/check';
    import { t } from '@/lib/i18n.svelte';
    import type { TranslationKey } from '@/lib/translations';

    type CategoryData = {
        category: string;
        amount: number;
        color: string;
    };

    type CategoryBreakdown = {
        id: number;
        name: string;
        icon: string;
        color: string;
        amount: number;
        percentage: number;
    };

    type DateRange = {
        from: string;
        to: string;
    };

    let {
        expenseByCategory = [],
        categoryBreakdown = [],
        totalExpenses = 0,
        dateRange = { from: '', to: '' },
    }: {
        expenseByCategory: CategoryData[];
        categoryBreakdown: CategoryBreakdown[];
        totalExpenses: number;
        dateRange: DateRange;
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

        grocery: '#10B981',          // مقاضي
        مقاضي: '#10B981',

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

    // تطبيق الألوان الموحدة على تفاصيل الفئات
    const formattedCategoryBreakdown = $derived(
        categoryBreakdown.map((item) => ({
            ...item,
            color: getCategoryColor(item.name),
        }))
    );

    function formatDate(d: Date) {
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    const todayStr = formatDate(new Date());

    const periodOptions = [
        { id: 'today', label: 'اليوم' },
        { id: 'yesterday', label: 'أمس' },
        { id: 'this_week', label: 'هذا الأسبوع' },
        { id: 'this_month', label: 'هذا الشهر' },
        { id: 'last_month', label: 'الشهر الماضي' },
        { id: 'custom', label: 'تاريخ مخصص...' },
    ];

    let isOpen = $state(false);
    let selectedPeriod = $state('today');
    let fromDate = $state(dateRange.from || todayStr);
    let toDate = $state(dateRange.to || todayStr);

    const currentLabel = $derived(
        periodOptions.find((p) => p.id === selectedPeriod)?.label ?? 'اليوم'
    );

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

    // حساب أعلى قيمة للسكيل
    const maxAmount = $derived(
        formattedCategoryBreakdown.length > 0 
            ? Math.max(...formattedCategoryBreakdown.map((c) => c.amount), 1) 
            : 1
    );

    function applyFilter(from: string, to: string) {
        fromDate = from;
        toDate = to;
        router.get(
            reports.url(),
            { from: fromDate, to: toDate },
            { preserveState: true }
        );
    }

    function selectOption(id: string) {
        selectedPeriod = id;
        isOpen = false;

        const currNow = new Date();

        if (id === 'today') {
            const today = formatDate(currNow);
            applyFilter(today, today);
        } else if (id === 'yesterday') {
            const yesterdayObj = new Date(currNow);
            yesterdayObj.setDate(currNow.getDate() - 1);
            const yesterday = formatDate(yesterdayObj);
            applyFilter(yesterday, yesterday);
        } else if (id === 'this_week') {
            const sun = new Date(currNow);
            sun.setDate(currNow.getDate() - currNow.getDay());
            const from = formatDate(sun);
            const to = formatDate(currNow);
            applyFilter(from, to);
        } else if (id === 'this_month') {
            const year = currNow.getFullYear();
            const month = currNow.getMonth();
            const from = formatDate(new Date(year, month, 1));
            const to = formatDate(new Date(year, month + 1, 0));
            applyFilter(from, to);
        } else if (id === 'last_month') {
            const year = currNow.getFullYear();
            const month = currNow.getMonth();
            const from = formatDate(new Date(year, month - 1, 1));
            const to = formatDate(new Date(year, month, 0));
            applyFilter(from, to);
        }
    }

    function getIcon(iconName: string) {
        return iconMap[iconName] ?? MoreHorizontal;
    }

    function translateCategoryName(name: string): string {
        if (!name) return '';
        const key1 = `category.${name.toLowerCase()}` as TranslationKey;
        const translated1 = t(key1);
        if (translated1 && translated1 !== key1) return translated1;

        const key2 = `categories.${name.toLowerCase()}` as TranslationKey;
        const translated2 = t(key2);
        if (translated2 && translated2 !== key2) return translated2;

        const direct = t(name as any);
        if (direct && direct !== name) return direct;

        const arabicMap: Record<string, string> = {
            food: 'طعام',
            'food & drinks': 'طعام ومشروبات',
            grocery: 'مقاضي',
            shopping: 'تسوق',
            transport: 'مواصلات',
            transportation: 'مواصلات',
            bills: 'فواتير',
            salary: 'راتب',
            entertainment: 'ترفيه',
            health: 'صحة',
            education: 'تعليم',
            housing: 'سكن',
        };

        return arabicMap[name.toLowerCase()] ?? name;
    }
</script>

<AppHead title={t('reports.title')} />

<div class="flex flex-1 flex-col gap-5 p-4 pb-24 sm:p-6 lg:pb-6 max-w-xl mx-auto w-full">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-black tracking-tight sm:text-2xl">{t('reports.title')}</h1>
            <p class="text-xs text-muted-foreground mt-0.5">{t('reports.subtitle')}</p>
        </div>
        
        <div class="text-left bg-card px-4 py-2 rounded-2xl border border-border/60 shadow-sm">
            <span class="text-[10px] text-muted-foreground font-semibold block">المجموع</span>
            <span class="text-lg font-black text-foreground tabular-nums">
                {totalExpenses.toLocaleString('en-SA')} 
                <span class="text-xs font-normal text-muted-foreground">{t('common.currency')}</span>
            </span>
        </div>
    </div>

    <div class="p-4 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col gap-3 relative">
        <div class="flex items-center gap-2 text-xs font-bold text-foreground px-0.5">
            <Calendar class="size-4 text-primary" />
            <span>تحديد الفترة الزمنية</span>
        </div>

        <div class="relative">
            <button
                type="button"
                onclick={() => (isOpen = !isOpen)}
                class="w-full flex items-center justify-between h-12 bg-muted/30 border border-border/50 rounded-2xl px-4 text-xs font-bold text-foreground hover:bg-muted/50 focus:ring-2 focus:ring-primary/20 transition-all duration-200 active:scale-[0.99]"
            >
                <span>{currentLabel}</span>
                <ChevronDown class="size-4 text-muted-foreground transition-transform duration-300 {isOpen ? 'rotate-180 text-primary' : ''}" />
            </button>

            {#if isOpen}
                <button 
                    type="button" 
                    aria-label="Close"
                    onclick={() => (isOpen = false)} 
                    class="fixed inset-0 z-20 cursor-default bg-transparent"
                ></button>

                <div class="absolute top-full right-0 left-0 mt-2 z-30 bg-card/95 backdrop-blur-xl border border-border/70 rounded-2xl p-1.5 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
                    {#each periodOptions as option}
                        {@const isSelected = selectedPeriod === option.id}
                        <button
                            type="button"
                            onclick={() => selectOption(option.id)}
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all duration-150 {isSelected ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted/50'}"
                        >
                            <span>{option.label}</span>
                            {#if isSelected}
                                <Check class="size-4 text-primary" />
                            {/if}
                        </button>
                    {/each}
                </div>
            {/if}
        </div>

        {#if selectedPeriod === 'custom'}
            <div class="flex flex-col gap-2.5 pt-2 animate-in fade-in slide-in-from-top-1 duration-200">
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="flex flex-1 items-center justify-between w-full bg-muted/40 p-2.5 px-3 rounded-2xl border border-border/30">
                        <span class="text-xs text-muted-foreground font-semibold">من:</span>
                        <input
                            type="date"
                            bind:value={fromDate}
                            class="bg-transparent text-xs font-bold text-foreground border-0 focus:outline-none cursor-pointer"
                        />
                    </div>

                    <div class="flex flex-1 items-center justify-between w-full bg-muted/40 p-2.5 px-3 rounded-2xl border border-border/30">
                        <span class="text-xs text-muted-foreground font-semibold">إلى:</span>
                        <input
                            type="date"
                            bind:value={toDate}
                            class="bg-transparent text-xs font-bold text-foreground border-0 focus:outline-none cursor-pointer"
                        />
                    </div>
                </div>

                <button
                    type="button"
                    onclick={() => applyFilter(fromDate, toDate)}
                    class="h-11 w-full rounded-2xl bg-primary text-primary-foreground font-bold text-xs shadow-md active:scale-95 transition-all flex items-center justify-center gap-2"
                >
                    <Filter class="size-4" />
                    عرض
                </button>
            </div>
        {/if}
    </div>

    {#if formattedCategoryBreakdown.length === 0}
        <div class="flex flex-col items-center justify-center gap-2 py-20 rounded-3xl bg-card/50 border border-border/40 text-center">
            <Calendar class="size-10 text-muted-foreground/30" />
            <p class="text-sm font-bold text-foreground">{t('reports.noData')}</p>
            <p class="text-xs text-muted-foreground">{t('reports.noDataHint')}</p>
        </div>
    {:else}
        <div class="p-6 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col gap-4">
            <h2 class="text-xs font-bold text-muted-foreground">تحليل المصاريف</h2>
            
            <div class="h-56 flex items-end justify-around gap-2 pt-6 pb-2 border-b border-border/30 px-1 overflow-x-auto">
                {#each formattedCategoryBreakdown as cat}
                    {@const heightPercent = Math.max(Math.round((cat.amount / maxAmount) * 100), 12)}
                    {@const Icon = getIcon(cat.icon)}
                    
                    <div class="flex flex-col items-center gap-1.5 h-full justify-end flex-1 min-w-[48px] max-w-[60px] group">
                        <span class="text-[10px] font-bold text-muted-foreground group-hover:text-foreground transition-colors tabular-nums">
                            {cat.amount}
                        </span>

                        <div class="w-full bg-secondary/30 rounded-2xl overflow-hidden flex items-end h-full max-h-[120px] p-0.5">
                            <div
                                class="w-full rounded-xl transition-all duration-500 group-hover:brightness-110"
                                style="height: {heightPercent}%; background-color: {cat.color}"
                            ></div>
                        </div>

                        <div class="p-1 rounded-lg" style="color: {cat.color}">
                            <Icon class="size-4" />
                        </div>

                        <span class="text-[10px] font-bold text-muted-foreground truncate w-full text-center group-hover:text-foreground transition-colors">
                            {translateCategoryName(cat.name)}
                        </span>
                    </div>
                {/each}
            </div>
        </div>

        <div class="flex flex-col gap-2.5">
            <h2 class="text-xs font-bold text-muted-foreground px-1">الفئات</h2>

            <div class="flex flex-col gap-2">
                {#each formattedCategoryBreakdown as cat}
                    {@const Icon = getIcon(cat.icon)}
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-card border border-border/50 hover:border-border/80 transition-all duration-200">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex size-10 shrink-0 items-center justify-center rounded-2xl shadow-sm"
                                style="background-color: {cat.color}15; color: {cat.color}"
                            >
                                <Icon class="size-5" />
                            </div>
                            <span class="text-sm font-bold text-foreground">{translateCategoryName(cat.name)}</span>
                        </div>

                        <div dir="ltr" class="text-right">
                            <span class="text-sm font-black tabular-nums">{cat.amount.toLocaleString('en-SA')}</span>
                            <span class="text-xs font-medium text-muted-foreground ml-0.5">{t('common.currency')}</span>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    {/if}
</div>