<script lang="ts">
    import { Card, CardContent } from '@/components/ui/card';
    import { t } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';
    import { Link } from '@inertiajs/svelte';

    type CategoryData = {
        category: string;
        amount: number;
        color: string;
    };

    type Props = {
        data?: CategoryData[];
        class?: string;
        href?: string;
    };

    let { data = [], class: className = '', href = '/reports' }: Props = $props();

    let activeIndex = $state<number | null>(null);

    // قاموس ترجمة الفئات لدعم اللغتين العربية والإنجليزية
    const CATEGORY_MAP: Record<string, { ar: string; en: string }> = {
        'food & drinks': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
        'food': { ar: 'طعام ومشروبات', en: 'Food & Drinks' },
        'housing': { ar: 'سكن', en: 'Housing' },
        'entertainment': { ar: 'ترفيه', en: 'Entertainment' },
        'health': { ar: 'صحة', en: 'Health' },
        'education': { ar: 'تعليم', en: 'Education' },
        'bills': { ar: 'فواتير', en: 'Bills' },
        'shopping': { ar: 'تسوق', en: 'Shopping' },
        'transportation': { ar: 'مواصلات', en: 'Transportation' },
        'transport': { ar: 'مواصلات', en: 'Transportation' },
        'other': { ar: 'أخرى', en: 'Other' },
        'salary': { ar: 'الراتب', en: 'Salary' },
        'freelance': { ar: 'عمل حر', en: 'Freelance' },
        'investment': { ar: 'استثمار', en: 'Investment' },
        'gift': { ar: 'هدية', en: 'Gift' },
    };

    // دالة لمعرفة ما إذا كانت الواجهة باللغة الإنجليزية
    function isEnglishLang(): boolean {
        try {
            if (typeof document !== 'undefined') {
                const lang = document.documentElement.lang;
                const dir = document.documentElement.dir;
                if (lang === 'en' || dir === 'ltr') return true;
                if (lang === 'ar' || dir === 'rtl') return false;
            }
            const currencyText = t('common.currency');
            return /[a-zA-Z]/.test(currencyText);
        } catch {
            return false;
        }
    }

    // دالة تجلب الترجمة المباشرة أو النص الاحتياطي بحسب اللغة
    function getText(key: string, arFallback: string, enFallback: string): string {
        try {
            const translated = t(key);
            if (translated && translated !== key) return translated;
        } catch {}
        return isEnglishLang() ? enFallback : arFallback;
    }

    function getCategoryName(name: string): string {
        if (!name) return name;

        try {
            const translated = t(name);
            if (translated && translated !== name) return translated;
        } catch {}

        const key = name.toLowerCase().trim();
        const mapped = CATEGORY_MAP[key];

        if (mapped) {
            return isEnglishLang() ? mapped.en : mapped.ar;
        }

        return name;
    }

    const totalAmount = $derived(data.reduce((acc, curr) => acc + Number(curr.amount || 0), 0));

    const processedData = $derived.by(() => {
        let accumulatedPercentage = 0;

        return data.map((item, index) => {
            const amount = Number(item.amount || 0);
            const percentage = totalAmount > 0 ? (amount / totalAmount) * 100 : 0;
            const offset = 100 - accumulatedPercentage;
            accumulatedPercentage += percentage;

            return {
                ...item,
                amount,
                index,
                percentage,
                formattedPercentage: percentage.toFixed(1),
                offset,
            };
        });
    });

    const activeItem = $derived(
        activeIndex !== null && processedData[activeIndex] ? processedData[activeIndex] : null
    );

    function formatNumber(val: number): string {
        return Math.abs(val).toLocaleString('en-US');
    }
</script>

