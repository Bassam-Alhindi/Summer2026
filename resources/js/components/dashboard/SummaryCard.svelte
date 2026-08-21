<script lang="ts">
    import { Card, CardContent } from '@/components/ui/card';

    let {
        title,
        value,
        subtitle,
        Icon,
        trend,
        trendLabel,
        colorClass = 'text-foreground',
        class: className = '',
        size = 'default',
    }: {
        title: string;
        value: string;
        subtitle?: string;
        Icon?: any;
        trend?: 'up' | 'down' | 'neutral';
        trendLabel?: string;
        colorClass?: string;
        class?: string;
        size?: 'default' | 'large';
    } = $props();
</script>

<Card class="group hover:bg-card/80 transition-colors {className}">
    <CardContent class="flex flex-col gap-1 {size === 'large' ? 'p-6' : ''}">
        {#if Icon}
            <div class="flex items-center gap-2 mb-1">
                <div
                    class="flex size-8 shrink-0 items-center justify-center rounded-lg {colorClass}"
                >
                    <Icon class="size-4" />
                </div>
                <span class="text-xs text-muted-foreground">{title}</span>
            </div>
        {:else}
            <span class="text-xs text-muted-foreground">{title}</span>
        {/if}
        
        <div class="flex items-baseline gap-2">
            <span dir="ltr" class="{size === 'large' ? 'text-4xl' : 'text-2xl'} font-bold tabular-nums tracking-tight">{value}</span>
        </div>
        
        {#if trend && trendLabel}
            <span
                class="text-xs font-medium {trend === 'up' ? 'text-emerald-600' : trend === 'down' ? 'text-rose-400' : 'text-muted-foreground'}"
            >
                {trendLabel}
            </span>
        {:else if subtitle}
            <span class="text-xs text-muted-foreground">{subtitle}</span>
        {/if}
    </CardContent>
</Card>
