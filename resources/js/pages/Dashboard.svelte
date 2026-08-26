<script module lang="ts">
  import { dashboard } from '@/routes';
  export const layout = {
    breadcrumbs: [
      {
        title: 'Dashboard',
        href: dashboard(),
      },
    ],
  };
</script>

<script lang="ts">
  import AppHead from '@/components/AppHead.svelte';
  import { router, Link } from '@inertiajs/svelte';
  import { fade, scale } from 'svelte/transition';
  import {
    Plus,
    ArrowUpRight,
    ArrowDownRight,
    ChevronLeft,
    ChevronUp,   
    ChevronDown,  
    Wallet,
    X,
    Receipt,
    LayoutDashboard,
    Mic
  } from 'lucide-svelte';
  import * as LucideIcons from 'lucide-svelte';
  import { t, getLocale, setLocale } from '@/lib/i18n.svelte';
  import type { TranslationKey } from '@/lib/translations';
  import { resolveCategoryMeta } from '@/lib/categories';
  import { localToday } from '@/lib/utils';
  import { toast } from 'svelte-sonner';


  let isAmountFocused = $state(false);

  function incrementAmount() {
    const val = parseFloat(formAmount) || 0;
    formAmount = (val + 10).toString();
  }

  function decrementAmount() {
    const val = parseFloat(formAmount) || 0;
    const next = Math.max(0, val - 10);
    formAmount = next === 0 ? '' : next.toString();
  }
  // ----------------------------------------

  type CategoryObject = {
    id: number;
    name: string;
    type?: 'income' | 'expense';
    color?: string;
    icon?: string;
    budget_limit?: number | null;
  };

  type Transaction = {
    id: number;
    description?: string;
    category: string | CategoryObject;
    category_id?: number;
    type: 'income' | 'expense';
    amount: number;
    date: string;
    icon?: string;
    color?: string;
  };

  type ExpenseCategoryData = {
    category: string;
    category_id?: number;
    amount: number;
    color: string;
    budget_limit?: number | null;
  };

  let {
    netBalance = 0,
    totalIncome = 0,
    totalExpenses = 0,
    recentTransactions = [],
    categories = [],
    expenseByCategory = [],
    period = 'month',
    remainingDays = 30
  }: {
    netBalance: number;
    totalIncome: number;
    totalExpenses: number;
    recentTransactions: Transaction[];
    categories: CategoryObject[];
    expenseByCategory: ExpenseCategoryData[];
    period: string;
    remainingDays: number;
  } = $props();

  let selectedPeriod = $state(period || 'month');
  let currentLang = $state(getLocale());
  let isListening = $state(false);

  $effect(() => {
    if (period) {
      selectedPeriod = period;
    }
  });

  let dailyBudget = $derived.by(() => {
    const periodNetBalance = totalIncome - totalExpenses;

    if (periodNetBalance <= 0 || remainingDays <= 0) {
      return 0;
    }

    return periodNetBalance / remainingDays;
  });

  function toggleLanguage() {
    const nextLang = currentLang === 'ar' ? 'en' : 'ar';
    currentLang = nextLang;
    setLocale(nextLang);
    if (typeof document !== 'undefined') {
      document.documentElement.lang = nextLang;
      document.documentElement.dir = nextLang === 'ar' ? 'rtl' : 'ltr';
    }
  }

  function tr(key: string, fallbackAr: string, fallbackEn?: string): string {
    try {
      const translated = t(key as TranslationKey);
      if (translated && translated !== key) return translated;
    } catch {}
    return currentLang === 'en' ? (fallbackEn || fallbackAr) : fallbackAr;
  }

  let periods = $derived([
    { id: 'week', label: tr('period.week', 'هذا الأسبوع', 'This Week') },
    { id: 'month', label: tr('period.month', 'هذا الشهر', 'This Month') },
    { id: 'year', label: tr('period.year', 'هذه السنة', 'This Year') }
  ]);

  function changePeriod(pId: string) {
    selectedPeriod = pId;
    router.get(dashboard.url(), { period: pId }, { preserveState: true });
  }

  let isDialogOpen = $state(false);
  let formAmount = $state('');
  let formType = $state<'income' | 'expense'>('expense');
  let formCategoryId = $state<number | null>(null);
  let formDescription = $state('');
  let formDate = $state(localToday());
  let errorMessage = $state<string | null>(null);
  let isSubmitting = $state(false);

  let amountPlaceholder = $derived.by(() => {
    if (currentLang === 'en') return 'How much?';
    return formType === 'expense' ? 'كم صرفت؟' : 'كم كسبت؟';
  });

  function formatNumber(value: number): string {
    const num = Number(value) || 0;
    const formatted = num.toLocaleString('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    });

    return formatted.replace(/\.00$/, '');
  }

  function formatTransactionDate(dateStr: string, lang: string): string {
    if (!dateStr) return '';
    const target = new Date(dateStr);
    if (isNaN(target.getTime())) return dateStr;
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const targetDay = new Date(target.getFullYear(), target.getMonth(), target.getDate());
    const diffTime = today.getTime() - targetDay.getTime();
    const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
    if (diffDays === 0) {
      return lang === 'en' ? 'Today' : 'اليوم';
    } else if (diffDays === 1) {
      return lang === 'en' ? 'Yesterday' : 'أمس';
    } else {
      const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return `${target.getDate()} ${monthNames[target.getMonth()]}`;
    }
  }

  function resolveIcon(iconProp?: any, fallbackIcon?: any) {
    if (!iconProp) return fallbackIcon || Wallet;
    if (typeof iconProp !== 'string') return iconProp;
    if ((LucideIcons as any)[iconProp]) {
      return (LucideIcons as any)[iconProp];
    }
    const pascal = iconProp
      .replace(/(?:^|-|_)([a-z])/g, (_, c) => c.toUpperCase())
      .replace(/[^a-zA-Z0-9]/g, '');
    if ((LucideIcons as any)[pascal]) {
      return (LucideIcons as any)[pascal];
    }
    return fallbackIcon || Wallet;
  }

  function cleanArabicWord(word: string): string {
    return word
      .trim()
      .toLowerCase()
      .replace(/[\u064B-\u0652]/g, "")
      .replace(/[أإآ]/g, "ا")
      .replace(/ة/g, "ه")
      .replace(/ى/g, "ي")
      .replace(/^ال/, "");
  }

  function cleanArabicSentence(text: string): string {
    return text.split(/\s+/).map(cleanArabicWord).filter(Boolean).join('');
  }

  function cleanEnglishText(text: string): string {
    let str = text.trim().toLowerCase();
    if (str.endsWith('ies')) str = str.slice(0, -3) + 'y';
    else if (str.endsWith('es')) str = str.slice(0, -2);
    else if (str.endsWith('s') && !str.endsWith('ss')) str = str.slice(0, -1);
    return str;
  }

  function isOtherCategory(name: string): boolean {
    if (!name) return false;
    const ar = cleanArabicSentence(name);
    const en = cleanEnglishText(name);
    return ar === 'اخري' || en === 'other' || en === 'misc';
  }

  const SYNONYM_MAP: Record<string, string[]> = {
    transport: ['مواصلات', 'تكسي', 'سيارة', 'بنزين', 'وقود', 'اوبر', 'كريم', 'transport', 'transportation', 'transit', 'taxi', 'ride', 'car', 'fuel'],
    food: ['أكل', 'طعام', 'مطعم', 'مطاعم', 'وجبة', 'وجبات', 'قهوة', 'كافيه', 'مشروبات', 'food', 'drink', 'drinks', 'restaurant', 'dining', 'cafe', 'coffee'],
    gifts: ['هدية', 'هدايا', 'عطايا', 'gifts', 'gift', 'present', 'presents'],
    salary: ['راتب', 'رواتب', 'دخل', 'أجر', 'salary', 'wage', 'paycheck', 'income'],
    freelance: ['عمل حر', 'مشروع', 'فريلانس', 'freelance', 'freelancing', 'side hustle', 'gig'],
    investments: ['استثمار', 'أسهم', 'أرباح', 'تداول', 'invest', 'investment', 'investments', 'stocks'],
    shopping: ['تسوق', 'شراء', 'ملابس', 'مشتريات', 'سوبرماركت', 'shopping', 'store', 'clothes'],
    bills: ['فواتير', 'فاتورة', 'كهرباء', 'ماء', 'انترنت', 'اتصالات', 'bill', 'bills', 'utilities', 'internet'],
    housing: ['سكن', 'إيجار', 'بيت', 'housing', 'rent', 'home'],
    health: ['صحة', 'علاج', 'صيدلية', 'مستشفى', 'طبيب', 'health', 'medical', 'pharmacy', 'hospital'],
    entertainment: ['ترفيه', 'ألعاب', 'سينما', 'رحلة', 'entertainment', 'games', 'fun', 'movies']
  };

  function getCanonicalCategoryKey(rawName: string): string {
    if (!rawName) return 'uncategorized';
    const cleanedAr = cleanArabicSentence(rawName);
    const cleanedEn = cleanEnglishText(rawName);
    for (const [key, synonyms] of Object.entries(SYNONYM_MAP)) {
      for (const syn of synonyms) {
        const synAr = cleanArabicSentence(syn);
        const synEn = cleanEnglishText(syn);
        if (cleanedAr === synAr || cleanedEn === synEn || cleanedAr.includes(synAr) || synAr.includes(cleanedAr)) {
          return `cat_${key}`;
        }
      }
    }
    return `custom_${cleanedAr}_${cleanedEn}`;
  }

  let filteredCategories = $derived.by(() => {
    const list = categories.filter((c) => (!c.type || c.type === formType) && !isOtherCategory(c.name));
    const seenKeys = new Set<string>();
    const uniqueList: CategoryObject[] = [];
    for (const cat of list) {
      const canonicalKey = getCanonicalCategoryKey(cat.name);
      if (!seenKeys.has(canonicalKey)) {
        seenKeys.add(canonicalKey);
        uniqueList.push(cat);
      }
    }
    const targetDefaultKey = formType === 'expense' ? 'cat_food' : 'cat_salary';
    const defaultIndex = uniqueList.findIndex((c) => getCanonicalCategoryKey(c.name) === targetDefaultKey);
    if (defaultIndex > 0) {
      const matched = uniqueList[defaultIndex];
      return [matched, ...uniqueList.filter((_, idx) => idx !== defaultIndex)];
    }
    return uniqueList;
  });

  let selectedColor = $derived.by(() => {
    const cat = filteredCategories.find((c) => c.id === formCategoryId);
    if (!cat) return formType === 'income' ? '#10b981' : '#f43f5e';
    const meta = resolveCategoryMeta(cat.name ?? '');
    return cat.color || meta.color || '#10b981';
  });

  function openAddDialog() {
    formAmount = '';
    formType = 'expense';
    formDescription = '';
    formDate = localToday();
    errorMessage = null;
    const targetDefaultKey = 'cat_food';
    const found = categories.find((c) => getCanonicalCategoryKey(c.name) === targetDefaultKey);
    formCategoryId = found ? found.id : (filteredCategories[0]?.id ?? null);
    isDialogOpen = true;
  }

  function handleTypeChange(newType: 'income' | 'expense') {
    if (formType === newType) return;
    formType = newType;
    const targetDefaultKey = newType === 'expense' ? 'cat_food' : 'cat_salary';
    const list = categories.filter((c) => !c.type || c.type === newType);
    const found = list.find((c) => getCanonicalCategoryKey(c.name) === targetDefaultKey);
    if (found) {
      formCategoryId = found.id;
    } else if (list.length > 0) {
      formCategoryId = list[0].id;
    }
  }

  function triggerHapticFeedback() {
    if (typeof window !== 'undefined' && 'navigator' in window && 'vibrate' in navigator) {
      try {
        navigator.vibrate([20, 40, 20]);
      } catch {}
    }
  }

  const ARABIC_TEXT_NUMBERS: Record<string, number> = {
    واحد: 1, اثنين: 2, ثلاثة: 3, اربعة: 4, خمسة: 5, ستة: 6, سبعة: 7, ثمانية: 8, تسعة: 9, عشرة: 10,
    عشرين: 20, ثلاثين: 30, اربعين: 40, خمسين: 50, ستين: 60, سبعين: 70, ثمانين: 80, تسعين: 90, مئة: 100, مائة: 100, مئتين: 200, الف: 1000
  };

  function parseAmountFromText(text: string): string | null {
    const arabicDigits = '٠١٢٣٤٥٦٧٨٩';
    const normalized = text.replace(/[٠-٩]/g, (d) => arabicDigits.indexOf(d).toString());
    const match = normalized.match(/\d+(\.\d+)?/);
    if (match) return match[0];
    const words = text.split(/\s+/).map(cleanArabicWord);
    for (const word of words) {
      if (ARABIC_TEXT_NUMBERS[word]) {
        return ARABIC_TEXT_NUMBERS[word].toString();
      }
    }
    return null;
  }

  // عرف متغير عام برا الدالة عشان نحتفظ بنسخة التعرف النشطة ونقفلها لو كانت شغالة
let activeRecognition: any = null;

function startVoiceRecognition() {
    if (typeof window === 'undefined') return;
    const SpeechRecognition = (window as any).SpeechRecognition || (window as any).webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        toast.error(tr('voice.not_supported', 'التعرف الصوتي غير مدعوم في هذا المتصفح', 'Voice recognition not supported'));
        return;
    }
    
    // إذا كان فيه جلسة صوتية شغالـة، نوقفها أول عشان ما يتداخلون ويطلعون إشعارات مكررة
    if (activeRecognition) {
        try {
            activeRecognition.stop();
        } catch (e) {}
    }

    const recognition = new SpeechRecognition();
    activeRecognition = recognition; // حفظ المرجع
    
    recognition.lang = currentLang === 'ar' ? 'ar-SA' : 'en-US';
    recognition.interimResults = false;
    let voiceErrorNotified = false;
    
    function notifyVoiceError() {
        if (voiceErrorNotified) return;
        voiceErrorNotified = true;
        isListening = false;
        // نضمن إن الإشعار يظهر مرة واحدة فقط بدون تكرار
        toast.error(tr('voice.error', 'لم نتمكن من معالجة الصوت، حاول مرة أخرى', 'Could not process audio, try again'));
    }
    
    recognition.onstart = () => {
        isListening = true;
        triggerHapticFeedback();
    };
    
    recognition.onend = () => {
        isListening = false;
        if (activeRecognition === recognition) {
            activeRecognition = null;
        }
    };
    
    recognition.onerror = (event: any) => {
        // نتجاهل خطأ 'aborted' لأنه طبيعي لو وقفنا الجلسة يدوياً
        if (event && event.error === 'aborted') return;
        notifyVoiceError();
    };
    
    recognition.onresult = (event: any) => {
        const transcript = event.results[0][0].transcript;
        if (!transcript) return;
        
        openAddDialog();
        const extractedAmount = parseAmountFromText(transcript);
        if (extractedAmount) {
            formAmount = extractedAmount;
        }
        
        const wordsInTranscript = cleanArabicSentence(transcript).split(' ');
        let matchedCategory = categories.find((cat) => {
            const catCleanName = cleanArabicSentence(cat.name);
            const canonicalKey = getCanonicalCategoryKey(cat.name).replace('cat_', '');
            const synonyms = (SYNONYM_MAP[canonicalKey] || []).map((s) => cleanArabicSentence(s));
            return wordsInTranscript.some((word) =>
                word.length > 1 && (
                    catCleanName.includes(word) ||
                    synonyms.some((syn) => syn.includes(word) || word.includes(syn))
                )
            );
        });
        
        if (matchedCategory) {
            formCategoryId = matchedCategory.id;
            const canonicalKey = getCanonicalCategoryKey(matchedCategory.name);
            if (
                matchedCategory.type === 'income' ||
                canonicalKey.includes('salary') ||
                canonicalKey.includes('freelance') ||
                canonicalKey.includes('income')
            ) {
                formType = 'income';
            } else {
                formType = 'expense';
            }
        }
        
        triggerHapticFeedback();
    };
    
    try {
        recognition.start();
    } catch {
        notifyVoiceError();
    }
}

  function handleSubmit() {
    if (!formAmount || !formCategoryId) {
      errorMessage = tr('transaction.error_required', 'يرجى تحديد المبلغ والفئة أولاً', 'Please select amount and category');
      return;
    }
    errorMessage = null;
    isSubmitting = true;
    router.post(
      '/transactions',
      {
        amount: formAmount,
        type: formType,
        category_id: formCategoryId,
        description: formDescription,
        transaction_date: formDate,
      },
      {
        preserveScroll: true,
        onSuccess: () => {
          triggerHapticFeedback();
          isDialogOpen = false;
          isSubmitting = false;
          // Toast is handled by backend flash session - no duplicate here
        },
        onError: (errors: any) => {
          isSubmitting = false;
          errorMessage = Object.values(errors)[0] as string;
        },
      }
    );
  }

  function handleKeydown(event: KeyboardEvent) {
    if (event.key === 'Escape' && isDialogOpen) {
      isDialogOpen = false;
    }
  }

  const displayedTransactions = $derived(recentTransactions.slice(0, 4));
