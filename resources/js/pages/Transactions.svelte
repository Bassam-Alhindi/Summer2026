<script module lang="ts">
    import transactions from '@/routes/transactions';

    export const layout = {
        breadcrumbs: [
            {
                title: 'Transactions',
                href: transactions.index.url(),
            },
        ],
    };
</script>

<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import { Receipt, Trash2, ChevronLeft, ChevronRight, ChevronDown, Check, Filter } from 'lucide-svelte';
    import { flip } from 'svelte/animate';
    import { fade, slide, fly, scale } from 'svelte/transition';
    import { toast } from 'svelte-sonner';
    import AppHead from '@/components/AppHead.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import { Button } from '@/components/ui/button';
    import { Card, CardContent } from '@/components/ui/card';
    import { resolveCategoryMeta, sortFoodFirst } from '@/lib/categories';
    import { getLocale, t } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';

    type CategoryItem = {
        id: number;
        name: string;
        type?: string;
        color?: string;
        icon?: string;
    };

    type TransactionItem = {
        id: number;
        amount: string;
        type: 'income' | 'expense';
        description: string | null;
        transaction_date: string;
        category: CategoryItem | null;
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
        categories = [],
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

    let selectedTxId = $state<number | null>(null);
    let typeFilter = $state(filters.type || 'all');
    let categoryFilter = $state(filters.category_id || 'all');
    let isCategoryDropdownOpen = $state(false);

    // مرشح التاريخ: يوم واحد يُرسل كـ from/to لنفس التاريخ.
    let dateFilter = $state(filters.from || '');

    let availableCategories = $derived.by(() => {
        const seenIds = new Set<number>();
        const seenKeys = new Set<string>();
        const result: CategoryItem[] = [];

        const normalize = (str: string) => {
            let s = (str ?? '').toLowerCase().trim();
            if (!s) return '';
            
            if (s === 'هدايا' || s === 'هدية' || s === 'gifts' || s === 'gift') return 'gift';

            if (s.length > 3 && s.endsWith('s')) {
                s = s.slice(0, -1);
            }
            
            return s.replace(/ات$/, '').replace(/[ةه]$/, '');
        };

        for (const cat of categories) {
            if (seenIds.has(cat.id)) continue;

            // فلترة الفئات حسب نوع التبويب النشط (مصروفات / دخل)
            if (typeFilter !== 'all' && cat.type && cat.type !== typeFilter) {
                continue;
            }

            const meta = resolveCategoryMeta(cat.name ?? '', cat.color, cat.icon);
            const canonical = meta.en.toLowerCase().trim();
            const localizedAr = meta.ar.toLowerCase().trim();
            const rawName = (cat.name ?? '').toLowerCase().trim();

            // Exclude "Other" / "Other Income" (أخرى / دخل آخر)
            if (canonical.includes('other') || localizedAr.includes('أخرى') || localizedAr.includes('دخل آخر')) continue;

            const normEn = normalize(canonical);
            const normAr = normalize(localizedAr);
            const normRaw = normalize(rawName);

            if (
                (normEn && seenKeys.has(normEn)) ||
                (normAr && seenKeys.has(normAr)) ||
                (normRaw && seenKeys.has(normRaw))
            ) {
                continue;
            }

            seenIds.add(cat.id);
            if (normEn) seenKeys.add(normEn);
            if (normAr) seenKeys.add(normAr);
            if (normRaw) seenKeys.add(normRaw);

            result.push(cat);
        }

        return sortFoodFirst(result);
    });

    let typeTabs = $derived.by(() => {
        return [
            { id: 'all', label: t('transactions.all'), type: 'all' },
            { id: 'expense', label: t('transactions.expense'), type: 'expense' },
            { id: 'income', label: t('transactions.income'), type: 'income' },
        ];
    });

    function applyFilters() {
        router.get(
            transactions.index.url(),
            {
                type: typeFilter === 'all' ? '' : typeFilter,
                category_id: categoryFilter === 'all' ? '' : categoryFilter,
                from: dateFilter,
                to: dateFilter,
            },
            { preserveState: true, only: ['transactions'] }
        );
    }

    function changeType(val: string) {
        typeFilter = val;
        categoryFilter = 'all'; // إعادة ضبط الفئة عند تغيير التبويب
        applyFilters();
    }

    function selectCategory(catId: string) {
        categoryFilter = catId;
        isCategoryDropdownOpen = false;
        applyFilters();
    }

    function toggleSelectTx(id: number) {
        selectedTxId = selectedTxId === id ? null : id;
    }

    function formatAmount(tx: TransactionItem): string {
        const val = Math.abs(parseFloat(tx.amount));
        const hasDecimals = val % 1 !== 0;

        const num = val.toLocaleString('en-US', {
            minimumFractionDigits: hasDecimals ? 2 : 0,
            maximumFractionDigits: 2,
        });
        
        return tx.type === 'income' ? `+${num}` : `-${num}`;
    }

    function formatDate(dateStr: string): string {
        if (!dateStr) return '';
        const target = new Date(dateStr);
        if (isNaN(target.getTime())) return dateStr;

        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const targetDay = new Date(target.getFullYear(), target.getMonth(), target.getDate());

        const diffTime = today.getTime() - targetDay.getTime();
        const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays === 0) {
            return t('transactions.today');
        } else if (diffDays === 1) {
            return t('transactions.yesterday');
        } else {
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return `${target.getDate()} ${monthNames[target.getMonth()]}`;
        }
    }

    function deleteTransaction(id: number, event: MouseEvent) {
        event.stopPropagation();
        if (!confirm(t('transactions.deleteConfirm'))) return;

        router.delete(transactions.destroy.url(id), {
            preserveScroll: true,
            onSuccess: () => {
                selectedTxId = null;
            },
            onError: () => {
                toast.error(t('transactions.deleteError'));
            },
        });
    }

    function goToPage(url: string) {
        router.get(url, {}, { preserveState: true, only: ['transactions'] });
    }

    let currentSelectedCategory = $derived(
        availableCategories.find((c) => String(c.id) === String(categoryFilter))
    );
