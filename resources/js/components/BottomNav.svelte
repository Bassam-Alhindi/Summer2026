<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Receipt from 'lucide-svelte/icons/receipt';
    import Shapes from 'lucide-svelte/icons/shapes';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Bot from 'lucide-svelte/icons/bot';
    import { currentUrlState } from '@/lib/currentUrl.svelte';
    import { toUrl } from '@/lib/utils';
    import { dashboard, reports, aiAssistant } from '@/routes';
    import transactions from '@/routes/transactions';
    import { t } from '@/lib/i18n.svelte';
    import { cn } from '@/lib/utils';

    const url = currentUrlState();

    const navItems = $derived([
        {
            title: t('nav.dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        },
        {
            title: t('nav.transactions'),
            href: transactions.index(),
            icon: Receipt,
        },
        {
            title: t('nav.reports'),
            href: reports(),
            icon: BarChart3,
        },
        {
            title: t('nav.categories'),
            href: '/categories',
            icon: Shapes,
        },
        {
            title: t('nav.ai'),
            href: aiAssistant(),
            icon: Bot,
        },
    ]);
</script>

<nav class="fixed bottom-0 left-0 right-0 z-50 border-t border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80 lg:hidden">
    <div class="grid h-16 grid-cols-5">
        {#each navItems as item (toUrl(item.href))}
            {@const isActive = url.isCurrentUrl(item.href, url.currentUrl)}
            <Link
                href={toUrl(item.href)}
                class={cn(
                    'flex flex-col items-center justify-center gap-1 transition-colors',
                    isActive
                        ? 'text-primary'
                        : 'text-muted-foreground hover:text-foreground'
                )}
            >
                <item.icon class={cn('size-5', isActive && 'scale-110 transition-transform')} />
                <span class="text-[10px] font-medium">{item.title}</span>
            </Link>
        {/each}
    </div>
</nav>