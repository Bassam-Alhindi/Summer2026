<script lang="ts">
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import { ChartContainer, type ChartConfig } from '@/components/ui/chart';
    import { PieChart } from 'layerchart';
    import { getLocale, t } from '@/lib/i18n.svelte';

    type CategoryData = {
        category: string;
        amount: number;
        color: string;
    };

    let { data = [], class: className = '' }: { data: CategoryData[]; class?: string } = $props();

    const lang = $derived(getLocale());

    const categoryLabels = $derived({
        food: t('chart.food'),
        transport: t('chart.transport'),
        shopping: t('chart.shopping'),
        entertainment: t('chart.entertainment'),
        bills: t('chart.bills'),
        health: t('chart.health'),
        other: t('chart.other'),
    } as Record<string, string>);

    const chartConfig = $derived<ChartConfig>({
        food: { label: categoryLabels.food, color: '#ef4444' },
        transport: { label: categoryLabels.transport, color: '#f59e0b' },
        shopping: { label: categoryLabels.shopping, color: '#8b5cf6' },
        entertainment: { label: categoryLabels.entertainment, color: '#3b82f6' },
        bills: { label: categoryLabels.bills, color: '#10b981' },
        health: { label: categoryLabels.health, color: '#ec4899' },
        other: { label: categoryLabels.other, color: '#6b7280' },
    });

    const pieData = $derived(
        data.map((d) => ({
            key: d.category,
            label: categoryLabels[d.category] ?? d.category,
            value: d.amount,
        }))
    );

    const totalExpenses = $derived(data.reduce((sum, d) => sum + d.amount, 0));
</script>

<Card class={className}>
    <CardHeader class="pb-3">
        <CardTitle class="text-base">{t('chart.title')}</CardTitle>
        <p class="text-xs text-muted-foreground">{t('chart.subtitle')}</p>
    </CardHeader>
    <CardContent class="flex items-center justify-center">
        {#if pieData.length === 0}
            <div class="flex h-48 items-center justify-center">
                <p class="text-sm text-muted-foreground">{t('chart.noData')}</p>
            </div>
        {:else}
            <div class="flex flex-col items-center gap-4 w-full">
                <ChartContainer config={chartConfig} class="aspect-square h-[200px]">
                    <PieChart
                        data={pieData}
                        key="key"
                        label="label"
                        value="value"
                        innerRadius={0.65}
                        padAngle={0.03}
                        cornerRadius={4}
                    />
                </ChartContainer>
                <div class="text-center">
                    <p dir="ltr" class="text-2xl font-bold tabular-nums">{totalExpenses.toLocaleString('en-SA')}</p>
                    <p class="text-xs text-muted-foreground">{t('chart.totalSpent')} ({t('common.currency')})</p>
                </div>
            </div>
        {/if}
    </CardContent>
</Card>
