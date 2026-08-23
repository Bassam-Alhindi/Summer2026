<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { router } from '@inertiajs/svelte';
    import Calendar from 'lucide-svelte/icons/calendar';
    import ChevronDown from 'lucide-svelte/icons/chevron-down';
    import Check from 'lucide-svelte/icons/check';
    import { getLocale } from '@/lib/i18n.svelte';
    import { getCategoryColor, getCategoryIcon, translateCategory } from '@/lib/categories';

    type CategoryData = {
        category: string;
        amount: number;
        color: string;
        type?: 'income' | 'expense';
    };

    type TransactionItem = {
        id: number | string;
        title?: string;
        description?: string;
        note?: string;
        amount: number;
        date?: string;
        category_id?: number | string;
        category?: string | { id: number | string; name: string };
        type?: 'income' | 'expense';
    };

    type CategoryBreakdown = {
        id: number | string;
        name: string;
        icon?: string;
        color?: string;
        amount: number;
        percentage?: number;
        descriptions?: string[];
        transactions?: TransactionItem[];
        type?: 'income' | 'expense';
    };

    type DateRange = {
        from: string;
        to: string;
    };

    let {
        categoryBreakdown = [],
        dateRange = { from: '', to: '' },
    }: {
        expenseByCategory?: CategoryData[];
        categoryBreakdown?: CategoryBreakdown[];
        totalExpenses?: number;
        totalIncome?: number;
        dateRange?: DateRange;
        transactions?: TransactionItem[];
    } = $props();

    let currentLocale = $derived(getLocale());

    // إجمالي المبالغ للفترة المختارة
    const grandTotal = $derived(
        (categoryBreakdown || []).reduce((sum, cat) => sum + Math.abs(cat.amount), 0)
    );

    // توحيد جلب البيانات والألوان والترجمة من ملف `@/lib/categories`
    const normalizedCategories = $derived(
        (categoryBreakdown || []).map((cat) => {
            const isIncome = cat.type === 'income';
            const amountAbs = Math.abs(cat.amount);
            const exactPct = grandTotal > 0 ? (amountAbs / grandTotal) * 100 : 0;
            const displayPercentage = parseFloat(exactPct.toFixed(1));
            
            // جلب اللون والاسم المترجم من المصدر المركزي
            const assignedColor = getCategoryColor(cat.name, cat.color);
            const translatedName = translateCategory(cat.name, currentLocale);

            const rawDescriptions = (cat.descriptions && cat.descriptions.length > 0)
                ? cat.descriptions
                : (cat.transactions || []).map((t) => t.description || t.note || t.title).filter(Boolean) as string[];

            const uniqueDescriptions = Array.from(new Set(rawDescriptions));

            return {
                ...cat,
                originalName: cat.name,
                name: translatedName, 
                color: assignedColor,
                displayPercentage,
                exactPct,
                isIncome,
                amountAbs,
                descriptionText: uniqueDescriptions.join('، '),
            };
        })
    );

    let selectedCategoryId = $state<number | string | null>(null);
    let hoveredCategoryId = $state<number | string | null>(null);
    let openDescriptions = $state<Record<string | number, boolean>>({});

    const activeCategoryId = $derived(hoveredCategoryId ?? selectedCategoryId);

    const activeCategory = $derived(
        normalizedCategories.find((c) => String(c.id) === String(activeCategoryId)) ?? null
    );

    function handleCategoryClick(catId: number | string, fromDonut = false) {
        if (String(selectedCategoryId) === String(catId)) {
            selectedCategoryId = null;
        } else {
            selectedCategoryId = catId;
        }

        openDescriptions[catId] = !openDescriptions[catId];

        if (fromDonut && selectedCategoryId !== null) {
            const el = document.getElementById(`cat-card-${selectedCategoryId}`);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }

    const RADIUS = 40;
    const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

    const donutSegments = $derived.by(() => {
        let currentAngle = 0;
        return normalizedCategories.map((cat) => {
            const fraction = grandTotal > 0 ? cat.amountAbs / grandTotal : 0;
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

    const periodOptions = $derived([
        { id: 'today', label: currentLocale === 'en' ? 'Today' : 'اليوم' },
        { id: 'yesterday', label: currentLocale === 'en' ? 'Yesterday' : 'أمس' },
        { id: 'this_week', label: currentLocale === 'en' ? 'This Week' : 'هذا الأسبوع' },
        { id: 'this_month', label: currentLocale === 'en' ? 'This Month' : 'هذا الشهر' },
        { id: 'last_month', label: currentLocale === 'en' ? 'Last Month' : 'الشهر الماضي' },
        { id: 'custom', label: currentLocale === 'en' ? 'Custom Date...' : 'تاريخ مخصص...' },
    ]);

    let isOpen = $state(false);
    let selectedPeriod = $state('today');
    let fromDate = $state(dateRange?.from || todayStr);
    let toDate = $state(dateRange?.to || todayStr);

    const currentLabel = $derived(
        periodOptions.find((p) => p.id === selectedPeriod)?.label ?? (currentLocale === 'en' ? 'Today' : 'اليوم')
    );

    function applyFilter(from: string, to: string) {
        fromDate = from;
        toDate = to;
        router.get('/reports', { from: fromDate, to: toDate }, { preserveState: true, preserveScroll: true });
    }

    function selectOption(id: string) {
        selectedPeriod = id;
        isOpen = false;
        const currNow = new Date();

        if (id === 'today') {
            applyFilter(formatDate(currNow), formatDate(currNow));
        } else if (id === 'yesterday') {
            const yesterdayObj = new Date(currNow);
            yesterdayObj.setDate(currNow.getDate() - 1);
            applyFilter(formatDate(yesterdayObj), formatDate(yesterdayObj));
        } else if (id === 'this_week') {
            const sun = new Date(currNow);
            sun.setDate(currNow.getDate() - currNow.getDay());
            applyFilter(formatDate(sun), formatDate(currNow));
        } else if (id === 'this_month') {
            const year = currNow.getFullYear();
            const month = currNow.getMonth();
            applyFilter(formatDate(new Date(year, month, 1)), formatDate(new Date(year, month + 1, 0)));
        } else if (id === 'last_month') {
            const year = currNow.getFullYear();
            const month = currNow.getMonth();
            applyFilter(formatDate(new Date(year, month - 1, 1)), formatDate(new Date(year, month, 0)));
        }
    }
</script>

<AppHead title={currentLocale === 'en' ? 'Reports' : 'التقارير'} />

<div class="flex flex-1 flex-col gap-5 p-4 pb-24 sm:p-6 lg:pb-6 max-w-xl mx-auto w-full">
    <!-- العنوان الرئيسي -->
    <div>
        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{currentLocale === 'en' ? 'Reports' : 'التقارير'}</h1>
        <p class="text-xs text-muted-foreground mt-0.5">{currentLocale === 'en' ? 'Period expenses and details' : 'اعرف كل ريال فين انصرف'}</p>
    </div>

    <!-- التصفية والتاريخ -->
    <div class="p-4 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col gap-3 relative">
        <div class="flex items-center gap-2 text-xs font-bold text-foreground px-0.5">
            <Calendar class="size-4 text-primary" />
            <span>{currentLocale === 'en' ? 'Select Time Period' : 'تحديد الفترة الزمنية'}</span>
        </div>

        <div class="relative">
            <button
                type="button"
                onclick={() => (isOpen = !isOpen)}
                class="w-full flex items-center justify-between h-12 bg-muted/30 border border-border/50 rounded-2xl px-4 text-xs font-bold text-foreground hover:bg-muted/50 transition-all duration-200"
            >
                <span>{currentLabel}</span>
                <ChevronDown class="size-4 text-muted-foreground transition-transform duration-300 {isOpen ? 'rotate-180 text-primary' : ''}" />
            </button>

            {#if isOpen}
                <button type="button" onclick={() => (isOpen = false)} class="fixed inset-0 z-20 cursor-default bg-transparent"></button>
                <div class="absolute top-full right-0 left-0 mt-2 z-30 bg-card/95 backdrop-blur-xl border border-border/70 rounded-2xl p-1.5 shadow-2xl">
                    {#each periodOptions as option}
                        {@const isSelected = selectedPeriod === option.id}
                        <button
                            type="button"
                            onclick={() => selectOption(option.id)}
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold transition-all {isSelected ? 'bg-primary/10 text-primary' : 'text-foreground hover:bg-muted/50'}"
                        >
                            <span>{option.label}</span>
                            {#if isSelected}<Check class="size-4 text-primary" />{/if}
                        </button>
                    {/each}
                </div>
            {/if}
        </div>

        {#if selectedPeriod === 'custom'}
            <div class="flex flex-col gap-2.5 pt-2">
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="flex flex-1 items-center justify-between w-full bg-muted/40 p-2.5 px-3 rounded-2xl border border-border/30">
                        <span class="text-xs text-muted-foreground font-semibold">{currentLocale === 'en' ? 'From:' : 'من:'}</span>
                        <input type="date" bind:value={fromDate} class="bg-transparent text-xs font-bold text-foreground border-0 focus:outline-none" />
                    </div>
                    <div class="flex flex-1 items-center justify-between w-full bg-muted/40 p-2.5 px-3 rounded-2xl border border-border/30">
                        <span class="text-xs text-muted-foreground font-semibold">{currentLocale === 'en' ? 'To:' : 'إلى:'}</span>
                        <input type="date" bind:value={toDate} class="bg-transparent text-xs font-bold text-foreground border-0 focus:outline-none" />
                    </div>
                </div>
                <button type="button" onclick={() => applyFilter(fromDate, toDate)} class="h-11 w-full rounded-2xl bg-primary text-primary-foreground font-bold text-xs shadow-md">
                    {currentLocale === 'en' ? 'Apply' : 'تطبيق'}
                </button>
            </div>
        {/if}
    </div>

    {#if normalizedCategories.length === 0}
        <div class="flex flex-col items-center justify-center py-12 text-center px-4">
            <div class="mb-3 flex size-16 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground/70 ring-8 ring-muted/20">
                <Calendar class="size-8" />
            </div>
            <p class="text-sm font-medium text-foreground">{currentLocale === 'en' ? 'No data available' : 'لا توجد بيانات'}</p>
            <p class="mt-1 text-xs text-muted-foreground">{currentLocale === 'en' ? 'There are no data recorded for the selected period.' : 'لا توجد بيانات مسجلة خلال هذه الفترة.'}</p>
        </div>
    {:else}
        <!-- الرسم البياني (دائرة التوزيع) -->
        <div id="donut-chart-container" class="p-6 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col items-center gap-4">
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xs font-bold text-foreground">{currentLocale === 'en' ? 'Details' : 'التفاصيل'}</h2>
                {#if selectedCategoryId !== null}
                    <button type="button" onclick={() => (selectedCategoryId = null)} class="text-[11px] font-bold text-primary hover:underline">
                        {currentLocale === 'en' ? 'Show All' : 'عرض الكل'}
                    </button>
                {/if}
            </div>

            <div class="relative size-60 sm:size-64 flex items-center justify-center my-2">
                <svg class="size-full -rotate-90 transform" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r={RADIUS} fill="transparent" stroke="currentColor" stroke-width="10" class="text-muted/15" />
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
                            {activeCategory.name}
                        </span>
                        <div class="flex items-center gap-1 mt-0.5" dir="ltr">
                            <span class="text-2xl font-black tabular-nums text-foreground">
                                {activeCategory.amountAbs.toLocaleString('en-US')}
                            </span>
                            <span class="text-xs font-bold text-foreground">⃁</span>
                        </div>
                        <span class="text-xs font-semibold text-muted-foreground mt-0.5">
                            {activeCategory.displayPercentage}{currentLocale === 'en' ? '% of total' : '% من الإجمالي'}
                        </span>
                    {:else}
                        <span class="text-xs text-muted-foreground font-semibold">{currentLocale === 'en' ? 'Total' : 'المجموع'}</span>
                        <div class="flex items-center gap-1 mt-0.5" dir="ltr">
                            <span class="text-2xl font-black text-foreground tabular-nums">
                                {grandTotal.toLocaleString('en-US')}
                            </span>
                            <span class="text-xs font-bold text-foreground">⃁</span>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        <!-- قائمة الفئات -->
        <div class="flex flex-col gap-2.5">
            <h2 class="text-xs font-bold text-foreground px-1">{currentLocale === 'en' ? 'Categories' : 'الفئات'}</h2>

            <div class="flex flex-col gap-2">
                {#each normalizedCategories as cat}
                    {@const Icon = getCategoryIcon(cat.originalName, cat.icon)}
                    {@const isActive = String(activeCategoryId) === String(cat.id)}

                    <button
                        type="button"
                        id="cat-card-{cat.id}"
                        class="w-full text-start rounded-2xl border transition-all duration-300 overflow-hidden {isActive ? 'border-primary/50' : 'border-border/40 bg-card'}"
                        style={isActive ? `border-color: ${cat.color}; box-shadow: 0 0 0 1px ${cat.color}40; background-color: ${cat.color}0A;` : ''}
                        onclick={() => handleCategoryClick(cat.id, false)}
                        onmouseenter={() => (hoveredCategoryId = cat.id)}
                        onmouseleave={() => (hoveredCategoryId = null)}
                    >
                        <div class="w-full flex items-center justify-between p-4">
                            <div class="flex items-center gap-3 min-w-0">
                                {#if Icon}
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-2xl shadow-sm" style="background-color: {cat.color}1D; color: {cat.color}">
                                        <Icon class="size-5" />
                                    </div>
                                {:else}
                                    <div class="w-2 h-8 rounded-full shrink-0" style="background-color: {cat.color}"></div>
                                {/if}

                                <div class="flex flex-col items-start min-w-0 text-start">
                                    <div class="flex items-center gap-1.5 max-w-full">
                                        <!-- اسم الفئة مترجم كلياً باللغة الحالية -->
                                        <span class="text-sm font-bold text-foreground truncate">{cat.name}</span>
                                        {#if cat.descriptionText}
                                            <ChevronDown class="size-3.5 text-muted-foreground transition-transform duration-200 shrink-0 {openDescriptions[cat.id] ? 'rotate-180 text-primary' : ''}" />
                                        {/if}
                                    </div>
                                    {#if cat.descriptionText && openDescriptions[cat.id]}
                                        <span class="text-xs text-muted-foreground max-w-full font-medium mt-0.5 break-words">{cat.descriptionText}</span>
                                    {/if}
                                    <!-- النسبة المئوية مترجمة بالكامل -->
                                    <span class="text-xs font-semibold text-muted-foreground">
                                        {cat.displayPercentage}{currentLocale === 'en' ? '% of total' : '% من الإجمالي'}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1 shrink-0" dir="ltr">
                                <span class="text-sm font-black tabular-nums {cat.isIncome ? 'text-emerald-500' : 'text-red-500'}">
                                    {cat.isIncome ? '+' : '-'}{cat.amountAbs.toLocaleString('en-US')}
                                </span>
                                <span class="text-xs font-semibold text-foreground">⃁</span>
                            </div>
                        </div>
                    </button>
                {/each}
            </div>
        </div>
    {/if}
</div>