<Card class={cn("border-border/60 bg-card/60 backdrop-blur-sm flex flex-col justify-between overflow-hidden", className)}>
    <CardContent class="p-5 flex flex-col justify-between h-full space-y-4">
        <!-- هيدر الكارت مع رابط الانتقال لصفحة التقارير -->
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold tracking-tight">
                {getText('dashboard.spendingDetails', 'تفاصيل الإنفاق', 'Spending Details')}
            </h3>

            <Link
                {href}
                class="group inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
            >
                <span>{getText('common.viewAll', 'عرض الكل', 'View All')}</span>
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="14"
                    height="14"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    class="transition-transform group-hover:translate-x-0.5 rtl:group-hover:-translate-x-0.5 rtl:rotate-180"
                >
                    <path d="M5 12h14" />
                    <path d="m12 5 7 7-7 7" />
                </svg>
            </Link>
        </div>

        <!-- الرسم البياني الدائري -->
        <div class="relative flex items-center justify-center my-2 select-none">
            <svg class="size-48 -rotate-90 transform drop-shadow-sm" viewBox="0 0 36 36">
                <circle
                    cx="18"
                    cy="18"
                    r="15.9155"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3.5"
                    class="text-muted/15"
                />

                {#if totalAmount > 0}
                    {#each processedData as item (item.index)}
                        <circle
                            cx="18"
                            cy="18"
                            r="15.9155"
                            fill="none"
                            stroke={item.color}
                            stroke-width={activeIndex === item.index ? "5" : "3.8"}
                            stroke-dasharray="{item.percentage} {100 - item.percentage}"
                            stroke-dashoffset={item.offset}
                            stroke-linecap="round"
                            class="transition-all duration-300 cursor-pointer origin-center hover:opacity-100"
                            style="opacity: {activeIndex === null || activeIndex === item.index ? '1' : '0.35'};"
                            onmouseenter={() => (activeIndex = item.index)}
                            onmouseleave={() => (activeIndex = null)}
                            role="button"
                            tabindex="0"
                        />
                    {/each}
                {/if}
            </svg>

            <!-- منتصف الدائرة -->
            <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none p-2">
                {#if activeItem}
                    <span class="text-xs font-semibold text-muted-foreground truncate max-w-[100px] transition-all">
                        {getCategoryName(activeItem.category)}
                    </span>
                    <div class="flex items-baseline gap-1 my-0.5">
                        <span class="text-2xl font-black tabular-nums tracking-tight text-foreground">
                            {formatNumber(activeItem.amount)}
                        </span>
                        <span class="text-xs font-bold text-muted-foreground">
                            {t('common.currency')}
                        </span>
                    </div>
                    <span class="text-[10px] font-bold text-primary">
                        {activeItem.formattedPercentage}% {getText('dashboard.ofSpending', 'من الإنفاق', 'of spending')}
                    </span>
                {:else}
                    <span class="text-[11px] font-medium text-muted-foreground">
                        {getText('summary.totalExpenses', 'إجمالي الإنفاق', 'Total Expenses')}
                    </span>
                    <div class="flex items-baseline gap-1 my-0.5">
                        <span class="text-2xl font-black tabular-nums tracking-tight text-foreground">
                            {formatNumber(totalAmount)}
                        </span>
                        <span class="text-xs font-bold text-muted-foreground">
                            {t('common.currency')}
                        </span>
                    </div>
                {/if}
            </div>
        </div>

        <!-- قائمة الفئات -->
        {#if processedData.length > 0}
            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-border/40">
                {#each processedData as item (item.index)}
                    <button
                        type="button"
                        class={cn(
                            "flex items-center justify-between p-2 rounded-xl text-start transition-all cursor-pointer",
                            activeIndex === item.index ? "bg-muted/80 scale-[1.02]" : "hover:bg-muted/40 opacity-80 hover:opacity-100"
                        )}
                        onmouseenter={() => (activeIndex = item.index)}
                        onmouseleave={() => (activeIndex = null)}
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="size-2.5 rounded-full shrink-0" style="background-color: {item.color};"></span>
                            <span class="text-xs font-semibold truncate text-foreground">
                                {getCategoryName(item.category)}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 shrink-0 ms-1">
                            <span class="text-xs font-bold tabular-nums text-foreground">
                                {formatNumber(item.amount)}
                            </span>
                            <span class="text-[10px] font-semibold text-muted-foreground">
                                {t('common.currency')}
                            </span>
                        </div>
                    </button>
                {/each}
            </div>
        {:else}
            <div class="text-center text-xs text-muted-foreground py-2">
                {getText('common.noData', 'لا توجد بيانات إنفاق', 'No spending data')}
            </div>
        {/if}
    </CardContent>
</Card>