</script>

<svelte:window onkeydown={handleKeydown} />

<AppHead title={tr('dashboard.title', 'محفظتي', 'My Wallet')} />

<div class="flex flex-1 flex-col gap-5 p-4 pb-36 sm:p-6 max-w-lg mx-auto w-full">
  <!-- الهيدر والعنوان -->
  <div class="flex items-center justify-between gap-2">
    <div>
      <h1 class="text-2xl font-black tracking-tight text-foreground">{tr('dashboard.title', 'محفظتي', 'My Wallet')}</h1>
      <p class="text-xs text-muted-foreground mt-0.5 font-medium">{tr('dashboard.subtitle', 'اعرف كل ريال فين راح، وتطمن على جيبك', 'Manage smartly and keep track of your budget')}</p>
    </div>
    <button
      type="button"
      onclick={toggleLanguage}
      class="flex items-center gap-1.5 px-2.5 h-8 rounded-xl bg-muted/20 hover:bg-muted/50 border border-border/30 backdrop-blur-xs text-muted-foreground hover:text-foreground font-bold text-[11px] transition-all active:scale-95 cursor-pointer shrink-0"
    >
      <span class="tracking-tight">{currentLang === 'ar' ? 'English' : 'العربية'}</span>
      <svg class="size-3.5 shrink-0 opacity-70" viewBox="0 0 28 28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="2" y="2" width="15" height="15" rx="4" />
        <path d="M6 6.5h7M9.5 5v1.5M7 11.5c1.2-.8 2.5-2.2 2.5-4M12 11.5l-1.2-2-2.5-2.5" stroke-width="1.5" />
        <rect x="11" y="11" width="15" height="15" rx="4" />
        <path d="M15.5 22l3-7 3 7M16.5 19.5h4" stroke-width="1.5" />
      </svg>
    </button>
  </div>

  <!-- الأزرار الزمنية -->
  <div class="p-1 rounded-2xl bg-muted/50 border border-border/40 grid grid-cols-3 gap-1">
    {#each periods as p}
      {@const isActive = selectedPeriod === p.id}
      <button
        type="button"
        onclick={() => changePeriod(p.id)}
        class="py-2 rounded-xl text-xs font-bold transition-all duration-200 text-center {isActive ? 'bg-card text-foreground shadow-sm border border-border/50' : 'text-muted-foreground hover:text-foreground'}"
      >
        {p.label}
      </button>
    {/each}
  </div>

  <!-- بطاقة الرصيد والمبالغ -->
  <div class="rounded-3xl bg-gradient-to-br from-[#18181b] to-[#09090b] border border-white/10 p-5 shadow-xl flex flex-col gap-5">
    <div class="flex flex-col gap-1.5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-semibold text-muted-foreground flex items-center gap-1.5">
          <Wallet class="size-3.5 text-primary" />
          {tr('dashboard.net_balance', 'صافي الرصيد', 'Net Balance')}
        </span>
      </div>
      <div class="flex items-baseline gap-1.5 mt-1">
        <span class="text-3xl font-black text-foreground tabular-nums tracking-tight">
          {formatNumber(netBalance)}
        </span>
        <span class="text-base font-bold text-white">{tr('common.currency', '⃁', 'SAR')}</span>
      </div>
    </div>

    <div class="h-px bg-white/10 w-full"></div>

    {#if dailyBudget > 0}
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-1.5">
          <div class="size-2 rounded-full bg-amber-400 shadow-[0_0_6px_#fbbf24]"></div>
          <span class="text-[11px] font-semibold text-white/60">{tr('dashboard.daily_budget', 'الميزانية اليومية المقترحة', 'Suggested Daily Budget')}</span>
        </div>
        <div class="flex items-baseline gap-1">
          <span class="text-sm font-black tabular-nums text-amber-400">{formatNumber(dailyBudget)}</span>
          <span class="text-[11px] font-bold text-white/50">{tr('common.currency', '⃁', 'SAR')}</span>
        </div>
      </div>
      <div class="h-px bg-white/10 w-full"></div>
    {/if}

    <div class="grid grid-cols-2 gap-4">
      <!-- إجمالي الدخل -->
      <div class="flex items-center gap-3">
        <div class="size-9 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
          <ArrowUpRight class="size-4 stroke-[2.5]" />
        </div>
        <div class="flex flex-col">
          <span class="text-[11px] font-semibold text-muted-foreground">{tr('dashboard.total_income', 'إجمالي الدخل', 'Total Income')}</span>
          <div class="flex items-baseline gap-1 mt-0.5">
            <span class="text-lg font-black tabular-nums {totalIncome === 0 ? 'text-muted-foreground' : 'text-emerald-500'}">
              {formatNumber(totalIncome)}
            </span>
            <span class="text-sm font-bold text-white">{tr('common.currency', '⃁', 'SAR')}</span>
          </div>
        </div>
      </div>

      <!-- إجمالي المصاريف -->
      <div class="flex items-center gap-3">
        <div class="size-9 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0">
          <ArrowDownRight class="size-4 stroke-[2.5]" />
        </div>
        <div class="flex flex-col">
          <span class="text-[11px] font-semibold text-muted-foreground">{tr('dashboard.total_expenses', 'إجمالي المصاريف', 'Total Expenses')}</span>
          <div class="flex items-baseline gap-1 mt-0.5">
            <span class="text-lg font-black tabular-nums {totalExpenses === 0 ? 'text-muted-foreground' : 'text-rose-500'}">
              {formatNumber(totalExpenses)}
            </span>
            <span class="text-sm font-bold text-white">{tr('common.currency', '⃁', 'SAR')}</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- المعاملات الأخيرة -->
  <div class="flex flex-col gap-3">
    <div class="flex items-center justify-between px-1">
      <h2 class="text-lg font-black text-foreground">{tr('dashboard.recent_transactions', 'المعاملات الأخيرة', 'Recent Transactions')}</h2>
      <Link
        href="/transactions"
        class="text-xs font-medium text-muted-foreground hover:text-foreground transition-colors flex items-center gap-0.5"
      >
        <span>{tr('dashboard.view_all', 'عرض الكل', 'View All')}</span>
        <ChevronLeft class="size-3.5 rtl:rotate-0 ltr:rotate-180" />
      </Link>
    </div>

    <div class="flex flex-col gap-2">
      {#if displayedTransactions.length === 0}
        <div class="p-8 text-center rounded-2xl bg-card border border-border/40 flex flex-col items-center justify-center gap-2">
          <Receipt class="size-8 text-muted-foreground/50" />
          <p class="text-xs text-muted-foreground font-semibold">{tr('dashboard.no_recent_transactions', 'لا توجد معاملات مسجلة مؤخراً', 'No recent transactions recorded')}</p>
        </div>
      {:else}
        {#each displayedTransactions as item (item.id)}
          {@const catObj = typeof item.category === 'object'
            ? item.category
            : categories.find(c => c.id === item.category_id || String(c.id) === String(item.category) || c.name === item.category)}
          {@const rawName = catObj ? catObj.name : (typeof item.category === 'string' ? item.category : (item.description || ''))}
          {@const meta = resolveCategoryMeta(rawName)}
          {@const displayColor = catObj?.color || item.color || meta.color}
          {@const displayName = (currentLang === 'ar' ? meta.ar : meta.en) || rawName}
          {@const IconComponent = resolveIcon(catObj?.icon || item.icon, meta.icon)}
          {@const isIncome = item.type === 'income'}
          <div class="p-3.5 px-4 rounded-2xl bg-card border border-border/40 hover:border-border/80 transition-all flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
              <div
                class="size-10 rounded-xl flex items-center justify-center shrink-0 border transition-colors"
                style="background-color: {displayColor}20; color: {displayColor}; border-color: {displayColor}35;"
              >
                <IconComponent class="size-5" />
              </div>
              <div class="flex flex-col">
                <span class="text-sm font-bold text-foreground">
                  {displayName}
                </span>
                <span class="text-[11px] text-muted-foreground font-medium mt-0.5">
                  {formatTransactionDate(item.date, currentLang)}
                </span>
              </div>
            </div>
            <div class="flex items-center gap-1 dir-ltr">
              <span class="text-sm font-bold tabular-nums {item.amount === 0 ? 'text-muted-foreground' : (isIncome ? 'text-emerald-500' : 'text-rose-500')}">
                {item.amount === 0 ? '0' : (isIncome ? '+' : '') + formatNumber(Math.abs(item.amount))}
              </span>
              <span class="text-sm font-bold text-white">{tr('common.currency', '⃁', 'SAR')}</span>
            </div>
          </div>
        {/each}
      {/if}
    </div>
  </div>
</div>

<!-- شريط الإضافة الزجاجي والسفلي -->
<div class="fixed bottom-16 inset-x-0 z-40 max-w-lg mx-auto px-4 flex items-center justify-center pointer-events-none">
  <div class="flex items-center gap-2 p-1.5 rounded-full bg-[#121215]/80 border border-white/10 shadow-2xl backdrop-blur-xl pointer-events-auto">
    <!-- زر الإضافة الصوتية -->
    <button
      type="button"
      onclick={startVoiceRecognition}
      title={tr('voice.title', 'إضافة صوتیة', 'Voice Add')}
      class="relative size-11 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center transition-all cursor-pointer active:scale-95 shrink-0 {isListening ? 'border-rose-500 bg-rose-500/20 text-rose-400' : 'text-white/80 hover:text-white'}"
    >
      <Mic class="size-5 {isListening ? 'animate-pulse' : ''}" />
      {#if isListening}
        <span class="absolute top-0.5 right-0.5 flex size-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full size-3 bg-rose-500"></span>
        </span>
      {/if}
    </button>

    <div class="h-5 w-px bg-white/10 my-auto"></div>

    <!-- زر إضافة معاملة -->
    <button
      type="button"
      onclick={openAddDialog}
      class="h-11 px-5 rounded-full bg-primary text-primary-foreground font-bold text-xs shadow-lg hover:bg-primary/90 active:scale-95 transition-all flex items-center gap-2 shrink-0 cursor-pointer"
    >
      <Plus class="size-4 stroke-[2.5]" />
      <span>{tr('dashboard.add_transaction', 'إضافة معاملة', 'Add Transaction')}</span>
    </button>
  </div>
</div>

<!-- شريط التنقل السفلي -->
<div class="fixed bottom-0 inset-x-0 z-40 bg-[#09090b]/90 backdrop-blur-xl border-t border-white/10 px-6 py-2.5 max-w-lg mx-auto flex items-center justify-around">
  <Link href={dashboard()} class="relative flex flex-col items-center gap-1 text-primary transition-all">
    <span class="absolute -top-2 size-1.5 rounded-full bg-primary shadow-[0_0_8px_#3b82f6]"></span>
    <LayoutDashboard class="size-5" />
    <span class="text-[10px] font-bold">{tr('nav.home', 'الرئيسية', 'Home')}</span>
  </Link>
  <Link href="/transactions" class="flex flex-col items-center gap-1 text-muted-foreground hover:text-foreground transition-all">
    <Receipt class="size-5" />
    <span class="text-[10px] font-medium">{tr('nav.transactions', 'المعاملات', 'Transactions')}</span>
  </Link>
</div>

<!-- نافذة إضافة معاملة سريعة -->
<!-- شريط الإضافة الزجاجي والسفلي -->
<div class="fixed bottom-16 inset-x-0 z-40 max-w-lg mx-auto px-4 flex items-center justify-center pointer-events-none">
  <div class="flex items-center gap-2 p-1.5 rounded-full bg-[#121215]/80 border border-white/10 shadow-2xl backdrop-blur-xl pointer-events-auto">
    <!-- زر الإضافة الصوتية -->
    <button
      type="button"
      onclick={startVoiceRecognition}
      title={tr('voice.title', 'إضافة صوتية', 'Voice Add')}
      class="relative size-11 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 flex items-center justify-center transition-all cursor-pointer active:scale-95 shrink-0 {isListening ? 'border-rose-500 bg-rose-500/20 text-rose-400' : 'text-white/80 hover:text-white'}"
    >
      <Mic class="size-5 {isListening ? 'animate-pulse' : ''}" />
      {#if isListening}
        <span class="absolute top-0.5 right-0.5 flex size-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full size-3 bg-rose-500"></span>
        </span>
      {/if}
    </button>

    <div class="h-5 w-px bg-white/10 my-auto"></div>

    <!-- زر إضافة معاملة -->
    <button
      type="button"
      onclick={openAddDialog}
      class="h-11 px-5 rounded-full bg-primary text-primary-foreground font-bold text-xs shadow-lg hover:bg-primary/90 active:scale-95 transition-all flex items-center gap-2 shrink-0 cursor-pointer"
    >
      <Plus class="size-4 stroke-[2.5]" />
      <span>{tr('dashboard.add_transaction', 'إضافة معاملة', 'Add Transaction')}</span>
    </button>
  </div>
</div>

<!-- شريط التنقل السفلي -->
<div class="fixed bottom-0 inset-x-0 z-40 bg-[#09090b]/90 backdrop-blur-xl border-t border-white/10 px-6 py-2.5 max-w-lg mx-auto flex items-center justify-around">
  <Link href={dashboard()} class="relative flex flex-col items-center gap-1 text-primary transition-all">
    <span class="absolute -top-2 size-1.5 rounded-full bg-primary shadow-[0_0_8px_#3b82f6]"></span>
    <LayoutDashboard class="size-5" />
    <span class="text-[10px] font-bold">{tr('nav.home', 'الرئيسية', 'Home')}</span>
  </Link>
  <Link href="/transactions" class="flex flex-col items-center gap-1 text-muted-foreground hover:text-foreground transition-all">
    <Receipt class="size-5" />
    <span class="text-[10px] font-medium">{tr('nav.transactions', 'المعاملات', 'Transactions')}</span>
  </Link>
</div>

<!-- نافذة إضافة معاملة سريعة -->
{#if isDialogOpen}
  <div
    in:fade={{ duration: 150 }}
    out:fade={{ duration: 120 }}
    class="fixed inset-0 z-50 bg-black/80 backdrop-blur-md flex items-center justify-center p-4 overflow-hidden"
    role="dialog"
    aria-modal="true"
    onclick={(e) => e.target === e.currentTarget && (isDialogOpen = false)}
  >
    <div
      in:scale={{ duration: 200, start: 0.96 }}
      out:scale={{ duration: 150, start: 0.96 }}
      class="relative w-full max-w-sm rounded-3xl bg-[#121212] p-5 space-y-4 text-white border border-white/10 transition-all duration-300 overflow-hidden"
      style="
        --accent: {selectedColor};
        box-shadow: 0 25px 50px 12px rgba(0,0,0,0.85), 0 0 30px 10px color-mix(in srgb, var(--accent) 30%, transparent);
      "
    >
      <div
        class="absolute -top-20 left-1/2 -translate-x-1/2 size-56 rounded-full opacity-15 blur-3xl pointer-events-none transition-all duration-300 ease-out"
        style="background: var(--accent);"
      ></div>
      <div
        class="absolute top-0 inset-x-10 h-[1.5px] transition-all duration-300 ease-out opacity-60"
        style="background: linear-gradient(90deg, transparent, var(--accent), transparent);"
      ></div>

      <div class="flex items-center justify-between relative z-10">
        <h3 class="text-lg font-black tracking-tight">{tr('transaction.add_quick_transaction', 'إضافة معاملة سريعة', 'Add Quick Transaction')}</h3>
        <button
          type="button"
          onclick={() => (isDialogOpen = false)}
          class="size-7 rounded-full bg-white/5 flex items-center justify-center text-white/60 hover:text-white transition-colors cursor-pointer"
        >
          <X class="size-4" />
        </button>
      </div>

      {#if errorMessage}
        <div class="rounded-xl bg-rose-500/15 p-2.5 text-xs font-semibold text-rose-400 text-center border border-rose-500/20 relative z-10">
          {errorMessage}
        </div>
      {/if}

      <form class="flex flex-col gap-4 relative z-10" onsubmit={(e) => { e.preventDefault(); handleSubmit(); }}>
        <!-- نوع المعاملة -->
        <div class="relative grid grid-cols-2 p-1 rounded-xl bg-white/5 border border-white/5 select-none">
          <div
            class="absolute top-1 bottom-1 w-[calc(50%-4px)] rounded-lg bg-[#242424] border border-white/10 shadow-md transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] pointer-events-none will-change-transform {currentLang === 'ar' ? 'right-1' : 'left-1'}"
            style="transform: translateX({formType === 'income' ? (currentLang === 'ar' ? '-100%' : '100%') : '0%'});"
          ></div>
          <button
            type="button"
            aria-pressed={formType === 'expense'}
            class="relative z-10 py-2 text-xs font-extrabold transition-colors duration-200 cursor-pointer text-center {formType === 'expense' ? 'text-white' : 'text-white/40 hover:text-white/70'}"
            onclick={() => handleTypeChange('expense')}
          >
            {tr('transaction.expense', 'مصروف', 'Expense')}
          </button>
          <button
            type="button"
            aria-pressed={formType === 'income'}
            class="relative z-10 py-2 text-xs font-extrabold transition-colors duration-200 cursor-pointer text-center {formType === 'income' ? 'text-white' : 'text-white/40 hover:text-white/70'}"
            onclick={() => handleTypeChange('income')}
          >
            {tr('transaction.income', 'دخل', 'Income')}
          </button>
        </div>

        <!-- إدخال المبلغ (محمي من الزوم بـ text-base) -->
        <div class="flex flex-col gap-1.5">
          <label for="tx-amount" class="text-xs font-bold text-white/80">
            {tr('transaction.amount', 'المبلغ', 'Amount')}
          </label>
          <div class="relative flex items-center">
            <input
              id="tx-amount"
              type="number"
              step="0.01"
              min="0.01"
              bind:value={formAmount}
              placeholder={amountPlaceholder}
              required
              onfocus={() => (isAmountFocused = true)}
              onblur={() => (isAmountFocused = false)}
              class="h-11 w-full rounded-xl border border-white/10 bg-[#1a1a1a] ps-3.5 pe-12 text-start font-mono text-base font-bold text-white placeholder:text-white/25 focus:outline-none focus:border-white/30 transition-all focus:ring-2 focus:ring-white/10"
            />

            <!-- أزرار (+50 / -50) -->
            <div
              onmousedown={(e) => e.preventDefault()}
              class="absolute end-1.5 z-20 flex flex-col items-center overflow-hidden rounded-lg transition-all duration-300 ease-out {isAmountFocused ? 'opacity-100 scale-100 pointer-events-auto' : 'opacity-0 scale-90 pointer-events-none'}"
              style="
                background: rgba(24, 24, 27, 0.95);
                backdrop-filter: blur(8px);
                border: 1px solid color-mix(in srgb, var(--accent) 40%, rgba(255,255,255,0.15));
                box-shadow: 0 4px 12px color-mix(in srgb, var(--accent) 25%, transparent);
              "
            >
              <button
                type="button"
                onclick={incrementAmount}
                class="flex h-4.5 w-6 items-center justify-center transition-colors hover:bg-white/10 active:scale-90 cursor-pointer"
                style="color: var(--accent);"
                title="+50"
              >
                <ChevronUp class="size-3.5 stroke-[2.5]" />
              </button>

              <div class="h-[1px] w-full bg-white/10"></div>

              <button
                type="button"
                onclick={decrementAmount}
                class="flex h-4.5 w-6 items-center justify-center transition-colors hover:bg-white/10 active:scale-90 cursor-pointer"
                style="color: var(--accent);"
                title="-50"
              >
                <ChevronDown class="size-3.5 stroke-[2.5]" />
              </button>
            </div>
          </div>
        </div>

        <!-- اختيار الفئة -->
        <div class="flex flex-col gap-1.5">
          <label class="text-xs font-bold text-white/80">{tr('transaction.category', 'الفئة', 'Category')}</label>
          <div class="grid grid-cols-3 gap-2.5 max-h-52 overflow-y-auto p-1 scrollbar-none">
            {#each filteredCategories as cat (cat.id)}
              {@const meta = resolveCategoryMeta(cat.name ?? '')}
              {@const displayColor = cat.color || meta.color}
              {@const displayName = (currentLang === 'ar' ? meta.ar : meta.en) || cat.name}
              {@const CatIcon = resolveIcon(cat.icon, meta.icon)}
              {@const isSelected = formCategoryId === cat.id}
              <button
                type="button"
                aria-pressed={isSelected}
                onclick={() => (formCategoryId = cat.id)}
                class="group relative h-12 px-2.5 text-xs rounded-2xl transition-all duration-200 ease-out text-center truncate cursor-pointer select-none flex items-center justify-center gap-2 border-2 active:scale-95 will-change-transform {isSelected ? 'pulse-border-glow font-black border-2 z-10' : 'font-bold opacity-60 hover:opacity-100'}"
                style="
                  background-color: {displayColor}18;
                  color: {displayColor};
                  border-color: {isSelected ? displayColor : displayColor + '30'};
                  --glow-color: {displayColor};
                "
              >
                <CatIcon
                  class="size-4 shrink-0 transition-transform duration-200 {isSelected ? 'scale-110' : 'group-hover:scale-105'}"
                  style="color: {displayColor};"
                />
                <span class="truncate">{displayName}</span>
              </button>
            {/each}
          </div>
        </div>

        <!-- الوصف (تم تعديله إلى text-base لمنع الزوم) -->
        <div class="flex flex-col gap-1.5">
          <label for="tx-desc" class="text-xs font-bold text-white/80">{tr('transaction.description_optional', 'الوصف (اختياري)', 'Description (Optional)')}</label>
          <input
            id="tx-desc"
            type="text"
            bind:value={formDescription}
            placeholder={tr('transaction.description_placeholder', 'عن ماذا كانت هذه المعاملة؟', 'What is this for?')}
            class="h-10 w-full rounded-xl border border-white/10 bg-[#1a1a1a] px-3 text-base font-medium text-white placeholder:text-white/20 placeholder:text-xs focus:outline-none focus:border-white/30 focus:ring-1 focus:ring-white/20 transition-all"
          />
        </div>

        <!-- أزرار الإجراءات -->
        <div class="flex flex-col gap-2 pt-1">
          <button
            type="submit"
            disabled={isSubmitting || !formAmount || !formCategoryId}
            class="h-11 w-full rounded-xl bg-white hover:bg-white/90 text-black text-xs font-bold shadow-lg shadow-white/10 border border-white/20 disabled:opacity-40 transition-all cursor-pointer active:scale-[0.98] flex items-center justify-center gap-2"
          >
            <span>{isSubmitting ? tr('common.saving', 'جاري الحفظ...', 'Saving...') : tr('common.save', 'حفظ المعاملة', 'Save Transaction')}</span>
          </button>
          <button
            type="button"
            onclick={() => (isDialogOpen = false)}
            class="h-10 w-full rounded-xl bg-white/5 hover:bg-white/10 text-white/70 text-xs font-bold border border-white/5 transition-all cursor-pointer active:scale-[0.98]"
          >
            {tr('common.cancel', 'إلغاء', 'Cancel')}
          </button>
        </div>
      </form>
    </div>
  </div>
{/if}

<style>
  @keyframes glowPulseBorder {
    0%, 100% {
      box-shadow: 0 0 6px var(--glow-color), inset 0 0 3px var(--glow-color);
      opacity: 0.9;
    }
    50% {
      box-shadow: 0 0 12px var(--glow-color), inset 0 0 12px var(--glow-color);
      opacity: 1;
    }
  }

  .pulse-border-glow {
    animation: glowPulseBorder 2s infinite ease-in-out;
  }

  .scrollbar-none::-webkit-scrollbar {
    display: none;
  }

  .scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
</style>