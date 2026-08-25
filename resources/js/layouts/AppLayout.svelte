<script lang="ts">
    import type { Snippet } from 'svelte';
    import { Toaster } from 'svelte-sonner';
    import AppLayout from '@/layouts/app/AppSidebarLayout.svelte';
    import type { BreadcrumbItem } from '@/types';
    import { getLocale } from '@/lib/i18n.svelte';

    let {
        breadcrumbs = [],
        children,
    }: {
        breadcrumbs?: BreadcrumbItem[];
        children?: Snippet;
    } = $props();
</script>

<!-- إشعار علوي بتصميم نيون زجاجي عصري -->
<Toaster
    position="top-center"
    dir={getLocale() === 'ar' ? 'rtl' : 'ltr'}
    duration={4000}
    toastOptions={{
        classes: {
            toast: '!rounded-2xl !border !backdrop-blur-2xl !px-5 !py-3.5 !shadow-2xl flex items-center justify-center gap-3 text-center not-italic transition-all duration-300 font-medium',
            title: '!text-sm !font-bold',
            success: '!bg-emerald-950/85 !border-emerald-500/50 !text-emerald-200 !shadow-[0_0_25px_rgba(16,185,129,0.25)] [&_svg]:!text-emerald-400',
            warning: '!bg-amber-950/85 !border-amber-500/50 !text-amber-200 !shadow-[0_0_25px_rgba(245,158,11,0.25)] [&_svg]:!text-amber-400',
            error: '!bg-rose-950/85 !border-rose-500/50 !text-rose-200 !shadow-[0_0_25px_rgba(244,63,94,0.25)] [&_svg]:!text-rose-400',
            info: '!bg-sky-950/85 !border-sky-500/50 !text-sky-200 !shadow-[0_0_25px_rgba(14,165,233,0.25)] [&_svg]:!text-sky-400',
        },
    }}
/>

<AppLayout {breadcrumbs}>
    {@render children?.()}
</AppLayout>