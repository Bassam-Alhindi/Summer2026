<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { toUrl } from '@/lib/utils';
    import { getLocale, toggleLocale } from '@/lib/i18n.svelte';
    import { dashboard, login } from '@/routes';
    import {
        Languages,
        ArrowLeft,
        Calculator,
        TrendingUp,
        PieChart,
        ShieldCheck,
        SlidersHorizontal,
        Wallet,
        Zap,
    } from 'lucide-svelte';

    const auth = $derived(page.props.auth);
    const isLoggedIn = $derived(!!auth?.user);
    const isArabic = $derived(getLocale() === 'ar');

    const features = $derived([
        {
            title: isArabic ? 'الميزانية اليومية' : 'Daily Budget',
            desc: isArabic ? 'حساب تلقائي وذكي يُبقي مصاريفك دائماً على المسار الصحيح.' : 'Smart daily budget calculation to keep your spending on track.',
            icon: Calculator,
            borderColor: 'border-emerald-500/30 hover:border-emerald-500/60',
            iconBg: 'bg-emerald-950/60 border-emerald-500/40 text-emerald-400',
            glowColor: 'from-emerald-500/20',
        },
        {
            title: isArabic ? 'تتبّع المعاملات' : 'Transaction Tracking',
            desc: isArabic ? 'سجّل مصاريفك ودخلك في ثوانٍ معدودة وبكل سهولة.' : 'Log expenses and income effortlessly within seconds.',
            icon: TrendingUp,
            borderColor: 'border-indigo-500/30 hover:border-indigo-500/60',
            iconBg: 'bg-indigo-950/60 border-indigo-500/40 text-indigo-400',
            glowColor: 'from-indigo-500/20',
        },
        {
            title: isArabic ? 'تقارير وتحليلات' : 'Reports & Analytics',
            desc: isArabic ? 'رؤى بصرية واضحة تُظهر لك أين تذهب أموالك بالضبط.' : 'Clear visual charts showing exactly where your money goes.',
            icon: PieChart,
            borderColor: 'border-amber-500/30 hover:border-amber-500/60',
            iconBg: 'bg-amber-950/60 border-amber-500/40 text-amber-400',
            glowColor: 'from-amber-500/20',
        },
    ]);

    const targetUrl = $derived(toUrl(isLoggedIn ? dashboard() : login()));
</script>

<AppHead title={isArabic ? 'محفظتي | لكل ريال وجهة' : 'MyWallet | Smart Expense Tracker'} />

