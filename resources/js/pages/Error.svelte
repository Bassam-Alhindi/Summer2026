<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { getLocale } from '@/lib/i18n.svelte';
    import Home from 'lucide-svelte/icons/house';
    import RotateCcw from 'lucide-svelte/icons/rotate-ccw';
    import TriangleAlert from 'lucide-svelte/icons/triangle-alert';

    let { status = 500 }: { status?: number } = $props();

    const isArabic = $derived(getLocale() === 'ar');

    const copy = $derived.by(() => {
        const table: Record<number, { ar: [string, string]; en: [string, string] }> = {
            503: {
                ar: ['الخدمة تحت الصيانة', 'نرجع بعد شوي، حاول مرة ثانية بعد دقائق.'],
                en: ['Down for maintenance', 'We will be back shortly. Please try again in a few minutes.'],
            },
            500: {
                ar: ['صار خطأ غير متوقع', 'سجّلنا المشكلة وبنشتغل عليها. جرّب تحدّث الصفحة.'],
                en: ['Something went wrong', 'The error has been logged. Try refreshing the page.'],
            },
            404: {
                ar: ['الصفحة غير موجودة', 'الرابط اللي فتحته ما عاد موجود أو تغيّر.'],
                en: ['Page not found', 'The page you are looking for does not exist.'],
            },
            403: {
                ar: ['ما عندك صلاحية', 'هذي الصفحة مو متاحة لحسابك.'],
                en: ['Access denied', 'You do not have permission to view this page.'],
            },
            429: {
                ar: ['طلبات كثيرة', 'هدّي شوي وحاول بعد دقيقة.'],
                en: ['Too many requests', 'Please slow down and try again in a minute.'],
            },
        };

        const entry = table[status] ?? table[500];

        return isArabic
            ? { title: entry.ar[0], body: entry.ar[1] }
            : { title: entry.en[0], body: entry.en[1] };
    });
</script>

<AppHead title={`${status}`} />

<div class="flex min-h-svh flex-col items-center justify-center gap-6 bg-[#08080b] px-6 text-center">
    <div class="pointer-events-none absolute inset-0 overflow-hidden">
        <div class="absolute -top-24 left-1/2 size-80 -translate-x-1/2 rounded-full bg-rose-500/10 blur-[120px]"></div>
    </div>

    <div class="relative z-10 flex max-w-sm flex-col items-center gap-5">
        <div class="flex size-14 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.04] text-rose-400 backdrop-blur-xl">
            <TriangleAlert class="size-6" />
        </div>

        <div class="flex flex-col gap-2">
            <span class="text-5xl font-black tabular-nums tracking-tight text-white/85">{status}</span>
            <h1 class="text-lg font-bold text-white">{copy.title}</h1>
            <p class="text-sm leading-relaxed text-white/50">{copy.body}</p>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-2">
            <button
                type="button"
                onclick={() => window.location.reload()}
                class="flex h-10 items-center gap-2 rounded-xl border border-white/10 bg-white/[0.05] px-4 text-xs font-bold text-white/80 backdrop-blur-xl transition-colors hover:bg-white/[0.1] hover:text-white"
            >
                <RotateCcw class="size-3.5" />
                <span>{isArabic ? 'تحديث الصفحة' : 'Refresh'}</span>
            </button>

            <Link
                href="/"
                class="flex h-10 items-center gap-2 rounded-xl bg-white px-4 text-xs font-bold text-zinc-950 transition-colors hover:bg-white/90"
            >
                <Home class="size-3.5" />
                <span>{isArabic ? 'الرئيسية' : 'Home'}</span>
            </Link>
        </div>
    </div>
</div>
