<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import type { Snippet } from 'svelte';
    import { Wallet } from 'lucide-svelte';
    import { home } from '@/routes';

    let {
        title = '',
        description = '',
        children,
    }: {
        title?: string;
        description?: string;
        children?: Snippet;
    } = $props();
</script>

<div
    class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10"
>
    <div class="w-full max-w-md">
        <div class="flex flex-col gap-6">
            <!-- App Logo & Name (Summer) -->
            <div class="flex flex-col items-center gap-4">
                <Link
                    href={home()}
                    class="group flex flex-col items-center gap-2.5 transition-transform active:scale-95"
                >
                    <div class="relative flex size-12 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.05] backdrop-blur-md shadow-lg shadow-cyan-500/10 transition-all duration-300 group-hover:border-cyan-400/40 group-hover:bg-cyan-500/10">
                        <div class="pointer-events-none absolute inset-0 rounded-2xl bg-cyan-400/20 blur-md"></div>
                        <Wallet class="relative size-6 text-cyan-400 stroke-[2] transition-transform duration-300 group-hover:scale-110" />
                    </div>
                    <span class="text-xl font-extrabold tracking-tight text-white">Summer</span>
                </Link>

                <div class="space-y-1 text-center">
                    {#if title}
                        <h1 class="text-lg font-bold text-slate-100">{title}</h1>
                    {/if}
                    {#if description}
                        <p class="text-center text-xs text-slate-400">
                            {description}
                        </p>
                    {/if}
                </div>
            </div>

            {@render children?.()}
        </div>
    </div>
</div>