<div class="relative min-h-screen overflow-x-hidden bg-[#050508] text-slate-100 antialiased selection:bg-violet-500/30">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="animate-aurora-1 absolute -top-32 left-1/2 h-[480px] w-[580px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-violet-600/10 via-cyan-600/10 to-transparent blur-[120px]"></div>
        <div class="animate-aurora-2 absolute top-1/2 -right-28 h-80 w-80 rounded-full bg-cyan-500/5 blur-[130px]"></div>
    </div>

    <div class="relative z-10 flex min-h-screen flex-col">
        <header class="absolute inset-x-0 top-0 z-50 mx-auto max-w-md px-5 pt-4">
            <div class="flex items-center justify-between">
                
                <div class="group flex cursor-pointer items-center gap-2.5 active:scale-95 transition-transform">
                    <div class="relative flex items-center justify-center">
                        <div class="clean-ambient-glow absolute -inset-2 rounded-full bg-gradient-to-r from-cyan-500/20 to-violet-500/20 blur-md opacity-40"></div>

                        <div class="clean-logo-box relative flex size-9 items-center justify-center overflow-hidden rounded-[13px] bg-[#0c0e18] border border-cyan-500/20 shadow-[0_0_8px_rgba(6,182,212,0.12)] transition-colors">
                            <Wallet class="clean-wallet-icon size-4 text-cyan-400 stroke-[2.2] transition-transform duration-300 group-hover:scale-110" />
                        </div>
                    </div>

                    <span class="animated-gradient-text text-[16.5px] font-black tracking-tight drop-shadow-sm">
                        {isArabic ? 'محفظتي' : 'MyWallet'}
                    </span>
                </div>

                <button
                    type="button"
                    onclick={toggleLocale}
                    class="flex h-7 items-center justify-center gap-1.5 rounded-lg border border-white/15 bg-slate-900/80 px-2.5 text-[10.5px] font-semibold text-slate-200 backdrop-blur-xl transition-all active:scale-95 hover:border-white/30 hover:text-white"
                >
                    <Languages class="size-3 text-cyan-400" />
                    <span>{isArabic ? 'English' : 'العربية'}</span>
                </button>
            </div>
        </header>

        <main class="mx-auto flex w-full max-w-md flex-1 flex-col px-5 pb-12 pt-20">
            <section class="relative flex flex-col items-center text-center">
                
                <!-- خلفية إضاءة احترافية وداكنة خلف النص -->
                <div class="pointer-events-none absolute left-1/2 top-1/2 -z-10 -translate-x-1/2 -translate-y-1/2 w-full max-w-xs h-48">
                    <!-- وهج عميق داكن لتخفيف السواد ونقاوة النص -->
                    <div class="absolute inset-0 mx-auto h-full w-full rounded-full bg-gradient-to-tr from-indigo-950/80 via-purple-900/30 to-slate-900/50 blur-[70px] opacity-70"></div>
                    <!-- انعكاس ضوئي خافت جداً بالمنتصف -->
                    <div class="absolute left-1/2 top-1/3 h-16 w-40 -translate-x-1/2 rounded-full bg-gradient-to-r from-cyan-500/10 via-violet-500/15 to-transparent blur-2xl"></div>
                </div>

                <h1 class="mt-2 text-3xl font-black leading-snug tracking-tight text-white drop-shadow-[0_4px_16px_rgba(0,0,0,0.6)]">
                    <span class="inline-block drop-shadow-[0_2px_10px_rgba(255,255,255,0.15)]">
                        {isArabic ? 'لكل ريال وجهة' : 'Every Cent Has a Purpose'}
                    </span>
                    <br />
                    <span class="subtle-hero-gradient inline-block mt-1 font-extrabold text-slate-300">
                        {isArabic ? 'ولكل هدف وصول' : 'And Every Goal Gets There'}
                    </span>
                </h1>

                <p class="mt-4 text-xs leading-relaxed text-slate-300 max-w-xs font-normal">
                    {isArabic 
                        ? 'فلوسك أوضح، قراراتك أذكى، وحياتك المالية أسهل.' 
                        : 'A modern toolkit designed to give you total control over your daily budget and finances without hassle.'}
                </p>
            </section>

            <section class="mt-8 flex flex-col gap-3">
                <Link href={targetUrl} class="w-full">
                    <div class="group relative overflow-hidden rounded-2xl p-px shadow-lg transition-transform active:scale-[0.98]">
                        <div class="animate-shimmer absolute inset-0 bg-[length:200%_100%] bg-gradient-to-r from-violet-600 via-cyan-400 to-violet-600 opacity-90"></div>
                        
                        <div class="relative flex h-12.5 w-full items-center justify-center gap-2 rounded-[15px] bg-[#0b0b14]/90 backdrop-blur-2xl px-4 text-xs font-extrabold text-white">
                            <span>{isLoggedIn ? (isArabic ? 'تتبع مصاريفك الآن' : 'Track your expenses now') : (isArabic ? 'تتبع مصاريفك الآن' : 'Track your expenses now')}</span>
                            <ArrowLeft class="size-4 text-cyan-400 transition-transform rtl:rotate-0 rotate-180 group-hover:-translate-x-1 rtl:group-hover:-translate-x-1" />
                        </div>
                    </div>
                </Link>

                <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                    <div class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.05] px-3 py-1.5 text-[10.5px] font-semibold text-slate-200 backdrop-blur-md shadow-sm">
                        <SlidersHorizontal class="size-3 text-cyan-400" />
                        <span>{isArabic ? 'سهولة ومرونة' : 'Easy & Flexible'}</span>
                    </div>
                    
                    <div class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.05] px-3 py-1.5 text-[10.5px] font-semibold text-slate-200 backdrop-blur-md shadow-sm">
                        <ShieldCheck class="size-3 text-violet-400" />
                        <span>{isArabic ? 'خصوصية تامة' : '100% Private'}</span>
                    </div>

                    <div class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.05] px-3 py-1.5 text-[10.5px] font-semibold text-slate-200 backdrop-blur-md shadow-sm">
                        <Zap class="size-3 text-amber-400" />
                        <span>{isArabic ? 'دقة وسرعة' : 'Fast & Accurate'}</span>
                    </div>
                </div>
            </section>

            <section class="mt-9 flex flex-col gap-3.5">
                {#each features as feature (feature.title)}
                    {@const Icon = feature.icon}
                    <div class="group relative overflow-hidden rounded-2xl border bg-[#0a0a14]/90 p-4.5 backdrop-blur-2xl shadow-md transition-all duration-300 hover:-translate-y-0.5 active:scale-[0.98] {feature.borderColor}">
                        <div class="pointer-events-none absolute -right-8 -top-8 size-32 rounded-full bg-gradient-to-br {feature.glowColor} to-transparent blur-2xl opacity-40 transition-opacity duration-300 group-hover:opacity-80"></div>
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>

                        <div class="relative z-10 flex items-center gap-4">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl border shadow-inner backdrop-blur-md transition-transform duration-300 group-hover:scale-105 {feature.iconBg}">
                                <Icon class="size-5.5" />
                            </div>

                            <div class="flex flex-1 flex-col justify-center text-start">
                                <h3 class="text-xs font-bold text-slate-100 tracking-tight transition-colors group-hover:text-white">{feature.title}</h3>
                                <p class="mt-1 text-[11px] font-normal leading-relaxed text-slate-400 transition-colors group-hover:text-slate-300">
                                    {feature.desc}
                                </p>
                            </div>
                        </div>
                    </div>
                {/each}
            </section>
        </main>

        <footer class="mt-auto border-t border-white/[0.06] bg-[#050508]/95 py-5 text-center backdrop-blur-xl">
            <p class="text-[11px] text-slate-500">
                &copy; {new Date().getFullYear()} {isArabic ? 'محفظتي. جميع الحقوق محفوظة.' : 'MyWallet. All rights reserved.'}
            </p>
        </footer>
    </div>
</div>

<style>
    .animated-gradient-text {
        background: linear-gradient(
            110deg,
            #ffffff 0%,
            #ffffff 35%,
            #7dd3fc 50%,
            #c084fc 65%,
            #ffffff 80%,
            #ffffff 100%
        );
        background-size: 220% 100%;
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: 
            textEntrance3s 3.0s cubic-bezier(0.16, 1, 0.3, 1) forwards,
            textColorWave 4.5s ease-in-out infinite 3.0s;
    }

    .subtle-hero-gradient {
        background: linear-gradient(
            120deg,
            #cbd5e1 0%,
            #e2e8f0 25%,
            #94a3b8 45%,
            #a5f3fc 65%,
            #ddd6fe 85%,
            #cbd5e1 100%
        );
        background-size: 250% 100%;
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: softColorShift 8s ease-in-out infinite;
        filter: drop-shadow(0 2px 8px rgba(192, 132, 252, 0.1));
    }

    @keyframes softColorShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    .clean-logo-box { 
        animation: logoBoxEntrance3s 3.0s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
    }

    .clean-wallet-icon { 
        animation: walletIconEntrance3s 3.0s cubic-bezier(0.16, 1, 0.3, 1) forwards; 
    }

    .clean-ambient-glow { 
        animation: glowEntrance3s 3.0s ease-out forwards; 
    }

    @keyframes logoBoxEntrance3s {
        0% { opacity: 0; transform: scale(0.2) rotate(-25deg); filter: blur(12px); }
        35% { opacity: 1; transform: scale(1.18) rotate(6deg); filter: blur(0px); }
        55% { transform: scale(0.92) rotate(-2deg); }
        75% { transform: scale(1.04) rotate(0deg); }
        100% { opacity: 1; transform: scale(1) rotate(0deg); }
    }

    @keyframes walletIconEntrance3s {
        0% { opacity: 0; transform: scale(0.3) translateY(8px); }
        30% { opacity: 0; transform: scale(0.3) translateY(8px); }
        60% { opacity: 1; transform: scale(1.25) translateY(0); }
        80% { transform: scale(0.95); }
        100% { opacity: 1; transform: scale(1); }
    }

    @keyframes textEntrance3s {
        0% { opacity: 0; transform: translateX(25px); filter: blur(10px); }
        35% { opacity: 0; transform: translateX(20px); filter: blur(8px); }
        75% { opacity: 1; transform: translateX(-2px); filter: blur(0px); }
        100% { opacity: 1; transform: translateX(0); filter: blur(0px); }
    }

    @keyframes glowEntrance3s {
        0% { opacity: 0; transform: scale(0.4); }
        50% { opacity: 0.5; transform: scale(1.1); }
        80% { opacity: 0.25; transform: scale(0.95); }
        100% { opacity: 0.35; transform: scale(1); }
    }

    @keyframes textColorWave {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .animate-aurora-1 { animation: aurora1 10s ease-in-out infinite; }
    .animate-aurora-2 { animation: aurora2 12s ease-in-out infinite; }
    .animate-shimmer { animation: shimmer 4s linear infinite; }

    @keyframes aurora1 { 0%, 100% { opacity: 0.5; } 50% { opacity: 0.75; } }
    @keyframes aurora2 { 0%, 100% { opacity: 0.4; } 50% { opacity: 0.75; } }
    @keyframes shimmer { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
</style>