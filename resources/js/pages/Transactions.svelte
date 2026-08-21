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
    import AppHead from '@/components/AppHead.svelte';
    import { Card, CardContent } from '@/components/ui/card';
    import { Input } from '@/components/ui/input';
    import { Button } from '@/components/ui/button';
    import ShoppingBag from 'lucide-svelte/icons/shopping-bag';
    import Car from 'lucide-svelte/icons/car';
    import Receipt from 'lucide-svelte/icons/receipt';
    import Banknote from 'lucide-svelte/icons/banknote';
    import Film from 'lucide-svelte/icons/film';
    import Heart from 'lucide-svelte/icons/heart';
    import UtensilsCrossed from 'lucide-svelte/icons/utensils-crossed';
    import CircleDollarSign from 'lucide-svelte/icons/circle-dollar-sign';
    import Home from 'lucide-svelte/icons/home';
    import GraduationCap from 'lucide-svelte/icons/graduation-cap';
    import Gift from 'lucide-svelte/icons/gift';
    import Briefcase from 'lucide-svelte/icons/briefcase';
    import TrendingUp from 'lucide-svelte/icons/trending-up';
    import Search from 'lucide-svelte/icons/search';
    import Trash2 from 'lucide-svelte/icons/trash-2';
    import ChevronLeft from 'lucide-svelte/icons/chevron-left';
    import ChevronRight from 'lucide-svelte/icons/chevron-right';
    import { router } from '@inertiajs/svelte';
    import { t } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';

    type CategoryItem = {
        id: number;
        name: string;
        icon: string;
        color: string;
        type: string;
    };

    type TransactionItem = {
        id: number;
        amount: string;
        type: 'income' | 'expense';
        description: string | null;
        transaction_date: string;
        category: CategoryItem;
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
        categories,
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

    const categoryIconMap: Record<string, any> = {
        food: UtensilsCrossed,
        grocery: ShoppingBag,
        shopping: ShoppingBag,
        transport: Car,
        bills: Receipt,
        salary: Banknote,
        entertainment: Film,
        health: Heart,
        housing: Home,
        education: GraduationCap,
        gift: Gift,
        freelance: Briefcase,
        investment: TrendingUp,
        other: CircleDollarSign,
    };

    function getIcon(categoryName: string): any {
        const key = categoryName.toLowerCase().replace(/[^a-z]/g, '');
        for (const [mapKey, icon] of Object.entries(categoryIconMap)) {
            if (key.includes(mapKey)) return icon;
        }
        return CircleDollarSign;
    }

    let searchQuery = $state(filters.search);
    let typeFilter = $state(filters.type || 'all');
    let categoryFilter = $state(filters.category_id || 'all');

    let reloadTimeout: ReturnType<typeof setTimeout> | undefined;

    function applyFilters() {
        if (reloadTimeout) clearTimeout(reloadTimeout);
        reloadTimeout = setTimeout(() => {
            router.get(
                transactions.index.url(),
                {
                    search: searchQuery || '',
                    type: typeFilter === 'all' ? '' : typeFilter,
                    category_id: categoryFilter === 'all' ? '' : categoryFilter,
                },
                { preserveState: true, only: ['transactions'] }
            );
        }, 300);
    }

    function formatAmount(tx: TransactionItem): string {
        const sign = tx.type === 'income' ? '+' : '-';
        return `${sign}${Math.abs(parseFloat(tx.amount)).toLocaleString('en-SA')} ${t('common.currency')}`;
    }

    function formatDate(dateStr: string): string {
        return new Date(dateStr).toLocaleDateString('en-SA', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    }

    function deleteTransaction(id: number) {
        if (!confirm(t('transactions.deleteConfirm'))) return;
        router.delete(transactions.destroy.url(id), {
            preserveScroll: true,
        });
    }

    function goToPage(url: string) {
        router.get(url, {}, { preserveState: true, only: ['transactions'] });
    }

    const selectClass =
        'h-9 appearance-none rounded-lg border border-input bg-background px-3 pe-8 text-sm font-medium text-foreground shadow-sm outline-none transition-colors hover:bg-muted/50 focus:border-ring focus:ring-2 focus:ring-ring/30 cursor-pointer';
</script>

<AppHead title={t('transactions.pageTitle')} />

<div class="flex flex-1 flex-col gap-4 p-4 pb-24 sm:p-6 lg:pb-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('transactions.pageTitle')}</h1>
        <p class="text-sm text-muted-foreground">{t('transactions.pageSubtitle')}</p>
    </div>

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <Search class="absolute start-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
            <Input
                bind:value={searchQuery}
                oninput={applyFilters}
                placeholder={t('transactions.searchPlaceholder')}
                class="ps-9"
            />
        </div>

        <div class="flex gap-2">
            <div class="relative">
                <select bind:value={typeFilter} onchange={applyFilters} class={selectClass}>
                    <option value="all">{t('transactions.allTypes')}</option>
                    <option value="income">{t('transactions.income')}</option>
                    <option value="expense">{t('transactions.expense')}</option>
                </select>
            </div>

            <div class="relative">
                <select bind:value={categoryFilter} onchange={applyFilters} class={selectClass}>
                    <option value="all">{t('transactions.allCategories')}</option>
                    {#each categories as cat}
                        <option value={String(cat.id)}>{cat.name}</option>
                    {/each}
                </select>
            </div>
        </div>
    </div>

    <Card>
        <CardContent class="p-0">
            {#if transactionsProp.data.length === 0}
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <p class="text-sm font-medium">{t('transactions.noTransactions')}</p>
                    <p class="mt-1 text-xs text-muted-foreground">{t('transactions.noTransactionsHint')}</p>
                </div>
            {:else}
                <ul class="divide-y divide-border">
                    {#each transactionsProp.data as tx (tx.id)}
                        {@const CategoryIcon = getIcon(tx.category.name)}
                        <li class="flex items-center gap-3 px-4 py-3 transition-colors hover:bg-muted/50">
                            <div
                                class={cn(
                                    'flex size-10 shrink-0 items-center justify-center rounded-lg',
                                    tx.type === 'income' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-400'
                                )}
                            >
                                <CategoryIcon class="size-5" />
                            </div>
                            <div class="flex flex-1 flex-col min-w-0">
                                <span class="text-sm font-medium">{tx.description || tx.category.name}</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs text-muted-foreground">{formatDate(tx.transaction_date)}</span>
                                    <span class="text-xs text-muted-foreground/50">&middot;</span>
                                    <span class="text-xs text-muted-foreground">{tx.category.name}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    dir="ltr"
                                    class={cn(
                                        'text-sm font-semibold tabular-nums',
                                        tx.type === 'income' ? 'text-emerald-600' : 'text-rose-400'
                                    )}
                                >
                                    {formatAmount(tx)}
                                </span>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="size-8 text-muted-foreground hover:text-destructive"
                                    onclick={() => deleteTransaction(tx.id)}
                                >
                                    <Trash2 class="size-4" />
                                </Button>
                            </div>
                        </li>
                    {/each}
                </ul>
            {/if}
        </CardContent>
    </Card>

    {#if transactionsProp.last_page > 1}
        <div class="flex items-center justify-between">
            <p class="text-xs text-muted-foreground">
                {transactionsProp.from}–{transactionsProp.to} / {transactionsProp.total}
            </p>
            <div class="flex items-center gap-1">
                {#each transactionsProp.links as link}
                    {#if link.label === '&laquo; Previous'}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronLeft class="size-4" />
                        </Button>
                    {:else if link.label === 'Next &raquo;'}
                        <Button
                            variant="outline"
                            size="icon"
                            class="size-8"
                            disabled={!link.url}
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            <ChevronRight class="size-4" />
                        </Button>
                    {:else}
                        <Button
                            variant={link.active ? 'default' : 'outline'}
                            size="icon"
                            class="size-8 text-xs"
                            onclick={() => link.url && goToPage(link.url)}
                        >
                            {link.label}
                        </Button>
                    {/if}
                {/each}
            </div>
        </div>
    {/if}
</div>
