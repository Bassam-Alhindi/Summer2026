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

<div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
    <div class="w-full max-w-md">
        <div class="flex flex-col gap-6">
            <!-- App Logo & Name -->
            <div class="flex flex-col items-center gap-4">
                <Link
                    href={home()}
                    class="group flex flex-col items-center gap-2.5 transition-transform active:scale-95"
                >
                    <!-- Logo Box with Glow & Float Animation -->
                    <div class="animated-logo relative flex size-12 items-center justify-center rounded-2xl border border-cyan-500/30 bg-white/[0.05] backdrop-blur-md transition-all duration-300 group-hover:border-cyan-400 group-hover:bg-cyan-500/10">
                        <div class="pulse-bg pointer-events-none absolute inset-0 rounded-2xl bg-cyan-400/20 blur-md"></div>
                        <Wallet class="relative size-6 text-cyan-400 stroke-[2] transition-transform duration-300 group-hover:scale-110" />
                    </div>
                    <span class="shimmer-text text-xl font-extrabold tracking-tight">MyWallet</span>
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

<style>
    /* حركة طفو الشعار وتوهج الحدود */
    .animated-logo {
        animation: float 3s ease-in-out infinite, logo-glow 2.5s ease-in-out infinite alternate;
    }

    /* نبض التوهج الخارجي */
    .pulse-bg {
        animation: pulse-glow 2.5s ease-in-out infinite alternate;
    }

    /* حركة اللمعان لنص الاسم */
    .shimmer-text {
        background: linear-gradient(
            90deg, 
            #10b981 0%, 
            #06b6d4 30%, 
            #ffffff 50%, 
            #06b6d4 70%, 
            #10b981 100%
        );
        background-size: 200% auto;
        color: transparent;
        -webkit-background-clip: text;
        background-clip: text;
        animation: shine 2s linear infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-5px);
        }
    }

    @keyframes logo-glow {
        0% {
            box-shadow: 0 0 10px rgba(6, 182, 212, 0.15);
        }
        100% {
            box-shadow: 0 0 25px rgba(6, 182, 212, 0.45);
        }
    }

    @keyframes pulse-glow {
        0% {
            opacity: 0.3;
            transform: scale(0.95);
        }
        100% {
            opacity: 0.8;
            transform: scale(1.08);
        }
    }

    @keyframes shine {
        0% {
            background-position: 0% center;
        }
        100% {
            background-position: -200% center;
        }
    }
</style>