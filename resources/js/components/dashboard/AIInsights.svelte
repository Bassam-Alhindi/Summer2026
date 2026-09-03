<script lang="ts">
    import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import Lightbulb from 'lucide-svelte/icons/lightbulb';
    import { t } from '@/lib/i18n.svelte';

    type Insight = {
        type: 'tip' | 'warning' | 'alert';
        message: string;
    };

    let { insights = [], class: className = '' }: { insights: Insight[]; class?: string } = $props();

    const iconMap: Record<string, any> = {
        tip: Lightbulb,
        warning: AlertTriangle,
        alert: TrendingDown,
    };

    const colorMap: Record<string, string> = {
        tip: 'text-emerald-600 bg-emerald-500/10',
        warning: 'text-amber-500 bg-amber-500/10',
        alert: 'text-rose-400 bg-rose-500/10',
    };
</script>

<Card class={className}>
    <CardHeader class="pb-3">
        <CardTitle class="flex items-center gap-2 text-base">
            <Sparkles class="size-4 text-primary" />
            {t('insights.title')}
        </CardTitle>
    </CardHeader>
    <CardContent>
        {#if insights.length === 0}
            <div class="flex flex-col items-center justify-center py-8 text-center">
                <Sparkles class="size-8 text-muted-foreground/50" />
                <p class="mt-2 text-sm text-muted-foreground">{t('insights.empty')}</p>
            </div>
        {:else}
            <div class="flex gap-3 overflow-x-auto snap-x snap-mandatory pb-1 -mx-1 px-1 scrollbar-none">
                {#each insights as insight, i (i)}
                    {@const InsightIcon = iconMap[insight.type]}
                    <div class="flex min-w-[260px] max-w-[300px] shrink-0 snap-start items-start gap-3 rounded-lg bg-muted/50 p-4">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {colorMap[insight.type]}"
                        >
                            <InsightIcon class="size-4" />
                        </div>
                        <p dir="ltr" class="text-sm leading-relaxed pt-0.5">{insight.message}</p>
                    </div>
                {/each}
            </div>
        {/if}
    </CardContent>
</Card>