</script>

<AppHead title={t('transactions.title')} />

<div class="flex flex-1 flex-col gap-4 p-4 pb-24 sm:p-6 lg:pb-6">
    <!-- الهيدر -->
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">
                {t('transactions.title')}
            </h1>
            <p class="mt-1 text-xs text-muted-foreground sm:text-sm">
                {t('transactions.subtitle')}
            </p>
        </div>
    </div>

    <!-- تبويبات التصفية -->
    <div class="inline-flex p-1 bg-muted/60 rounded-2xl gap-1 w-full border border-border/40">
        {#each typeTabs as tab}
            {@const isActive = typeFilter === tab.id}
            <button
                type="button"
                onclick={() => changeType(tab.id)}
                class={cn(
                    "flex-1 px-4 py-2.5 text-xs font-bold rounded-xl transition-all duration-300 flex items-center justify-center gap-1.5 active:scale-95",
                    isActive && tab.id === 'all' && "bg-card text-foreground shadow-xs border border-border/50",
                    isActive && tab.id === 'expense' && "bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/30 shadow-xs",
                    isActive && tab.id === 'income' && "bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 shadow-xs",
                    !isActive && "text-muted-foreground hover:text-foreground hover:bg-muted/40"
                )}
            >
                <span>{tab.label}</span>
            </button>
        {/each}
    </div>

    <!-- مرشحا الفئة والتاريخ جنباً إلى جنب -->
    <div class="flex flex-wrap items-start gap-2">
        <div class="relative z-30 min-w-0 flex-1 basis-40">
            <button
                type="button"
                onclick={() => (isCategoryDropdownOpen = !isCategoryDropdownOpen)}
                class={cn(
                    "w-full flex items-center justify-between h-11 px-4 rounded-2xl bg-card border border-border/50 text-sm font-medium transition-all duration-200 hover:bg-muted/30 active:scale-[0.99]",
                    isCategoryDropdownOpen && "ring-2 ring-primary/20 bg-card border-primary/40 shadow-sm"
                )}
            >
                <div class="flex items-center gap-2.5 truncate">
                    {#if currentSelectedCategory}
                        {@const selMeta = resolveCategoryMeta(currentSelectedCategory.name ?? '', currentSelectedCategory.color, currentSelectedCategory.icon)}
                        {@const SelectedIcon = selMeta.icon}
                        <div
                            class="size-6 rounded-lg flex items-center justify-center shrink-0 border"
                            style="background-color: color-mix(in srgb, {selMeta.color} 15%, transparent); color: {selMeta.color}; border-color: color-mix(in srgb, {selMeta.color} 30%, transparent);"
                        >
                            <SelectedIcon class="size-3.5" />
                        </div>
                        <span class="font-bold text-foreground text-xs sm:text-sm truncate">
                            {getLocale() === 'ar' ? selMeta.ar : selMeta.en}
                        </span>
                    {:else}
                        <div class="size-6 rounded-lg bg-muted/80 text-muted-foreground flex items-center justify-center shrink-0 border border-border/40">
                            <Filter class="size-3.5" />
                        </div>
                        <span class="text-muted-foreground text-xs sm:text-sm font-medium">
                            {t('transactions.selectCategory')}
                        </span>
                    {/if}
                </div>

                <ChevronDown
                    class={cn(
                        "size-4 text-muted-foreground transition-transform duration-300 shrink-0",
                        isCategoryDropdownOpen && "rotate-180 text-foreground"
                    )}
                />
            </button>

            {#if isCategoryDropdownOpen}
                <button
                    type="button"
                    aria-label="Close"
                    transition:fade={{ duration: 100 }}
                    onclick={() => (isCategoryDropdownOpen = false)}
                    class="fixed inset-0 z-40 bg-black/20 backdrop-blur-[1px]"
                ></button>

                <div
                    transition:fly={{ y: -6, duration: 150 }}
                    class="absolute top-full start-0 end-0 mt-2 z-50 bg-popover text-popover-foreground rounded-2xl p-1.5 shadow-2xl border border-border/60 max-h-60 overflow-y-auto space-y-1"
                >
                    <button
                        type="button"
                        onclick={() => selectCategory('all')}
                        class={cn(
                            "w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-colors",
                            categoryFilter === 'all'
                                ? "bg-primary/10 text-primary border border-primary/20"
                                : "text-foreground hover:bg-muted/60"
                        )}
                    >
                        <div class="flex items-center gap-2.5">
                            <div class="size-6 rounded-lg bg-muted text-muted-foreground flex items-center justify-center shrink-0 border border-border/40">
                                <Filter class="size-3.5" />
                            </div>
                            <span>{t('transactions.selectCategory')}</span>
                        </div>
                        {#if categoryFilter === 'all'}
                            <Check class="size-4 text-primary" />
                        {/if}
                    </button>

                    {#each availableCategories as cat}
                        {@const catMeta = resolveCategoryMeta(cat.name ?? '', cat.color, cat.icon)}
                        {@const CatIcon = catMeta.icon}
                        {@const isSelected = String(categoryFilter) === String(cat.id)}
                        <button
                            type="button"
                            onclick={() => selectCategory(String(cat.id))}
                            class={cn(
                                "w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-colors",
                                isSelected
                                    ? "bg-primary/10 text-primary border border-primary/20"
                                    : "text-foreground hover:bg-muted/60"
                            )}
                        >
                            <div class="flex items-center gap-2.5 truncate">
                                <div
                                    class="size-6 rounded-lg flex items-center justify-center shrink-0 border"
                                    style="background-color: color-mix(in srgb, {catMeta.color} 15%, transparent); color: {catMeta.color}; border-color: color-mix(in srgb, {catMeta.color} 30%, transparent);"
                                >
                                    <CatIcon class="size-3.5" />
                                </div>
                                <span class="truncate font-bold">{getLocale() === 'ar' ? catMeta.ar : catMeta.en}</span>
                            </div>
                            {#if isSelected}
                                <Check class="size-4 text-primary" />
                            {/if}
                        </button>
                    {/each}
                </div>
            {/if}
        </div>

        <!-- مرشح التاريخ: نفس منتقي "الإضافة السريعة"، بعرض مضغوط -->
        <DatePicker
            bind:value={dateFilter}
            class="min-w-0 flex-1 basis-28 sm:w-32 sm:flex-none sm:basis-auto"
            triggerClass="h-11 rounded-2xl bg-card text-xs sm:text-sm"
            placeholder={t('transactions.date')}
            ariaLabel={t('transactions.date')}
            clearable
            onselect={applyFilters}
        />
    </div>

    <!-- قائمة المعاملات -->
    <Card class="border border-border/40 shadow-xs overflow-hidden rounded-2xl bg-card">
        <CardContent class="p-0">
            {#if transactionsProp.data.length === 0}
                <div 
                    in:fade={{ duration: 200 }}
                    class="flex flex-col items-center justify-center py-14 text-center px-4"
                >
                    <div class="mb-3 flex size-16 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground/70 ring-8 ring-muted/20 border border-border/40">
                        <Receipt class="size-8" />
                    </div>
                    <p class="text-sm font-bold">
                        {t('transactions.emptyState')}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {t('transactions.emptyHint')}
                    </p>
                </div>
            {:else}
                <ul class="divide-y divide-border/30">
                    {#each transactionsProp.data as tx (tx.id)}
                        {@const meta = resolveCategoryMeta(tx.category?.name ?? '', tx.category?.color, tx.category?.icon)}
                        {@const IconComponent = meta.icon}
                        {@const isSelected = selectedTxId === tx.id}
                        {@const isIncome = tx.type === 'income'}

                        <li 
                            animate:flip={{ duration: 300 }}
                            in:fly={{ y: 8, duration: 200 }}
                            out:slide={{ duration: 250 }}
                            role="button"
                            tabindex="0"
                            onclick={() => toggleSelectTx(tx.id)}
                            onkeydown={(e) => (e.key === 'Enter' || e.key === ' ') && toggleSelectTx(tx.id)}
                            class={cn(
                                "group relative flex items-center gap-3 ps-5 pe-4 py-3.5 transition-all duration-200 cursor-pointer select-none outline-none focus:outline-none active:scale-[0.998]",
                                isSelected 
                                    ? "bg-primary/[0.06] dark:bg-primary/[0.1]" 
                                    : "hover:bg-muted/30"
                            )}
                        >
                            {#if isSelected}
                                <span 
                                    transition:scale={{ duration: 150, start: 0.7 }}
                                    class="absolute start-2 top-3 bottom-3 w-1 rounded-full bg-primary shadow-xs shadow-primary/50"
                                ></span>
                            {/if}

                            <!-- أيقونة الفئة -->
                            <div
                                class="flex size-11 shrink-0 items-center justify-center rounded-xl shadow-xs transition-transform duration-200 group-hover:scale-105 border"
                                style="background-color: color-mix(in srgb, {meta.color} 14%, transparent); color: {meta.color}; border-color: color-mix(in srgb, {meta.color} 28%, transparent);"
                            >
                                <IconComponent class="size-5" />
                            </div>

                            <!-- التفاصيل -->
                            <div class="flex flex-1 flex-col min-w-0">
                                <span class="text-sm font-bold text-foreground truncate">
                                    {getLocale() === 'ar' ? meta.ar : meta.en}
                                </span>
                                <span class="text-xs text-muted-foreground font-medium mt-0.5">
                                    {formatDate(tx.transaction_date)}
                                </span>
                            </div>

                            <!-- المبلغ والعملة -->
                            <div class="flex items-center">
                                <div 
                                    dir="ltr" 
                                    class={cn(
                                        "flex items-center gap-1 px-2.5 py-1 rounded-lg font-bold tabular-nums text-xs sm:text-sm border transition-colors duration-200",
                                        isIncome 
                                            ? "text-emerald-600 dark:text-emerald-400 bg-emerald-500/10 border-emerald-500/20" 
                                            : "text-rose-600 dark:text-rose-400 bg-rose-500/10 border-rose-500/20"
                                    )}
                                >
                                    <span>{formatAmount(tx)}</span>
                                    <span class="text-[11px] font-semibold inline-block" style="filter: brightness(0) invert(1);">⃁</span>
                                </div>
                                <!-- زر الحذف فقط -->
                                <div
                                    class={cn(
                                        "flex items-center justify-end gap-1 transition-all duration-300 ease-out overflow-hidden shrink-0",
                                        isSelected ? "w-[2.5rem] opacity-100 scale-100 ms-2" : "w-0 opacity-0 scale-75 ms-0 pointer-events-none"
                                    )}
                                >
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title={t('transactions.deleteTitle')}
                                        tabindex={isSelected ? 0 : -1}
                                        class="size-8 text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-xl shrink-0 transition-all duration-200 active:scale-90"
                                        onclick={(e) => deleteTransaction(tx.id, e)}
                                    >
                                        <Trash2 class="size-4" />
                                    </Button>
                                </div>
                            </div>
                        </li>
                    {/each}
                </ul>
            {/if}
        </CardContent>
    </Card>

    <!-- الترقيم (Pagination) -->
    {#if transactionsProp.last_page > 1}
        <div class="flex items-center justify-between pt-1">
            <p class="text-xs font-semibold text-muted-foreground">
                {transactionsProp.from}–{transactionsProp.to} / {transactionsProp.total}
            </p>
            <div class="flex items-center gap-1">
                {#each transactionsProp.links as link}
                    {#if link.label.includes('Previous') || link.label.includes('السابق') || link.label.includes('&laquo;')}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8 rounded-xl transition-transform active:scale-90 border-border/50"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronLeft class="size-4 rtl:rotate-180" />
                        </Button>
                    {:else if link.label.includes('Next') || link.label.includes('التالي') || link.label.includes('&raquo;')}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8 rounded-xl transition-transform active:scale-90 border-border/50"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronRight class="size-4 rtl:rotate-180" />
                        </Button>
                    {:else}
                        <Button
                            variant={link.active ? 'default' : 'outline'}
                            size="icon"
                            class="size-8 rounded-xl text-xs font-bold transition-transform active:scale-90 border-border/50"
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            {@html link.label}
                        </Button>
                    {/if}
                {/each}
            </div>
        </div>
    {/if}
</div>