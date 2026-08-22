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

    type CategoryData = {
        category: string;
        amount: number;
        color: string;
    };

    type TransactionItem = {
        id: number | string;
        title?: string;
        description?: string;
        note?: string;
        notes?: string;
        memo?: string;
        details?: string;
        name?: string;
        amount: number;
        date?: string;
        created_at?: string;
        category_id?: number | string;
        category?: string | { id: number | string; name: string };
    };

    type CategoryBreakdown = {
        id: number | string;
        name: string;
        icon?: string;
        color?: string;
        amount: number;
        percentage?: number;
        description?: string;
        descriptions?: string[];
        transactions?: TransactionItem[];
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
        transactions = [],
    }: {
        expenseByCategory?: CategoryData[];
        categoryBreakdown?: CategoryBreakdown[];
        totalExpenses?: number;
        dateRange?: DateRange;
        transactions?: TransactionItem[];
    } = $props();

    function generateDistinctColor(index: number): string {
        const hue = (index * 137.5) % 360;
        return `hsl(${Math.round(hue)}, 70%, 50%)`;
    }

    const formattedCategoryBreakdown = $derived(
        (categoryBreakdown || []).map((item, idx) => {
            const calculatedPct = totalExpenses > 0 ? Math.round((item.amount / totalExpenses) * 100) : (item.percentage || 0);
            const assignedColor = item.color && item.color.trim() !== '' ? item.color : generateDistinctColor(idx);

            return {
                ...item,
                color: assignedColor,
                percentage: calculatedPct,
            };
        })
    );

    let selectedCategoryId = $state<number | string | null>(null);
    let hoveredCategoryId = $state<number | string | null>(null);

    const activeCategoryId = $derived(hoveredCategoryId ?? selectedCategoryId);

    const activeCategory = $derived(
        formattedCategoryBreakdown.find((c) => String(c.id) === String(activeCategoryId)) ?? null
    );

    function handleCategoryClick(catId: number | string, fromDonut = false) {
        if (String(selectedCategoryId) === String(catId)) {
            selectedCategoryId = null;
        } else {
            selectedCategoryId = catId;
        }

        if (fromDonut && selectedCategoryId !== null) {
            const el = document.getElementById(`cat-card-${selectedCategoryId}`);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    function getCategoryTransactions(cat: CategoryBreakdown): TransactionItem[] {
        if (cat.transactions && cat.transactions.length > 0) {
            return cat.transactions;
        }
        if (!transactions || transactions.length === 0) return [];

        return transactions.filter((tx) => {
            if (tx.category_id !== undefined && tx.category_id !== null && String(tx.category_id) === String(cat.id)) {
                return true;
            }
            if (typeof tx.category === 'object' && tx.category !== null && String(tx.category.id) === String(cat.id)) {
                return true;
            }
            if (typeof tx.category === 'string' && tx.category.trim().toLowerCase() === cat.name.trim().toLowerCase()) {
                return true;
            }
            return false;
        });
    }

    const RADIUS = 40;
    const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

    const donutSegments = $derived.by(() => {
        let currentAngle = 0;

        return formattedCategoryBreakdown.map((cat) => {
            const fraction = totalExpenses > 0 ? cat.amount / totalExpenses : 0;
            const strokeLength = fraction * CIRCUMFERENCE;
            const strokeDasharray = `${strokeLength} ${CIRCUMFERENCE}`;
            const strokeDashoffset = -currentAngle;

            currentAngle += strokeLength;

            return {
                ...cat,
                fraction,
                strokeDasharray,
                strokeDashoffset,
            };
        });
    });

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
    let selectedPeriod = $state('this_month');
    let fromDate = $state(dateRange?.from || todayStr);
    let toDate = $state(dateRange?.to || todayStr);

    const currentLabel = $derived(
        periodOptions.find((p) => p.id === selectedPeriod)?.label ?? 'هذا الشهر'
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

    function applyFilter(from: string, to: string) {
        fromDate = from;
        toDate = to;
        router.get('/reports', { from: fromDate, to: toDate }, { preserveState: true });
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

    function getIcon(iconName?: string) {
        if (!iconName) return null;
        return iconMap[iconName] ?? null;
    }

    function translateCategoryName(name: string): string {
        if (!name) return '';
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
    <!-- الهيدر -->
    <div>
        <h1 class="text-xl font-black tracking-tight sm:text-2xl">{t('reports.title')}</h1>
        <p class="text-xs text-muted-foreground mt-0.5">{t('reports.subtitle')}</p>
    </div>

    <!-- تصفية الفترة الزمنية -->
    <div class="p-4 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col gap-3 relative">
        <div class="flex items-center gap-2 text-xs font-bold text-foreground px-0.5">
            <Calendar class="size-4 text-primary" />
            <span>تحديد الفترة الزمنية</span>
        </div>

        <div class="relative">
            <button
                type="button"
                onclick={() => (isOpen = !isOpen)}
                class="w-full flex items-center justify-between h-12 bg-muted/30 border border-border/50 rounded-2xl px-4 text-xs font-bold text-foreground hover:bg-muted/50 transition-all duration-200 active:scale-[0.99]"
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
        <!-- الرسم البياني -->
        <div id="donut-chart-container" class="p-6 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col items-center gap-4">
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xs font-bold text-muted-foreground">توزيع المصاريف</h2>
                {#if selectedCategoryId !== null}
                    <button
                        type="button"
                        onclick={() => (selectedCategoryId = null)}
                        class="text-[11px] font-bold text-primary hover:underline"
                    >
                        عرض الكل
                    </button>
                {/if}
            </div>

            <div class="relative size-60 sm:size-64 flex items-center justify-center my-2">
                <svg class="size-full -rotate-90 transform" viewBox="0 0 100 100">
                    <circle
                        cx="50"
                        cy="50"
                        r={RADIUS}
                        fill="transparent"
                        stroke="currentColor"
                        stroke-width="10"
                        class="text-muted/15"
                    />
                    {#each donutSegments as segment}
                        {@const isActive = String(activeCategoryId) === String(segment.id)}
                        {@const isAnyActive = activeCategoryId !== null}
                        {#if segment.fraction > 0}
                            <circle
                                cx="50"
                                cy="50"
                                r={RADIUS}
                                fill="transparent"
                                stroke={segment.color}
                                stroke-width={isActive ? '14' : '10'}
                                stroke-dasharray={segment.strokeDasharray}
                                stroke-dashoffset={segment.strokeDashoffset}
                                stroke-linecap={isActive ? 'round' : 'butt'}
                                style="transition: all 0.25s ease-in-out;"
                                class="cursor-pointer origin-center {isAnyActive && !isActive ? 'opacity-25' : 'opacity-100'}"
                                onclick={() => handleCategoryClick(segment.id, true)}
                                onmouseenter={() => (hoveredCategoryId = segment.id)}
                                onmouseleave={() => (hoveredCategoryId = null)}
                                role="button"
                                tabindex="0"
                                aria-label={segment.name}
                            />
                        {/if}
                    {/each}
                </svg>

                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none p-4">
                    {#if activeCategory}
                        <span class="text-xs font-bold truncate max-w-[130px]" style="color: {activeCategory.color}">
                            {translateCategoryName(activeCategory.name)}
                        </span>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="text-2xl font-black text-foreground tabular-nums">
                                {activeCategory.amount.toLocaleString('en-SA')}
                            </span>
                            <span class="text-xs font-bold text-muted-foreground">{t('common.currency')}</span>
                        </div>
                        <span class="text-[10px] font-semibold text-muted-foreground mt-0.5">
                            {activeCategory.percentage}% من الإجمالي
                        </span>
                    {:else}
                        <span class="text-[11px] text-muted-foreground font-semibold">إجمالي المصاريف</span>
                        <div class="flex items-center gap-1 mt-0.5">
                            <span class="text-2xl font-black text-foreground tabular-nums">
                                {totalExpenses.toLocaleString('en-SA')}
                            </span>
                            <span class="text-xs font-bold text-muted-foreground">{t('common.currency')}</span>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        <!-- قائمة الفئات مع استبعاد الأوصاف المكررة وتنسيق الخط الشفاف -->
        <div class="flex flex-col gap-2.5">
            <h2 class="text-xs font-bold text-muted-foreground px-1">الفئات</h2>

            <div class="flex flex-col gap-2">
                {#each formattedCategoryBreakdown as cat}
                    {@const Icon = getIcon(cat.icon)}
                    {@const isActive = String(activeCategoryId) === String(cat.id)}
                    
                    {@const rawDescriptions = cat.descriptions && cat.descriptions.length > 0
                        ? cat.descriptions
                        : getCategoryTransactions(cat).map(tx => tx.description || tx.note || tx.title || '')}
                        
                    {@const uniqueDescriptions = Array.from(
                        new Set(
                            rawDescriptions
                                .map(d => d?.trim())
                                .filter((d): d is string => Boolean(d && d !== ''))
                        )
                    )}
                    
                    {@const descriptionText = uniqueDescriptions.join('، ')}

                    <div 
                        id="cat-card-{cat.id}"
                        class="rounded-2xl border transition-all duration-300 overflow-hidden {isActive ? 'border-primary/50' : 'border-border/40 bg-card'}"
                        style={isActive ? `border-color: ${cat.color}; box-shadow: 0 0 0 1px ${cat.color}40; background-color: ${cat.color}0A;` : ''}
                    >
                        <div
                            class="w-full flex items-center justify-between p-4 text-right cursor-pointer"
                            onclick={() => handleCategoryClick(cat.id, false)}
                            onmouseenter={() => (hoveredCategoryId = cat.id)}
                            onmouseleave={() => (hoveredCategoryId = null)}
                            role="button"
                            tabindex="0"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                {#if Icon}
                                    <div
                                        class="flex size-10 shrink-0 items-center justify-center rounded-2xl shadow-sm transition-transform duration-300"
                                        style="background-color: {cat.color}1D; color: {cat.color}"
                                    >
                                        <Icon class="size-5" />
                                    </div>
                                {:else}
                                    <div class="w-2 h-8 rounded-full shrink-0" style="background-color: {cat.color}"></div>
                                {/if}

                                <div class="flex flex-col min-w-0 truncate">
                                    <span class="text-sm font-bold text-foreground truncate">{translateCategoryName(cat.name)}</span>
                                    
                                    {#if descriptionText}
                                        <span class="text-[11px] font-normal text-muted-foreground/70 truncate leading-tight mt-0.5">
                                            {descriptionText}
                                        </span>
                                    {/if}
                                    
                                    <span class="text-[10px] text-muted-foreground font-semibold mt-0.5">{cat.percentage}% من الإجمالي</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0">
                                <span class="text-sm font-black text-foreground tabular-nums">
                                    {cat.amount.toLocaleString('en-SA')}
                                </span>
                                <span class="text-xs font-semibold text-muted-foreground">{t('common.currency')}</span>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    {/if}
</div>