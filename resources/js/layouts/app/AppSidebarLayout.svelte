<script lang="ts">
    import type { Snippet } from 'svelte';
    import AppContent from '@/components/AppContent.svelte';
    import AppShell from '@/components/AppShell.svelte';
    import AppSidebar from '@/components/AppSidebar.svelte';
    import BottomNav from '@/components/BottomNav.svelte';
    import type { BreadcrumbItem } from '@/types';

    let {
        breadcrumbs: _breadcrumbs,
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<AppShell variant="sidebar">
    <AppSidebar />
    <AppContent variant="sidebar" class="overflow-x-hidden pb-20 lg:pb-0">
        {@render children?.()}
    </AppContent>
    <BottomNav />
</AppShell>