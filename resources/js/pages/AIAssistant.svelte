<script module lang="ts">
    import { aiAssistant } from '@/routes';

    export const layout = {
        breadcrumbs: [
            {
                title: 'AI Assistant',
                href: aiAssistant(),
            },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { Card, CardContent } from '@/components/ui/card';
    import { Button } from '@/components/ui/button';
    import Sparkles from 'lucide-svelte/icons/sparkles';
    import Send from 'lucide-svelte/icons/send';
    import TrendingDown from 'lucide-svelte/icons/trending-down';
    import AlertTriangle from 'lucide-svelte/icons/alert-triangle';
    import Lightbulb from 'lucide-svelte/icons/lightbulb';
    import { t, isRTL } from '@/lib/i18n.svelte';

    type Message = {
        id: number;
        role: 'user' | 'assistant';
        content: string;
        type?: 'tip' | 'warning' | 'alert' | 'info';
    };

    type Insight = {
        type: 'tip' | 'warning' | 'alert';
        messageKey: string;
    };

    let inputValue = $state('');

    let messages: Message[] = $state([
        {
            id: 1,
            role: 'assistant',
            content: t('ai.welcomeMessage'),
            type: 'info',
        },
    ]);

    const insights: Insight[] = [
        { type: 'tip', messageKey: 'ai.insight.foodHigh' },
        { type: 'warning', messageKey: 'ai.insight.incomeSpent' },
        { type: 'tip', messageKey: 'ai.insight.transportLow' },
        { type: 'alert', messageKey: 'ai.insight.subscription' },
    ];

    const iconMap: Record<string, any> = {
        tip: Lightbulb,
        warning: AlertTriangle,
        alert: TrendingDown,
    };

    const colorMap: Record<string, string> = {
        tip: 'text-emerald-600 bg-emerald-500/10',
        warning: 'text-amber-500 bg-amber-500/10',
        alert: 'text-rose-400 bg-rose-500/10',
        info: 'text-blue-500 bg-blue-500/10',
    };

    function sendMessage() {
        if (!inputValue.trim()) return;
        inputValue = '';
    }

    const quickActionKeys = [
        'ai.action.summary',
        'ai.action.analyze',
        'ai.action.tips',
        'ai.action.anomalies',
    ];
</script>

<AppHead title={t('ai.title')} />

<div class="flex h-full flex-1 flex-col gap-4 p-4 pb-24 sm:p-6 lg:pb-6">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-2">
            <Sparkles class="size-5 text-primary" />
            <h1 class="text-xl font-bold tracking-tight sm:text-2xl">{t('ai.title')}</h1>
        </div>
        <p class="text-sm text-muted-foreground">{t('ai.subtitle')}</p>
    </div>

    <div class="grid flex-1 grid-cols-1 gap-4 md:grid-cols-3">
        <div class="flex flex-col gap-4 md:col-span-2">
            <Card class="flex flex-1 flex-col">
                <CardContent class="flex flex-1 flex-col gap-3 p-4">
                    <div class="flex flex-1 flex-col gap-3 overflow-y-auto">
                        {#each messages as msg (msg.id)}
                            <div class="flex {msg.role === 'user' ? 'justify-end' : 'justify-start'}">
                                <div
                                    class="max-w-[85%] rounded-2xl px-4 py-2.5 text-sm leading-relaxed {msg.role === 'user'
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-muted/70 text-foreground'}"
                                >
                                    {msg.content}
                                </div>
                            </div>
                        {/each}
                    </div>

                    <div class="flex gap-2 border-t border-border/50 pt-3">
                        <input
                            bind:value={inputValue}
                            placeholder={t('ai.typePlaceholder')}
                            class="flex-1 rounded-xl border border-input bg-background px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-ring/50"
                            onkeydown={(e) => e.key === 'Enter' && sendMessage()}
                        />
                        <Button size="icon" class="size-10 rounded-xl" onclick={sendMessage}>
                            <Send class="size-4 {isRTL() ? 'rotate-180' : ''}" />
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <div class="flex flex-wrap gap-2">
                {#each quickActionKeys as key}
                    <button
                        type="button"
                        class="rounded-full border border-border bg-background px-3 py-1.5 text-xs font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                    >
                        {t(key)}
                    </button>
                {/each}
            </div>
        </div>

        <div class="flex flex-col gap-3">
            <h3 class="text-sm font-semibold">{t('insights.title')}</h3>
            <div class="flex flex-col gap-3 overflow-y-auto">
                {#each insights as insight, i (i)}
                    {@const InsightIcon = iconMap[insight.type]}
                    <div class="flex items-start gap-3 rounded-lg bg-muted/50 p-3">
                        <div
                            class="flex size-8 shrink-0 items-center justify-center rounded-lg {colorMap[insight.type]}"
                        >
                            <InsightIcon class="size-4" />
                        </div>
                        <p dir="ltr" class="text-xs leading-relaxed pt-0.5">{t(insight.messageKey)}</p>
                    </div>
                {/each}
            </div>
        </div>
    </div>
</div>
