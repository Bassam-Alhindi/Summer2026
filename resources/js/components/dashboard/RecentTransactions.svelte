<script lang="ts">
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { Link } from '@inertiajs/svelte';
    import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
    import Car from 'lucide-svelte/icons/car';
    import Receipt from 'lucide-svelte/icons/receipt';
    import Banknote from 'lucide-svelte/icons/banknote';
    import Film from 'lucide-svelte/icons/film';
    import Heart from 'lucide-svelte/icons/heart';
    import UtensilsCrossed from 'lucide-svelte/icons/utensils-crossed';
    import CircleDollarSign from 'lucide-svelte/icons/circle-dollar-sign';
    import ArrowLeft from 'lucide-svelte/icons/arrow-left';
    import ReceiptText from 'lucide-svelte/icons/receipt-text';
    import GraduationalCap from 'lucide-svelte/icons/graduation-cap';
    import { t, isRTL } from '@/lib/i18n.svelte';
    import { toUrl } from '@/lib/utils';
    import transactions from '@/routes/transactions';

    type Transaction = {
        id: number;
        description: string;
        amount: number;
        type: 'income' | 'expense';
        category: string;
        date: string;
    };

    let { transactions: txList = [], class: className = '', compact = false }: { transactions: Transaction[]; class?: string; compact?: boolean } = $props();

    const categoryIconMap: Record<string, any> = {
        Food: UtensilsCrossed,
        'Food & Drinks': UtensilsCrossed,
        طعام: UtensilsCrossed,
        Grocery: ShoppingBag,
        مقاضي: ShoppingBag,
        Shopping: ShoppingBag,
        تسوق: ShoppingBag,
        Transport: Car,
        Transportation: Car,
        مواصلات: Car,
        Bills: Receipt,
        فواتير: Receipt,
        Salary: Banknote,
        راتب: Banknote,
        Entertainment: Film,
        ترفيه: Film,
        Health: Heart,
        صحة: Heart,
        Education: GraduationalCap,
        تعليم: GraduationalCap,
    };

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

    function getIcon(category: string): any {
        if (!category) return CircleDollarSign;
        const key = category.trim();
        return categoryIconMap[key] ?? categoryIconMap[key.toLowerCase()] ?? CircleDollarSign;
    }

    // دالة ترجمة التصنيفات للقائمة
    function translateCategory(name: string): string {
        if (!name) return '';
        const key = `categories.${name.toLowerCase()}`;
        const translated = t(key as any);
        if (translated && translated !== key) return translated;

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

    let displayTransactions = $derived(compact ? txList.slice(0, 5) : txList);
</script>

<Card class={className}>
    <CardHeader class="pb-3">
        <div class="flex items-center justify-between">
            <div>
                <CardTitle class="text-base">{t('transactions.title')}</CardTitle>
                <p class="text-xs text-muted-foreground">{t('transactions.subtitle')}</p>
            </div>
            {#if compact}
                <Link
                    href={toUrl(transactions.index())}
                    class="inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline"
                >
                    {t('common.viewAll')}
                    <ArrowLeft class="size-3 {isRTL() ? 'rotate-180' : ''}" />
                </Link>
            {/if}
        </div>
    </CardHeader>
    <CardContent>
        {#if displayTransactions.length === 0}
            <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-border/60 bg-muted/10 p-8 text-center my-2">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-muted text-muted-foreground mb-3">
                    <ReceiptText class="h-6 w-6" />
                </div>
                <p class="text-sm font-semibold text-foreground">{t('transactions.empty')}</p>
                <p class="mt-1 text-xs text-muted-foreground max-w-xs">{t('transactions.emptyHint')}</p>
            </div>
        {:else}
            <ul class="flex flex-col gap-1">
                {#each displayTransactions as tx (tx.id)}
                    {@const CategoryIcon = getIcon(tx.category)}
                    {@const catColor = getCategoryColor(tx.category)}
                    
                    <li class="flex items-center gap-3 rounded-lg px-3 py-2.5 transition-colors hover:bg-muted/50 group">
                        <!-- الخلفية والأيقونة تعتمدان على لون التصنيف الموحد -->
                        <div
                            class="flex size-9 shrink-0 items-center justify-center rounded-lg transition-colors"
                            style="background-color: {catColor}20; color: {catColor};"
                        >
                            <CategoryIcon class="size-4" />
                        </div>
                        <div class="flex flex-1 flex-col min-w-0">
                            <span class="text-sm font-medium truncate">{tx.description}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs text-muted-foreground">{tx.date}</span>
                                <span class="text-xs text-muted-foreground/50">&middot;</span>
                                <span class="text-xs font-medium truncate" style="color: {catColor}">
                                    {translateCategory(tx.category)}
                                </span>
                            </div>
                        </div>
                        <span
                            dir="ltr"
                            class="text-sm font-semibold tabular-nums {tx.type === 'income' ? 'text-emerald-600' : 'text-rose-400'}"
                        >
                            {tx.type === 'income' ? '+' : '-'}{Math.abs(tx.amount).toLocaleString('en-SA')} {t('common.currency')}
                        </span>
                    </li>
                {/each}
            </ul>
        {/if}
    </CardContent>
</Card>