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
            borderColor: 'border-emerald-500/25',
            iconBg: 'bg-emerald-950/50 border-emerald-500/30 text-emerald-400',
            glowColor: 'from-emerald-500/15',
        },
        {
            title: isArabic ? 'تتبّع المعاملات' : 'Transaction Tracking',
            desc: isArabic ? 'سجّل مصاريفك ودخلك في ثوانٍ معدودة وبكل سهولة.' : 'Log expenses and income effortlessly within seconds.',
            icon: TrendingUp,
            borderColor: 'border-indigo-500/25',
            iconBg: 'bg-indigo-950/50 border-indigo-500/30 text-indigo-400',
            glowColor: 'from-indigo-500/15',
        },
        {
            title: isArabic ? 'تقارير وتحليلات' : 'Reports & Analytics',
            desc: isArabic ? 'رؤى بصرية واضحة تُظهر لك أين تذهب أموالك بالضبط.' : 'Clear visual charts showing exactly where your money goes.',
            icon: PieChart,
            borderColor: 'border-amber-500/25',
            iconBg: 'bg-amber-950/50 border-amber-500/30 text-amber-400',
            glowColor: 'from-amber-500/15',
        },
    ]);

    const targetUrl = $derived(toUrl(isLoggedIn ? dashboard() : login()));
</script>

<AppHead title={isArabic ? 'محفظتي | لكل ريال وجهة' : 'MyWallet | Smart Expense Tracker'} />

