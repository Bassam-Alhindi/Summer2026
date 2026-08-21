<script lang="ts">
    import { t } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';

    let { period = $bindable('month') }: { period?: string } = $props();

    const options = [
        { value: 'week', labelKey: 'dashboard.period.week' },
        { value: 'month', labelKey: 'dashboard.period.month' },
        { value: 'year', labelKey: 'dashboard.period.year' },
    ];
</script>

<div class="inline-flex h-9 items-center gap-0.5 rounded-xl bg-muted/60 p-1 dark:bg-zinc-800/80" role="tablist">
    {#each options as option}
        <button
            type="button"
            role="tab"
            aria-selected={period === option.value}
            onclick={() => (period = option.value)}
            class={cn(
                'relative flex h-full min-w-[72px] items-center justify-center rounded-lg px-3 text-xs font-medium transition-all duration-200',
                period === option.value
                    ? 'bg-background text-foreground shadow-sm dark:bg-zinc-700 dark:text-white'
                    : 'text-muted-foreground hover:text-foreground'
            )}
        >
            {t(option.labelKey)}
        </button>
    {/each}
</div>
