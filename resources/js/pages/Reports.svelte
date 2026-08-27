<script lang="ts">
  import AppHead from '@/components/AppHead.svelte';
  import { router } from '@inertiajs/svelte';
  import { Calendar, ChevronDown, Check } from 'lucide-svelte';
  import { getLocale } from '@/lib/i18n.svelte';
  import { getCategoryColor, getCategoryIcon, translateCategory } from '@/lib/categories';

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
    budget_limit?: number | null;
  };

  type DateRange = {
    from: string;
    to: string;
  };

  let {
    categoryBreakdown = [],
    dateRange = { from: '', to: '' },
  }: {
    categoryBreakdown?: CategoryBreakdown[];
    dateRange?: DateRange;
  } = $props();

  let currentLocale = $derived(getLocale());

  function formatAmount(val: number) {
    return val.toLocaleString('en-US', {
      minimumFractionDigits: val % 1 === 0 ? 0 : 2,
      maximumFractionDigits: 2,
    });
  }

  const totalExpensesAbs = $derived(
    (categoryBreakdown || []).filter((cat) => cat.type !== 'income').reduce((sum, cat) => sum + Math.abs(cat.amount), 0)
  );

  const totalIncomeAbs = $derived(
    (categoryBreakdown || []).filter((cat) => cat.type === 'income').reduce((sum, cat) => sum + Math.abs(cat.amount), 0)
  );

  const grandTotal = $derived(totalExpensesAbs + totalIncomeAbs);

  const normalizedCategories = $derived(
    (categoryBreakdown || []).map((cat) => {
      const isIncome = cat.type === 'income';
      const amountAbs = Math.abs(cat.amount);
      const exactPct = grandTotal > 0 ? (amountAbs / grandTotal) * 100 : 0;
      const displayPercentage = parseFloat(exactPct.toFixed(1));
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
        descriptionText: uniqueDescriptions.join(', '),
      };
    })
  );

  let reportBudgetAlerts = $derived(
    (categoryBreakdown || []).filter((cat) => {
      if (!cat.budget_limit || cat.budget_limit <= 0) return false;
      if (cat.type === 'income') return false;
      return Math.abs(cat.amount) >= cat.budget_limit * 0.8;
    })
  );

  let selectedCategoryId = $state<number | string | null>(null);
  let hoveredCategoryId = $state<number | string | null>(null);
  let openDescriptions = $state<Record<string | number, boolean>>({});
  let isBudgetAlertsOpen = $state(false);

  const activeCategoryId = $derived(hoveredCategoryId ?? selectedCategoryId);
  const activeCategory = $derived(
    normalizedCategories.find((c) => String(c.id) === String(activeCategoryId)) ?? null
  );

  function handleCategoryClick(catId: number | string, fromDonut = false, e?: MouseEvent) {
    if (e) e.stopPropagation();
    if (String(selectedCategoryId) === String(catId)) {
      // إلغاء التحديد يرجّع العرض لـ"الكل"؛ لازم نصفّر الـhover كمان
      // وإلا يظل المؤشر فوق البطاقة فتبقى الفئة محددة بصرياً.
      selectedCategoryId = null;
      hoveredCategoryId = null;
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

  function toggleDescription(catId: number | string, e: MouseEvent) {
    e.stopPropagation();
    openDescriptions[catId] = !openDescriptions[catId];
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

  function resolvePeriodFromDateRange(from?: string, to?: string): string {
    if (!from || !to) {
      return 'this_month';
    }

    const now = new Date();
    const today = formatDate(now);
    const yesterday = formatDate(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1));
    const weekStart = formatDate(new Date(now.getFullYear(), now.getMonth(), now.getDate() - now.getDay()));
    const thisMonthFrom = formatDate(new Date(now.getFullYear(), now.getMonth(), 1));
    const thisMonthTo = formatDate(new Date(now.getFullYear(), now.getMonth() + 1, 0));
    const lastMonthFrom = formatDate(new Date(now.getFullYear(), now.getMonth() - 1, 1));
    const lastMonthTo = formatDate(new Date(now.getFullYear(), now.getMonth(), 0));

    if (from === to && from === today) {
      return 'today';
    }

    if (from === to && from === yesterday) {
      return 'yesterday';
    }

    if (from === weekStart && to === today) {
      return 'this_week';
    }

    if (from === thisMonthFrom && to === thisMonthTo) {
      return 'this_month';
    }

    if (from === lastMonthFrom && to === lastMonthTo) {
      return 'last_month';
    }

    return 'custom';
  }

  const periodOptions = $derived([
    { id: 'today', label: currentLocale === 'en' ? 'Today' : 'اليوم' },
    { id: 'yesterday', label: currentLocale === 'en' ? 'Yesterday' : 'أمس' },
    { id: 'this_week', label: currentLocale === 'en' ? 'This Week' : 'هذا الأسبوع' },
    { id: 'this_month', label: currentLocale === 'en' ? 'This Month' : 'هذا الشهر' },
    { id: 'last_month', label: currentLocale === 'en' ? 'Last Month' : 'الشهر الماضي' },
    { id: 'custom', label: currentLocale === 'en' ? 'Custom Date...' : 'تاريخ مخصص...' }
  ]);

  let isopen = $state(false);
  let selectedPeriod = $state(resolvePeriodFromDateRange(dateRange?.from, dateRange?.to));
  let fromDate = $state(dateRange?.from || todayStr);
  let toDate = $state(dateRange?.to || todayStr);

  $effect(() => {
    if (dateRange?.from) fromDate = dateRange.from;
    if (dateRange?.to) toDate = dateRange.to;
    if (dateRange?.from || dateRange?.to) {
      selectedPeriod = resolvePeriodFromDateRange(dateRange?.from, dateRange?.to);
    }
  });

  const currentLabel = $derived(
    periodOptions.find((p) => p.id === selectedPeriod)?.label ?? (currentLocale === 'en' ? 'This Month' : 'هذا الشهر')
  );

  function applyFilter(from: string, to: string) {
    fromDate = from;
    toDate = to;
    router.get('/reports', { from: fromDate, to: toDate }, { preserveState: true, preserveScroll: true });
  }

  function selectOption(id: string) {
    selectedPeriod = id;
    isopen = false;
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

<svelte:window onclick={() => (selectedCategoryId = null)} />

<AppHead title={currentLocale === 'en' ? 'Reports' : 'التقارير'} />

<div class="flex flex-1 flex-col gap-5 p-4 pb-24 sm:p-6 lg:pb-6 max-w-xl mx-auto w-full">
  <!-- هيدر الصفحة -->
  <div>
    <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
      {currentLocale === 'en' ? 'Reports' : 'التقارير'}
    </h1>
    <p class="text-xs text-muted-foreground mt-0.5">
      {currentLocale === 'en' ? 'Period expenses and details' : 'أرقامك تحكي الكثير'}
    </p>
  </div>

  <!-- مرشح الفترة الزمنية -->
  <div class="p-4 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col gap-3 relative" onclick={(e) => e.stopPropagation()} role="presentation">
    <div class="flex items-center gap-2 text-xs font-bold text-foreground px-0.5">
      <Calendar class="size-4 text-primary" />
      <span>{currentLocale === 'en' ? 'Select Time Period' : 'تحديد فترة زمنية'}</span>
    </div>
    <div class="relative">
      <button
        type="button"
        onclick={() => (isopen = !isopen)}
        class="w-full flex items-center justify-between h-12 bg-muted/30 border border-border/50 rounded-2xl px-4 text-xs font-bold text-foreground hover:bg-muted/50 transition-all duration-200"
      >
        <span>{currentLabel}</span>
        <ChevronDown class="size-4 text-muted-foreground transition-transform duration-300 {isopen ? 'rotate-180 text-primary' : ''}" />
      </button>

      {#if isopen}
        <button type="button" onclick={() => (isopen = false)} class="fixed inset-0 z-20 cursor-default bg-transparent"></button>
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
      <p class="mt-1 text-xs text-muted-foreground">{currentLocale === 'en' ? 'There are no data recorded for the selected period.' : 'لا توجد بيانات مسجلة للفترة المحددة.'}</p>
    </div>
  {:else}
    <!-- رسم الدونات الدائري -->
    <div id="donut-chart-container" class="p-6 rounded-3xl bg-card border border-border/60 shadow-sm flex flex-col items-center gap-4">
      <div class="flex items-center justify-between w-full">
        <h2 class="text-xs font-bold text-foreground">{currentLocale === 'en' ? 'Details' : 'التفاصيل'}</h2>
        {#if selectedCategoryId !== null}
          <button type="button" onclick={(e) => { e.stopPropagation(); selectedCategoryId = null; }} class="text-[11px] font-bold text-primary hover:underline">
            {currentLocale === 'en' ? 'Show All' : 'عرض الكل'}
          </button>
        {/if}
      </div>

      <div class="relative size-60 sm:size-64 flex items-center justify-center my-2">
        <svg class="size-full -rotate-90 transform" viewBox="0 0 100 100">
          <circle cx="50" cy="50" r={RADIUS} fill="transparent" stroke="currentColor" stroke-width="10" class="text-muted/15 cursor-pointer" onclick={() => (selectedCategoryId = null)} />
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
                onclick={(e) => handleCategoryClick(segment.id, true, e)}
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
                {formatAmount(activeCategory.amountAbs)}
              </span>
              <span class="text-xs font-bold text-foreground">{currentLocale === 'en' ? 'SAR' : '⃁'}</span>
            </div>
            <span class="text-xs font-semibold text-muted-foreground mt-0.5">
              {activeCategory.displayPercentage}% {currentLocale === 'en' ? 'of total' : 'من الإجمالي'}
            </span>
          {:else}
            <span class="text-xs text-muted-foreground font-semibold">{currentLocale === 'en' ? 'Total' : 'المجموع'}</span>
            <div class="flex items-center gap-1 mt-0.5" dir="ltr">
              <span class="text-2xl font-black text-foreground tabular-nums">
                {formatAmount(grandTotal)}
              </span>
              <span class="text-xs font-bold text-foreground">{currentLocale === 'en' ? 'SAR' : '⃁'}</span>
            </div>
          {/if}
        </div>
      </div>
    </div>

    <!-- تنبيهات الميزانية -->
    {#if reportBudgetAlerts.length > 0}
      <div class="flex flex-col gap-2.5" onclick={(e) => e.stopPropagation()} role="presentation">
        <h2 class="text-xs font-bold text-foreground px-1">{currentLocale === 'en' ? 'Budget Warnings' : 'تنبيهات الميزانية'}</h2>

        {#if reportBudgetAlerts.length === 1}
          {@const alert = reportBudgetAlerts[0]}
          {@const assignedColor = getCategoryColor(alert.name, alert.color)}
          {@const translatedName = translateCategory(alert.name, currentLocale)}
          {@const amountAbs = Math.abs(alert.amount)}
          {@const limit = alert.budget_limit || 1}
          {@const pct = Math.round((amountAbs / limit) * 100)}
          {@const remaining = limit - amountAbs}
          {@const isExceeded = remaining < 0}

          <div class="p-4 rounded-3xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3 min-w-0">
              <div class="size-8 rounded-xl {isExceeded ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400'} flex items-center justify-center shrink-0">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="flex flex-col min-w-0">
                <span class="text-xs font-bold text-foreground truncate">{translatedName}</span>
                <span class="text-[11px] font-medium text-muted-foreground">
                  {pct}% {currentLocale === 'en' ? 'used' : 'مستهلك من الميزانية'}
                </span>
              </div>
            </div>

            <div class="flex flex-col items-end shrink-0" dir="ltr">
              <span class="text-xs font-black tabular-nums {isExceeded ? 'text-rose-500' : 'text-amber-400'}">
                {isExceeded 
                  ? (currentLocale === 'en' ? `+${formatAmount(Math.abs(remaining))} SAR over` : `تجاوز بـ ${formatAmount(Math.abs(remaining))} ⃁`)
                  : `${formatAmount(remaining)} ${currentLocale === 'en' ? 'SAR left' : '⃁ متبقي'}`}
              </span>
            </div>
          </div>

        {:else}
          <div class="rounded-3xl bg-amber-500/10 border border-amber-500/30 shadow-sm overflow-hidden transition-all">
            <button
              type="button"
              onclick={() => (isBudgetAlertsOpen = !isBudgetAlertsOpen)}
              class="w-full p-4 flex items-center justify-between gap-3 text-start hover:bg-amber-500/5 transition-colors"
            >
              <div class="flex items-center gap-2.5">
                <span class="text-base">⚠️</span>
                <span class="text-xs font-bold text-amber-500">
                  {currentLocale === 'en' 
                    ? `There are ${reportBudgetAlerts.length} budget warnings` 
                    : `يوجد ${reportBudgetAlerts.length} تنبيهات ميزانية`}
                </span>
              </div>
              <ChevronDown class="size-4 text-amber-500 transition-transform duration-300 {isBudgetAlertsOpen ? 'rotate-180' : ''}" />
            </button>

            {#if isBudgetAlertsOpen}
              <div class="px-4 pb-4 pt-2 flex flex-col gap-3.5 border-t border-amber-500/20">
                {#each reportBudgetAlerts as alert}
                  {@const assignedColor = getCategoryColor(alert.name, alert.color)}
                  {@const translatedName = translateCategory(alert.name, currentLocale)}
                  {@const amountAbs = Math.abs(alert.amount)}
                  {@const limit = alert.budget_limit || 1}
                  {@const pct = Math.round((amountAbs / limit) * 100)}
                  {@const remaining = limit - amountAbs}
                  {@const isExceeded = remaining < 0}

                  <div class="flex items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2.5 min-w-0">
                      <div class="size-7 rounded-xl {isExceeded ? 'bg-rose-500/20 text-rose-400' : 'bg-amber-500/20 text-amber-400'} flex items-center justify-center shrink-0">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                      </div>
                      <div class="flex flex-col min-w-0">
                        <span class="font-bold text-foreground truncate">{translatedName}</span>
                        <span class="text-[11px] font-medium text-muted-foreground">
                          {pct}% {currentLocale === 'en' ? 'used' : 'مستهلك من الميزانية'}
                        </span>
                      </div>
                    </div>

                    <div class="flex flex-col items-end shrink-0" dir="ltr">
                      <span class="text-xs font-black tabular-nums {isExceeded ? 'text-rose-500' : 'text-amber-400'}">
                        {isExceeded 
                          ? (currentLocale === 'en' ? `+${formatAmount(Math.abs(remaining))} SAR over` : `تجاوز بـ ${formatAmount(Math.abs(remaining))} ⃁`)
                          : `${formatAmount(remaining)} ${currentLocale === 'en' ? 'SAR left' : '⃁ متبقي'}`}
                      </span>
                    </div>
                  </div>
                {/each}
              </div>
            {/if}
          </div>
        {/if}
      </div>
    {/if}

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
            onclick={(e) => handleCategoryClick(cat.id, false, e)}
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
                    <span class="text-sm font-bold text-foreground truncate">{cat.name}</span>
                    {#if cat.descriptionText}
                      <button
                        type="button"
                        onclick={(e) => toggleDescription(cat.id, e)}
                        class="p-0.5 hover:bg-muted/50 rounded transition-colors"
                      >
                        <ChevronDown class="size-3.5 text-muted-foreground transition-transform duration-200 shrink-0 {openDescriptions[cat.id] ? 'rotate-180 text-primary' : ''}" />
                      </button>
                    {/if}
                  </div>
                  {#if cat.descriptionText && openDescriptions[cat.id]}
                    <span class="text-xs text-muted-foreground max-w-full font-medium mt-0.5 break-words">{cat.descriptionText}</span>
                  {/if}
                  <span class="text-xs font-semibold text-muted-foreground mt-0.5">
                    {cat.displayPercentage}% {currentLocale === 'en' ? 'of total' : 'من الإجمالي'}
                  </span>
                </div>
              </div>
              <div class="flex items-center gap-1 shrink-0" dir="ltr">
                <span class="text-sm font-black tabular-nums {cat.isIncome ? 'text-emerald-500' : 'text-rose-500'}">
                  {cat.isIncome ? '+' : ''}{formatAmount(cat.amountAbs)}
                </span>
                <span class="text-xs font-semibold text-foreground">{currentLocale === 'en' ? 'SAR' : '⃁'}</span>
              </div>
            </div>
          </button>
        {/each}
      </div>
    </div>
  {/if}
</div>