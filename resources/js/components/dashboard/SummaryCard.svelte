<script lang="ts">
    import { Card, CardContent } from '@/components/ui/card';
    import { cn } from '@/lib/utils';
    import type { Component } from 'svelte';

    type Props = {
        title: string;
        value: string;
        Icon?: Component<any>;
        trend?: 'up' | 'down';
        trendLabel?: string;
        colorClass?: string;
        size?: 'normal' | 'large';
        class?: string;
    };

    let {
        title,
        value,
        Icon,
        trend,
        trendLabel,
        colorClass = 'text-primary bg-primary/10',
        size = 'normal',
        class: className = '',
    }: Props = $props();

    // التحقق مما إذا كانت الحالة عجزاً أو سالبة
    const isNegative = $derived(trend === 'down' || value.includes('-'));
</script>

<Card class={cn("border-border/60 bg-card/60 backdrop-blur-sm flex flex-col justify-between h-full min-h-[120px]", className)}>
    <CardContent class="p-4 flex flex-col justify-between h-full space-y-3">
        <!-- العنوان والأيقونة -->
        <div class="flex items-center justify-between gap-2">
            <span class="text-xs font-semibold text-muted-foreground truncate">{title}</span>
            {#if Icon}
                <div class={cn("flex size-7 shrink-0 items-center justify-center rounded-lg", colorClass)}>
                    <Icon class="size-4" />
                </div>
            {/if}
        </div>

        <!-- القيمة والنص الفرعي -->
        <div class="flex flex-col justify-end space-y-1">
            <!-- تغيير لون الرقم إلى الأحمر التنبيهي في حال وجود عجز -->
            <div
                dir="ltr"
                class={cn(
                    "text-lg sm:text-2xl font-black tracking-tight tabular-nums text-start whitespace-nowrap overflow-hidden text-ellipsis transition-colors",
                    isNegative ? "text-rose-500" : "text-foreground"
                )}
            >
                {value}
            </div>

            <!-- لون العجز أحمر ولون الفائض أخضر -->
            <div class="h-4 flex items-center">
                {#if trendLabel}
                    <p class={cn(
                        "text-[11px] font-semibold truncate",
                        isNegative ? "text-rose-500" : "text-emerald-500"
                    )}>
                        {trendLabel}
                    </p>
                {/if}
            </div>
        </div>
    </CardContent>
</Card>