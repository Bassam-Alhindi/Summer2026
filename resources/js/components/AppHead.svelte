<script lang="ts">
    import type { Snippet } from 'svelte';

    let {
        title: _title,
        children,
    }: {
        title?: string;
        children?: Snippet;
    } = $props();

    const fullTitle = 'محفظتي | لكل ريال وجهة';
</script>

<svelte:head>
    <title>{fullTitle}</title>
    {@render children?.()}
</svelte:head>