<div class="relative min-h-screen overflow-x-hidden bg-[#050508] text-slate-100 antialiased selection:bg-violet-500/30">
    
    <!-- خلفيات توهج هادئة -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="animate-aurora-1 absolute -top-32 left-1/2 h-[480px] w-[580px] -translate-x-1/2 rounded-full bg-gradient-to-tr from-violet-600/10 via-cyan-900/10 to-transparent blur-[140px]"></div>
        <div class="animate-aurora-2 absolute top-1/2 -right-28 h-80 w-80 rounded-full bg-cyan-500/5 blur-[150px]"></div>
    </div>

    <div class="relative z-10 flex min-h-screen flex-col">
        
        <!-- الهيدر العلوي -->
        <header class="fixed inset-x-0 top-0 z-50 mx-auto max-w-md px-5 pt-4">
            <div class="absolute inset-x-5 top-0 h-px bg-gradient-to-r from-transparent via-cyan-400/30 via-violet-500/30 to-transparent"></div>

            <div class="flex items-center justify-between">
                
                <!-- الشعار -->
                <div class="group cursor-pointer flex items-center gap-2.5 transition-transform active:scale-95">
                    <div class="animate-wallet-smooth relative flex size-8.5 items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] backdrop-blur-md shadow-sm transition-all duration-300 group-hover:border-cyan-400/40 group-active:border-cyan-400/40 group-hover:bg-cyan-500/10 group-active:bg-cyan-500/10">
                        <div class="animate-glow-soft pointer-events-none absolute inset-0 rounded-xl bg-cyan-400/25 blur-md"></div>
                        <Wallet class="relative size-4 text-cyan-400 stroke-[2] transition-transform duration-300 ease-out group-hover:-rotate-12 group-active:-rotate-12 group-hover:scale-110 group-active:scale-110" />
                    </div>

                    <span class="animate-text-smooth text-sm font-bold tracking-tight text-white transition-colors duration-300 group-hover:text-cyan-200 group-active:text-cyan-200">
                        {isArabic ? 'محفظتي' : 'MyWallet'}
                    </span>
                </div>

                <!-- زر تغيير اللغة -->
                <button
                    type="button"
                    onclick={toggleLocale}
                    class="flex h-8.5 items-center justify-center gap-2 rounded-xl border border-white/10 bg-[#0a0a12]/80 px-3 text-xs font-semibold text-slate-200 backdrop-blur-2xl shadow-sm transition-all active:scale-95 hover:border-white/20"
                >
                    <Languages class="size-3.5 text-cyan-400" />
                    <span>{isArabic ? 'English' : 'العربية'}</span>
                </button>
            </div>
        </header>

        <!-- المحتوى الرئيسي -->
        <main class="mx-auto flex w-full max-w-md flex-1 flex-col px-5 pb-12 pt-24">
            
            <section class="flex flex-col items-center text-center">
                <div class="inline-flex items-center gap-2 rounded-full border border-violet-500/30 bg-violet-500/10 px-4 py-1.5 text-xs font-semibold text-violet-300 backdrop-blur-xl shadow-[0_0_15px_rgba(139,92,246,0.15)]">
                    <span class="relative flex size-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
                        <span class="relative inline-flex size-2 rounded-full bg-cyan-400"></span>
                    </span>
                    <span>{isArabic ? 'إدارة مالية ذكية وبسيطة' : 'Smart & Simple Expense Control'}</span>
                </div>

                <h1 class="mt-5 text-3xl font-black leading-snug tracking-tight text-white">
                    {isArabic ? 'لكل ريال وجهة...' : 'Every Cent Has a Purpose...'} <br />
                    <span class="bg-gradient-to-r from-violet-200 via-slate-100 to-cyan-200 bg-clip-text text-transparent">
                        {isArabic ? 'ولكل قرار اثر' : 'And Every Decision Leaves a Mark'}
                    </span>
                </h1>

                <p class="mt-3 text-xs leading-relaxed text-slate-400 max-w-xs font-normal">
                    {isArabic 
                        ? 'منظومة عصرية تمنحك السيطرة الكاملة على ميزانيتك لليومية والتزاماتك بدون أي تعقيد.' 
                        : 'A modern toolkit designed to give you total control over your daily budget and finances without hassle.'}
                </p>
            </section>

            <!-- منطقة الأزرار الرئيسية -->
            <section class="mt-8 flex flex-col gap-3">
                <Link href={targetUrl} class="w-full">
                    <div class="group relative overflow-hidden rounded-2xl p-px shadow-lg transition-transform active:scale-[0.98]">
                        <div class="animate-shimmer absolute inset-0 bg-[length:200%_100%] bg-gradient-to-r from-violet-600 via-cyan-400 to-violet-600 opacity-90"></div>
                        
                        <div class="relative flex h-12.5 w-full items-center justify-center gap-2 rounded-[15px] bg-[#0b0b14]/90 backdrop-blur-2xl px-4 text-xs font-extrabold text-white">
                            <span>{isLoggedIn ? (isArabic ? 'تتبع مصاريفك الآن' : 'Track your expenses now') : (isArabic ? 'تتبع مصاريفك الآن' : 'Track your expenses now')}</span>
                            <ArrowLeft class="size-4 text-cyan-400 transition-transform rtl:rotate-0 rotate-180" />
                        </div>
                    </div>
                </Link>

                <!-- الخانات الثلاث -->
                <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                    <div class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[10.5px] font-semibold text-slate-300 backdrop-blur-md shadow-sm">
                        <SlidersHorizontal class="size-3 text-cyan-400" />
                        <span>{isArabic ? 'سهولة ومرونة' : 'Easy & Flexible'}</span>
                    </div>
                    
                    <div class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[10.5px] font-semibold text-slate-300 backdrop-blur-md shadow-sm">
                        <ShieldCheck class="size-3 text-violet-400" />
                        <span>{isArabic ? 'خصوصية تامة' : '100% Private'}</span>
                    </div>

                    <div class="flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1.5 text-[10.5px] font-semibold text-slate-300 backdrop-blur-md shadow-sm">
                        <Zap class="size-3 text-amber-400" />
                        <span>{isArabic ? 'دقة وسرعة' : 'Fast & Accurate'}</span>
                    </div>
                </div>
            </section>

            <!-- الميزات -->
            <section class="mt-9 flex flex-col gap-3.5">
                {#each features as feature (feature.title)}
                    {@const Icon = feature.icon}
                    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0a0a14]/80 p-4.5 backdrop-blur-2xl shadow-[inset_0_1px_1px_0_rgba(255,255,255,0.08)] transition-transform duration-150 active:scale-[0.98]">
                        <div class="pointer-events-none absolute -right-8 -top-8 size-28 rounded-full bg-gradient-to-br {feature.glowColor} to-transparent blur-2xl opacity-60"></div>
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/15 to-transparent"></div>

                        <div class="relative z-10 flex items-start gap-3.5">
                            <div class="flex size-11 shrink-0 items-center justify-center rounded-xl border shadow-inner backdrop-blur-md {feature.iconBg}">
                                <Icon class="size-5" />
                            </div>

                            <div class="flex flex-1 flex-col text-start justify-center min-h-[44px]">
                                <h3 class="text-xs font-bold text-slate-100 tracking-tight">{feature.title}</h3>
                                <p class="mt-1 text-[11px] font-normal leading-relaxed text-slate-400">
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
    @keyframes walletSmooth {
        0% {
            transform: scale(0.35) translateY(18px) rotate(-18deg);
            opacity: 0;
            filter: blur(10px);
        }
        50% {
            filter: blur(0px);
            opacity: 1;
        }
        75% {
            transform: scale(1.08) translateY(-3deg) rotate(4deg);
        }
        90% {
            transform: scale(0.97) translateY(0px) rotate(-1deg);
        }
        100% {
            transform: scale(1) translateY(0) rotate(0deg);
            opacity: 1;
            filter: blur(0px);
        }
    }

    @keyframes glowSoft {
        0% { opacity: 0; transform: scale(0.5); }
        45% { opacity: 0.9; transform: scale(1.35); }
        100% { opacity: 0; transform: scale(1.8); }
    }

    @keyframes textSmooth {
        0% { opacity: 0; transform: translateX(10px); filter: blur(4px); }
        100% { opacity: 1; transform: translateX(0); filter: blur(0px); }
    }

    @keyframes aurora1 {
        0%, 100% { transform: translate(-50%, 0) scale(1); }
        50% { transform: translate(-45%, -20px) scale(1.1); }
    }
    @keyframes aurora2 {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(-20px, 15px) scale(1.15); }
    }
    @keyframes shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .animate-wallet-smooth {
        animation: walletSmooth 1.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .animate-glow-soft {
        animation: glowSoft 1.9s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .animate-text-smooth {
        animation: textSmooth 1.3s cubic-bezier(0.16, 1, 0.3, 1) 0.35s both;
    }
    .animate-aurora-1 { animation: aurora1 10s ease-in-out infinite; }
    .animate-aurora-2 { animation: aurora2 12s ease-in-out infinite; }
    .animate-shimmer { animation: shimmer 4s linear infinite; }
</style>