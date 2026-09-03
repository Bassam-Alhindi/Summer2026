<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import LayoutGrid from 'lucide-svelte/icons/layout-grid';
    import Receipt from 'lucide-svelte/icons/receipt';
    import BarChart3 from 'lucide-svelte/icons/bar-chart-3';
    import Bot from 'lucide-svelte/icons/bot';
    import LogOut from 'lucide-svelte/icons/log-out';
    import type { Snippet } from 'svelte';
    import AppLogo from '@/components/AppLogo.svelte';
    import LanguageSwitcher from '@/components/LanguageSwitcher.svelte';
    import NavMain from '@/components/NavMain.svelte';
    import NavUser from '@/components/NavUser.svelte';
    import {
        Sidebar,
        SidebarContent,
        SidebarFooter,
        SidebarHeader,
        SidebarMenu,
        SidebarMenuButton,
        SidebarMenuItem,
    } from '@/components/ui/sidebar';
    import { toUrl } from '@/lib/utils';
    import { dashboard, reports, aiAssistant, logout } from '@/routes';
    import transactions from '@/routes/transactions';
    import type { NavItem } from '@/types';
    import { t } from '@/lib/i18n.svelte';

    let {
        children,
    }: {
        children?: Snippet;
    } = $props();

    const mainNavItems: NavItem[] = $derived([
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
            title: t('nav.ai'),
            href: aiAssistant(),
            icon: Bot,
        },
    ]);
</script>

<Sidebar collapsible="icon" variant="inset">
    <SidebarHeader>
        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton size="lg">
                    {#snippet child({ props })}
                        <Link
                            {...props}
                            href={toUrl(dashboard())}
                            class={props.class}
                        >
                            <AppLogo />
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
        <NavMain items={mainNavItems} />
    </SidebarContent>

    <SidebarFooter>
        <LanguageSwitcher />
        <NavUser />

        <SidebarMenu>
            <SidebarMenuItem>
                <SidebarMenuButton tooltip={t('nav.logout')}>
                    {#snippet child({ props })}
                        <Link
                            {...props}
                            href={logout()}
                            as="button"
                            data-test="sidebar-logout-button"
                            class="{props.class} w-full text-rose-500 hover:bg-rose-500/10 hover:text-rose-400"
                        >
                            <LogOut class="size-4 shrink-0" />
                            <span>{t('nav.logout')}</span>
                        </Link>
                    {/snippet}
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarFooter>
</Sidebar>
{@render children?.()}
